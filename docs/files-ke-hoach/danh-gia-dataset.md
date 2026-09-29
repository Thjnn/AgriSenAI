# Đánh giá khả năng sử dụng dataset — Module AI đánh giá chất lượng

*Bước 2. Căn cứ: `reports/dataset_report.md` (số liệu) + soi ảnh bằng mắt
(`reports/preview_cam_theo_lop.png`).*

Kết luận một dòng: **dataset cam dùng được, nhưng CHỈ dùng làm nguồn ảnh hạng
THẤP. Ảnh hạng CAO bắt buộc phải chụp mới — nếu không, AI sẽ chấm sai theo một
kiểu rất khó phát hiện.**

---

## 1. Nhãn là BỆNH hay HẠNG CHẤT LƯỢNG?

**Là BỆNH.** Không có bất kỳ nhãn hạng nào.

4 thư mục `fresh` / `blackspot` / `canker` / `grenning` là tên **tác nhân gây
bệnh**, không phải mức chất lượng thương phẩm. Trong dataset không có file nhãn
nào (0 file `.csv/.json/.xml`) — nhãn nằm hoàn toàn ở tên thư mục.

Quan trọng: **tên bệnh không quy ra được hạng.** Một quả `canker` có 1 vết sẹo
nhỏ 2% bề mặt và một quả `canker` nứt toác nửa vỏ nằm chung một thư mục, nhưng
một quả đạt hạng II còn quả kia không đạt. Hạng phải chấm theo **diện tích và độ
sâu khuyết tật**, đúng nguyên tắc "AI chỉ chấm cái nhìn thấy được" đã chốt.

Ánh xạ đề xuất — **theo % diện tích, KHÔNG theo tên bệnh**:

| Điều kiện quan sát được | Hạng |
|---|---|
| Vỏ sạch, màu đều, không khuyết tật đáng kể | `dac_biet` |
| Khuyết tật vỏ nhẹ, tổng ≲ 1/10 bề mặt, không ăn vào thịt quả | `hang_1` |
| Khuyết tật vỏ rõ, tổng ≲ 1/4 bề mặt, vẫn chưa ăn vào thịt quả | `hang_2` |
| Vượt 1/4 bề mặt, hoặc thối/mốc/nứt ăn vào thịt, hoặc méo mó nặng | `khong_dat` |

> ⚠️ Hai ngưỡng 1/10 và 1/4 là ngưỡng thông dụng của tiêu chuẩn quả có múi
> (Codex/TCVN). **Tôi không có bản TCVN 9766:2013 trong tay để trích đúng điều
> khoản.** Bạn cần đối chiếu bản gốc và xác nhận trước khi chốt, vì con số này
> sẽ đi thẳng vào prompt và vào phần thuyết minh khi thi.

Riêng **greening (vàng lá gân xanh, HLB)**: đây là bệnh kiểm dịch, quả bị méo,
chín lệch màu, ruột đắng. Nhìn thấy được qua hình dạng méo + màu loang lổ → xếp
`khong_dat` hoặc `hang_2` tuỳ mức méo, và nên để **rule engine** chặn riêng.

---

## 2. Dùng few-shot trực tiếp được không?

**Không.** Có ba lý do, xếp theo mức nguy hiểm giảm dần.

### 2.1. 🔴 CHÍ MẠNG — lớp `fresh` là ảnh stock, không phải ảnh chụp thật

Toàn bộ 314 ảnh `fresh` là **ảnh quảng cáo tải từ web**: quả hoàn hảo, nền trắng
tinh, ánh sáng studio, có quả còn kèm lá xanh cắm nghệ thuật. Bằng chứng số:
`fresh` là **PNG 100%** với **143 kích thước khác nhau / 281 ảnh** (mỗi ảnh một
cỡ = scrape web), trong khi 3 lớp bệnh là **JPEG 100%, đúng 1 kích thước
800×800** (chụp cùng một bộ giá đỡ).

Nếu lấy ảnh stock này làm **"neo trần"** (ảnh đẹp nhất, 9–10 điểm), Gemini sẽ
neo khái niệm "chất lượng cao" vào **thẩm mỹ ảnh quảng cáo**, chứ không vào
**chất lượng quả**. Hậu quả: nông dân chụp một quả quýt hồng loại 1 thật sự đẹp
bằng điện thoại, nền là cái rổ nhựa ngoài vườn — AI thấy "không giống ảnh stock"
→ chấm 6–7 điểm. **Quả đẹp thật vẫn không bao giờ chạm được hạng đặc biệt.**

Đây đúng là lỗi "AI ngại chấm điểm cao" mà đề bài lo, nhưng nguyên nhân nằm ở
ảnh neo trần chứ không ở neo sàn.

→ **Neo trần bắt buộc phải là ảnh chụp thật của một quả đẹp thật.**

### 2.2. 🔴 CHÍ MẠNG — cam sành Việt Nam chín vẫn có VỎ XANH

Trong dataset này, **vỏ xanh = bệnh/chưa chín = điểm thấp**: lớp `grenning` toàn
quả xanh loang lổ, lớp `fresh` toàn quả cam rực. Nhưng **cam sành Đồng Tháp chín
tới vẫn giữ vỏ xanh sần**, và đó là quả ngon nhất.

Nếu dạy AI bằng dataset này mà không sửa, nó sẽ chấm **cam sành chín ngon là
"chưa chín / nghi nhiễm greening"** → hạ hạng oan → nông dân mất tiền ngay buổi
demo. Đây là lỗi sẽ nổ đúng lúc trình bày trước ban giám khảo Đồng Tháp.

→ Bộ reference **bắt buộc phải có ít nhất 1 ảnh cam sành vỏ xanh, chín, hạng
cao**, kèm reasoning nói thẳng: *"vỏ xanh là đặc trưng giống cam sành khi chín,
không phải dấu hiệu chưa chín hay nhiễm bệnh"*.

### 2.3. 🟠 Rò rỉ nền (background leak) — nền dự đoán được nhãn

Nền tương quan gần như hoàn hảo với lớp: `fresh` nền trắng/ảnh web, `blackspot`
+ `canker` nền giấy xám sáng (độ sáng nền ~160/255), `grenning` **nền đen tuyền
(0/255)**. Cộng thêm định dạng (PNG vs JPEG) và kích thước (loạn xạ vs 800×800),
có tới **ba tín hiệu độc lập** cho phép đoán đúng lớp mà **không cần nhìn quả**.

Đề bài yêu cầu "trộn ngẫu nhiên nền giữa các hạng" — **ở dataset này không trộn
được**, vì không tồn tại ảnh quả khoẻ chụp trên nền đen, cũng không có ảnh
greening trên nền trắng. Nền và lớp dính chặt vào nhau từ gốc.

→ Không trộn được thì **khử**: xem phương án ở mục 4.

---

## 3. Có phải nông sản Việt Nam không?

**Không.** Cam/quýt trong dataset là giống ngoại (cam navel, quýt nhập), chụp ở
nước ngoài. Xoài (đã loại) là xoài vườn Ấn Độ/Úc.

Rủi ro theo từng sản phẩm chủ lực:

| Sản phẩm | Mức rủi ro | Vì sao |
|---|---|---|
| **Cam sành Đồng Tháp** | 🔴 Cao | Chín vẫn vỏ xanh sần — dataset dạy ngược (xem 2.2) |
| **Quýt hồng Lai Vung** | 🟡 Vừa | Vỏ cam đỏ, khá giống quýt trong dataset. Nhưng quýt hồng có sắc đỏ cam đặc trưng và vỏ mỏng bóng — AI dễ nhầm sang cam thường |
| **Xoài Cao Lãnh** | ⚫ Không có dữ liệu | Dataset đã loại hoàn toàn |

Phải nêu rủi ro này trong thuyết minh dự thi, đừng giấu — giám khảo Đồng Tháp
biết cam sành nhà mình vỏ xanh.

---

## 4. Phương án cụ thể

### 4.1. Khử rò rỉ nền: chuẩn hoá ảnh trước khi đưa vào reference

Vì quả có múi gần **hình cầu**, có thể khử nền rất rẻ mà không cần segment phức
tạp:

1. Tự động tìm quả bằng ngưỡng bão hoà màu (nền trắng có S thấp, nền đen có V
   thấp, quả có S cao) → lấy bounding box
2. Crop vuông sát quả, chừa lề ~5% (vẫn giữ được độ méo của quả — `shape` là 1
   trong 5 tiêu chí chấm, không được cắt mất)
3. **Mask tròn**: mọi pixel ngoài đường tròn nội tiếp → tô **xám trung tính đồng
   nhất**
4. Resize 768×768, lưu **JPEG quality 85**

Sau bước này, cả 3 tín hiệu rò rỉ chết sạch: định dạng giống nhau (JPEG), kích
thước giống nhau (768), nền giống nhau (xám). Gemini **buộc phải nhìn vỏ quả**.

Áp dụng **y hệt** cho ảnh reference, ảnh test, **và ảnh nông dân upload lúc
chạy thật** — nếu chỉ chuẩn hoá reference mà không chuẩn hoá ảnh đầu vào thì lại
lệch phân phối. Đây là lý do bước tiền xử lý này phải viết lại trong Laravel,
không chỉ nằm ở script Python.

### 4.2. Nguồn ảnh cho từng hạng

| Hạng | Nguồn | Ghi chú |
|---|---|---|
| `dac_biet` (neo trần) | **Chụp mới** — quýt hồng Lai Vung / cam sành loại 1 | ❗Không được dùng ảnh stock (xem 2.1) |
| `hang_1` | **Chụp mới** + có 1 ảnh **cam sành vỏ xanh chín** | ❗Bắt buộc (xem 2.2) |
| `hang_2` | Dataset — `blackspot`/`canker` mức nhẹ–vừa | ✅ Dataset mạnh ở đây |
| `khong_dat` (neo sàn) | Dataset — `canker` nặng / `grenning` méo mó | ✅ Dataset mạnh ở đây |

Nói cách khác: **dataset cam cho ta nửa dưới của thang điểm, ta phải tự chụp nửa
trên.** Đó là kết luận thực tế nhất.

### 4.3. Ảnh chụp mới — cần bao nhiêu?

Rất ít, vì few-shot không cần dữ liệu lớn:

- **Cam/quýt:** 2–3 quả (1 quýt hồng đẹp, 1 cam sành vỏ xanh chín, 1 quả khá)
- **Xoài:** 6 quả — từ đẹp nhất đến quả loại bỏ (xoài Cát Hoà Lộc / Cát Chu)

Cách chụp (giữ nhất quán với ảnh nông dân sẽ chụp sau này):
- Chụp bằng **điện thoại**, không dùng máy ảnh studio
- Nền **giấy A3 xám** hoặc mặt bàn trơn màu trung tính
- Ánh sáng ban ngày trong bóng râm, **không dùng flash**
- Mỗi quả 1 ảnh, quả chiếm ~80% khung

Ra vựa/chợ Cao Lãnh hoặc Lai Vung xin chụp là đủ — chi phí gần bằng 0, mà giải
quyết được cả ba rủi ro: giống đúng, nền thật, ảnh điện thoại thật.

### 4.4. Ai gán nhãn vàng?

Tôi có thể đề xuất nhãn cho ~6 ảnh reference + ~25 ảnh test. **Nhưng tôi không
phải chuyên gia nông nghiệp.** Nếu con số "AI khớp chuyên gia X%" định đem đi
thi, nhãn vàng phải được **người có chuyên môn duyệt lại** (thầy hướng dẫn, cán
bộ khuyến nông, hoặc chủ vựa). Không có bước duyệt đó thì con số accuracy chỉ là
"AI khớp với chính AI" — giám khảo hỏi một câu là vỡ.

---

## 5. Chốt lại

| Câu hỏi | Trả lời |
|---|---|
| Nhãn là bệnh hay hạng? | **Bệnh.** Phải gán lại theo hạng (chỉ ~31 ảnh, làm tay được) |
| Few-shot trực tiếp được không? | **Không.** Phải chuẩn hoá ảnh + bổ sung ảnh hạng cao |
| Nông sản Việt Nam? | **Không.** Cam sành vỏ xanh là rủi ro chí mạng |
| Dataset còn dùng được phần nào? | **Nửa dưới thang điểm** (`hang_2`, `khong_dat`) — chỗ này dataset rất tốt |
| Việc bắt buộc phải làm thêm | Chụp 2–3 quả cam/quýt VN + 6 quả xoài |
