# Quá trình xây dựng module AI đánh giá chất lượng nông sản

**Dự án:** AgriSenAi — sàn nông sản Đồng Tháp
**Cách tiếp cận:** Gemini Vision + few-shot prompting. **Không train model, không fine-tune.**
**Cập nhật:** 13/07/2026

Tài liệu này ghi lại *quá trình* — gồm cả những chỗ làm sai và phải làm lại.
Người đọc nên hiểu được **vì sao** từng quyết định được đưa ra, không chỉ **cái gì**
đã được làm.

Xem thêm: [`danh-gia-dataset.md`](danh-gia-dataset.md) — đánh giá chi tiết từng dataset.

---

## 1. Vấn đề cần giải

Nông dân chụp ảnh nông sản bằng điện thoại → AI chấm chất lượng → gợi ý hạng và giá bán.

**Nguyên tắc thiết kế đã chốt từ đầu:**

| Nguyên tắc | Lý do |
|---|---|
| AI chỉ chấm những gì **nhìn thấy được** (màu, bề mặt, khuyết tật, hình dạng) | Không để AI đoán bừa |
| **Khối lượng (gram) và độ Brix do nông dân tự nhập** — AI không được suy đoán | Ảnh không cho biết quả nặng bao nhiêu hay ngọt cỡ nào |
| Thang hạng neo theo **TCVN 9766:2013**: `dac_biet` / `hang_1` / `hang_2` / `khong_dat` | Có chuẩn để đối chiếu, không chấm cảm tính |
| **Rule engine được quyền HẠ hạng. AI không được quyền NÂNG hạng.** | An toàn: AI sai theo hướng dễ dãi thì rule engine chặn lại |

Nguyên tắc cuối cùng hoá ra là thứ **cứu cả dự án** — xem mục 5.

---

## 2. Few-shot prompting hoạt động thế nào (và vì sao có file `labels.json`)

Đây là điểm hay bị hiểu nhầm nhất, nên nói rõ.

`labels.json` **không phải file train.** Nó là **nội dung của prompt.**

Mỗi lần nông dân upload một ảnh, Laravel dựng một request gửi Gemini có dạng:

```
[system prompt: thang hạng TCVN, danh sách loại khuyết tật, mục đích bán...]

Ví dụ 1: <ảnh 01_dac_biet_chin.jpg>  → {"grade":"dac_biet","overall_score":9.3, ...}
Ví dụ 2: <ảnh 02_dac_biet_xanh.jpg>  → {"grade":"dac_biet","overall_score":9.3, ...}
...
Ví dụ 6: <ảnh 06_khong_dat.jpg>      → {"grade":"khong_dat","overall_score":3.4, ...}

Giờ chấm ảnh này: <ảnh nông dân vừa upload>
```

Sáu cặp (ảnh + nhãn) ở giữa **chính là** 6 ảnh trong `storage/app/reference/xoai/`
và 6 nhãn trong `labels.json`. Hai thứ đó được đọc lại **ở mọi request, trong
production**. Không có chúng thì không có few-shot — chỉ còn zero-shot, và Gemini
sẽ chấm lung tung, dồn hết điểm về khoảng 6–8.

**Hệ quả:** `reference/*.jpg` + `labels.json` là **ruột của module AI**, phải commit
lên git. Còn `scripts/analyze_dataset.py` chỉ là **đồ nghề offline**, chạy vài lần
rồi thôi, không dính gì tới production.

---

## 3. Khảo sát dataset — và hai lần phải bỏ dataset

### 3.1. Dataset xoài thứ nhất: bỏ hoàn toàn

Dataset xoài ban đầu (1025 ảnh, nhãn YOLO) hoá ra là dataset **đếm quả trên cây**,
không phải chấm chất lượng:

- Nhãn YOLO chỉ có **1 class duy nhất** (`0` = "quả xoài") trên 4.394 bounding box.
  Không nhãn bệnh, không nhãn hạng.
- Quả **quá nhỏ trong khung hình**: quả to nhất mỗi ảnh chỉ chiếm **trung vị 4,96%**
  khung. Chỉ **1/1025 ảnh** có quả chiếm ≥20% khung.
- Cắt thử 6 quả to nhất toàn dataset → vẫn là xoài **xanh non trên cây**, nửa quả
  trong bóng nắng gắt, không quả nào có khuyết tật.

Không thể chấm xước vỏ hay nấm trên vật thể chiếm 5% khung ảnh. **Bỏ.**

### 3.2. Dataset cam: dùng được một nửa, nhưng dính bẫy nặng

1090 ảnh, 4 lớp theo **bệnh** (`fresh` / `greening` / `canker` / `blackspot`) —
**không phải theo hạng**. Sau khi soi ảnh, phát hiện 3 vấn đề:

**🔴 Lớp `fresh` là ảnh quảng cáo, không phải ảnh chụp thật.**
Bằng chứng số: `fresh` là **PNG 100%** với **143 kích thước khác nhau / 281 ảnh**
(mỗi ảnh một cỡ = scrape web), trong khi 3 lớp bệnh là **JPEG 100%, đúng một cỡ
800×800** (chụp cùng một bộ giá đỡ studio).

→ Lấy ảnh stock này làm "neo trần" thì Gemini sẽ neo *"chất lượng cao"* vào **thẩm
mỹ ảnh quảng cáo**, không phải vào **chất lượng quả**. Nông dân chụp quả đẹp thật
bằng điện thoại, nền là cái rổ nhựa → AI thấy "không giống ảnh stock" → chấm 6–7 điểm.
**Quả đẹp thật không bao giờ chạm nổi hạng đặc biệt.**

**🔴 Cam sành Việt Nam chín vẫn có vỏ XANH.**
Trong dataset này, vỏ xanh = bệnh = điểm thấp. Nhưng cam sành Đồng Tháp chín tới
**vẫn giữ vỏ xanh sần** — và đó là quả ngon nhất. Dùng thẳng dataset thì AI chấm
cam sành chín ngon là *"chưa chín / nghi nhiễm greening"*.

**🟠 Rò rỉ nền (background leak).**
Nền tương quan gần như hoàn hảo với lớp: `fresh` nền trắng, `blackspot`/`canker`
nền giấy xám (~160/255), `grenning` **nền đen tuyền (0/255)**. Cộng thêm định dạng
và kích thước → **ba tín hiệu độc lập** cho phép đoán đúng lớp mà **không cần nhìn quả**.

**Kết luận:** dataset cam chỉ cho ta **nửa dưới của thang điểm** (`hang_2`, `khong_dat`).
Nửa trên phải tự chụp. → **Hạ ưu tiên cam, tập trung vào xoài.**

### 3.3. Dataset xoài thứ hai (tự chụp): đây mới là thứ dùng được

69 ảnh / 71 quả, tự chụp bằng điện thoại, gán nhãn trên Roboflow.

| Tiêu chí | Kết quả |
|---|---|
| Ảnh thật, điện thoại thật, xoài Việt đã thu hoạch | ✅ |
| Một quả / ảnh | ✅ |
| **Không rò rỉ nền** | ✅ nền lớp `Xoai Vang` RGB(146,146,128) vs `Xoai Xanh` RGB(149,147,129) — lệch 3/255, bằng không |
| Độ phân giải đủ | ✅ crop quanh quả có cạnh nhỏ nhất trung vị 677px |
| Dải khuyết tật | ✅ từ vỏ sạch → đốm nhẹ → mảng thán thư lớn |
| Nhãn là **hạng**? | ❌ nhãn là **độ chín** (vàng/xanh) → phải gán lại tay |
| Có quả **thối/mốc/nứt**? | ❌ **không có quả nào** → thiếu neo sàn thật |

---

## 4. Ba lỗi kỹ thuật phát hiện được nhờ soi dataset

Đây là phần giá trị nhất của việc khảo sát kỹ — cả ba lỗi này đều sẽ nổ **lúc demo**
nếu không tìm ra sớm.

### 4.1. 🔴 EXIF Orientation — bug production, không phải bug dataset

20/69 ảnh (chụp bằng iPhone, 4032×3024) có **`EXIF Orientation = 6`**: ảnh nằm
**ngang** trong file nhưng hiển thị **dọc**. Lúc cắt ảnh theo bounding box, kết quả
ra toàn nền — phải áp `exif_transpose` mới trúng quả.

**Nghĩa là:** ảnh nông dân chụp bằng iPhone gửi lên Laravel sẽ **bị xoay 90°** khi
đưa vào Gemini nếu không xử lý EXIF. Quả xoài nằm ngang → AI chấm sai hết.

→ `ImageNormalizer.php` **bắt buộc** đọc `exif_read_data()` và xoay lại trước khi crop.
Nếu không có dataset này, bug đã nằm im tới ngày demo.

### 4.2. 🟠 Bounding box YOLO vẽ ẩu

Kể cả sau khi sửa EXIF, nhiều box vẫn lệch hoặc cắt mất nửa quả.

→ **Không dùng box.** Với 6 ảnh reference, khung cắt được chỉ định **bằng tay** và
ghi vào `source.json`. Với 25 ảnh test, dùng box YOLO nới rộng 45% — đủ tốt vì ảnh
test không cần chính xác tuyệt đối.

Có thử phân vùng quả **tự động** bằng ngưỡng màu HSV, nhưng **thất bại**: hue của
vỏ xoài vàng trùng với hue của mặt bàn gỗ. Đã bỏ hướng này thay vì cố cứu.

### 4.3. 🔴 Phép đo tự động sai — và bài học

Có viết một phép đo "tỉ lệ đốm đen" để xếp hạng quả từ tệ đến tốt. Kết quả: quả
"43,6% đốm đen" hoá ra là crop **dính thanh sắt đen của cái máy**, không phải đốm
trên vỏ. Box ẩu → nền lọt vào → thuật toán đếm nhầm nền là khuyết tật.

**Phép đo bị bỏ đi hoàn toàn. Việc chọn ảnh làm bằng mắt.**

Bài học: một con số tự động trông có vẻ khách quan vẫn có thể sai hoàn toàn. Phải
kiểm tra bằng mắt trước khi tin nó.

---

## 5. Vấn đề lớn nhất: KHÔNG CÓ NEO SÀN

Trong 71 quả xoài **không có quả nào thối, mốc, nứt, hay dập nát**. Chỗ tệ nhất chỉ
là *"vàng có đốm đen rải rác"*.

Thiếu neo sàn thì model **ngại chấm điểm thấp** và dồn hết về 6–8 điểm.

### Cách xử lý — và cách KHÔNG được xử lý

**❌ Tuyệt đối không: gán quả đốm-nhiều-nhất thành `khong_dat` cho đủ mâm.**
Quả đó thật sự chỉ là `hang_2`. Gán nó thành `khong_dat` là dạy AI **loại oan quả
còn bán được** → nông dân mất tiền. Thà thiếu neo sàn và biết mình thiếu, còn hơn có
neo sàn giả và tưởng mình đủ.

**✅ Cách làm: neo sàn bằng LUẬT thay vì bằng ẢNH.**

Chia lại vai trò:

| | Việc |
|---|---|
| **Gemini** | **MÔ TẢ** — phát hiện và mô tả khuyết tật (loại, độ nặng, % diện tích) |
| **Rule engine** | **PHÁN QUYẾT** — ép xuống `khong_dat` nếu khuyết tật ăn vào thịt quả |

Gemini vốn **đã biết** quả thối, mốc, nứt trông ra sao — nó là mô hình thị giác tổng
quát, không cần ta dạy. Việc của few-shot **không phải** dạy nó "thế nào là thối",
mà là **hiệu chỉnh thang điểm cho những ca tinh tế** — quả đốm 3% khác quả đốm 12%
chỗ nào. Đúng những ca mà 69 ảnh phủ rất tốt.

Còn ca thảm hoạ thì rule engine lo: AI trả về `{"type":"thoi","area_percent":30}`
→ **ép xuống `khong_dat`**, bất kể AI chấm mấy điểm.

Cấu hình ở `config/agrisenai.php`:

```php
'defects_loai_bo' => ['thoi', 'moc', 'nut'],
```

Điều này khớp đúng nguyên tắc **"rule engine được quyền HẠ hạng"** đã khoá từ đầu.
Nguyên tắc đó, viết ra từ trước khi biết dataset thiếu gì, hoá ra lại là thứ cứu
được tình huống này.

---

## 6. Xây bộ ảnh reference (6 ảnh)

Nguyên tắc: **PHỦ BIÊN, KHÔNG PHỦ GIỮA** — chọn ảnh ở ranh giới giữa các hạng,
vì đó là chỗ model dễ sai nhất.

| File | Hạng | Độ chín | Nền | Vai trò |
|---|---|---|---|---|
| `01_dac_biet_chin.jpg` | `dac_biet` | chín vàng | băng chuyền xanh | **NEO TRẦN** (chín) |
| `02_dac_biet_xanh.jpg` | `dac_biet` | xanh | bàn gỗ | **NEO TRẦN** (xanh) |
| `03_hang_1.jpg` | `hang_1` | chín vàng | băng chuyền xanh | Biên `dac_biet` / `hang_1` |
| `04_hang_2.jpg` | `hang_2` | ương | bàn gỗ | Biên `hang_1` / `hang_2` |
| `05_hang_2_nang.jpg` | `hang_2` | ương | bàn gỗ | Biên `hang_2` / `khong_dat` |
| `06_khong_dat.jpg` | `khong_dat` | chín vàng | băng chuyền xanh | **NEO SÀN** |

### Hai kiểu rò rỉ đã chủ động chặn

**Rò rỉ nền.** Nếu ảnh hạng cao toàn nền trắng còn hạng thấp toàn nền vườn, model
sẽ học *"nền trắng = điểm cao"*. Nên nền được **trộn có chủ ý**: băng chuyền xanh
xuất hiện ở cả `dac_biet` (01) lẫn `khong_dat` (06); bàn gỗ xuất hiện ở cả `dac_biet`
(02) lẫn `hang_2` (04, 05).

**Rò rỉ độ chín — bẫy riêng của xoài, suýt mắc.**
Ban đầu chỉ quả **xanh** được `dac_biet` (vì không tìm được quả chín nào sạch tuyệt đối).
Nhưng như vậy model sẽ học *"xanh = điểm cao, vàng = điểm thấp"* — một kiểu rò rỉ khác.

→ Bắt buộc phải có quả **chín** ở hạng cao nhất. Nên: `dac_biet` có **cả quả chín (01)
lẫn quả xanh (02)**, còn `khong_dat` (06) là quả **chín vàng**. Giờ độ chín không dự
đoán được hạng.

### Vì sao xoài không dùng mask tròn

Với **cam** (hình cầu), cách khử rò rỉ nền tốt nhất là crop vuông + **mask tròn** +
tô nền xám đồng nhất. Nhưng **xoài thuôn dài** — mask tròn sẽ **cắt mất hai đầu quả**,
mà hình dạng (`shape`) là 1 trong 5 tiêu chí chấm.

Và xoài **không có rò rỉ nền** (đã đo: lệch 3/255). Nên chỉ **crop vuông ôm quả +
resize 768**, không mask.

### Một lần chọn sai và phải làm lại

Lần đầu chọn `01_dac_biet_chin` là một quả có **vết nâu rõ giữa thân** — tức nó thật
sự là `hang_1`. Gọi nó là `dac_biet` chính là **tự bịa neo trần**, đúng cái vừa cấm
ở mục 5. Đã đổi sang quả có vết nâu **ở cuống** (vết nhựa lúc cắt quả — không ăn vào
thịt quả, tiêu chuẩn cho phép ở hạng đặc biệt).

Đồng thời sửa `config`: `dac_biet` ban đầu đặt `dien_tich_khuyet_tat_toi_da = 0` là
**quá cứng, sai so với chuẩn thật** — tiêu chuẩn quả tươi **có** cho phép khuyết tật
bề mặt rất nhẹ ở hạng đặc biệt. Đã đổi thành `2` (%).

---

## 7. Nhãn vàng (`labels.json`)

`reasoning` phải **NÊU LÝ DO theo TCVN**, không chỉ mô tả:

> ❌ Sai: *"Quả này có đốm đen"*
> ✅ Đúng: *"Đốm đen rải rác ~12% bề mặt, đã vượt ngưỡng 1/10 của hạng I nhưng vẫn
> dưới 1/4 bề mặt. Các đốm còn nông, bề mặt chưa lõm, tức chưa ăn vào thịt quả.
> Theo TCVN 9766:2013, quả đạt hạng II."*

Lý do: nếu ví dụ mẫu chỉ mô tả, Gemini sẽ bắt chước và cũng chỉ mô tả. Nếu ví dụ mẫu
**lập luận theo chuẩn**, Gemini sẽ lập luận theo chuẩn — và nông dân đọc được **vì sao**
quả mình bị hạ hạng, chứ không chỉ biết là bị hạ.

---

## 8. Tập test (25 ảnh)

25 ảnh **không dùng làm reference** (đã kiểm chứng bằng script: không có ảnh gốc nào
trùng nhau), kèm `ground_truth.json`.

Phân bố hạng — **lệch nặng, và đó là sự thật phải nói ra**:

| Hạng | Số quả |
|---|---:|
| `dac_biet` | 5 |
| `hang_1` | 7 |
| `hang_2` | 11 |
| `khong_dat` | **2** |

Hai lỗ hổng phải ghi vào thuyết minh, **đừng giấu**:

1. **`khong_dat` chỉ có 2 quả.** Với n=2, con số accuracy trên hạng này gần như vô nghĩa.
2. **Cả 5 quả `dac_biet` đều là xoài XANH.** Trong 69 ảnh không có quả **chín** nào
   sạch đủ để đạt hạng đặc biệt. Nghĩa là tập test **không đo được** khả năng AI chấm
   hạng đặc biệt cho quả chín.

---

## 9. Kết quả đo lần đầu — và bằng chứng cho mục 5

Chạy `php artisan quality:eval xoai` trên 25 ảnh test, model `gemini-3.1-flash-lite`:

| | Kết quả |
|---|---|
| Khớp chính xác | **56%** (14/25) |
| Khớp hoặc lệch 1 bậc | **96%** (24/25) |
| Rule engine phải hạ hạng | 0 lần |

Đoán bừa trên 4 hạng chỉ được 25%, nên 56% là có học được thật.

### Ma trận nhầm lẫn (hạng thật → AI chấm)

```
dac_biet   →  dac_biet=3,  hang_1=1,  hang_2=1
hang_1     →  hang_1=4,    hang_2=2,  dac_biet=1
hang_2     →  hang_2=7,    hang_1=4
khong_dat  →  hang_2=2                          ← 0/2
```

### Phát hiện chính: AI KHÔNG BAO GIỜ chấm `khong_dat`

Cả 2 quả loại bỏ đều bị nâng lên `hang_2`. Và nhìn rộng ra, AI **nới tay có hệ thống**:
4 quả `hang_2` bị nâng lên `hang_1`, chỉ 2 quả `hang_1` bị hạ xuống.

Soi kết quả thô của 2 quả `khong_dat` thì thấy cơ chế: AI ước lượng khuyết tật
**20–23%** — nằm **ngay dưới** ngưỡng 25%. Nó dừng sát vạch mà không vượt, nên rule
engine **không có gì để bám vào mà hạ**. Nguyên nhân: trong 6 ảnh mẫu chỉ có **đúng
một** ảnh `khong_dat`, nên model bắt chước mẫu `05_hang_2_nang` (23%) thay vì mẫu
`06_khong_dat` (30%).

### Đã thử vá bằng prompt — THẤT BẠI

Thêm vào system prompt các luật kiểu *"khi phân vân hãy chọn hạng thấp hơn"*,
*"đừng ước lượng diện tích dè dặt"*, *"thán thư phủ 1/4 bề mặt thì chấm `khong_dat`"*:

- **Vẫn 0/2 trên `khong_dat`** — không nhúc nhích ở đúng chỗ quan trọng nhất
- **Sinh lỗi MỚI, tệ hơn**: một quả xoài xanh **sạch** bị chấm `hang_2` (lệch 2 bậc)
- Tỉ lệ khớp 56% → 60%, nhưng xem mục dưới

→ **Đã hoàn nguyên prompt.** Kết luận: **chữ không thay được ảnh.**

### Bài học về phương pháp đo: 56% vs 60% là NHIỄU

Chạy **cùng một prompt** hai lần cho ra **100%** rồi **96%** ở chỉ số "khớp hoặc lệch
1 bậc". Tức có nhiễu giữa các lần chạy ngay cả ở `temperature = 0.1`.

Nghĩa là mọi so sánh 56% vs 60% ở trên **nằm trong nhiễu, không kết luận được gì**.
Với n=25, sai số khoảng **±10 điểm phần trăm**.

**Chỉ có đúng một phát hiện đứng vững qua cả ba lần chạy: `khong_dat` → 0/2.**

Cũng vì vậy mà **đã dừng tinh chỉnh prompt**: chỉnh thêm vài vòng nữa thì số sẽ đẹp
lên, nhưng chỉ đẹp trên đúng 25 tấm ảnh này (overfit), còn ảnh thật vẫn hỏng.

### Tin tốt

- **`dac_biet` đúng 3/5** — AI nhận ra xoài **xanh** sạch là hạng cao. Nghĩa là bẫy
  *"vỏ xanh = chưa chín"* (rủi ro chí mạng ở mục 3.2) **đã được chặn thành công**.
- **Không sai thảm hoạ**: 24/25 ảnh lệch tối đa 1 bậc.
- Rule engine chạy đúng, chỉ là **chưa có gì để bắt** — vì AI chưa từng báo
  `thoi`/`moc`/`nut` (nó chưa hề thấy quả nào như vậy trong 6 ảnh mẫu).

---

## 10. Kiến trúc

```
scripts/analyze_dataset.py        ← CÔNG CỤ OFFLINE, không chạy production
                                     (khảo sát dataset, phát hiện rò rỉ nền)

config/agrisenai.php              ← NGUỒN SỰ THẬT DUY NHẤT
                                     thang hạng, defect type, ngưỡng TCVN,
                                     tham số chuẩn hoá ảnh

app/Services/Quality/
  ImageNormalizer.php             ← xoay EXIF → crop vuông → 768 → JPEG q85
  FewShotPromptBuilder.php        ← (chưa làm) ghép reference + labels → prompt
  GeminiVisionClient.php          ← (chưa làm) gọi API
  GradeRuleEngine.php             ← (chưa làm) CHỈ được hạ hạng

app/Console/Commands/
  BuildReferenceSet.php           ← php artisan reference:build xoai
                                     php artisan reference:build xoai --set=testset

storage/app/reference/xoai/       ← ✅ COMMIT — ruột của module AI
  01..06.jpg                        6 ảnh few-shot, 768×768, ~630 KB
  labels.json                       nhãn vàng → nội dung prompt
  source.json                       nguồn gốc + khung cắt (dựng lại được)

storage/app/testset/xoai/         ← ✅ COMMIT
  t01..t25.jpg                      25 ảnh test
  ground_truth.json                 nhãn thật để đo accuracy
  source.json

dataset/                          ← ❌ KHÔNG commit (964 MB)
reports/                          ← ❌ KHÔNG commit (tự sinh)
```

**Quyết định kiến trúc quan trọng nhất: chuẩn hoá ảnh viết bằng PHP, không phải Python.**

Ảnh reference và ảnh nông dân upload **phải qua đúng cùng một hàm**. Viết logic đó hai
lần (một bản Python để dựng reference, một bản PHP để chạy production) thì hai bản sẽ
lệch nhau — mà lệch phân phối là AI chấm sai. Nên chỉ viết **một lần** trong
`ImageNormalizer.php`, rồi `php artisan reference:build` gọi lại chính class đó.

Kết quả: Python co lại còn đúng một việc là **khảo sát**.

---

## 11. Hai cái bẫy khi đấu nối Gemini API

**Định dạng API key.** Google đang lưu hành **hai** định dạng key: kiểu cũ `AIza...`
và kiểu mới **`AQ....`**. Cả hai đều hợp lệ và đều gửi qua header `x-goog-api-key`.

Đừng thấy `AQ.` mà tưởng là OAuth token rồi gửi `Authorization: Bearer` — sẽ dính 401.
Cách phân biệt chắc chắn nhất: **gọi thử API**. Nếu key sai thì trả 401/403; nếu trả
**503 "high demand"** thì key đã xác thực xong, chỉ là model đang quá tải.

**Chọn model.** Alias `gemini-flash-latest` và `gemini-3-flash-preview` thường xuyên
trả **503 (quá tải)**, còn `gemini-2.0-flash` / `gemini-pro-latest` trả **429 (hết hạn
mức miễn phí)**. Model chạy ổn định: **`gemini-3.1-flash-lite`**.

Client đã có sẵn cơ chế **thử lại (4 lần, cách nhau 3 giây)** cho 429/500/503, vì chạy
25 ảnh liên tục rất dễ đụng giới hạn.

Timeout phải để **180 giây**, không phải 60 — mỗi request nhúng 7 ảnh (6 mẫu + 1 ảnh
thật) ≈ **0,9 MB** sau khi mã hoá base64.

---

## 12. Hạn chế đã biết — phải ghi vào thuyết minh dự thi

Giấu là mất điểm nặng hơn nói thẳng.

| # | Hạn chế | Ảnh hưởng | Cách khắc phục |
|---|---|---|---|
| 1 | **Chưa có ảnh quả loại bỏ thật** (thối/mốc/nứt) | **ĐÃ ĐO ĐƯỢC:** AI chấm `khong_dat` **0/2** trên tập test, và **vá bằng prompt không ăn thua** (mục 9). Đây là hạn chế nghiêm trọng nhất | Chụp/tìm ~8 quả hỏng — đủ kiểu: thối/mốc, nứt/chảy nhựa, dập/thâm. Chỗ để: `dataset/thu-thap/xoai-hong/` |
| 2 | **Chưa kiểm chứng trên cam sành vỏ xanh** | Đã có luật chặn trong prompt, nhưng **không có ảnh để đo** | Chụp ~10 quả cam sành chín |
| 3 | **Nhãn vàng do AI đề xuất**, chưa có chuyên gia nông nghiệp duyệt | Con số accuracy chưa có giá trị đối chứng | Nhờ thầy hướng dẫn / cán bộ khuyến nông / chủ vựa soát lại |
| 4 | **Ngưỡng 1/10 và 1/4 bề mặt** lấy theo thông lệ tiêu chuẩn quả tươi, **chưa đối chiếu bản TCVN 9766:2013 gốc** | Con số này đi thẳng vào prompt | Đối chiếu văn bản gốc |
| 5 | **Giống xoài** ghi là "Xoài Cát Chu" — phỏng đoán theo hình dáng | AI sẽ học theo, nếu sai giống thì sai từ gốc | Người chụp ảnh xác nhận |
| 6 | Cam/quýt chưa làm | Sàn chưa chấm được cam | Cần ảnh cam sành / quýt hồng thật |

Cả 6 hạn chế đều giải quyết được bằng **vài chục tấm ảnh** — không cần thêm mô hình,
không cần train. Và đó chính là **lộ trình**: ảnh sẽ được bổ sung liên tục từ chính
nông dân đăng bán trên sàn (*active learning*).

---

## 13. Còn phải làm

**Đã xong:**

- [x] `FewShotPromptBuilder.php` — ghép reference + labels thành prompt
- [x] `GeminiVisionClient.php` — gọi API thật (có retry cho 429/503)
- [x] `GradeRuleEngine.php` — chỉ được hạ hạng (đã kiểm chứng 7/7 tình huống)
- [x] `QualityAssessor.php` — bộ điều phối
- [x] API key Gemini trong `.env`
- [x] `php artisan quality:eval xoai` — đo accuracy trên tập test

**Còn lại:**

- [ ] **Bổ sung ~8 ảnh xoài hỏng** ← việc quan trọng nhất, xem mục 9
- [ ] Chuyên gia nông nghiệp duyệt lại nhãn vàng
- [ ] Đối chiếu ngưỡng 1/10 và 1/4 với bản TCVN 9766:2013 gốc
- [ ] Xác nhận giống xoài (đang ghi "Xoài Cát Chu" — phỏng đoán)
- [ ] Controller + route + màn hình upload
- [ ] Khung hướng dẫn chụp trên giao diện ("đặt quả vào trong khung") — để ảnh nông
      dân gửi lên có bố cục giống ảnh reference
- [ ] Cam/quýt: cần ảnh cam sành / quýt hồng thật
