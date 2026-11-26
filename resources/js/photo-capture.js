/**
 * Chụp ảnh chuẩn hoá cho hồ sơ lô.
 *
 *  - Camera (getUserMedia) + khung ngắm vuông: người dùng đặt quả vào giữa khung,
 *    ảnh gửi đi là ĐÚNG vùng trong khung (tách quả bằng màu đã thất bại ở v1 —
 *    vỏ xoài vàng trùng màu mặt bàn gỗ — nên dùng khung là cách rẻ và đúng).
 *  - Ảnh chọn từ thư viện cũng đi qua cùng bước kiểm tra.
 *  - Kiểm tra mờ / tối / cháy sáng / quá nhỏ ngay trên máy; KHÔNG đạt thì không cho gửi.
 *    Server kiểm lại (PhotoQualityChecker) — không tin client.
 *  - Luôn mã hoá lại thành JPEG: ảnh HEIC của iPhone cũng gửi được.
 *
 * Ngưỡng đọc từ data-* trên form (lấy từ config/agrisenai.php photo_check).
 *
 * Mất mạng: ảnh đã đạt được cất vào IndexedDB trên máy, có mạng lại (sự kiện
 * `online` hoặc lần mở trang sau) thì tự gửi. Không dùng Background Sync vì
 * iOS Safari không hỗ trợ — trang phải đang mở để gửi.
 */

// ── Hàng đợi ảnh chờ gửi (IndexedDB) ─────────────────────────────
const DB = 'agrisen-offline', STORE = 'photos';

function db() {
    return new Promise((resolve, reject) => {
        const r = indexedDB.open(DB, 1);
        r.onupgradeneeded = () => r.result.createObjectStore(STORE, { keyPath: 'id', autoIncrement: true });
        r.onsuccess = () => resolve(r.result);
        r.onerror = () => reject(r.error);
    });
}

async function tx(mode, fn) {
    const d = await db();
    return new Promise((resolve, reject) => {
        const t = d.transaction(STORE, mode);
        const req = fn(t.objectStore(STORE));
        t.oncomplete = () => resolve(req?.result);
        t.onerror = () => reject(t.error);
    });
}

const queueAdd = (item) => tx('readwrite', (s) => s.add(item));
const queueAll = () => tx('readonly', (s) => s.getAll());
const queueDel = (id) => tx('readwrite', (s) => s.delete(id));

// Thành công = redirect (controller trả back()); redirect: 'manual' giữ flash
// message trong session cho lần tải lại trang. Accept JSON để hết phiên / sai
// token trả 401/419 thay vì redirect sang trang đăng nhập (dễ tưởng là đã gửi).
// Trả 'sent' | 'offline' | mã HTTP lỗi.
async function post(action, token, view, blob) {
    const fd = new FormData();
    fd.append('_token', token);
    fd.append('view', view);
    fd.append('anh', blob, 'anh.jpg');
    try {
        const res = await fetch(action, {
            method: 'POST', body: fd, redirect: 'manual', credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });
        return res.type === 'opaqueredirect' ? 'sent' : res.status;
    } catch {
        return 'offline';
    }
}

let flushing = false;
async function flush(token) {
    if (flushing || !navigator.onLine) return;
    flushing = true;
    let sent = 0;
    try {
        for (const item of await queueAll()) {
            // Dùng token của trang hiện tại: token lúc chụp có thể đã hết hạn.
            const r = await post(item.action, token, item.view, item.blob);
            if (r === 'offline' || r === 401 || r === 419 || r >= 500) break; // giữ lại, thử sau
            await queueDel(item.id); // 'sent' hoặc server từ chối hẳn (422, 403, 404)
            if (r === 'sent') sent++;
        }
    } finally {
        flushing = false;
    }
    if (sent) location.reload();
}

export function checkCanvas(canvas, cfg) {
    const side = cfg.sampleSide;
    const s = document.createElement('canvas');
    s.width = s.height = side;
    const ctx = s.getContext('2d', { willReadFrequently: true });
    ctx.drawImage(canvas, 0, 0, side, side);
    const px = ctx.getImageData(0, 0, side, side).data;

    const g = new Float32Array(side * side);
    let sum = 0;
    for (let i = 0; i < g.length; i++) {
        g[i] = 0.299 * px[i * 4] + 0.587 * px[i * 4 + 1] + 0.114 * px[i * 4 + 2];
        sum += g[i];
    }
    const brightness = sum / g.length;

    let n = 0, s1 = 0, s2 = 0;
    for (let y = 1; y < side - 1; y++) {
        for (let x = 1; x < side - 1; x++) {
            const i = y * side + x;
            const lap = g[i - side] + g[i + side] + g[i - 1] + g[i + 1] - 4 * g[i];
            s1 += lap; s2 += lap * lap; n++;
        }
    }
    const blur = s2 / n - (s1 / n) ** 2;

    const issues = [];
    if (Math.min(canvas.width, canvas.height) < cfg.minSide) issues.push('Ảnh quá nhỏ — chụp gần hơn.');
    if (blur < cfg.blurMin) issues.push('Ảnh bị mờ — giữ máy chắc tay và chạm vào quả để lấy nét.');
    if (brightness < cfg.darkMax) issues.push('Ảnh quá tối — ra chỗ sáng hơn.');
    else if (brightness > cfg.brightMin) issues.push('Ảnh bị cháy sáng — tránh nắng gắt chiếu thẳng.');

    return { ok: issues.length === 0, issues, blur, brightness };
}

function init(form) {
    const cfg = {
        blurMin: +form.dataset.blurMin,
        darkMax: +form.dataset.darkMax,
        brightMin: +form.dataset.brightMin,
        minSide: +form.dataset.minSide,
        sampleSide: +form.dataset.sampleSide,
    };
    const fileInput = form.querySelector('input[type=file]');
    const pick = form.querySelector('[data-pick]');
    const video = form.querySelector('video');
    const camBox = form.querySelector('[data-camera]');
    const openBtn = form.querySelector('[data-open-camera]');
    const shotBtn = form.querySelector('[data-shoot]');
    const closeBtn = form.querySelector('[data-close-camera]');
    const preview = form.querySelector('[data-preview]');
    const msg = form.querySelector('[data-msg]');
    const submit = form.querySelector('[type=submit]');
    const queueMsg = form.querySelector('[data-queue]');
    const token = form.querySelector('input[name=_token]').value;
    let stream = null;

    const showQueue = async () => {
        const n = (await queueAll()).filter((i) => i.action === form.action).length;
        queueMsg.hidden = n === 0;
        queueMsg.textContent = `📶 ${n} ảnh đã lưu trên máy, sẽ tự gửi khi có mạng. Đừng đóng trang.`;
    };

    const show = (text, ok) => {
        msg.textContent = text;
        msg.className = 'text-[13px] font-bold ' + (ok ? 'text-brand' : 'text-red-700');
    };

    // Kiểm tra canvas; đạt thì gắn JPEG vào input file và cho gửi.
    const accept = (canvas) => {
        const r = checkCanvas(canvas, cfg);
        preview.src = canvas.toDataURL('image/jpeg', 0.6);
        preview.hidden = false;
        if (!r.ok) {
            submit.disabled = true;
            show('Chưa đạt: ' + r.issues.join(' ') + ' Hãy chụp lại.', false);
            return;
        }
        canvas.toBlob((blob) => {
            const dt = new DataTransfer();
            dt.items.add(new File([blob], 'anh.jpg', { type: 'image/jpeg' }));
            fileInput.files = dt.files;
            submit.disabled = false;
            show('Ảnh đạt chất lượng — bấm “Lưu ảnh”.', true);
        }, 'image/jpeg', 0.92);
    };

    pick.addEventListener('change', () => {
        const file = pick.files[0];
        if (!file) return;
        const img = new Image();
        img.onload = () => {
            const c = document.createElement('canvas');
            c.width = img.naturalWidth;
            c.height = img.naturalHeight;
            c.getContext('2d').drawImage(img, 0, 0);
            URL.revokeObjectURL(img.src);
            accept(c);
        };
        img.onerror = () => show('Không đọc được ảnh này. Thử ảnh khác.', false);
        img.src = URL.createObjectURL(file);
    });

    const stop = () => {
        stream?.getTracks().forEach((t) => t.stop());
        stream = null;
        camBox.hidden = true;
    };

    openBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1920 } },
                audio: false,
            });
        } catch (e) {
            // Camera cần HTTPS (hoặc localhost). Không có thì vẫn chọn ảnh được.
            show('Không mở được camera (' + e.name + '). Dùng nút “Chọn ảnh”.', false);
            return;
        }
        video.srcObject = stream;
        camBox.hidden = false;
    });

    // Cắt đúng ô vuông ở giữa khung hình = vùng khung ngắm vẽ trên màn hình.
    shotBtn.addEventListener('click', () => {
        const w = video.videoWidth, h = video.videoHeight;
        const side = Math.round(Math.min(w, h) * 0.8);
        const c = document.createElement('canvas');
        c.width = c.height = side;
        c.getContext('2d').drawImage(video, (w - side) / 2, (h - side) / 2, side, side, 0, 0, side, side);
        stop();
        accept(c);
    });

    closeBtn.addEventListener('click', stop);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        submit.disabled = true;
        const blob = fileInput.files[0];
        const view = form.elements.view.value;

        const r = await post(form.action, token, view, blob);
        if (r === 'sent') {
            location.reload();
            return;
        }
        if (r !== 'offline') {
            submit.disabled = false;
            show(r === 422 ? 'Ảnh không hợp lệ — chụp lại.' : `Lỗi máy chủ (${r}) — tải lại trang rồi thử lại.`, false);
            return;
        }

        // Mất mạng: cất ảnh lại, cho chụp tiếp.
        await queueAdd({ action: form.action, view, blob, at: Date.now() });
        fileInput.value = '';
        preview.hidden = true;
        show('Mất mạng — ảnh đã lưu trên máy. Cứ chụp tiếp.', true);
        showQueue();
    });

    showQueue();
    flush(token);
    window.addEventListener('online', () => flush(token));
}

document.querySelectorAll('form[data-photo-capture]').forEach(init);
