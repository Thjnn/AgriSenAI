# Few-shot Prompting với Gemini Vision cho AgriSenAi

Tài liệu kỹ thuật — module AI đánh giá chất lượng nông sản
Cập nhật: 12/07/2026

---

## 1. Kết luận quan trọng nhất (đọc trước khi làm)

Có một sự thật kỹ thuật bạn cần biết ngay, vì nó quyết định toàn bộ thiết kế và cũng sẽ là câu hỏi đầu tiên ban giám khảo hỏi:

> **Few-shot bằng hình ảnh không "dạy" model nhìn giỏi hơn. Nó chỉ dạy model chấm điểm nhất quán hơn.**

Gemini vốn đã nhìn ra được "quả xoài này vàng, có đốm đen, hơi méo". Cái nó **không** biết là: *"vàng thế này ở Đồng Tháp thì gọi là loại mấy? đốm đen bao nhiêu thì bị loại?"*. Few-shot lấp đúng khoảng trống đó — nó neo thang điểm, không nâng thị lực.

Hệ quả trực tiếp:

| Chỉ tiêu | Chụp ảnh có suy ra được không? | Xử lý |
|---|---|---|
| Màu sắc, độ đồng đều màu | ✅ Được | AI chấm |
| Vết thâm, nám, xước, sâu bệnh | ✅ Được (ước lượng % diện tích) | AI chấm |
| Hình dạng, méo mó | ✅ Được | AI chấm |
| Độ chín biểu kiến | ⚠️ Ước lượng gián tiếp qua màu | AI chấm, ghi rõ "ước lượng" |
| **Khối lượng (gram)** | ❌ **Không** | Nông dân nhập tay |
| **Độ Brix (độ ngọt)** | ❌ **Không** | Nông dân nhập tay (nếu có) |
| **Độ chắc thịt quả** | ❌ **Không** | Bỏ hoặc nhập tay |

**Đừng để Gemini bịa ra số gram hay độ Brix từ ảnh.** Nó sẽ bịa rất tự tin, và giám khảo sẽ bắt được. Thiết kế đúng là: **ảnh → AI chấm phần nhìn được; form → nông dân nhập phần cân đo được; hệ thống → gộp lại thành điểm cuối.** Đây chính là điểm bạn ghi điểm về sự trung thực kỹ thuật.

---

## 2. Neo vào tiêu chuẩn thật: TCVN 9766:2013

Đây là vũ khí lớn nhất của bạn so với các đội khác. Đừng tự bịa thang điểm — hãy neo vào tiêu chuẩn quốc gia có thật.

**TCVN 9766:2013** (chuyển từ CODEX STAN 184-1993) quy định xoài quả tươi chia **3 hạng**:

### Hạng "Đặc biệt"
<cite index="21-3">Phải có chất lượng cao nhất, đặc trưng cho giống, không được có các khuyết tật, trừ các khuyết tật rất nhẹ không ảnh hưởng đến hình thức bên ngoài, chất lượng và cách trình bày sản phẩm.</cite>

### Hạng I
<cite index="21-4">Chất lượng tốt, cho phép các khuyết tật nhẹ.</cite> Cụ thể: <cite index="21-4">khuyết tật nhẹ trên vỏ do cháy nắng hoặc xước, vết bẩn do nhựa tiết ra, vết thâm — **không lớn hơn 3 cm², 4 cm², 5 cm²** tương ứng với nhóm kích cỡ A, B, C.</cite>

### Hạng II
<cite index="21-1">Không đáp ứng hạng cao hơn nhưng vẫn đạt yêu cầu tối thiểu.</cite> Ngưỡng khuyết tật: <cite index="21-1">**không lớn hơn 5 cm², 6 cm², 7 cm²** tương ứng nhóm kích cỡ A, B, C.</cite>

### Quy định chung cho hạng I và II
<cite index="21-1">Với giống xoài xanh, vỏ quả có thể chuyển vàng do tiếp xúc trực tiếp ánh nắng, nhưng **không vượt quá 40% diện tích bề mặt** và không có dấu hiệu hư hỏng.</cite>

<cite index="21-1">Kích cỡ được xác định theo **khối lượng quả**. Sự khác biệt tối đa cho phép giữa các quả trong cùng bao bì thuộc nhóm A, B, C tương ứng là 75 g, 100 g và 125 g.</cite>

### Nhóm kích cỡ (theo khối lượng)

Bạn cần tra bảng chính thức trong TCVN 9766:2013 để lấy số chính xác. Cấu trúc là 3 nhóm:

| Nhóm | Khoảng khối lượng | Dung sai trong cùng bao bì |
|---|---|---|
| A | (nhóm nhỏ) | ± 75 g |
| B | (nhóm trung) | ± 100 g |
| C | (nhóm lớn) | ± 125 g |

> ⚠️ **Hành động cần làm:** Mua/tải bản TCVN 9766:2013 đầy đủ để lấy đúng bảng khối lượng A/B/C. Trong bài thuyết trình, việc trích dẫn đúng số hiệu tiêu chuẩn + đúng con số là điểm cộng lớn.

### Lưu ý về TCVN 11041

Có nguồn thương mại nêu <cite index="28-1">TCVN 11041:2023 yêu cầu xoài cao cấp đạt Brix ≥ 16°, dư lượng BVTV < 0.01 ppm, trọng lượng 300–500g/trái, màu vàng đều không nám.</cite> **Nhưng TCVN 11041 thực chất là bộ tiêu chuẩn về nông nghiệp hữu cơ**, không phải phân hạng xoài. Nguồn này có thể nhầm lẫn. **Đừng trích dẫn số này trong bài thi mà không kiểm chứng** — hãy dùng TCVN 9766:2013 làm nền tảng chính.

---

## 3. Kiến trúc luồng xử lý

```
┌─────────────────────────────────────────────────────────┐
│  NÔNG DÂN                                               │
│  1. Chọn loại nông sản  →  xoài Cát Hòa Lộc            │
│  2. Chụp 1–3 ảnh        →  toàn quả / cận vỏ / mặt cắt  │
│  3. Nhập số cân được    →  khối lượng TB (g), số trái   │
└───────────────────────┬─────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────────┐
│  LARAVEL BACKEND — dựng prompt                          │
│                                                         │
│  [System Instruction]  ← vai trò + rubric TCVN         │
│  [Ảnh mẫu chuẩn 1] + [Nhãn: Đặc biệt, 9.4đ, vì...]     │
│  [Ảnh mẫu chuẩn 2] + [Nhãn: Hạng I,   7.8đ, vì...]     │
│  [Ảnh mẫu chuẩn 3] + [Nhãn: Hạng II,  5.6đ, vì...]     │
│  [Ảnh mẫu chuẩn 4] + [Nhãn: Loại,     2.1đ, vì...]     │
│  ───────────────────────────────────────────────        │
│  [ẢNH CỦA NÔNG DÂN]  + [Yêu cầu chấm theo rubric trên] │
│                                                         │
│  → responseSchema (JSON bắt buộc)                       │
└───────────────────────┬─────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────────┐
│  GEMINI 2.5 / 3.x FLASH                                 │
│  Trả JSON: {grade, scores{}, defects[], reasoning}      │
└───────────────────────┬─────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────────┐
│  LARAVEL — hậu xử lý                                    │
│  • Validate JSON theo schema                            │
│  • Gộp điểm nhìn (AI) + điểm cân đo (nông dân nhập)     │
│  • Áp trần hạng theo TCVN (vd: khuyết tật > 5cm² →      │
│    không thể là hạng I dù AI chấm cao)                  │
│  • Lưu ai_quality_assessments                           │
│  • Đẩy sang module khuyến nghị giá                      │
└─────────────────────────────────────────────────────────┘
```

Điểm mấu chốt ở bước hậu xử lý: **hệ thống có quyền hạ hạng mà AI không có quyền nâng.** Rule-based override đảm bảo kết quả không bao giờ vi phạm TCVN, kể cả khi model "rộng lượng".

---

## 4. Bộ ảnh mẫu (reference set) — thiết kế thế nào

### Số lượng
**4–6 ảnh mẫu mỗi loại nông sản.** Đừng tham.

- Ít hơn 3: không đủ neo thang điểm
- Nhiều hơn 8: tốn token, tăng latency, và model bắt đầu "nhiễu" giữa các mẫu

### Chọn ảnh nào?
Nguyên tắc: **phủ biên, không phủ giữa.** Chọn ảnh đại diện cho *ranh giới* giữa các hạng, vì đó là chỗ model dễ sai nhất.

| # | Vai trò | Mô tả |
|---|---|---|
| 1 | Neo trần | Xoài Đặc biệt — vàng đều, vỏ sạch, không tì vết |
| 2 | Ranh giới Đặc biệt / I | Xoài đẹp nhưng có 1 vết xước rất nhỏ (~1cm²) |
| 3 | Hạng I điển hình | Vài đốm nhỏ, tổng ~3cm², màu tốt |
| 4 | Ranh giới I / II | Khuyết tật ~5cm², rõ nhưng chưa vào thịt |
| 5 | Hạng II điển hình | Nám nắng rộng, thâm ~6–7cm² |
| 6 | Neo sàn (loại) | Thối, nứt vào thịt, mốc — **phải có mẫu này** |

Mẫu số 6 cực kỳ quan trọng. Không có nó, model sẽ ngại chấm điểm thấp và dồn hết vào 6–8 điểm.

### Chuẩn hoá ảnh mẫu
- **Nền trắng hoặc xám đồng nhất** cho tất cả ảnh mẫu
- Cùng góc chụp, cùng khoảng cách
- Ảnh 768×768px là đủ (Gemini tile ảnh; ảnh to hơn tốn token mà không lợi)
- Nén JPEG quality 85 — giảm dung lượng, không mất chi tiết cần thiết

> 💡 Nếu ảnh nông dân chụp lộn xộn (nền vườn, ánh sáng vàng) mà ảnh mẫu lại nền studio trắng, model có thể bị nhiễu. **Cách chữa:** thêm 1 câu trong prompt — *"Bỏ qua nền và điều kiện ánh sáng, chỉ đánh giá bản thân quả."*

### Nguồn ảnh mẫu
Bạn đang làm 1 mình, không có dataset. Cách thực tế nhất:
1. **Tự chụp** — ra chợ/vườn ở Tiền Giang hoặc Đồng Tháp, mua 15–20 trái xoài đủ chất lượng, chụp trên nền giấy trắng A3. Chi phí ~200k, mất 1 buổi.
2. Nhờ **1 thương lái hoặc cán bộ khuyến nông** phân hạng giúp → đây chính là "nhãn vàng" (ground truth) và cũng là **bằng chứng có tham vấn chuyên gia** để đưa vào thuyết minh.
3. Tuyệt đối **không lấy ảnh từ Google** làm ảnh mẫu chính thức — vi phạm bản quyền và ảnh không đại diện cho xoài Đồng Tháp.

Việc bạn tự chụp bộ ảnh chuẩn + có chuyên gia gán nhãn là **một tài sản của dự án**, nên nêu trong thuyết minh như một đóng góp riêng.

---

## 5. Prompt hoàn chỉnh

### 5.1 System Instruction (cache được — xem mục 7)

```
Bạn là chuyên gia phân loại nông sản theo TCVN 9766:2013, làm việc cho
sàn thương mại điện tử AgriSenAi tại Đồng Tháp.

NHIỆM VỤ
Đánh giá chất lượng quả từ ảnh chụp, phân hạng theo TCVN 9766:2013.

QUY TẮC BẮT BUỘC
1. CHỈ đánh giá những gì nhìn thấy được trong ảnh.
2. TUYỆT ĐỐI KHÔNG suy đoán: khối lượng (gram), độ Brix, độ chắc thịt quả,
   dư lượng thuốc BVTV. Nếu người dùng không cung cấp, để null.
3. Bỏ qua nền ảnh và điều kiện ánh sáng. Chỉ đánh giá bản thân quả.
4. Nếu ảnh mờ, thiếu sáng, hoặc không phải nông sản cần đánh giá:
   đặt "usable": false và giải thích.
5. Ước lượng diện tích khuyết tật theo % bề mặt quả nhìn thấy được,
   KHÔNG đưa ra con số cm² tuyệt đối (vì không biết kích thước thật).

THANG HẠNG THEO TCVN 9766:2013
• "dac_biet"  — Không khuyết tật, trừ khuyết tật rất nhẹ không ảnh hưởng
                hình thức. Màu đặc trưng giống, đồng đều.
• "hang_1"    — Khuyết tật nhẹ (xước, nám nắng, vết nhựa, thâm).
                Ngưỡng: ~3–5 cm² tuỳ nhóm kích cỡ A/B/C.
• "hang_2"    — Khuyết tật rõ hơn nhưng KHÔNG ảnh hưởng thịt quả.
                Ngưỡng: ~5–7 cm² tuỳ nhóm kích cỡ.
• "khong_dat" — Khuyết tật ăn vào thịt quả, thối, mốc, nứt hở,
                hoặc dấu hiệu nấm/vi khuẩn.

LƯU Ý: Với giống xoài xanh, vỏ chuyển vàng do nắng được chấp nhận ở hạng I
và II nếu KHÔNG vượt quá 40% diện tích bề mặt và không có dấu hiệu hư hỏng.

GIỌNG VĂN
Viết nhận xét bằng tiếng Việt, ngắn gọn, dễ hiểu với nông dân.
Không dùng thuật ngữ kỹ thuật khó.
```

### 5.2 Few-shot: ảnh mẫu + nhãn xen kẽ

Theo tài liệu Google: <cite index="4-1">model có thể nhận nhiều input làm ví dụ để hiểu output bạn muốn; việc thêm ví dụ giúp model nhận ra pattern và áp dụng quan hệ giữa ảnh và câu trả lời cho ví dụ mới — đây gọi là few-shot learning.</cite>

Cấu trúc mỗi cặp mẫu (dùng role `user` → `model`):

```
user:  [ẢNH_MẪU_1]
       Phân hạng quả xoài này theo TCVN 9766:2013.

model: {"grade":"dac_biet","overall_score":9.4,
        "scores":{"color":9.6,"surface":9.8,"shape":9.2,"uniformity":9.0},
        "defects":[],
        "reasoning":"Vỏ vàng cam đều từ cuống đến đuôi, đặc trưng giống Cát
                     Hòa Lộc. Bề mặt sạch, không thấy vết xước hay thâm.
                     Hình dạng cân đối, không méo."}

user:  [ẢNH_MẪU_2]
       Phân hạng quả xoài này theo TCVN 9766:2013.

model: {"grade":"hang_1","overall_score":7.8,
        "scores":{"color":8.5,"surface":7.0,"shape":8.2,"uniformity":7.5},
        "defects":[
          {"type":"xuoc_vo","severity":"nhe","area_percent":2,
           "note":"Vết xước dài ~2cm ở phần vai quả, đã khô, không vào thịt"}
        ],
        "reasoning":"Màu vàng tốt nhưng có 1 vết xước nhẹ ở vai quả. Khuyết
                     tật nông, không ảnh hưởng thịt quả nên vẫn đạt hạng I."}
```

...lặp lại cho 4–6 mẫu.

### 5.3 Câu hỏi thật

```
user:  [ẢNH_CỦA_NÔNG_DÂN]
       Phân hạng quả xoài này theo TCVN 9766:2013.

       Thông tin nông dân cung cấp:
       - Giống: Xoài Cát Hòa Lộc
       - Khối lượng trung bình: 420 g/trái  (do nông dân cân)
       - Số trái trong lô: 85
       - Vùng trồng: Cao Lãnh, Đồng Tháp
```

**Lưu ý thứ tự:** Google khuyến nghị <cite index="4-1">với prompt chỉ có 1 ảnh, đặt ảnh **trước** phần text sẽ cho kết quả tốt hơn.</cite> Nhưng <cite index="4-1">với prompt cần ảnh xen kẽ text để có nghĩa, hãy dùng thứ tự tự nhiên nhất.</cite> → Few-shot của bạn thuộc trường hợp thứ 2: xen kẽ ảnh–nhãn–ảnh–nhãn.

---

## 6. Structured Output — bắt buộc dùng

Đừng parse text thô. Google khuyến nghị <cite index="8-1">dùng tính năng structured output của Gemini API khi cần JSON Schema phức tạp, thay vì chỉ mô tả format trong prompt.</cite>

### responseSchema

```json
{
  "type": "object",
  "required": ["usable", "grade", "overall_score", "scores", "defects", "reasoning"],
  "properties": {
    "usable": {
      "type": "boolean",
      "description": "false nếu ảnh mờ/thiếu sáng/không phải nông sản cần đánh giá"
    },
    "unusable_reason": { "type": "string", "nullable": true },
    "detected_product": {
      "type": "string",
      "description": "Loại nông sản model nhận ra trong ảnh"
    },
    "grade": {
      "type": "string",
      "enum": ["dac_biet", "hang_1", "hang_2", "khong_dat"]
    },
    "overall_score": {
      "type": "number",
      "description": "Điểm 0-10, chỉ dựa trên yếu tố nhìn thấy được"
    },
    "confidence": {
      "type": "number",
      "description": "Độ tự tin 0-1"
    },
    "scores": {
      "type": "object",
      "required": ["color", "surface", "shape", "uniformity", "ripeness"],
      "properties": {
        "color":      { "type": "number" },
        "surface":    { "type": "number" },
        "shape":      { "type": "number" },
        "uniformity": { "type": "number" },
        "ripeness":   { "type": "number" }
      }
    },
    "defects": {
      "type": "array",
      "items": {
        "type": "object",
        "required": ["type", "severity", "area_percent", "note"],
        "properties": {
          "type": {
            "type": "string",
            "enum": ["xuoc_vo","nam_nang","vet_nhua","tham","sau_benh",
                     "nut","thoi","moc","meo_mo","khac"]
          },
          "severity":     { "type": "string", "enum": ["nhe","trung_binh","nang"] },
          "area_percent": { "type": "number" },
          "note":         { "type": "string" }
        }
      }
    },
    "reasoning": { "type": "string" },
    "advice":    { "type": "string", "description": "Lời khuyên cho nông dân" }
  }
}
```

Trường `usable` và `confidence` là hai trường **cứu bạn khi demo**. Ảnh nông dân chụp ngoài vườn rất dễ mờ. Có `usable: false` thì UI hiện "Ảnh chưa rõ, vui lòng chụp lại" — chuyên nghiệp hơn nhiều so với việc AI cố chấm bừa.

---

## 7. Chi phí & Context Caching

Đây là phần bạn cần nắm để trả lời câu hỏi "chi phí vận hành thế nào?".

### Vấn đề
Bộ ảnh mẫu (4–6 ảnh) được gửi lại **mỗi lần** nông dân chấm 1 quả. Ảnh tốn nhiều token. Nếu 20 hộ × 10 lần/ngày = 200 request/ngày, bạn trả tiền cho cùng bộ ảnh mẫu 200 lần.

### Giải pháp: Context Caching

<cite index="13-1">Implicit caching được bật mặc định cho tất cả model Gemini 2.5 trở lên. Google tự động chuyển tiết kiệm chi phí cho bạn khi request trúng cache. Bạn không cần làm gì để bật nó.</cite>

<cite index="12-1">Với Gemini 2.5 trở lên, mức giảm giá cho token đã cache là 90%.</cite>

Hai chế độ:

| | Implicit (ngầm định) | Explicit (tường minh) |
|---|---|---|
| Bật | Tự động, không cần code | Phải gọi `caches.create()` |
| Giảm giá | Có, nhưng **không đảm bảo** | **Đảm bảo** 90% |
| Phí lưu trữ | Không | Có, tính theo TTL |
| Token tối thiểu | 1.024–2.048 (Flash) | Cao hơn nhiều |

<cite index="20-1">Cách tăng khả năng trúng implicit cache: đặt phần nội dung lớn và dùng chung ở **đầu prompt**, và gửi các request có prefix giống nhau trong khoảng thời gian ngắn.</cite>

### Khuyến nghị cho AgriSenAi

**Giai đoạn thi (demo, ít traffic): dùng implicit caching.**

Lý do — nguồn phân tích chi phí chỉ ra: <cite index="11-1">Flash input vốn đã rẻ, cached read giảm xuống rất thấp nên khoản tiết kiệm tuyệt đối mỗi lần đọc là nhỏ, trong khi phí lưu trữ theo giờ ăn mòn nhanh khoản đó. Trên Flash, implicit caching thường đã bắt được phần lợi ích thực tế mà không phải trả tiền giữ cache.</cite>

Cụ thể hơn: <cite index="16-1">với kịch bản 1 người dùng, lưu lượng thấp, explicit caching tốn kém hơn 88%. Nên dùng implicit caching (miễn phí, tự động).</cite>

**→ Việc bạn cần làm chỉ là: đặt system instruction + bộ ảnh mẫu ở ĐẦU prompt, ảnh người dùng ở CUỐI.** Thế là đủ. Prefix giống nhau → implicit cache tự trúng.

**Giai đoạn mở rộng (nhiều hộ, nhiều request):** cân nhắc explicit caching, tính điểm hoà vốn theo <cite index="16-1">quy tắc: cần đủ tần suất query để bù phí lưu trữ.</cite>

### Model nên dùng

Theo tài liệu dự án bạn đã chọn Gemini 2.5 Flash / 3.1 Flash-Lite. Hợp lý. Lưu ý cập nhật:
- Có nguồn ghi nhận <cite index="14-1">Gemini 2.0 Flash và 2.0 Flash-Lite bị ngừng hỗ trợ từ 01/06/2026</cite> → bạn đã tránh đúng.
- Tài liệu Google hiện đã tham chiếu tới **gemini-3.5-flash**. Trước khi nộp, vào https://ai.google.dev/gemini-api/docs/pricing kiểm tra model nào còn free tier và giá hiện hành.
- <cite index="14-1">Free tier hiện chỉ còn Flash và Flash-Lite; Pro yêu cầu API key trả phí.</cite> → Đừng thiết kế phụ thuộc Pro.

---

## 8. Tham số sinh (generation config)

```php
'generationConfig' => [
    'temperature' => 0.1,        // Chấm điểm cần ổn định, không sáng tạo
    'topP' => 0.95,
    'maxOutputTokens' => 2048,
    'responseMimeType' => 'application/json',
    'responseSchema' => $schema,
]
```

`temperature = 0.1` là then chốt. Cùng 1 quả xoài mà chấm 2 lần ra 2 điểm khác nhau thì giám khảo sẽ hỏi ngay.

> ⚠️ **Cảnh báo cho model Gemini 3.x:** Google ghi rõ <cite index="8-1">với model Gemini 3.x, nên **giữ nguyên giá trị mặc định** của temperature, topP, topK. Thay đổi (ví dụ đặt temperature dưới 1.0) có thể gây hành vi bất thường như lặp vòng hoặc giảm chất lượng, đặc biệt ở tác vụ suy luận phức tạp.</cite>
>
> → Nếu dùng **2.5 Flash**: đặt temperature 0.1 ✅
> → Nếu dùng **3.x**: để mặc định, và ép tính ổn định bằng few-shot + schema thay vì bằng temperature.

---

## 9. Code Laravel 13

### 9.1 Config

`config/agrisenai.php`

```php
<?php
return [
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model'   => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'endpoint'=> 'https://generativelanguage.googleapis.com/v1beta/models',
    ],
];
```

### 9.2 Bộ ảnh mẫu

Cấu trúc thư mục:

```
storage/app/reference/
├── xoai/
│   ├── 01_dac_biet.jpg
│   ├── 02_ranh_gioi_db_h1.jpg
│   ├── 03_hang_1.jpg
│   ├── 04_ranh_gioi_h1_h2.jpg
│   ├── 05_hang_2.jpg
│   ├── 06_khong_dat.jpg
│   └── labels.json
├── quyt_hong/
└── sen/
```

`labels.json`

```json
[
  {
    "file": "01_dac_biet.jpg",
    "label": {
      "usable": true,
      "detected_product": "Xoài Cát Hòa Lộc",
      "grade": "dac_biet",
      "overall_score": 9.4,
      "confidence": 0.95,
      "scores": {"color":9.6,"surface":9.8,"shape":9.2,"uniformity":9.0,"ripeness":9.0},
      "defects": [],
      "reasoning": "Vỏ vàng cam đều từ cuống đến đuôi, đặc trưng giống Cát Hòa Lộc. Bề mặt sạch, không thấy vết xước hay thâm. Hình dạng cân đối.",
      "advice": "Chất lượng hạng Đặc biệt, đủ chuẩn xuất khẩu. Nên bán ở phân khúc cao cấp."
    }
  },
  {
    "file": "06_khong_dat.jpg",
    "label": {
      "usable": true,
      "detected_product": "Xoài Cát Hòa Lộc",
      "grade": "khong_dat",
      "overall_score": 2.1,
      "confidence": 0.93,
      "scores": {"color":3.0,"surface":1.5,"shape":4.0,"uniformity":2.0,"ripeness":2.5},
      "defects": [
        {"type":"thoi","severity":"nang","area_percent":25,
         "note":"Vùng thối mềm lan rộng ở phần đuôi quả, đã ăn vào thịt"},
        {"type":"moc","severity":"nang","area_percent":8,
         "note":"Có mốc trắng quanh vùng thối"}
      ],
      "reasoning": "Quả có vùng thối lan rộng đã ăn vào thịt và xuất hiện nấm mốc. Theo TCVN 9766:2013, khuyết tật ảnh hưởng đến thịt quả thì không đạt bất kỳ hạng nào.",
      "advice": "Không nên bán quả này. Tách riêng để tránh lây lan sang các quả khác trong lô."
    }
  }
]
```

### 9.3 Service

`app/Services/QualityAssessmentService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QualityAssessmentService
{
    private string $apiKey;
    private string $model;
    private string $endpoint;

    public function __construct()
    {
        $this->apiKey   = config('agrisenai.gemini.api_key');
        $this->model    = config('agrisenai.gemini.model');
        $this->endpoint = config('agrisenai.gemini.endpoint');
    }

    /**
     * Đánh giá chất lượng nông sản từ ảnh.
     *
     * @param string $imagePath  Đường dẫn ảnh nông dân upload
     * @param string $productKey vd: 'xoai', 'quyt_hong', 'sen'
     * @param array  $farmerData Dữ liệu nông dân tự nhập (khối lượng, số trái...)
     */
    public function assess(string $imagePath, string $productKey, array $farmerData = []): array
    {
        $contents = [];

        // ── PHẦN CACHE ĐƯỢC: ảnh mẫu + nhãn, đặt ở ĐẦU prompt ──
        foreach ($this->loadReferenceSet($productKey) as $ref) {
            // Lượt user: ảnh mẫu + câu hỏi
            $contents[] = [
                'role'  => 'user',
                'parts' => [
                    ['inline_data' => [
                        'mime_type' => 'image/jpeg',
                        'data'      => $ref['image_b64'],
                    ]],
                    ['text' => $this->questionText($productKey)],
                ],
            ];
            // Lượt model: câu trả lời mẫu (chính là "nhãn vàng")
            $contents[] = [
                'role'  => 'model',
                'parts' => [
                    ['text' => json_encode($ref['label'], JSON_UNESCAPED_UNICODE)],
                ],
            ];
        }

        // ── PHẦN THAY ĐỔI: ảnh thật của nông dân, đặt ở CUỐI ──
        $contents[] = [
            'role'  => 'user',
            'parts' => [
                ['inline_data' => [
                    'mime_type' => 'image/jpeg',
                    'data'      => base64_encode(file_get_contents($imagePath)),
                ]],
                ['text' => $this->questionText($productKey, $farmerData)],
            ],
        ];

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $this->systemInstruction($productKey)]],
            ],
            'contents'         => $contents,
            'generationConfig' => [
                'temperature'      => 0.1,
                'topP'             => 0.95,
                'maxOutputTokens'  => 2048,
                'responseMimeType' => 'application/json',
                'responseSchema'   => $this->schema(),
            ],
        ];

        $res = Http::timeout(60)
            ->withHeaders(['x-goog-api-key' => $this->apiKey])
            ->post("{$this->endpoint}/{$this->model}:generateContent", $payload);

        if ($res->failed()) {
            Log::error('Gemini API lỗi', ['body' => $res->body()]);
            throw new \RuntimeException('Không kết nối được dịch vụ AI. Vui lòng thử lại.');
        }

        $body = $res->json();

        // Ghi log token để theo dõi chi phí + kiểm tra cache có trúng không
        Log::info('Gemini usage', [
            'prompt_tokens' => $body['usageMetadata']['promptTokenCount'] ?? null,
            'cached_tokens' => $body['usageMetadata']['cachedContentTokenCount'] ?? 0,
            'output_tokens' => $body['usageMetadata']['candidatesTokenCount'] ?? null,
        ]);

        $raw = $body['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $ai  = json_decode($raw, true) ?? [];

        return $this->postProcess($ai, $farmerData);
    }

    /**
     * Hậu xử lý: áp luật TCVN. Hệ thống có quyền HẠ hạng, không có quyền NÂNG.
     */
    private function postProcess(array $ai, array $farmerData): array
    {
        if (($ai['usable'] ?? false) === false) {
            return $ai; // Ảnh không dùng được, trả về nguyên trạng
        }

        $defects = $ai['defects'] ?? [];

        // Luật 1: khuyết tật ăn vào thịt quả → không đạt, bất kể AI chấm gì
        $critical = ['thoi', 'moc', 'nut', 'sau_benh'];
        foreach ($defects as $d) {
            if (in_array($d['type'], $critical) && $d['severity'] === 'nang') {
                $ai['grade'] = 'khong_dat';
                $ai['overall_score'] = min($ai['overall_score'], 3.0);
                $ai['override_note'] = 'Hệ thống hạ hạng: phát hiện khuyết tật ảnh hưởng thịt quả (TCVN 9766:2013).';
                break;
            }
        }

        // Luật 2: tổng diện tích khuyết tật quá lớn → tối đa hạng 2
        $totalDefect = array_sum(array_column($defects, 'area_percent'));
        if ($totalDefect > 10 && in_array($ai['grade'], ['dac_biet', 'hang_1'])) {
            $ai['grade'] = 'hang_2';
            $ai['override_note'] = 'Hệ thống hạ hạng: tổng khuyết tật vượt ngưỡng hạng I.';
        }

        // Luật 3: gộp dữ liệu cân đo do nông dân nhập (AI KHÔNG suy đoán được)
        $ai['farmer_input'] = [
            'weight_g'  => $farmerData['weight_g']  ?? null,
            'brix'      => $farmerData['brix']      ?? null,
            'quantity'  => $farmerData['quantity']  ?? null,
        ];

        // Luật 4: cảnh báo nếu độ tự tin thấp
        if (($ai['confidence'] ?? 1) < 0.6) {
            $ai['low_confidence_warning'] =
                'AI chưa chắc chắn về kết quả này. Nên chụp lại ảnh rõ hơn hoặc nhờ người có kinh nghiệm xem thêm.';
        }

        return $ai;
    }

    private function loadReferenceSet(string $productKey): array
    {
        $dir    = "reference/{$productKey}";
        $labels = json_decode(Storage::get("{$dir}/labels.json"), true);

        return collect($labels)->map(fn ($item) => [
            'image_b64' => base64_encode(Storage::get("{$dir}/{$item['file']}")),
            'label'     => $item['label'],
        ])->all();
    }

    private function questionText(string $productKey, array $farmerData = []): string
    {
        $names = [
            'xoai'      => 'xoài',
            'quyt_hong' => 'quýt hồng',
            'sen'       => 'sen',
        ];
        $name = $names[$productKey] ?? 'nông sản';

        $text = "Phân hạng quả {$name} này theo TCVN 9766:2013.";

        if ($farmerData) {
            $text .= "\n\nThông tin nông dân cung cấp:";
            if (!empty($farmerData['variety']))  $text .= "\n- Giống: {$farmerData['variety']}";
            if (!empty($farmerData['weight_g'])) $text .= "\n- Khối lượng TB: {$farmerData['weight_g']} g/trái (nông dân cân)";
            if (!empty($farmerData['quantity'])) $text .= "\n- Số trái trong lô: {$farmerData['quantity']}";
            if (!empty($farmerData['region']))   $text .= "\n- Vùng trồng: {$farmerData['region']}";
        }

        return $text;
    }

    private function systemInstruction(string $productKey): string
    {
        // Nội dung ở mục 5.1
        return view("prompts.system.{$productKey}")->render();
    }

    private function schema(): array
    {
        // Nội dung ở mục 6
        return config('agrisenai.schema');
    }
}
```

### 9.4 Controller

```php
public function assess(Request $request, QualityAssessmentService $ai)
{
    $request->validate([
        'image'      => 'required|image|max:8192',   // 8MB
        'product'    => 'required|in:xoai,quyt_hong,sen',
        'weight_g'   => 'nullable|numeric|min:50|max:5000',
        'quantity'   => 'nullable|integer|min:1',
    ]);

    $path = $request->file('image')->store('uploads/assessments');

    try {
        $result = $ai->assess(
            Storage::path($path),
            $request->product,
            $request->only(['weight_g', 'quantity', 'variety', 'region'])
        );
    } catch (\RuntimeException $e) {
        return back()->withErrors(['ai' => $e->getMessage()]);
    }

    // Lưu vào bảng ai_quality_assessments
    $assessment = AiQualityAssessment::create([
        'user_id'       => auth()->id(),
        'image_path'    => $path,
        'product_type'  => $request->product,
        'grade'         => $result['grade'] ?? null,
        'overall_score' => $result['overall_score'] ?? null,
        'raw_response'  => $result,
    ]);

    return view('farmer.assessment-result', compact('result', 'assessment'));
}
```

---

## 10. Cách kiểm chứng (phần này sẽ được hỏi)

Bạn nói AI chấm chính xác — chứng minh thế nào?

### Bộ test tách biệt
Chụp thêm **20–30 quả KHÔNG dùng làm ảnh mẫu**. Nhờ thương lái/cán bộ khuyến nông phân hạng → đó là đáp án.

### Đo 3 chỉ số

| Chỉ số | Cách tính | Mục tiêu demo |
|---|---|---|
| **Độ chính xác hạng** | % số quả AI xếp đúng hạng | ≥ 70% |
| **Sai lệch kề** | % AI sai nhưng chỉ lệch 1 hạng | Càng cao càng tốt |
| **Độ ổn định** | Chấm lại cùng 1 ảnh 5 lần, đo độ lệch chuẩn điểm | σ < 0.5 |

Chỉ số thứ 3 là điểm ăn tiền. Chạy 1 vòng lặp gọi API 5 lần trên cùng 1 ảnh, chụp màn hình kết quả gần như y hệt → chứng minh few-shot + temperature thấp thực sự có tác dụng.

### So sánh có/không few-shot
Chạy cùng bộ test **2 lần**: một lần zero-shot (không ảnh mẫu), một lần few-shot. Đưa bảng so sánh vào slide.

Google ghi rõ: <cite index="8-1">prompt không có few-shot example thường kém hiệu quả hơn.</cite> Nhưng có số liệu **của chính bạn** thì thuyết phục hơn nhiều so với trích dẫn tài liệu.

---

## 11. Những cái bẫy cần tránh

**1. Ảnh mẫu và ảnh thật khác môi trường quá xa.**
Ảnh mẫu nền trắng studio, ảnh nông dân chụp giữa vườn nắng gắt. → Chụp thêm 1–2 ảnh mẫu "trong điều kiện thực tế" để model quen.

**2. Model bịa số gram/Brix.**
Đã chặn bằng system instruction + schema `nullable`, nhưng vẫn phải test kỹ. Nếu nó vẫn bịa, thêm 1 ảnh mẫu có `"weight_g": null` để dạy nó biết trả null.

**3. Dồn điểm về giữa.**
Không có mẫu "không đạt" → model ngại chấm thấp. Bắt buộc phải có mẫu neo sàn.

**4. Prompt quá dài, quá nhiều mẫu.**
Google cảnh báo: <cite index="10-1">luôn phải kèm hướng dẫn rõ ràng cùng với few-shot example. Không có hướng dẫn rõ ràng, model có thể bắt được những pattern hoặc quan hệ **ngoài ý muốn** từ các ví dụ, dẫn tới kết quả kém.</cite>
→ Nếu tất cả ảnh mẫu hạng cao đều chụp nền trắng còn hạng thấp chụp nền vườn, model sẽ học nhầm rằng **nền trắng = điểm cao**. Phải trộn nền ngẫu nhiên giữa các hạng.

**5. Không có fallback.**
API Gemini có thể timeout hoặc hết quota giữa buổi demo. → Cache kết quả các ảnh demo sẵn, có nút "xem kết quả mẫu" phòng khi mạng hỏng.

**6. Ảnh quá nặng.**
Google: <cite index="4-1">luôn dùng Files API khi tổng dung lượng request vượt 100 MB.</cite> Với ảnh mẫu 768px thì `inline_data` là đủ, nhưng nhớ resize ảnh nông dân upload trước khi gửi (đừng gửi ảnh 12MP thẳng lên).

---

## 12. Việc cần làm — theo thứ tự

- [ ] **Tuần này:** Mua 20–30 trái xoài đủ chất lượng, chụp trên nền giấy trắng + vài ảnh nền vườn
- [ ] Nhờ thương lái/cán bộ khuyến nông phân hạng → lấy nhãn vàng (nhớ chụp ảnh lúc tham vấn, làm bằng chứng cho thuyết minh)
- [ ] Tải bản TCVN 9766:2013 đầy đủ, lấy đúng bảng khối lượng nhóm A/B/C
- [ ] Chia: 6 ảnh làm reference set, còn lại làm test set
- [ ] Viết `labels.json` cho 6 ảnh mẫu
- [ ] Dựng `QualityAssessmentService`, test bằng Tinker trước khi làm UI
- [ ] Chạy đo 3 chỉ số ở mục 10 → đưa số vào slide
- [ ] Chạy so sánh zero-shot vs few-shot → đưa bảng vào slide
- [ ] Kiểm tra lại giá + free tier tại https://ai.google.dev/gemini-api/docs/pricing trước ngày nộp (31/07)

---

## 13. Câu trả lời cho ban giám khảo

**"Sao không tự train model riêng?"**
> Train model phân loại nông sản cần vài nghìn ảnh có nhãn và hạ tầng GPU — không khả thi với 1 sinh viên trong 6 tháng. Quan trọng hơn, mỗi vùng và mỗi giống có tiêu chuẩn khác nhau; model train cứng sẽ phải train lại từ đầu khi mở rộng sang loại nông sản mới. Few-shot cho phép em thêm 1 loại nông sản mới chỉ bằng cách bổ sung 6 ảnh mẫu — mất 1 buổi thay vì 3 tháng. Khi có đủ dữ liệu thực từ người dùng, em sẽ chuyển sang fine-tuning ở giai đoạn 2.

**"AI có chính xác không?"**
> Trên bộ test 25 quả do [tên chuyên gia] phân hạng, hệ thống đạt X% chính xác tuyệt đối và Y% sai lệch không quá 1 hạng. Độ ổn định: chấm lại cùng 1 ảnh 5 lần, độ lệch chuẩn 0.Zđ. Em cũng chủ động **không** để AI đoán khối lượng hay độ Brix từ ảnh — những chỉ tiêu đó nông dân tự cân và nhập, vì suy đoán từ ảnh là không đáng tin.

**"Nếu AI chấm sai thì sao?"**
> Hệ thống có 3 lớp bảo vệ: (1) trường `confidence` — dưới 0.6 sẽ cảnh báo người dùng; (2) rule-based override theo TCVN — hệ thống có quyền hạ hạng nhưng AI không có quyền nâng hạng vượt ngưỡng tiêu chuẩn; (3) điểm AI là **tham khảo**, hiển thị kèm ảnh gốc để người mua tự kiểm chứng, không thay thế kiểm định.

---

## Nguồn tham khảo

- TCVN 9766:2013 — Xoài quả tươi (CODEX STAN 184-1993)
- TCVN 5008:2007 (ISO 6660:1993) — Xoài, bảo quản lạnh
- Gemini API — File prompting strategies: https://ai.google.dev/gemini-api/docs/file-prompting-strategies
- Gemini API — Prompt design strategies: https://ai.google.dev/gemini-api/docs/prompting-strategies
- Gemini API — Context caching: https://ai.google.dev/gemini-api/docs/caching
- Gemini API — Pricing: https://ai.google.dev/gemini-api/docs/pricing
- Vertex AI — Few-shot examples: https://cloud.google.com/vertex-ai/generative-ai/docs/learn/prompts/few-shot-examples
