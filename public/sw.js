/* AgriSenAi Service Worker */

const VERSION = "v2.0.0";
const SHELL_CACHE = `agrisenai-shell-${VERSION}`;
const ASSET_CACHE = `agrisenai-assets-${VERSION}`;
const PAGE_CACHE = `agrisenai-pages-${VERSION}`;
const IMG_CACHE = `agrisenai-img-${VERSION}`;

const OFFLINE_URL = "/offline";

/* Chỉ precache những thứ chắc chắn tồn tại và không đổi tên */
const SHELL_ASSETS = [
    OFFLINE_URL,
    "/favicon.ico",
    "/manifest.json",
    "/icons/icon-192.png",
    "/icons/icon-512.png",
];

/* ---------- INSTALL: precache shell ---------- */
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches
            .open(SHELL_CACHE)
            .then((cache) =>
                Promise.all(
                    SHELL_ASSETS.map((url) =>
                        cache
                            .add(new Request(url, { cache: "reload" }))
                            .catch((err) =>
                                console.warn("[SW] Bỏ qua", url, err.message),
                            ),
                    ),
                ),
            )
            .then(() => self.skipWaiting()),
    );
});

/* ---------- ACTIVATE: dọn cache cũ ---------- */
self.addEventListener("activate", (event) => {
    const keep = [SHELL_CACHE, ASSET_CACHE, PAGE_CACHE, IMG_CACHE];
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((k) => !keep.includes(k))
                        .map((k) => caches.delete(k)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

/* ---------- FETCH ---------- */
self.addEventListener("fetch", (event) => {
    const { request } = event;

    // Chỉ xử lý GET cùng origin
    if (request.method !== "GET") return;
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;
    if (isBypass(url)) return;

    // 1. Điều hướng trang → network-first, fallback cache → offline
    if (request.mode === "navigate") {
        event.respondWith(networkFirstPage(request));
        return;
    }

    // 2. Asset build (Vite, có hash) → cache-first
    if (url.pathname.startsWith("/build/")) {
        event.respondWith(cacheFirst(request, ASSET_CACHE));
        return;
    }

    // 3. Ảnh → stale-while-revalidate (giới hạn số lượng)
    if (request.destination === "image") {
        event.respondWith(staleWhileRevalidate(request, IMG_CACHE, 60));
        return;
    }

    // 4. CSS / JS / font khác → stale-while-revalidate
    if (["style", "script", "font"].includes(request.destination)) {
        event.respondWith(staleWhileRevalidate(request, ASSET_CACHE, 80));
        return;
    }
});

/* ---------- Đường dẫn KHÔNG cache (dữ liệu động / nhạy cảm) ----------
   Giữ khớp route thật của app. Thêm vào đây khi có /api, auth, v.v. */
function isBypass(url) {
    const p = url.pathname;
    return (
        p.startsWith("/api/") ||
        p.startsWith("/livewire/") ||
        p.startsWith("/broadcasting/") ||
        // Màn hình nhân viên (đăng nhập, trạng thái lô thay đổi liên tục) không cache
        p.startsWith("/lo") ||
        p.startsWith("/thong-ke") ||
        p.startsWith("/dang-nhap") ||
        // Không cache endpoint sinh mã QR (SVG động) để luôn khớp dữ liệu mới
        /^\/ho-so\/[^/]+\/qr$/.test(p)
    );
}

/* ---------- Chiến lược ---------- */

// Ưu tiên mạng; lỗi thì lấy cache; không có nữa thì trang offline
async function networkFirstPage(request) {
    const cache = await caches.open(PAGE_CACHE);
    try {
        const fresh = await fetch(request);
        if (fresh.ok) {
            cache.put(request, fresh.clone());
            trimCache(PAGE_CACHE, 40);
        }
        return fresh;
    } catch (e) {
        const cached = await cache.match(request);
        if (cached) return cached;
        const offline = await caches.match(OFFLINE_URL);
        return (
            offline ||
            new Response("Không có kết nối mạng", {
                status: 503,
                headers: { "Content-Type": "text/plain; charset=utf-8" },
            })
        );
    }
}

// Có cache thì dùng luôn; không thì tải và lưu
async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    if (cached) return cached;
    try {
        const fresh = await fetch(request);
        if (fresh.ok) cache.put(request, fresh.clone());
        return fresh;
    } catch (e) {
        return Response.error();
    }
}

// Trả cache ngay, đồng thời cập nhật ngầm; offline + chưa cache → lỗi mạng sạch
async function staleWhileRevalidate(request, cacheName, maxEntries) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const network = fetch(request)
        .then((res) => {
            if (res.ok) {
                cache.put(request, res.clone());
                if (maxEntries) trimCache(cacheName, maxEntries);
            }
            return res;
        })
        .catch(() => null);

    return cached || (await network) || Response.error();
}

// Xóa bớt entry cũ nhất khi cache phình (theo thứ tự chèn)
async function trimCache(cacheName, maxEntries) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    if (keys.length <= maxEntries) return;
    for (let i = 0; i < keys.length - maxEntries; i++) {
        await cache.delete(keys[i]);
    }
}

/* ---------- Cho phép trang yêu cầu SW mới kích hoạt ngay ---------- */
self.addEventListener("message", (event) => {
    if (event.data === "SKIP_WAITING") self.skipWaiting();
});
