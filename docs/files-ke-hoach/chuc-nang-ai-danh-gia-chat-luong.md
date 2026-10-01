# Chức năng AI đánh giá chất lượng qua ảnh (camera AI)

Tài liệu này mô tả chức năng ở trang `/danh-gia`: gồm những phần nào, mỗi phần nằm ở file nào, dùng thuật toán và mô hình gì.

Viết theo mã nguồn ngày 01/10/2026. Lịch sử vì sao làm như vậy nằm ở `quy-trinh-xay-module-ai.md` cùng thư mục.

---

## 1. Tóm tắt

Nông dân chụp hoặc tải một ảnh quả xoài. Hệ thống trả về hạng chất lượng (Đặc biệt / Hạng I / Hạng II / Không đạt), điểm 0–10, danh sách khuyết tật, lý do và lời khuyên bán hàng.

Ba điểm cần nắm trước:

- **Không có mô hình tự huấn luyện.** Trong repo không có file `.pt`, `.onnx`, `.tflite` nào. Mô hình là **Gemini** của Google, gọi qua API.
- **Phương pháp là few-shot prompting.** Mỗi lần chấm, hệ thống gửi kèm 6 ảnh mẫu đã có nhãn để Gemini chấm theo đúng thang của mình.
- **Kết quả của AI không phải kết quả cuối.** Một bộ luật viết bằng PHP đối chiếu lại với ngưỡng TCVN và chỉ được phép hạ hạng, không bao giờ nâng.

---

## 2. Luồng xử lý

```
Trình duyệt (resources/views/quality/index.blade.php)
   │  chọn loại nông sản + chụp/tải ảnh
   │  POST /danh-gia  (fetch, FormData: anh, loai)
   ▼
QualityController::assess()
   │  1. kiểm tra đầu vào (ảnh jpg/png/webp, tối đa 8 MB)
   ▼
ImageNormalizer::normalize()
   │  2. xoay theo EXIF → cắt vuông ở giữa → 768×768 → JPEG chất lượng 85
   ▼
QualityAssessor::assess()            ← bộ điều phối
   │
   ├─► FewShotPromptBuilder
   │      3. chỉ dẫn hệ thống (thang hạng, luật chấm)
   │      4. 6 cặp (ảnh mẫu → nhãn vàng)
   │      5. lượt cuối: ảnh của nông dân
   │      6. schema JSON bắt buộc
   │
   ├─► GeminiVisionClient
   │      7. gọi Gemini generateContent, nhận JSON
   │
   └─► GradeRuleEngine
          8. đối chiếu TCVN, chỉ hạ hạng
   ▼
QualityController::toPayload()
   │  9. đổi key sang nhãn tiếng Việt + emoji
   ▼
Trình duyệt hiển thị kết quả (renderResult trong cùng file blade)
```

Mỗi lần chấm là một request tới Gemini, mang theo 7 ảnh (6 ảnh mẫu + 1 ảnh thật).

---

## 3. Danh sách file

### 3.1. Mã chạy mỗi request

| File | Vai trò |
|---|---|
| `routes/web.php` (dòng 14–15) | `GET /danh-gia` mở trang, `POST /danh-gia` chấm ảnh |
| `app/Http/Controllers/QualityController.php` | Nhận ảnh, kiểm tra, gọi bộ chấm, trả JSON cho giao diện |
| `app/Services/Quality/QualityAssessor.php` | Điều phối 4 bước bên dưới |
| `app/Services/Quality/ImageNormalizer.php` | Chuẩn hoá ảnh |
| `app/Services/Quality/FewShotPromptBuilder.php` | Dựng prompt, ghép ảnh mẫu, định nghĩa schema trả về |
| `app/Services/Quality/GeminiVisionClient.php` | Gọi API Gemini |
| `app/Services/Quality/GradeRuleEngine.php` | Luật đối chiếu TCVN |
| `resources/views/quality/index.blade.php` | Giao diện: chọn loại, chụp/tải ảnh, hiển thị kết quả |

### 3.2. Cấu hình

| File | Chứa gì |
|---|---|
| `config/agrisenai.php` | Thang hạng, ngưỡng diện tích, 5 tiêu chí điểm, 13 loại khuyết tật, khuyết tật loại bỏ, danh mục sản phẩm, tham số ảnh |
| `config/services.php` (mục `gemini`) | Đọc key, tên model, timeout từ `.env` |
| `.env` | `GEMINI_API_KEY`, `GEMINI_MODEL`, `GEMINI_TIMEOUT` (không lên git) |
| `config/navigation.php` | Các mục menu trỏ tới `/danh-gia` |

`config/agrisenai.php` là nguồn duy nhất cho thang hạng và ngưỡng. Prompt, schema và rule engine đều đọc từ đây, nên đổi tiêu chuẩn chỉ sửa một chỗ.

### 3.3. Dữ liệu ảnh mẫu và tập test (có trên git)

| Đường dẫn | Chứa gì |
|---|---|
| `storage/app/reference/xoai/01…06_*.jpg` | 6 ảnh mẫu few-shot, đã chuẩn hoá 768×768 |
| `storage/app/reference/xoai/labels.json` | 6 nhãn vàng tương ứng (là nội dung prompt, không phải file train) |
| `storage/app/reference/xoai/source.json` | Ảnh gốc nào, cắt khung nào, nguyên tắc chọn |
| `storage/app/testset/xoai/t01…t25.jpg` | 25 ảnh test đã chuẩn hoá |
| `storage/app/testset/xoai/ground_truth.json` | Hạng đúng của 25 ảnh test |

### 3.4. Công cụ dòng lệnh

| File | Lệnh | Dùng để |
|---|---|---|
| `app/Console/Commands/BuildReferenceSet.php` | `php artisan reference:build xoai` | Dựng lại 6 ảnh mẫu từ ảnh gốc (thêm `--set=testset` để dựng tập test) |
| `app/Console/Commands/EvalTestset.php` | `php artisan quality:eval xoai` | Chấm 25 ảnh test, in tỉ lệ khớp và ma trận nhầm lẫn |
| `scripts/analyze_dataset.py` | `python scripts/analyze_dataset.py --dataset xoai="dataset/Mango_Dataset" --out reports` | Khảo sát dataset ảnh gốc, xuất báo cáo |

### 3.5. Lưu trữ kết quả

| File | Vai trò |
|---|---|
| `database/migrations/2026_07_10_000005_create_ai_quality_assessments_table.php` | Tạo bảng `ai_quality_assessments` |
| `database/migrations/2026_07_21_000001_add_integrity_to_ai_quality_assessments.php` | Thêm cột `image_sha256`, `integrity_hash` |
| `app/Models/AiQualityAssessment.php` | Model của bảng trên |
| `app/Services/TraceabilityService.php` | Tính và kiểm tra hash toàn vẹn |

Xem mục 8 về việc bảng này hiện chưa nối với trang `/danh-gia`.

### 3.6. Không có trên git

| Thứ | Lý do |
|---|---|
| `dataset/` (ảnh gốc 69 ảnh / 71 quả) | Nằm trong `.gitignore` |
| `reports/` (kết quả của `analyze_dataset.py`) | Nằm trong `.gitignore` |
| `.env` (API key) | Nằm trong `.gitignore` |

Người clone repo vẫn chạy được chức năng chấm, vì 6 ảnh mẫu đã commit sẵn. Họ chỉ cần tự điền `GEMINI_API_KEY`. Họ không chạy được `reference:build` và `analyze_dataset.py` vì thiếu `dataset/`.

---

## 4. Mô hình

| Mục | Giá trị |
|---|---|
| Nhà cung cấp | Google Gemini (Generative Language API) |
| Địa chỉ gọi | `https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent` |
| Model đang dùng trên máy dev | `gemini-3.1-flash-lite` (theo `.env`) |
| Model trong `.env.example` | `gemini-3-flash-preview` |
| Model mặc định nếu thiếu biến | `gemini-flash-latest` (`config/services.php`) |
| Xác thực | Header `x-goog-api-key` |
| `temperature` | `0.1` |
| Định dạng trả về | `application/json`, ép theo `responseSchema` |
| Thử lại | Tối đa 4 lần, cách 3 giây, khi gặp lỗi mạng hoặc mã 429 / 500 / 503 |
| Timeout | `GEMINI_TIMEOUT` (máy dev đang để 180 giây, mặc định 60) |

Ba tên model ở ba chỗ khác nhau, nên kết quả đo ở mục 7 chỉ đúng với `gemini-3.1-flash-lite`.

Mô hình chạy trên máy chủ Google. Máy mình không cần GPU, nhưng cần Internet và mỗi lần chấm tốn một lượt gọi API.

---

## 5. Thuật toán từng bước

### 5.1. Chuẩn hoá ảnh — `ImageNormalizer.php`

Dùng thư viện GD của PHP. Bốn bước:

1. **Xoay theo EXIF.** Ảnh điện thoại lưu điểm ảnh thô kèm thẻ Orientation. GD bỏ qua thẻ này nên ảnh sẽ nằm ngang nếu không xoay. Xử lý Orientation 3, 6, 8.
2. **Cắt vuông.** Với ảnh nông dân: cắt hình vuông lớn nhất ở giữa ảnh. Với ảnh mẫu: cắt theo khung `[left, top, side]` ghi trong `source.json`.
3. **Resize về 768×768** bằng `imagecopyresampled`.
4. **Lưu JPEG chất lượng 85**, nền xám `(128,128,128)` cho ảnh có kênh trong suốt.

Ảnh mẫu và ảnh nông dân đi qua cùng một hàm này. Nếu chỉ chuẩn hoá một bên, hai loại ảnh lệch nhau và AI chấm sai.

Hạn chế: quả nằm lệch mép sẽ bị cắt mất một phần. Giao diện chỉ nhắc bằng chữ "đặt quả ngay giữa khung", chưa có khung hướng dẫn vẽ trên màn hình.

### 5.2. Dựng prompt few-shot — `FewShotPromptBuilder.php`

Few-shot nghĩa là đặt sẵn các cặp "ảnh → câu trả lời đúng" trước ảnh cần chấm, để mô hình bắt chước cách chấm. Không có bước huấn luyện nào.

Prompt gồm bốn phần:

**a) Chỉ dẫn hệ thống** (`systemInstruction()`):

- Thang hạng và ngưỡng, sinh tự động từ `config/agrisenai.php`.
- Phân hạng theo tổng diện tích và độ sâu khuyết tật, không theo tên bệnh.
- Khuyết tật ăn vào thịt quả thì `khong_dat`, không xét diện tích.
- Chỉ chấm thứ nhìn thấy được. Cấm đoán khối lượng và độ Brix.
- Vỏ xanh không phải lỗi: độ chín chấm so với mục đích bán (`an_xanh` / `an_chin`).
- Ảnh mờ, tối, không phải nông sản: đặt `usable = false`, không bịa điểm.
- `reasoning` phải nêu lý do theo ngưỡng, không chỉ mô tả.

**b) 6 cặp ví dụ** (`fewShotTurns()`), đọc từ `storage/app/reference/xoai/`:

| Ảnh mẫu | Hạng | Điểm | Mục đích bán | Khuyết tật (% bề mặt) |
|---|---|---|---|---|
| `01_dac_biet_chin.jpg` | dac_biet | 9.3 | an_chin | vet_nhua 2% |
| `02_dac_biet_xanh.jpg` | dac_biet | 9.3 | an_xanh | xuoc_vo 1% |
| `03_hang_1.jpg` | hang_1 | 8.2 | an_chin | nam_nang 5% |
| `04_hang_2.jpg` | hang_2 | 6.5 | an_chin | blackspot 12% |
| `05_hang_2_nang.jpg` | hang_2 | 5.6 | an_chin | nam_nang 18%, blackspot 5% |
| `06_khong_dat.jpg` | khong_dat | 3.4 | an_chin | nam_nang 30% |

Mỗi cặp là một lượt `user` (câu hỏi + ảnh dạng base64) và một lượt `model` (nhãn vàng dạng JSON).

Nguyên tắc chọn 6 ảnh (ghi trong `source.json`): chọn ảnh ở ranh giới giữa các hạng; trộn nền để nền không gắn với hạng; trộn độ chín để mô hình không học "xanh là điểm cao" hay "vàng là điểm thấp".

**c) Lượt cuối** (`userTurn()`): ảnh của nông dân kèm mục đích bán.

**d) Schema trả về** (`responseSchema()`): ép Gemini trả đúng các trường sau.

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `usable` | boolean | Ảnh có chấm được không |
| `detected_product` | string | AI nhận ra đây là gì |
| `grade` | enum | `dac_biet` / `hang_1` / `hang_2` / `khong_dat` |
| `overall_score` | number | Điểm tổng 0–10 |
| `confidence` | number | Độ tin cậy 0–1 |
| `scores` | object | 5 điểm: `color`, `surface`, `shape`, `uniformity`, `ripeness` |
| `defects[]` | array | Mỗi khuyết tật: `type`, `severity`, `area_percent`, `note` |
| `reasoning` | string | Lý do xếp hạng |
| `advice` | string | Lời khuyên bán hàng |

Schema cố ý không có trường khối lượng hay Brix, để mô hình không có chỗ đoán.

Loại nông sản chưa có `labels.json` thì `fewShotTurns()` trả mảng rỗng, tức chấm zero-shot (chỉ có chỉ dẫn hệ thống, không có ảnh mẫu). Hiện danh mục chỉ bật xoài.

### 5.3. Gọi mô hình — `GeminiVisionClient.php`

Gửi chỉ dẫn hệ thống, các lượt hội thoại và schema lên Gemini. Nhận về chuỗi JSON, giải mã thành mảng. File này không quyết định hạng.

Các lỗi được báo rõ: thiếu API key, API trả lỗi (kèm gợi ý đổi model khi gặp 503), Gemini không trả nội dung, JSON hỏng.

### 5.4. Luật đối chiếu — `GradeRuleEngine.php`

Nguyên tắc: **chỉ hạ hạng, không bao giờ nâng**. Lý do: AI chấm dễ dãi thì khách mua phải hàng xấu và sàn mất uy tín; AI chấm khắt khe thì nông dân thiệt ít hơn và còn khiếu nại được.

Ngưỡng lấy từ `config/agrisenai.php`:

| Hạng | Điểm tối thiểu | Tổng khuyết tật tối đa |
|---|---|---|
| `dac_biet` (Hạng đặc biệt) | 9.0 | 2% bề mặt |
| `hang_1` (Hạng I) | 7.0 | 10% |
| `hang_2` (Hạng II) | 5.0 | 25% |
| `khong_dat` (Không đạt) | 0.0 | 100% |

Các luật áp theo thứ tự, luật sau chỉ có hiệu lực nếu cho ra hạng thấp hơn:

1. **Ảnh không dùng được** (`usable = false`): không chấm, đánh dấu cần người xem lại.
2. **Luật 1 — khuyết tật loại bỏ.** Có khuyết tật thuộc `thoi`, `moc`, `nut` thì ép xuống `khong_dat`, bất kể điểm và diện tích.
3. **Luật 2 — diện tích.** Cộng `area_percent` của mọi khuyết tật. Tổng vượt ngưỡng của hạng nào thì hạ xuống hạng có ngưỡng chứa được.
4. **Luật 3 — điểm.** `overall_score` dưới điểm tối thiểu của hạng thì hạ xuống hạng tương ứng.
5. **Cờ xem lại.** `confidence` dưới 0,6 thì đánh dấu `can_nguoi_xem_lai`, hạng giữ nguyên.

Ví dụ: AI chấm `hang_1`, điểm 7.5, khuyết tật 8% + 9% = 17%. Luật 2 thấy 17% vượt ngưỡng 10% của Hạng I nên hạ xuống `hang_2`.

Kết quả kèm mục `rule_engine` ghi lại: hạng AI chấm, hạng cuối, có hạ hay không, tổng diện tích, các luật đã áp.

### 5.5. Trả về giao diện — `QualityController::toPayload()`

Đổi key kỹ thuật sang nhãn tiếng Việt (ví dụ `surface` thành "Bề mặt vỏ", `blackspot` thành "Đốm đen ⚫"). Nếu rule engine đã hạ hạng thì thêm `override_note` giải thích.

---

## 6. Giao diện và "camera"

File: `resources/views/quality/index.blade.php`.

- **Chụp ảnh** dùng `<input type="file" accept="image/*" capture="environment">`. Trên điện thoại, thẻ này mở thẳng camera sau. Trên máy tính, nó mở hộp chọn file. Không có luồng video trực tiếp (`getUserMedia`) và không quét liên tục.
- **Có 3 ô ảnh** (Toàn quả, Cận vỏ, Mặt cắt), nhưng JavaScript chỉ gửi **ảnh "Toàn quả"** lên máy chủ. Hai ô còn lại chưa được dùng để chấm.
- **Gửi đi** bằng `fetch` tới `POST /danh-gia`, kèm token CSRF. Hai trường: `anh` và `loai`.
- **Hiển thị** bằng hàm `renderResult()` (có kết quả) hoặc `renderUnusable()` (ảnh không dùng được).
- **Dấu ✓ trên chip sản phẩm** nghĩa là loại đó có `labels.json`, tức chấm few-shot.

Giao diện không gửi mục đích bán, nên máy chủ luôn dùng mặc định `an_chin`. Xoài bán để ăn xanh hiện chưa được chấm theo đúng mục đích.

---

## 7. Độ chính xác đã đo

Số liệu lấy từ `quy-trinh-xay-module-ai.md` mục 9, đo bằng `php artisan quality:eval xoai` trên 25 ảnh test, model `gemini-3.1-flash-lite`. Mình không chạy lại lệnh khi viết tài liệu này.

| Chỉ số | Kết quả |
|---|---|
| Khớp chính xác hạng | 56% (14/25) |
| Khớp hoặc lệch 1 bậc | 96% (24/25) |
| Số lần rule engine hạ hạng | 0 |
| Chấm đúng `khong_dat` | 0/2 |

Cách đọc:

- Với 25 ảnh, sai số khoảng ±10 điểm phần trăm. Chạy lại cùng prompt cho kết quả khác nhau.
- Trên tập test này AI chưa chấm `khong_dat` lần nào. Hai quả loại bỏ đều bị xếp `hang_2`, vì AI ước lượng khuyết tật 20–23%, ngay dưới ngưỡng 25%, nên rule engine không có căn cứ để hạ.
- Bẫy "vỏ xanh là chưa chín" đã được chặn: xoài xanh sạch được nhận là hạng cao.

### Đo bổ sung ngày 01/10/2026

Đo trên 60 ảnh từ bộ MangoFruitDDS (xoài Senegal) và chạy lại 25 ảnh test, vẫn với 6 ảnh mẫu đang dùng:

| Nhóm | Kết quả |
|---|---|
| Quả thối rõ, bắt đúng `khong_dat` | 30/30 |
| Quả mốc đen vết nhỏ, bắt đúng `khong_dat` | 9/10 |
| Quả đốm nhẹ, bị loại thành `khong_dat` | 3/10 |
| Quả lành, bị loại thành `khong_dat` | 0/10 |
| 25 ảnh test, đúng hạng | 14/25 (56%) |
| 25 ảnh test, đúng hoặc lệch 1 bậc | 25/25 |

AI nhận ra quả thối rữa, nhưng vẫn sai ở quả sát ngưỡng (2 quả `khong_dat` của tập test vẫn ra `hang_2`) và chấm đốm đen nhỏ không nhất quán. Thử thêm 2 ảnh thối vào bộ mẫu không cải thiện được gì. Chi tiết và giới hạn của phép đo: `ket-qua-do-xoai-hong-2026-10-01.md`.

---

## 8. Hạn chế hiện tại

| # | Hạn chế | Ở đâu |
|---|---|---|
| 1 | Trang `/danh-gia` **không lưu kết quả vào database**. Bảng `ai_quality_assessments` chỉ có dữ liệu do `MarketplaceDemoSeeder` tạo | `QualityController.php`, `TraceabilityService.php` |
| 2 | Chỉ ảnh đầu tiên được chấm, 2 ô ảnh còn lại bị bỏ | `quality/index.blade.php` |
| 3 | Mục đích bán không gửi từ giao diện, luôn là `an_chin` | `QualityController.php` |
| 4 | Quả hỏng sát ngưỡng (thán thư nặng) vẫn bị xếp `hang_2`, 0/2; đốm đen nhỏ chấm không nhất quán. Quả thối rõ thì bắt được | `storage/app/reference/xoai/` |
| 5 | Nhãn vàng do AI đề xuất, chưa có chuyên gia nông nghiệp duyệt | `labels.json`, `ground_truth.json` |
| 6 | Ngưỡng 10% và 25% lấy theo thông lệ, chưa đối chiếu bản gốc TCVN 9766:2013 | `config/agrisenai.php` |
| 7 | Cắt vuông ở giữa có thể cắt mất quả nằm lệch | `ImageNormalizer.php` |
| 8 | Chưa có test tự động cho module này | `tests/` |
| 9 | Cột `quality_score` trong database là thang 0–100, còn AI trả thang 0–10 | migration `000005` |

---

## 9. Cách chạy thử

1. Điền key vào `.env`:

   ```
   GEMINI_API_KEY=...
   GEMINI_MODEL=gemini-3.1-flash-lite
   ```

   Lấy key tại `https://aistudio.google.com/apikey`.

2. Mở `http://agrisenai.test/danh-gia`, chọn Xoài, tải ảnh "Toàn quả", bấm đánh giá.

3. Đo trên tập test (tốn 25 lượt gọi API):

   ```powershell
   php artisan quality:eval xoai
   php artisan quality:eval xoai --limit=5
   ```

## 10. Muốn sửa gì thì sửa ở đâu

| Muốn | Sửa file |
|---|---|
| Đổi ngưỡng điểm / diện tích của hạng | `config/agrisenai.php` → `grades` |
| Thêm loại khuyết tật | `config/agrisenai.php` → `defect_types`, và `DEFECT_META` trong `QualityController.php` |
| Đổi khuyết tật nào bị loại thẳng | `config/agrisenai.php` → `defects_loai_bo` |
| Đổi model Gemini | `.env` → `GEMINI_MODEL` |
| Đổi lời chỉ dẫn cho AI | `FewShotPromptBuilder.php` → `systemInstruction()` |
| Đổi ảnh mẫu hoặc nhãn | `storage/app/reference/xoai/` (`source.json`, `labels.json`), rồi `php artisan reference:build xoai` |
| Thêm loại nông sản | Thêm dòng vào `san_pham` trong `config/agrisenai.php`; muốn few-shot thì thêm thư mục `storage/app/reference/<key>/` |
| Đổi kích thước ảnh | `config/agrisenai.php` → `image`, rồi dựng lại ảnh mẫu và tập test |
| Đổi giao diện kết quả | `resources/views/quality/index.blade.php` → `renderResult()` |

## 11. Tài liệu liên quan

| File | Nội dung |
|---|---|
| `quy-trinh-xay-module-ai.md` | Quá trình xây module, kết quả đo, hạn chế |
| `nghien-cuu-few-shot-gemini.md` | Nghiên cứu few-shot và cách dùng Gemini |
| `danh-gia-dataset.md` | Đánh giá dataset ảnh |
