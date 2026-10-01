# Kết quả đo: AI có bắt được xoài hỏng không, và thêm ảnh mẫu thối có giúp không

Ngày đo: 01/10/2026. Model: `gemini-3.1-flash-lite`. Tổng 170 lần chấm.

## Kết luận

1. **AI đang dùng đã bắt được xoài thối rõ.** Với 6 ảnh mẫu hiện tại, nó chấm "không đạt" 30/30 quả thối rõ và 9/10 quả mốc đen vết nhỏ.
2. **Thêm 2 ảnh thối vào bộ mẫu không cải thiện được gì đo được.** Kết quả hai cấu hình gần như giống hệt. Giữ nguyên bộ 6 ảnh mẫu.
3. **Điểm yếu thật nằm ở vùng ranh giới,** không phải ở quả thối rữa:
   - Hai quả "không đạt" trong tập test cũ (thán thư nặng) vẫn bị chấm Hạng II ở cả hai cấu hình.
   - Đốm đen nhỏ được chấm không nhất quán: 3/10 quả bị loại thẳng, số còn lại được Hạng I hoặc cao hơn.

## Cách đo

Mỗi ảnh được chấm hai lần, xen kẽ nhau để cùng điều kiện:

- **Cấu hình A:** 6 ảnh mẫu đang dùng (`storage/app/reference/xoai/`).
- **Cấu hình B:** 6 ảnh đó cộng 2 ảnh thối lấy từ bộ MangoFruitDDS: một quả thối cuống khoảng 40% bề mặt, một quả có vết thối lõm nhỏ khoảng 8%.

Tập đo gồm 85 ảnh:

| Nhóm | Số ảnh | Nguồn | Kết quả đúng |
|---|---|---|---|
| Thối rõ | 30 | MangoFruitDDS, lớp `Stem end Rot` | Không đạt |
| Mốc đen vết nhỏ | 10 | MangoFruitDDS, lớp `Black Mould Rot` | Không đạt |
| Đốm nhẹ (đối chứng) | 10 | MangoFruitDDS, lớp `Alternaria` | Không nên là "không đạt" |
| Quả lành (đối chứng) | 10 | MangoFruitDDS, lớp `Healthy` | Không được là "không đạt" |
| Tập test cũ | 25 | Ảnh tự chụp, `storage/app/testset/xoai/` | Theo `ground_truth.json` |

Hai nhóm đối chứng dùng để phát hiện trường hợp AI học nhầm "ảnh mờ" hoặc "nền gạch" nghĩa là quả hỏng.

## Số liệu

| Nhóm | A: 6 ảnh mẫu | B: 8 ảnh mẫu |
|---|---|---|
| Thối rõ, bắt đúng "không đạt" | 30/30 | 30/30 |
| Mốc đen vết nhỏ, bắt đúng "không đạt" | 9/10 | 10/10 |
| Đốm nhẹ, bị loại thành "không đạt" | 3/10 | 3/10 |
| Quả lành, bị loại thành "không đạt" | 0/10 | 0/10 |
| Test cũ, đúng hạng | 14/25 (56%) | 14/25 (56%) |
| Test cũ, đúng hoặc lệch 1 bậc | 25/25 | 25/25 |
| Test cũ, bắt đúng 2 quả "không đạt" | 0/2 | 0/2 |

Ở 40 quả hỏng, AI tự chấm "không đạt" và tự gọi đúng loại khuyết tật (`thoi` hoặc `moc`). Bộ luật hạ hạng không phải can thiệp lần nào.

Ma trận nhầm lẫn trên tập test cũ, cấu hình A (hạng đúng → AI chấm):

```
dac_biet   →  dac_biet=3, hang_1=2
hang_1     →  hang_1=5,   hang_2=1, dac_biet=1
hang_2     →  hang_2=6,   hang_1=5
khong_dat  →  hang_2=2
```

Giữa hai cấu hình chỉ có 3 ảnh đổi kết quả: 1 ảnh mốc đen được bắt thêm ở B, 1 ảnh test cũ đúng hơn ở B, 1 ảnh test cũ sai hơn ở B. Mức chênh này nằm trong dao động giữa các lần chạy.

## Đọc kết quả

**Vì sao tài liệu cũ ghi "AI không bao giờ chấm không đạt" mà giờ lại 39/40?**
Hai con số đo hai loại quả khác nhau. Hai quả "không đạt" trong tập test cũ là thán thư lan rộng, AI ước lượng 15–22% bề mặt và gọi là `nam_nang`, nằm dưới ngưỡng 25% nên không bị loại. Còn 40 quả mới là thối và mốc thật, AI nhận ra ngay. Kết luận cũ vẫn đúng với quả ở sát ngưỡng; nó không đúng với quả thối rữa.

**Vì sao thêm ảnh thối không giúp?**
Vì AI vốn đã nhận ra quả thối. Hai ảnh mới dạy lại đúng thứ nó đã biết. Thứ nó chưa biết là ranh giới Hạng II / không đạt của quả thán thư, và hai ảnh thối không nói gì về ranh giới đó.

**Ba quả đốm nhẹ bị loại.**
Mỗi quả chỉ có một đốm đen khoảng 1–2% bề mặt. AI ghi "thối 5%, mức nặng" và loại. Các quả khác trong cùng nhóm có đốm gần giống hệt lại được Hạng I, có quả được Hạng đặc biệt. Cả hai cấu hình đều như vậy, nên đây là hành vi sẵn có của AI.

## Giới hạn của phép đo này

- **Ảnh nhóm mới là xoài Senegal** (giống Kent/Keitt), độ phân giải gốc 224×224, phóng lên 768×768 nên mờ. Kết quả trên xoài Việt Nam chụp nét có thể khác.
- **Nhóm "thối rõ" do Claude chọn bằng mắt** từ những ảnh thối rõ ràng. Đây là trường hợp dễ, nên 30/30 không nói gì về quả hỏng vừa phải.
- **Đáp án chưa có chuyên gia duyệt.** Nhóm mới lấy theo tên bệnh của bộ ảnh; tập test cũ lấy theo nhãn do AI đề xuất. Riêng 3 quả đốm nhẹ, ở 224px không nhìn ra vết có lõm vào thịt hay không.
- **Mỗi ảnh chỉ chấm một lần cho mỗi cấu hình.** Chênh lệch 1–2 ảnh không có ý nghĩa.
- **Giấy phép bộ MangoFruitDDS chưa kiểm tra.** Ảnh chỉ dùng để đo nội bộ, không commit lên git.

## Ghi nhận phụ về tốc độ và độ ổn định của Gemini

- Thời gian một lần chấm: trung vị 10 giây (6 ảnh mẫu) và 13 giây (8 ảnh mẫu).
- Khoảng 1/7 số lần chấm mất hơn 30 giây. Lần lâu nhất hơn 6 phút.
- 4/174 lần gọi bị lỗi 503 (Gemini quá tải) dù đã tự thử lại.

Khi demo trước giám khảo cần có phương án dự phòng cho trường hợp Gemini chậm hoặc lỗi.

## Việc nên làm tiếp

1. **Nhờ người có chuyên môn xem lại 2 quả `t07`, `t24`** trong tập test cũ. Nếu họ xác nhận là Hạng II thì con số 0/2 biến mất; nếu là "không đạt" thì cần ảnh mẫu ở đúng ranh giới này.
2. **Bổ sung ảnh mẫu ở ranh giới Hạng II / không đạt,** tức quả thán thư nặng hoặc dập, chụp bằng xoài Việt Nam. Ảnh quả thối rữa không cần thêm.
3. **Làm rõ trong lời dặn và ảnh mẫu** sự khác nhau giữa đốm đen nông và vết thối lõm khi vết còn nhỏ.
4. **Giữ 40 ảnh quả hỏng làm tập đo phụ** để kiểm tra lại mỗi khi đổi ảnh mẫu hoặc đổi model.

## Dữ liệu gốc của phép đo

Nằm ở `reports/mangofruitdds/thu-nghiem-few-shot/` (ngoài git):

| File | Nội dung |
|---|---|
| `results.jsonl` | Kết quả từng lần chấm |
| `manifest.json` | Danh sách 85 ảnh và đáp án |
| `ket-qua.txt` | Bảng tổng hợp |
| `anh-do/` | 60 ảnh đo đã chuẩn hoá |
| `bo-mau-8-anh/` | Bộ mẫu thử nghiệm (6 ảnh cũ + 2 ảnh thối + `labels.json`) |
| `prep.php`, `run.php`, `report.py` | Mã dùng để chạy lại |
