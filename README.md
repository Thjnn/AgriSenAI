<div align="center">

# 🌾 AgriSenAi

**Hồ sơ lô xoài đáng tin — chụp ảnh chuẩn, AI gợi ý, con người xác nhận, QR kiểm chứng**

Nền tảng B2B SaaS giúp điểm thu gom / HTX xoài ở Đồng Tháp chuẩn hoá hồ sơ lô hàng theo cơ chế Human-in-the-loop.

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-v4-38B2AC?logo=tailwindcss&logoColor=white)
![Gemini](https://img.shields.io/badge/AI-Gemini%20Vision-4285F4?logo=google&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-installable-5A0FC8?logo=pwa&logoColor=white)
![Tests](https://img.shields.io/badge/tests-64%20passing-3DDC84)

</div>

---

## 🎯 Vấn đề & Giải pháp

Ở điểm thu gom, thông tin lô xoài nằm rải rác trong sổ tay, tin nhắn Zalo, ảnh trên điện thoại. Người mua B2B phải tới tận nơi xem hàng vì không có hồ sơ nào đáng tin.

**AgriSenAi** chuẩn hoá việc đó thành **5 bước**:

| # | Bước | Ai làm |
|---|---|---|
| 1 | **Tạo lô**: nguồn cung, giống, mục đích bán (ăn xanh / ăn chín), khối lượng, ngày thu hoạch → mã lô duy nhất | Nhân viên điểm thu gom |
| 2 | **Chụp ảnh chuẩn**: khung ngắm + kiểm tra mờ/tối ngay trên máy, không đạt thì chụp lại | Nhân viên |
| 3 | **AI gợi ý** ngoại quan theo 5 tiêu chí: màu sắc, độ chín, bề mặt/khuyết tật, hình dạng, độ đồng đều | Gemini Vision |
| 4 | **Người phụ trách xác nhận**: Đồng ý / Sửa / Bác bỏ từng gợi ý, hoặc nhập thủ công. Mỗi xác nhận là một nhãn dữ liệu | Nhân viên |
| 5 | **Công bố + tem QR**: hồ sơ khoá lại. Người mua quét QR → thấy ảnh, kết luận, **ai xác nhận, lúc nào**, và hồ sơ còn nguyên vẹn không | Người mua B2B |

Ba nguyên tắc: **AI gợi ý, con người quyết định**. **Không có điểm tổng hợp, không có xếp hạng**. **Mỗi lần xác nhận = một nhãn dữ liệu** (tài sản để huấn luyện mô hình riêng ở giai đoạn 2).

---

## ✨ Tính năng

| Tính năng | Đường dẫn |
|---|---|
| Đăng nhập nhân viên (SĐT + mật khẩu), mỗi tổ chức chỉ thấy lô của mình | `/dang-nhap` |
| Danh sách lô, lọc theo trạng thái | `/lo` |
| Tạo lô | `/lo/tao` |
| Chi tiết lô: chụp ảnh, AI gợi ý (chạy nền, trang tự cập nhật), 5 thẻ xác nhận, công bố | `/lo/{mã}` |
| Tem QR 50×30 mm để in dán thùng | `/lo/{mã}/tem` |
| Hồ sơ công khai (không cần đăng nhập), dải xanh/đỏ xác minh toàn vẹn | `/ho-so/{mã}` |
| Tra cứu theo mã lô | `/tra-cuu` |
| Thống kê vận hành (thời gian lập hồ sơ, tỉ lệ AI được chấp nhận) + **xuất dataset** ảnh + nhãn | `/thong-ke` (admin) |
| PWA: cài như app, trang offline | |

---

## 🧠 AI gợi ý hoạt động thế nào

```
Ảnh chụp (đã kiểm tra mờ/tối ở máy)
  → ImageNormalizer      (xoay EXIF, cắt vuông, 768×768 JPEG) + SHA-256
  → PhotoQualityChecker  (Laplacian + độ sáng + kích thước; server kiểm lại, không tin client)
  → FewShotPromptBuilder (6 ảnh mẫu + nhãn mẫu, tối đa 4 ảnh của lô)
  → GeminiVisionClient   (ép JSON schema, retry 429/503)
  → ai_suggestions       (luôn ghi lại, kể cả khi lỗi) → người xác nhận
```

- AI **chỉ nói những gì nhìn thấy**. Khối lượng, độ Brix, giống do người dùng nhập.
- **Vỏ xanh không phải lỗi**: AI xét độ chín theo mục đích bán đã khai.
- Gemini chậm hoặc lỗi thì vẫn **nhập thủ công** được, không bị chặn quy trình.

---

## 🔐 Nhật ký nối chuỗi băm

Mỗi bước (tạo lô, thêm ảnh, AI gợi ý, xác nhận, công bố) ghi một dòng `lot_audit_logs` với
`hash = sha256(hash_trước + payload)`. Bản công bố chứa SHA-256 của từng ảnh và kết luận cuối cùng. Trang QR kiểm tra lại cả ba:

- chuỗi băm còn liền mạch,
- ảnh trên đĩa khớp mã băm,
- kết luận trong DB khớp bản đã công bố.

> Thử: `UPDATE verified_labels SET final_value='{"value":"sach"}' WHERE criterion='surface';` → tải lại trang hồ sơ → dải chuyển đỏ.

---

## 🛠️ Công nghệ

- **Backend:** Laravel 13 (PHP 8.3), MySQL 8
- **Frontend:** Blade + Tailwind CSS v4 + Vite, PWA (service worker), camera `getUserMedia`
- **AI:** Google Gemini Vision (few-shot, structured JSON output)
- **QR:** `simplesoftwareio/simple-qrcode`
- **Kiểm thử:** PHPUnit (SQLite in-memory), Gemini được giả lập bằng `Http::fake`

---

## 🚀 Cài đặt & chạy (local)

Yêu cầu: PHP 8.3+ (ext gd, exif, zip), Composer, Node.js, MySQL.

```bash
git clone https://github.com/Thjnn/AgriSenAI.git
cd AgriSenAI
composer install
npm install

cp .env.example .env
php artisan key:generate
#   Sửa .env: DB_DATABASE=agrisenai, GEMINI_API_KEY=... (https://aistudio.google.com/apikey)

php artisan migrate --seed
php artisan storage:link
npm run build        # hoặc npm run dev khi sửa giao diện
php artisan serve    # → http://127.0.0.1:8000
```

Tài khoản demo (mật khẩu `agrisen123`):

| SĐT | Vai trò | Tổ chức |
|---|---|---|
| `0900000000` | Admin (xem thống kê, xuất dataset) | AgriSenAi |
| `0911111111` | Nhân viên | Vựa xoài Mỹ Xương |
| `0922222222` | Nhân viên | HTX Xoài Cao Lãnh |

> Camera trên điện thoại cần **HTTPS**. Chia sẻ tạm bằng Cloudflare Tunnel: xem `docs/files-ke-hoach/huong-dan-tao-link-tam-thoi.md`.

---

## 🧪 Kiểm thử

```bash
php artisan test                 # 64 test, không gọi mạng
php artisan quality:eval xoai    # đo độ khớp gợi ý AI trên 25 ảnh test (gọi Gemini thật)
```

---

## 📁 Cấu trúc chính

```
app/
├─ Http/Controllers/   Auth, Lot (nhân viên), PublicLot (QR), Stats (thống kê + dataset)
├─ Jobs/AnalyzeLot     Gọi Gemini sau khi trả response
├─ Models/             Lot, LotPhoto, AiSuggestion, VerifiedLabel, AuditLog
└─ Services/
   ├─ LotWorkflow      5 bước: create → addPhoto → analyze → confirm → publish
   ├─ AuditChain       Ghi + kiểm tra chuỗi băm
   └─ Quality/         Chuẩn hoá ảnh, kiểm tra chất lượng, prompt, Gemini
config/agrisenai.php   5 tiêu chí + lựa chọn, loại khuyết tật, ngưỡng ảnh (nguồn sự thật duy nhất)
resources/js/photo-capture.js   Camera + khung ngắm + kiểm tra ảnh phía client
storage/app/reference/xoai/     6 ảnh mẫu few-shot + labels.json
storage/app/testset/xoai/       25 ảnh test + ground_truth.json
```

---

## 🗺️ Lộ trình

- **Đã có (MVP):** trọn 5 bước, HITL theo tiêu chí, audit nối chuỗi băm, tem QR, thống kê, xuất dataset, PWA
- **Tiếp theo:** thí điểm tại một vựa ở Cao Lãnh để lấy số liệu ROI; chuyên gia soát nhãn 6 ảnh mẫu; deploy HTTPS
- **Giai đoạn 2:** RAG tra cứu TCVN 9766:2013 + yêu cầu siêu thị; huấn luyện mô hình riêng từ dataset đã xác nhận

---

## 👤 Tác giả

**Cao Phúc Thịnh** — Đồng Tháp
