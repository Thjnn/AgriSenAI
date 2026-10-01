# Câu hỏi giám khảo có thể hỏi về chức năng AI đánh giá chất lượng

Các câu trả lời dưới đây viết theo đúng mã nguồn và số đo hiện có (01/10/2026). Chi tiết kỹ thuật nằm ở `chuc-nang-ai-danh-gia-chat-luong.md`.

Nguyên tắc khi trả lời: nói đúng cái đang có, nêu hạn chế trước khi bị hỏi, rồi nói kế hoạch khắc phục. Giám khảo mở mã ra xem là biết ngay, nên nói quá sẽ mất điểm nặng hơn nói thật.

---

## Câu mở đầu nên thuộc (30 giây)

> Nông dân chụp một ảnh quả xoài. Ảnh được chuẩn hoá về cùng một khung 768×768, rồi gửi lên mô hình Gemini kèm 6 ảnh mẫu đã có nhãn để mô hình chấm theo đúng thang của bọn em. Kết quả AI trả về chưa phải kết quả cuối: một bộ luật theo ngưỡng TCVN kiểm tra lại, và bộ luật này chỉ được hạ hạng, không được nâng. Nông dân nhận về hạng, điểm, danh sách khuyết tật, lý do và lời khuyên bán hàng.

---

## Nhóm 1 — Mô hình và phương pháp

### 1. Em dùng mô hình AI gì? Tự huấn luyện hay dùng có sẵn?

> Em dùng mô hình Gemini của Google, gọi qua API. Em không tự huấn luyện mô hình nào. Phương pháp là few-shot prompting: mỗi lần chấm, em gửi kèm 6 ảnh mẫu có nhãn để mô hình hiểu thang chấm của mình.

Nếu hỏi thêm tên model: `gemini-3.1-flash-lite`, đổi được trong file `.env` mà không sửa mã.

### 2. Sao không tự huấn luyện một mô hình riêng, ví dụ YOLO?

> Có ba lý do. Một là dữ liệu: em mới có 69 ảnh tự chụp, huấn luyện một mô hình phân hạng cần vài nghìn ảnh có nhãn. Hai là YOLO giỏi việc tìm vị trí vật thể, còn bài toán của em là xếp hạng chất lượng và giải thích lý do bằng tiếng Việt cho nông dân. Ba là few-shot cho phép đổi tiêu chuẩn chỉ bằng cách đổi ảnh mẫu và ngưỡng, không phải huấn luyện lại.
>
> Em có gán nhãn dataset theo định dạng YOLO trên Roboflow, nhưng mới dùng các khung đó để cắt ảnh mẫu, chưa huấn luyện YOLO.

Đừng nói "em có dùng YOLO". Trong dự án không có mô hình YOLO nào.

### 3. Few-shot là gì? Khác huấn luyện ở đâu?

> Huấn luyện là thay đổi mô hình bằng dữ liệu. Few-shot không đổi mô hình: em đặt sẵn vài cặp "ảnh và câu trả lời đúng" ngay trong câu hỏi, rồi mới đưa ảnh cần chấm. Mô hình bắt chước cách chấm của các ví dụ đó. Với em là 6 cặp, gửi lại trong mỗi lần chấm.

### 4. Sáu ảnh mẫu chọn theo tiêu chí nào?

> Em chọn theo ba nguyên tắc. Chọn ảnh nằm ở ranh giới giữa các hạng, vì đó là chỗ dễ chấm sai nhất. Trộn nền: nền băng chuyền và nền bàn gỗ đều có ở cả hạng cao lẫn hạng thấp, để mô hình không học "nền này là điểm cao". Trộn độ chín: hạng cao nhất có cả quả chín vàng và quả xanh, hạng thấp nhất là quả chín vàng, để mô hình không học "xanh là tốt" hay "vàng là xấu".

Sáu ảnh gồm: 2 đặc biệt, 1 hạng I, 2 hạng II, 1 không đạt. Nằm ở `storage/app/reference/xoai/`.

### 5. Em chuẩn hoá ảnh thế nào? Vì sao phải làm?

> Bốn bước: xoay ảnh theo thông tin hướng chụp của điện thoại, cắt vuông ở giữa, đưa về 768×768, lưu JPEG. Ảnh mẫu và ảnh nông dân đi qua cùng một hàm. Nếu không làm, ảnh điện thoại sẽ bị nằm ngang và ảnh mẫu với ảnh thật khác khung, làm mô hình chấm lệch.

---

## Nhóm 2 — Độ chính xác

### 6. AI của em chính xác bao nhiêu? Đo thế nào?

> Em đo trên 25 ảnh test tách riêng, không trùng 6 ảnh mẫu. Khớp đúng hạng là 56%, tức 14 trên 25. Khớp hoặc lệch một bậc là 96%, tức 24 trên 25. Đoán ngẫu nhiên trên 4 hạng chỉ được 25%.
>
> Em xin nói rõ hai giới hạn của con số này. Tập test chỉ có 25 ảnh nên sai số khoảng cộng trừ 10 điểm phần trăm. Và đáp án của tập test do AI đề xuất rồi em soát lại, chưa có chuyên gia nông nghiệp duyệt.

Lệnh đo: `php artisan quality:eval xoai`. Nếu đo lại trước ngày thi thì cập nhật số ở đây.

Đo lại ngày 01/10/2026 ra 14/25 đúng hạng và 25/25 lệch tối đa một bậc, tức con số 56% lặp lại được.

### 7. 56% là thấp. Sao dám đưa cho nông dân dùng?

> Em đồng ý 56% chưa đủ để thay kiểm định. Hiện kết quả là để tham khảo. Điểm em thấy quan trọng hơn là hướng sai: 24 trên 25 ảnh chỉ lệch tối đa một bậc, và bộ luật của em chỉ cho phép hạ hạng, nên hệ thống nghiêng về phía an toàn cho người mua. Việc cần làm tiếp là bổ sung ảnh và nhờ người có chuyên môn duyệt nhãn.

### 8. AI có phát hiện được xoài thối, dập, thâm không?

> Quả thối rõ thì bắt được. Em đo trên 40 ảnh xoài thối và mốc lấy từ một bộ ảnh công khai: AI chấm "không đạt" 39 trên 40, và tự gọi đúng khuyết tật là thối hoặc mốc. Quả lành thì không bị loại oan, 10 trên 10.
>
> Chỗ còn yếu là quả hỏng vừa phải, ở sát ngưỡng. Hai quả thán thư nặng trong tập test của em vẫn bị xếp hạng II thay vì không đạt, và đốm đen nhỏ thì AI chấm chưa nhất quán, có quả bị loại thẳng, có quả được hạng I. Em đã thử thêm ảnh mẫu quả thối và thử sửa lời dặn, cả hai đều không cải thiện chỗ này.
>
> Việc tiếp theo của em là bổ sung ảnh mẫu ở đúng ranh giới hạng II và không đạt, và nhờ người có chuyên môn xác nhận nhãn của các quả đó.

Đây là câu dễ bị hỏi nhất vì giảng viên đã góp ý "tập trung phát hiện xoài thâm". Trả lời thẳng, đừng vòng.

Số liệu và giới hạn của phép đo nằm ở `ket-qua-do-xoai-hong-2026-10-01.md`. Nếu bị hỏi kỹ, nói rõ 40 ảnh đó là xoài Senegal, ảnh nhỏ, và là những quả thối rõ ràng.

### 9. Chấm lại cùng một ảnh có ra cùng kết quả không?

> Em đặt độ ngẫu nhiên của mô hình ở mức thấp, 0,1, để kết quả ổn định. Tuy vậy vẫn có dao động: chạy cùng tập test hai lần, chỉ số "khớp hoặc lệch một bậc" ra 100% rồi 96%. Em chưa đo riêng độ lệch khi chấm một ảnh nhiều lần.

### 10. Có ảnh mẫu thì tốt hơn không có ảnh mẫu bao nhiêu?

> Em chưa đo so sánh hai cách trên cùng tập test. Đây là phép đo em cần bổ sung.

Nếu kịp, chạy đo trước ngày thi rồi điền số vào đây. Chưa đo thì nói chưa đo.

### 11. Nhãn của ảnh mẫu do ai gán? Có chuyên gia xác nhận không?

> Nhãn do AI đề xuất, em soát lại theo ngưỡng diện tích khuyết tật. Chưa có chuyên gia nông nghiệp duyệt. Em dự định nhờ cán bộ khuyến nông hoặc chủ vựa soát lại.

---

## Nhóm 3 — Tiêu chuẩn và luật

### 12. Em chấm theo tiêu chuẩn nào?

> Em neo theo TCVN 9766:2013 về xoài quả tươi, chia bốn mức: đặc biệt, hạng I, hạng II và không đạt. Tiêu chí chính là tổng diện tích khuyết tật trên bề mặt và khuyết tật có ăn vào thịt quả hay không.
>
> Hai ngưỡng 10% và 25% bề mặt em lấy theo thông lệ tiêu chuẩn quả tươi, chưa đối chiếu được với văn bản gốc. Em sẽ đối chiếu và chỉnh lại nếu lệch.

Bảng ngưỡng đang dùng:

| Hạng | Điểm tối thiểu | Khuyết tật tối đa |
|---|---|---|
| Đặc biệt | 9.0 | 2% bề mặt |
| Hạng I | 7.0 | 10% |
| Hạng II | 5.0 | 25% |
| Không đạt | dưới 5.0 | trên 25% |

### 13. Nếu AI chấm sai thì sao? Ai chịu trách nhiệm?

> Em có ba lớp. Lớp một là bộ luật: có khuyết tật thối, mốc, nứt thì ép xuống không đạt; tổng diện tích khuyết tật vượt ngưỡng thì hạ hạng; điểm không đủ thì hạ hạng. Bộ luật không bao giờ nâng hạng. Lớp hai là độ tin cậy: dưới 0,6 thì hệ thống đánh dấu cần người xem lại. Lớp ba là minh bạch: kết quả kèm lý do và danh sách khuyết tật để người xem tự kiểm chứng. Kết quả AI là tham khảo, không thay kiểm định.

### 14. Vì sao chỉ hạ hạng mà không nâng hạng?

> Vì hai kiểu sai có hậu quả khác nhau. AI chấm dễ dãi thì người mua nhận hàng xấu và sàn mất uy tín. AI chấm khắt khe thì nông dân thiệt một ít và còn khiếu nại được. Nên em thiết kế để hệ thống luôn nghiêng về phía an toàn.

Nói thêm nếu bị hỏi sâu: trên tập test, bộ luật chưa phải hạ hạng lần nào, vì AI ước lượng khuyết tật ngay dưới ngưỡng. Bộ luật đúng nhưng phụ thuộc AI báo đúng khuyết tật.

### 15. AI có đoán được khối lượng hay độ ngọt không?

> Không, và em cố ý chặn. Ảnh không cho biết khối lượng hay độ Brix, nên em cấm trong lời dặn và không để trường nào cho hai số đó trong kết quả trả về. Hai chỉ số này để nông dân tự cân, tự đo và nhập.

### 16. Xoài xanh thì AI có chấm là chưa chín, trừ điểm không?

> Em đã xử lý trường hợp này. Xoài xanh là một sản phẩm riêng, và một số giống chín vẫn còn vỏ xanh. Trong lời dặn em quy định độ chín chấm so với mục đích bán, và trong ảnh mẫu có một quả xanh được xếp hạng đặc biệt. Trên tập test, xoài xanh sạch được nhận là hạng cao.
>
> Phần còn thiếu là giao diện chưa cho nông dân chọn mục đích bán, nên hiện hệ thống mặc định là bán để ăn chín.

---

## Nhóm 4 — Thực tế sử dụng

### 17. Ngoài vườn ánh sáng, nền, góc chụp khác nhau thì sao?

> Em xử lý ở ba chỗ. Ảnh được chuẩn hoá về cùng một khung. Ảnh mẫu trộn hai loại nền để mô hình không phụ thuộc nền. Và nếu ảnh mờ, tối hoặc không phải nông sản thì AI trả về "ảnh chưa dùng được" chứ không bịa điểm.
>
> Hạn chế hiện tại là ảnh bị cắt vuông ở giữa, nên quả nằm lệch sẽ bị cắt mất một phần. Em đang nhắc bằng chữ trên giao diện. Bước tiếp theo trong kế hoạch là vẽ khung ngắm trên màn hình chụp và kiểm tra ảnh mờ, tối ngay trên điện thoại.

### 18. "Camera AI" của em là quét trực tiếp hay chụp ảnh?

> Là chụp ảnh. Trên điện thoại, nút chụp mở thẳng camera sau, nông dân chụp một tấm rồi gửi. Em chưa làm quét video liên tục, vì mỗi lần chấm là một lần gọi mô hình trên mạng.

Giao diện có 3 ô ảnh (toàn quả, cận vỏ, mặt cắt), nhưng hiện chỉ ảnh "toàn quả" được chấm. Nếu demo, đừng nói AI dùng cả ba ảnh.

### 19. Không có mạng thì có dùng được không?

> Chưa. Mô hình chạy trên máy chủ của Google nên cần Internet để chấm. Chụp khi mất sóng rồi tự gửi khi có mạng nằm trong giai đoạn 3 của kế hoạch.

### 20. Chi phí mỗi lần chấm là bao nhiêu? Mở rộng thì có gánh nổi không?

> Mỗi lần chấm là một lần gọi API, gửi 7 ảnh cỡ 768×768 gồm 6 ảnh mẫu và 1 ảnh thật. Em dùng dòng model Flash-Lite là dòng rẻ nhất. Sáu ảnh mẫu luôn đặt ở đầu và giống nhau giữa các lần gọi, nên có thể được tính giá đã lưu đệm.

Chưa có con số tiền cụ thể trong tài liệu dự án. Trước ngày thi, tra giá hiện hành tại `https://ai.google.dev/gemini-api/docs/pricing`, tính ra tiền cho một lần chấm rồi điền vào đây. Đừng nêu số khi chưa tra.

### 21. Phụ thuộc Google như vậy, họ đổi giá hoặc ngừng model thì sao?

> Phần gọi mô hình nằm riêng trong một file, tên model nằm trong cấu hình, nên đổi model chỉ sửa một dòng. Thang hạng, ngưỡng và bộ luật là của em, không phụ thuộc Google. Khi có đủ ảnh thật từ người dùng, em có thể chuyển sang mô hình tự huấn luyện mà không đổi các phần còn lại.

### 22. Ảnh của nông dân gửi đi đâu? Có lưu lại không?

> Ảnh được gửi lên API của Google để chấm. Phía máy chủ của em, ảnh chỉ nằm trong file tạm và bị xoá ngay sau khi chấm xong.

### 23. Kết quả chấm có được lưu và gắn vào sản phẩm, mã QR không?

> Hiện trang đánh giá chấm xong chỉ hiển thị, chưa lưu. Bảng lưu kết quả và phần kiểm tra toàn vẹn bằng mã băm SHA-256 đã có, nhưng mới chạy với dữ liệu mẫu. Nối kết quả chấm thật sang lô hàng và mã QR là Task 1.1 đến 1.4 trong kế hoạch.

Khi làm xong Task 1.1–1.4 thì sửa lại câu này.

### 24. Thêm loại nông sản khác có khó không?

> Về kỹ thuật thì thêm một dòng cấu hình và một bộ 6 ảnh mẫu có nhãn. Nhưng em chủ động chỉ làm xoài ở giai đoạn này để làm cho tới nơi, vì mỗi loại quả cần ảnh và tiêu chuẩn riêng.

### 25. So với thương lái nhìn bằng mắt thì hơn ở đâu?

> AI của em chưa chính xác hơn người có kinh nghiệm. Cái hơn là nhất quán và có ghi lại: cùng một thang cho mọi lô, có lý do bằng chữ, có danh sách khuyết tật. Người mua ở xa xem được căn cứ xếp hạng thay vì chỉ nghe người bán nói.

---

## Những câu không nên nói

| Đừng nói | Vì sao | Nói thay |
|---|---|---|
| "Em dùng mô hình YOLO" | Không có mô hình YOLO trong dự án | "Em dùng Gemini với few-shot; dataset gán nhãn theo định dạng YOLO" |
| "Em huấn luyện AI" | Không có bước huấn luyện nào | "Em hướng dẫn mô hình bằng 6 ảnh mẫu" |
| "AI chính xác 96%" | 96% là khớp hoặc lệch một bậc | "56% đúng hạng, 96% lệch tối đa một bậc" |
| "AI phát hiện được mọi quả hỏng" | Thối rõ bắt được 39/40, nhưng quả sát ngưỡng vẫn 0/2 | "Bắt được quả thối rõ, còn yếu ở quả hỏng vừa phải" |
| "Quét camera thời gian thực" | Là chụp một ảnh rồi gửi | "Chụp ảnh bằng camera điện thoại" |
| "AI phân tích 3 góc chụp" | Chỉ ảnh đầu được chấm | "Hiện chấm một ảnh toàn quả" |
| "Đã được chuyên gia kiểm định" | Nhãn chưa có chuyên gia duyệt | "Nhãn chưa có chuyên gia duyệt, em sẽ nhờ soát lại" |
| "Đúng theo TCVN" | Ngưỡng chưa đối chiếu bản gốc | "Neo theo TCVN 9766:2013, ngưỡng đang chờ đối chiếu" |

---

## Việc nên làm trước ngày thi để trả lời chắc hơn

- [x] Đo khả năng bắt xoài thối (01/10/2026, xem `ket-qua-do-xoai-hong-2026-10-01.md`).
- [x] Chạy lại phép đo trên 25 ảnh test, số ở câu 6 lặp lại được.
- [ ] Nhờ người có chuyên môn xem lại 2 quả `t07`, `t24` trong tập test (câu 8).
- [ ] Bổ sung ảnh mẫu ở ranh giới hạng II / không đạt bằng xoài Việt Nam, đo lại câu 8.
- [ ] Đo so sánh có ảnh mẫu và không có ảnh mẫu (câu 10).
- [ ] Chấm một ảnh 5 lần, ghi lại độ lệch (câu 9).
- [ ] Tra giá Gemini, tính tiền một lần chấm (câu 20).
- [ ] Đối chiếu ngưỡng 10% và 25% với bản gốc TCVN 9766:2013 (câu 12).
- [ ] Nhờ người có chuyên môn soát nhãn, chụp ảnh buổi làm việc làm bằng chứng (câu 11).
- [ ] Soát file thuyết minh: nếu có ghi "YOLO" hoặc "huấn luyện mô hình" thì sửa cho khớp với mã.
- [ ] Chuẩn bị sẵn vài ảnh và kết quả đã chấm, phòng khi mất mạng hoặc Gemini quá tải lúc demo. Lần đo 01/10/2026 có khoảng 1/7 số lần chấm mất hơn 30 giây và 4/174 lần bị lỗi 503.
