<div align="center">

# 🌾 AgriSenAi

**Sàn nông sản Đồng Tháp ứng dụng AI — đánh giá chất lượng qua ảnh, truy xuất nguồn gốc bằng QR**

Kết nối trực tiếp nông dân & hộ OCOP với người mua, minh bạch chất lượng bằng AI, xác thực nguồn gốc bằng mã băm.

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-v4-38B2AC?logo=tailwindcss&logoColor=white)
![Gemini](https://img.shields.io/badge/AI-Gemini%20Vision-4285F4?logo=google&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-installable-5A0FC8?logo=pwa&logoColor=white)
![Tests](https://img.shields.io/badge/tests-79%20passing-3DDC84)

</div>

---

## 🎯 Vấn đề & Giải pháp

Nông dân Đồng Tháp thường **phụ thuộc thương lái, bị ép giá**, **thiếu công cụ đánh giá chất lượng khách quan**, và **thiếu thông tin để chọn thời điểm bán**.

**AgriSenAi** giải quyết bằng một nền tảng web (PWA) nơi nông dân:
1. **Chụp ảnh nông sản → AI chấm chất lượng** theo tiêu chuẩn TCVN 9766:2013.
2. **Đăng bán trên sàn**, sản phẩm gắn nhãn *"AI chứng nhận"*.
3. **Người mua quét QR** để xem nhật ký chất lượng, với **xác minh toàn vẹn bằng SHA-256** (sửa dữ liệu là phát hiện được ngay).

---

## ✨ Tính năng

| Tính năng | Mô tả | Trạng thái |
|---|---|---|
| 🤖 **AI đánh giá chất lượng** | Upload ảnh → Gemini Vision chấm điểm, độ chín, khuyết tật; neo theo TCVN 9766:2013 bằng few-shot + rule engine | ✅ Hoàn chỉnh |
| 🛒 **Sàn thị trường** | Danh sách sản phẩm "AI chứng nhận", lọc/sắp xếp, trang chi tiết, giỏ hàng (session), checkout | ✅ Hoàn chỉnh (thanh toán ở mức mockup) |
| 🔐 **QR truy xuất nguồn gốc** | Trang công khai `/trace/{qr}`: ảnh AI, điểm, nhà vườn, timeline + **dải xác minh SHA-256** | ✅ Hoàn chỉnh |
| 📱 **PWA** | Cài như app, chạy offline (service worker + trang offline), icon maskable | ✅ Hoàn chỉnh |
| 💰 **Khuyến nghị giá bán** | Gợi ý thời điểm & giá bán theo thị trường/thời tiết | 🚧 Kế hoạch |
| 👤 **Tài khoản thật** | Đăng nhập nông dân/người mua/admin (hiện đang guest/mock) | 🚧 Kế hoạch |

---

## 🧠 Module AI đánh giá hoạt động thế nào

AI **chỉ chấm những gì nhìn thấy qua ảnh** (màu sắc, bề mặt, hình dạng, độ đồng đều, độ chín) — khối lượng và độ Brix do nông dân tự nhập, để đảm bảo trung thực.

```
Ảnh nông dân
  → ImageNormalizer   (xoay EXIF, cắt vuông, chuẩn hoá 768×768)
  → FewShotPromptBuilder (ghép 6 ảnh mẫu + nhãn vàng vào prompt)
  → GeminiVisionClient   (gọi Gemini Vision, ép JSON schema)
  → GradeRuleEngine      (đối chiếu TCVN — chỉ được HẠ hạng, không nâng)
  → Kết quả: hạng + điểm + khuyết tật + lý do + lời khuyên
```

- **Few-shot**: loại có bộ ảnh mẫu (`xoài`, `cam`) được chấm chuẩn nhất; loại chưa có dataset vẫn chấm được ở chế độ **zero-shot** (kém chính xác hơn).
- Thêm loại mới: `php artisan reference:build <loại>`.

---

## 🔐 Điểm nhấn: xác minh toàn vẹn QR

Mỗi lần AI chấm, hệ thống lưu **`integrity_hash`** = SHA-256 của chuỗi canonical (mã QR, điểm, độ chín, mã băm ảnh, nhà vườn, thời điểm) + **`image_sha256`** của file ảnh. Trang tra cứu tính lại và so:

- ✅ **Đã xác minh** — dữ liệu chưa bị chỉnh sửa
- ⚠️ **Không khớp** — dữ liệu đã bị sửa sau khi tạo

> Thử: `UPDATE ai_quality_assessments SET quality_score = 1 WHERE id = 1;` → tải lại trang trace → dải chuyển đỏ.

---

## 🛠️ Công nghệ

- **Backend:** Laravel (PHP 8.3), MySQL 8
- **Frontend:** Blade + Tailwind CSS v4 + Vite, PWA (service worker)
- **AI:** Google Gemini Vision (few-shot prompting, structured JSON output)
- **QR:** `simplesoftwareio/simple-qrcode` (SVG, sinh on-the-fly)
- **Kiểm thử:** PHPUnit (SQLite in-memory) — **79 test**

---

## 🚀 Cài đặt & chạy (local)

Yêu cầu: PHP 8.3+, Composer, Node.js, MySQL.

```bash
# 1. Clone & cài phụ thuộc
git clone https://github.com/Thjnn/AgriSenAI.git
cd AgriSenAI
composer install
npm install

# 2. Cấu hình môi trường
cp .env.example .env
php artisan key:generate
#   Mở .env, sửa:
#   - DB_DATABASE=agrisenai  (tạo database này trong MySQL)
#   - GEMINI_API_KEY=...     (lấy tại https://aistudio.google.com/apikey, bắt đầu "AIza...")
#   (GEMINI_MODEL đã có sẵn giá trị mặc định trong .env.example)

# 3. Database + dữ liệu demo + liên kết ảnh
php artisan migrate --seed
php artisan storage:link

# 4. Chạy
npm run dev          # terminal 1 (Vite)
php artisan serve    # terminal 2  → http://127.0.0.1:8000
```

Các trang chính:

| Đường dẫn | |
|---|---|
| `/` | Trang chủ |
| `/danh-gia` | AI đánh giá chất lượng (cần `GEMINI_API_KEY`) |
| `/thi-truong` | Sàn thị trường |
| `/thi-truong/{id}` | Chi tiết sản phẩm + mã QR |
| `/trace/{qr_code}` | Trang truy xuất nguồn gốc (công khai) |

---

## 🧪 Kiểm thử

```bash
php artisan test          # toàn bộ suite (79 test)
php artisan test --filter=TraceabilityServiceTest
php artisan quality:eval  # đánh giá độ chính xác module AI trên tập test
```

---

## 📁 Cấu trúc chính

```
app/
├─ Http/Controllers/     Home, Quality(AI), Marketplace, Cart, Checkout, Trace
├─ Models/               Product, AiQualityAssessment, FarmerProfile, Order, ...
├─ Services/
│  ├─ Quality/           Pipeline chấm điểm AI (Gemini, few-shot, rule engine)
│  └─ TraceabilityService  computeHash + verify (xác minh toàn vẹn)
resources/views/
├─ layouts/app           Khung chung + chrome (nav/drawer/footer/chat)
├─ marketplace/          Sàn, chi tiết, giỏ hàng, checkout
├─ trace/                Trang truy xuất QR
└─ quality/              Trang AI đánh giá
public/                  manifest.json, sw.js, icons (PWA)
storage/app/reference/   Bộ ảnh mẫu few-shot (cam, xoài)
docs/                    Thuyết minh, kế hoạch, nghiên cứu AI
```

---

## 🗺️ Lộ trình

- **MVP (đã có):** AI đánh giá, sàn thị trường, QR truy xuất, PWA
- **Tiếp theo:** khuyến nghị giá bán (thị trường + thời tiết), tài khoản/đăng nhập thật, cầu nối chấm-AI-trực-tiếp → sinh sản phẩm + QR, thanh toán thật
- **Hardening:** HMAC khoá `APP_KEY` cho integrity hash, HTTPS cho PWA production

---

## 🌍 Tác động (SDGs)

- **SDG 1 — Xoá nghèo:** tăng thu nhập nông dân qua bán trực tiếp, minh bạch giá
- **SDG 8 — Việc làm bền vững & tăng trưởng kinh tế**
- **SDG 12 — Tiêu dùng có trách nhiệm:** truy xuất nguồn gốc minh bạch

---

## 👤 Tác giả

**Cao Phúc Thịnh** — Đồng Tháp

> Dự án dự thi — Nền tảng số hoá nông sản Đồng Tháp.
