# Kế hoạch triển khai AgriSenAi (12/07 → 31/07/2026)

Từ demo UI → ứng dụng Laravel thật (CSDL + API + module AI đánh giá).
Lập ngày: 12/07/2026 · Deadline nộp: **31/07/2026 (Thứ 6)** · Còn **~19 ngày**.

---

## 0. Nguyên tắc chỉ đạo

1. **Mục tiêu là MVP thi đấu, không phải sản phẩm hoàn chỉnh.** Demo mượt 1 luồng lõi ăn điểm hơn 10 luồng dở dang.
2. **Đường găng (critical path) = bộ ảnh mẫu Gemini + module AI.** Bắt đầu việc "ngoài đời" NGAY hôm nay vì nó block mọi thứ.
3. **AI đánh giá phải chạy thật sớm** (mục tiêu: xong bản chạy được trước 22/07) để còn dư thời gian tinh chỉnh + đo chỉ số.
4. **Đã xong nên không đụng lại:** 13 migration (schema) + 6 trang UI demo. Chỉ "đổ" UI vào Blade, không vẽ lại.
5. Mỗi tối: commit + ghi 1 dòng nhật ký tiến độ. Trễ 1 ngày → xem ngay mục 5 (cắt scope).

---

## 1. Phạm vi MVP (MoSCoW)

### 🟢 MUST — bắt buộc có để demo & thuyết trình
- [ ] Đăng nhập / đăng ký theo **vai trò** (Nông dân · Người mua) bằng SĐT
- [ ] Nông dân đăng 1 sản phẩm (kèm ảnh)
- [ ] **AI đánh giá chất lượng chạy thật với Gemini** (luồng ngôi sao)
- [ ] Khuyến nghị giá bán (thời tiết + giá tham chiếu + gợi ý)
- [ ] Sàn thị trường: danh sách sản phẩm + bảng giá (đọc từ DB)

### 🟡 SHOULD — có thì tốt, làm nếu kịp
- [ ] Giỏ hàng + tạo đơn hàng (mức cơ bản)
- [ ] Trang chi tiết sản phẩm
- [ ] QR truy xuất nguồn gốc (chỉ cần trang xem, chưa cần in QR)

### ⚪ COULD — ưu tiên thấp
- [ ] Đánh giá/review, thông báo, chatbot nối API thật

### 🔴 WON'T (giai đoạn thi) — để lần sau
- Thanh toán online, fine-tuning model, explicit caching, app mobile native

---

## 2. ⚠️ Critical path — làm song song NGAY (không phải việc code)

Đây là việc ngoài đời, tốn thời gian thực tế và **block module AI**. Đừng để tới tuần cuối.

- [ ] **12–14/07:** Mua 20–30 trái xoài đủ loại chất lượng (từ đẹp → hỏng). Chi phí ~200k.
- [ ] **12–14/07:** Chụp trên nền giấy trắng A3 + vài ảnh nền vườn thật (768×768, JPEG q85).
- [ ] **13–15/07:** Nhờ **1 thương lái / cán bộ khuyến nông** phân hạng giúp → nhãn vàng (ground truth). **Chụp ảnh lúc tham vấn làm bằng chứng cho thuyết minh.**
- [ ] Chia: **6 ảnh** làm reference set (phủ biên: Đặc biệt → ranh giới → Hạng II → thối/mốc), phần còn lại làm **test set 20–25 ảnh**.
- [ ] Tải **TCVN 9766:2013** đầy đủ → lấy đúng bảng khối lượng nhóm A/B/C.
- [ ] Lấy **GEMINI_API_KEY** (Google AI Studio), kiểm tra free tier tại ai.google.dev/gemini-api/docs/pricing.

> Chi tiết cách làm: xem `nghien-cuu-few-shot-gemini.md` mục 4.

---

## 3. Lịch chi tiết theo ngày

Giả định: ~5–6 giờ code tập trung/ngày. Ngày có 🔴 = chạm đường găng, ưu tiên tuyệt đối.

### 🏁 SPRINT 0 — Khởi động & nền tảng (12–14/07, CN–T3)

**CN 12/07** — Chuẩn bị
- [ ] 🔴 Đi mua xoài, bắt đầu chụp ảnh (xem mục 2)
- [ ] Tạo `.env`, cấu hình DB (MySQL/Laragon), chạy `php artisan migrate` — kiểm tra 13 bảng lên OK
- [ ] Cài Laravel Breeze (`composer require laravel/breeze` → `blade` stack) làm khung auth

**T2 13/07** — Auth + vai trò
- [ ] 🔴 Liên hệ thương lái/cán bộ hẹn phân hạng
- [ ] Sửa auth: đăng nhập/đăng ký bằng **SĐT** (không phải email); thêm cột `role` (farmer/buyer) vào users
- [ ] Middleware phân quyền + redirect theo vai trò
- [ ] Đổ UI `agrisenai-dang-nhap.html` + `dang-ky.html` vào Blade

**T3 14/07** — Model & layout
- [ ] Sinh 13 Eloquent Model + khai báo quan hệ (hasMany/belongsTo)
- [ ] Seeder: categories, market_price_reference, vài product mẫu, 1 user farmer + 1 buyer
- [ ] Tách nav/drawer/footer thành **Blade component dùng chung** (`x-layout`)

**Mốc cuối sprint 0 (DoD):** đăng nhập được theo vai trò, migrate + seed sạch, layout chung dùng lại được.

---

### 🤖 SPRINT 1 — Module AI đánh giá (15–21/07, T4–T2)

Đây là tuần quan trọng nhất. Mục tiêu: **AI chạy thật end-to-end.**

**T4 15/07** — Kết nối Gemini
- [ ] 🔴 Hoàn tất bộ ảnh mẫu + viết `storage/app/reference/xoai/labels.json` (6 ảnh)
- [ ] `config/agrisenai.php` + gọi thử Gemini bằng **Tinker** (1 ảnh → nhận JSON)

**T5 16/07** — Service
- [ ] Dựng `QualityAssessmentService` theo tài liệu mục 9 (few-shot: ảnh mẫu đầu prompt, ảnh thật cuối)
- [ ] `responseSchema` (mục 6) + generationConfig (temperature 0.1 nếu 2.5 Flash)

**T6 17/07** — Hậu xử lý + lưu DB
- [ ] Hàm `postProcess`: áp luật TCVN (hệ thống hạ hạng, không nâng), gộp số cân đo
- [ ] Controller `assess()` → lưu bảng `ai_quality_assessments`
- [ ] Log token (kiểm tra implicit cache có trúng không)

**T7 18/07** — Nối UI
- [ ] Nối `agrisenai-ai-danh-gia.html` vào Blade thật: upload ảnh → gọi Service → render kết quả thật
- [ ] Xử lý `usable:false`, `confidence<0.6`, ảnh nặng (resize trước khi gửi)

**CN 19/07** — Buffer / test
- [ ] Test luồng AI với 5–7 ảnh thật, sửa lỗi lặt vặt. (Ngày nhẹ)

**T2 20/07** — Đăng sản phẩm (nông dân)
- [ ] Form đăng sản phẩm + upload ảnh (`products`, `product_images`)
- [ ] Gắn kết quả AI vào sản phẩm khi đăng

**T3 21/07** — Đo chỉ số (mục 10 tài liệu)
- [ ] Chạy test set 20–25 ảnh → tính **độ chính xác hạng, sai lệch kề, độ ổn định (σ)**
- [ ] Chạy **zero-shot vs few-shot** → lưu bảng so sánh cho slide

**Mốc cuối sprint 1 (DoD):** chụp ảnh xoài thật → AI phân hạng + lưu DB + hiện kết quả. Có số liệu chứng minh.

---

### 🛒 SPRINT 2 — Khuyến nghị giá + Sàn thị trường (22–28/07, T4–T2)

**T4 22/07** — Thời tiết
- [ ] Tích hợp API thời tiết (Open-Meteo miễn phí) + cache vào `weather_data_cache`

**T5 23/07** — Logic khuyến nghị
- [ ] Gộp thời tiết + `market_price_reference` → sinh `price_recommendations`
- [ ] Nối `agrisenai-khuyen-nghi.html` vào dữ liệu thật

**T6 24/07** — Sàn thị trường
- [ ] Danh sách sản phẩm (lọc theo loại) + bảng giá hôm nay đọc từ DB
- [ ] Nối `agrisenai-thi-truong.html`

**T7 25/07** — Trang chủ + chi tiết SP
- [ ] Đổ dữ liệu thật vào `agrisenai-home.html`
- [ ] Trang chi tiết sản phẩm (kèm điểm AI + ảnh)

**CN 26/07** — Buffer (ngày nghỉ nhẹ / bù việc trễ)

**T2 27/07** — Đơn hàng (SHOULD)
- [ ] Giỏ hàng + tạo đơn cơ bản (`orders`, `order_items`) — nếu kịp
- [ ] Trang QR truy xuất (xem `traceability_logs`) — mức tối thiểu

**T3 28/07** — Hoàn thiện & fallback
- [ ] Validate, xử lý lỗi, thông báo flash
- [ ] **Cache sẵn kết quả 2–3 ảnh demo** + nút "xem kết quả mẫu" phòng khi mạng/quota hỏng lúc demo (mục 11 tài liệu)

**Mốc cuối sprint 2 (DoD):** đủ luồng MUST + (nếu kịp) SHOULD. App demo được từ đầu tới cuối.

---

### 🎤 SPRINT 3 — Đánh bóng, slide & nộp (29–31/07, T4–T6)

**T4 29/07** — Kiểm thử toàn diện
- [ ] Test cả 2 vai trò từ đầu tới cuối trên máy sạch
- [ ] Sửa bug ưu tiên cao; kiểm tra lại giá + free tier Gemini

**T5 30/07** — Slide & kịch bản demo
- [ ] Slide: đưa số liệu 3 chỉ số + bảng zero/few-shot + ảnh tham vấn chuyên gia
- [ ] Chuẩn bị **câu trả lời ban giám khảo** (mục 13 tài liệu)
- [ ] Tập demo 1 lần, canh giờ

**T6 31/07** — Nộp
- [ ] Buffer cuối, deploy/đóng gói, **nộp bài** ✅

---

## 4. Milestones tóm tắt

| Ngày | Cột mốc |
|---|---|
| 14/07 | Nền tảng xong: auth theo vai trò + models + layout |
| 18/07 | **AI đánh giá chạy thật end-to-end** |
| 21/07 | Có số liệu chứng minh độ chính xác AI |
| 24/07 | Khuyến nghị giá + sàn thị trường chạy dữ liệu thật |
| 28/07 | MVP hoàn chỉnh, có fallback demo |
| 31/07 | Slide xong + **nộp** |

---

## 5. Kế hoạch dự phòng (khi bị trễ)

Cắt theo thứ tự này, **giữ nguyên phần AI đánh giá** vì đó là điểm ăn tiền:

1. Bỏ **QR truy xuất** → chỉ nói bằng slide.
2. Bỏ **giỏ hàng / đơn hàng** → để nút "Liên hệ mua" giả.
3. Bỏ **review, thông báo, chatbot thật** → giữ giao diện demo.
4. Khuyến nghị giá → dùng **giá tham chiếu tĩnh** thay vì API thời tiết realtime.
5. Cùng lắm: sàn thị trường để **dữ liệu seed cứng**, không cần đăng SP động.

> Tuyệt đối **không cắt**: đăng nhập theo vai trò + AI đánh giá chạy thật + số liệu đo được.

---

## 6. Chuẩn bị kỹ thuật cần có sớm

- [ ] `GEMINI_API_KEY` trong `.env` (+ `GEMINI_MODEL=gemini-2.5-flash`)
- [ ] Thư viện xử lý ảnh (Intervention Image) để resize ảnh upload
- [ ] Chọn model đúng: chỉ dùng **Flash / Flash-Lite** (free tier), tránh Pro (mục 7 tài liệu)
- [ ] Bật queue nếu gọi AI async (đã có sẵn trong `composer dev`)

---

## 7. Rủi ro & cách chặn

| Rủi ro | Cách chặn |
|---|---|
| Không kịp chụp/gán nhãn ảnh mẫu | Làm NGAY tuần này; nếu bí, tự phân hạng tạm rồi nhờ chuyên gia xác nhận sau |
| Gemini hết quota/timeout lúc demo | Cache sẵn kết quả demo (mục 3 · T2 28/07) |
| AI bịa số gram/Brix | Đã chặn bằng system instruction + schema nullable; test kỹ (mục 11) |
| Ôm đồm nhiều tính năng | Bám MoSCoW; trễ là cắt theo mục 5 ngay |
| Auth SĐT tốn thời gian | Nếu kẹt, cho phép đăng nhập email tạm — không phải điểm chấm chính |

---

*Kế hoạch này là mục tiêu, điều chỉnh theo thực tế. Ưu tiên số 1 luôn là: AI đánh giá chạy thật + có số liệu.*
