/*!
 * AgriQR — bộ sinh mã QR tối giản cho demo AgriSenAi.
 * Chế độ byte (UTF-8), mức sửa lỗi M, phiên bản 1–10 (tự chọn theo độ dài).
 * Dùng: AgriQR.render(canvasEl, "https://...", { size: 200, margin: 4 });
 */
(function (global) {
    "use strict";

    /* ---------- Bảng thông số theo phiên bản (mức sửa lỗi M) ----------
       [số từ mã sửa lỗi mỗi khối, số khối nhóm 1, số từ mã dữ liệu nhóm 1,
        số khối nhóm 2, số từ mã dữ liệu nhóm 2]                          */
    const ECC_M = {
        1: [10, 1, 16, 0, 0],
        2: [16, 1, 28, 0, 0],
        3: [26, 1, 44, 0, 0],
        4: [18, 2, 32, 0, 0],
        5: [24, 2, 43, 0, 0],
        6: [16, 4, 27, 0, 0],
        7: [18, 4, 31, 0, 0],
        8: [22, 2, 38, 2, 39],
        9: [22, 3, 36, 2, 37],
        10: [26, 4, 43, 1, 44],
    };

    const ALIGN_POS = {
        1: [],
        2: [6, 18],
        3: [6, 22],
        4: [6, 26],
        5: [6, 30],
        6: [6, 34],
        7: [6, 22, 38],
        8: [6, 24, 42],
        9: [6, 26, 46],
        10: [6, 28, 50],
    };

    // Số bit thừa phải chèn sau chuỗi từ mã, theo phiên bản.
    const REMAINDER_BITS = { 1: 0, 2: 7, 3: 7, 4: 7, 5: 7, 6: 7, 7: 0, 8: 0, 9: 0, 10: 0 };

    /* ---------- Số học trên trường Galois GF(256) ---------- */
    const EXP = new Uint8Array(512);
    const LOG = new Uint8Array(256);
    (function initGF() {
        let x = 1;
        for (let i = 0; i < 255; i++) {
            EXP[i] = x;
            LOG[x] = i;
            x <<= 1;
            if (x & 0x100) x ^= 0x11d; // đa thức nguyên thuỷ của QR
        }
        for (let i = 255; i < 512; i++) EXP[i] = EXP[i - 255];
    })();

    const gfMul = (a, b) => (a === 0 || b === 0 ? 0 : EXP[LOG[a] + LOG[b]]);

    function rsGenPoly(degree) {
        let poly = [1];
        for (let i = 0; i < degree; i++) {
            const next = new Array(poly.length + 1).fill(0);
            // Nhân đa thức hiện tại với (x + α^i); hệ số xếp theo bậc giảm dần.
            for (let j = 0; j < poly.length; j++) {
                next[j] ^= poly[j];
                next[j + 1] ^= gfMul(poly[j], EXP[i]);
            }
            poly = next;
        }
        return poly;
    }

    function rsEncode(data, ecLen) {
        const gen = rsGenPoly(ecLen);
        const rem = new Array(ecLen).fill(0);
        for (const byte of data) {
            const factor = byte ^ rem[0];
            rem.shift();
            rem.push(0);
            if (factor !== 0) {
                for (let i = 0; i < ecLen; i++) rem[i] ^= gfMul(gen[i + 1], factor);
            }
        }
        return rem;
    }

    /* ---------- Mã hoá dữ liệu ---------- */
    function utf8Bytes(str) {
        const out = [];
        for (const ch of str) {
            let cp = ch.codePointAt(0);
            if (cp < 0x80) out.push(cp);
            else if (cp < 0x800) out.push(0xc0 | (cp >> 6), 0x80 | (cp & 0x3f));
            else if (cp < 0x10000)
                out.push(0xe0 | (cp >> 12), 0x80 | ((cp >> 6) & 0x3f), 0x80 | (cp & 0x3f));
            else
                out.push(
                    0xf0 | (cp >> 18),
                    0x80 | ((cp >> 12) & 0x3f),
                    0x80 | ((cp >> 6) & 0x3f),
                    0x80 | (cp & 0x3f)
                );
        }
        return out;
    }

    const dataCapacity = (v) => {
        const [, g1, d1, g2, d2] = ECC_M[v];
        return g1 * d1 + g2 * d2;
    };

    function pickVersion(byteLen) {
        for (let v = 1; v <= 10; v++) {
            const cci = v < 10 ? 8 : 16;
            const needBits = 4 + cci + byteLen * 8;
            if (needBits <= dataCapacity(v) * 8) return v;
        }
        throw new Error("AgriQR: chuỗi quá dài (tối đa phiên bản 10)");
    }

    function buildCodewords(bytes, version) {
        const bits = [];
        const push = (val, len) => {
            for (let i = len - 1; i >= 0; i--) bits.push((val >> i) & 1);
        };

        push(0b0100, 4); // chỉ báo chế độ byte
        push(bytes.length, version < 10 ? 8 : 16);
        for (const b of bytes) push(b, 8);

        const capacityBits = dataCapacity(version) * 8;
        for (let i = 0; i < 4 && bits.length < capacityBits; i++) bits.push(0); // kết thúc
        while (bits.length % 8 !== 0) bits.push(0);

        const data = [];
        for (let i = 0; i < bits.length; i += 8) {
            let byte = 0;
            for (let j = 0; j < 8; j++) byte = (byte << 1) | bits[i + j];
            data.push(byte);
        }
        const PADS = [0xec, 0x11];
        for (let i = 0; data.length < dataCapacity(version); i++) data.push(PADS[i % 2]);

        // Chia khối, tính từ mã sửa lỗi, rồi trộn xen kẽ.
        const [ecLen, g1, d1, g2, d2] = ECC_M[version];
        const dataBlocks = [];
        const ecBlocks = [];
        let offset = 0;
        for (let i = 0; i < g1 + g2; i++) {
            const len = i < g1 ? d1 : d2;
            const block = data.slice(offset, offset + len);
            offset += len;
            dataBlocks.push(block);
            ecBlocks.push(rsEncode(block, ecLen));
        }

        const out = [];
        const maxData = Math.max(d1, d2);
        for (let i = 0; i < maxData; i++)
            for (const block of dataBlocks) if (i < block.length) out.push(block[i]);
        for (let i = 0; i < ecLen; i++) for (const block of ecBlocks) out.push(block[i]);

        const finalBits = [];
        for (const byte of out) for (let i = 7; i >= 0; i--) finalBits.push((byte >> i) & 1);
        for (let i = 0; i < REMAINDER_BITS[version]; i++) finalBits.push(0);
        return finalBits;
    }

    /* ---------- Dựng ma trận ---------- */
    function newMatrix(size) {
        const m = [];
        for (let i = 0; i < size; i++) m.push(new Array(size).fill(null)); // null = chưa đặt
        return m;
    }

    function placeFinder(m, row, col) {
        for (let r = -1; r <= 7; r++) {
            for (let c = -1; c <= 7; c++) {
                const rr = row + r;
                const cc = col + c;
                if (rr < 0 || cc < 0 || rr >= m.length || cc >= m.length) continue;
                const inRing = (r >= 0 && r <= 6 && (c === 0 || c === 6)) ||
                    (c >= 0 && c <= 6 && (r === 0 || r === 6));
                const inCore = r >= 2 && r <= 4 && c >= 2 && c <= 4;
                m[rr][cc] = inRing || inCore ? 1 : 0;
            }
        }
    }

    function placeAlignment(m, version) {
        const pos = ALIGN_POS[version];
        const last = m.length - 1;
        for (const r of pos) {
            for (const c of pos) {
                // Bỏ 3 ô trùng với hoạ tiết định vị góc.
                if ((r === 6 && c === 6) || (r === 6 && c === last - 6) || (r === last - 6 && c === 6))
                    continue;
                for (let dr = -2; dr <= 2; dr++)
                    for (let dc = -2; dc <= 2; dc++)
                        m[r + dr][c + dc] =
                            Math.max(Math.abs(dr), Math.abs(dc)) !== 1 ? 1 : 0;
            }
        }
    }

    function placeFunctionPatterns(m, version) {
        const size = m.length;
        placeFinder(m, 0, 0);
        placeFinder(m, 0, size - 7);
        placeFinder(m, size - 7, 0);
        placeAlignment(m, version);

        for (let i = 8; i < size - 8; i++) {
            const bit = i % 2 === 0 ? 1 : 0;
            m[6][i] = bit;
            m[i][6] = bit;
        }
        m[4 * version + 9][8] = 1; // ô tối bắt buộc

        // Giữ chỗ cho thông tin định dạng (đặt 0 tạm, ghi đè sau).
        for (let i = 0; i < 9; i++) {
            if (m[8][i] === null) m[8][i] = 0;
            if (m[i][8] === null) m[i][8] = 0;
        }
        for (let i = size - 8; i < size; i++) {
            if (m[8][i] === null) m[8][i] = 0;
            if (m[i][8] === null) m[i][8] = 0;
        }

        // Giữ chỗ cho thông tin phiên bản (từ phiên bản 7 trở lên).
        if (version >= 7) {
            for (let i = 0; i < 6; i++)
                for (let j = 0; j < 3; j++) {
                    m[size - 11 + j][i] = 0;
                    m[i][size - 11 + j] = 0;
                }
        }
    }

    function placeData(m, bits) {
        const size = m.length;
        let idx = 0;
        let upward = true;
        for (let right = size - 1; right >= 1; right -= 2) {
            if (right === 6) right = 5; // cột 6 là hàng định thời, bỏ qua
            for (let step = 0; step < size; step++) {
                const row = upward ? size - 1 - step : step;
                for (const col of [right, right - 1]) {
                    if (m[row][col] !== null) continue;
                    m[row][col] = idx < bits.length ? bits[idx] : 0;
                    idx++;
                }
            }
            upward = !upward;
        }
    }

    const MASKS = [
        (r, c) => (r + c) % 2 === 0,
        (r) => r % 2 === 0,
        (r, c) => c % 3 === 0,
        (r, c) => (r + c) % 3 === 0,
        (r, c) => (Math.floor(r / 2) + Math.floor(c / 3)) % 2 === 0,
        (r, c) => ((r * c) % 2) + ((r * c) % 3) === 0,
        (r, c) => (((r * c) % 2) + ((r * c) % 3)) % 2 === 0,
        (r, c) => (((r + c) % 2) + ((r * c) % 3)) % 2 === 0,
    ];

    function penalty(m) {
        const size = m.length;
        let score = 0;

        // Quy tắc 1: chuỗi ≥5 ô cùng màu liên tiếp.
        const runScore = (get) => {
            let s = 0;
            for (let a = 0; a < size; a++) {
                let run = 1;
                for (let b = 1; b < size; b++) {
                    if (get(a, b) === get(a, b - 1)) {
                        run++;
                    } else {
                        if (run >= 5) s += 3 + (run - 5);
                        run = 1;
                    }
                }
                if (run >= 5) s += 3 + (run - 5);
            }
            return s;
        };
        score += runScore((r, c) => m[r][c]);
        score += runScore((c, r) => m[r][c]);

        // Quy tắc 2: khối 2x2 cùng màu.
        for (let r = 0; r < size - 1; r++)
            for (let c = 0; c < size - 1; c++) {
                const v = m[r][c];
                if (v === m[r][c + 1] && v === m[r + 1][c] && v === m[r + 1][c + 1]) score += 3;
            }

        // Quy tắc 3: mẫu 1:1:3:1:1 kèm vùng trắng 4 ô.
        const P1 = [1, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0];
        const P2 = [0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1];
        const match = (get, a, b, pat) => {
            for (let i = 0; i < 11; i++) if (get(a, b + i) !== pat[i]) return false;
            return true;
        };
        for (let a = 0; a < size; a++)
            for (let b = 0; b + 11 <= size; b++) {
                if (match((x, y) => m[x][y], a, b, P1)) score += 40;
                if (match((x, y) => m[x][y], a, b, P2)) score += 40;
                if (match((x, y) => m[y][x], a, b, P1)) score += 40;
                if (match((x, y) => m[y][x], a, b, P2)) score += 40;
            }

        // Quy tắc 4: tỉ lệ ô tối lệch khỏi 50%.
        let dark = 0;
        for (let r = 0; r < size; r++) for (let c = 0; c < size; c++) dark += m[r][c];
        const pct = (dark * 100) / (size * size);
        score += Math.floor(Math.abs(pct - 50) / 5) * 10;
        return score;
    }

    function formatBits(mask) {
        // Mức sửa lỗi M = 00, nối 3 bit mặt nạ, thêm BCH(15,5), rồi XOR 0x5412.
        let data = (0b00 << 3) | mask;
        let rem = data << 10;
        for (let i = 14; i >= 10; i--) if ((rem >> i) & 1) rem ^= 0x537 << (i - 10);
        return ((data << 10) | rem) ^ 0x5412;
    }

    function versionBits(version) {
        let rem = version << 12;
        for (let i = 17; i >= 12; i--) if ((rem >> i) & 1) rem ^= 0x1f25 << (i - 12);
        return (version << 12) | rem;
    }

    function applyFormat(m, mask) {
        const size = m.length;
        const bits = formatBits(mask);
        const bit = (i) => (bits >> i) & 1;

        // Bản sao 1: dọc theo cột 8 rồi vòng sang hàng 8.
        for (let i = 0; i <= 5; i++) m[i][8] = bit(i);
        m[7][8] = bit(6);
        m[8][8] = bit(7);
        m[8][7] = bit(8);
        for (let i = 9; i <= 14; i++) m[8][14 - i] = bit(i);

        // Bản sao 2: hàng 8 phía phải và cột 8 phía dưới.
        for (let i = 0; i <= 7; i++) m[8][size - 1 - i] = bit(i);
        for (let i = 8; i <= 14; i++) m[size - 15 + i][8] = bit(i);
    }

    function applyVersion(m, version) {
        if (version < 7) return;
        const size = m.length;
        const bits = versionBits(version);
        for (let i = 0; i < 18; i++) {
            const b = (bits >> i) & 1;
            const r = Math.floor(i / 3);
            const c = i % 3;
            m[size - 11 + c][r] = b;
            m[r][size - 11 + c] = b;
        }
    }

    /* ---------- API công khai ---------- */
    function build(text) {
        const bytes = utf8Bytes(String(text));
        const version = pickVersion(bytes.length);
        const bits = buildCodewords(bytes, version);
        const size = version * 4 + 17;

        const base = newMatrix(size);
        placeFunctionPatterns(base, version);
        const reserved = base.map((row) => row.map((v) => v !== null)); // ô của hoạ tiết chức năng
        placeData(base, bits);

        let best = null;
        let bestScore = Infinity;
        for (let mask = 0; mask < 8; mask++) {
            const cand = base.map((row) => row.slice());
            for (let r = 0; r < size; r++)
                for (let c = 0; c < size; c++)
                    if (!reserved[r][c] && MASKS[mask](r, c)) cand[r][c] ^= 1;
            applyFormat(cand, mask);
            applyVersion(cand, version);
            const s = penalty(cand);
            if (s < bestScore) {
                bestScore = s;
                best = cand;
            }
        }
        return best;
    }

    function render(canvas, text, opts) {
        const o = opts || {};
        const margin = o.margin == null ? 4 : o.margin;
        const dark = o.dark || "#1a1a1a";
        const light = o.light || "#ffffff";
        const matrix = build(text);
        const modules = matrix.length + margin * 2;

        // Làm tròn kích thước ô về số nguyên để các cạnh không bị nhoè.
        const target = o.size || 200;
        const scale = Math.max(1, Math.floor(target / modules));
        const px = modules * scale;
        const dpr = global.devicePixelRatio || 1;

        canvas.width = px * dpr;
        canvas.height = px * dpr;
        canvas.style.width = px + "px";
        canvas.style.height = px + "px";

        const ctx = canvas.getContext("2d");
        ctx.scale(dpr, dpr);
        ctx.fillStyle = light;
        ctx.fillRect(0, 0, px, px);
        ctx.fillStyle = dark;
        for (let r = 0; r < matrix.length; r++)
            for (let c = 0; c < matrix.length; c++)
                if (matrix[r][c])
                    ctx.fillRect((c + margin) * scale, (r + margin) * scale, scale, scale);
        return canvas;
    }

    global.AgriQR = { build, render };
})(typeof window !== "undefined" ? window : globalThis);
