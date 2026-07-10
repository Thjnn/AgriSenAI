**THUYẾT MINH DỰ ÁN**

**NỀN TẢNG SỐ HÓA NÔNG SẢN ĐỒNG THÁP**

_Ứng dụng AI đánh giá chất lượng & khuyến nghị thời điểm bán nông sản_

Lĩnh vực dự thi: CNTT – Chuyển đổi số | Chủ đề: Khởi nghiệp xanh – Công nghệ số – Phát triển bền vững

# **1. LÝ DO CHỌN ĐỀ TÀI / TÍNH CẤP THIẾT**

Đồng Tháp là địa phương có thế mạnh về nông nghiệp với nhiều sản phẩm chủ lực như xoài Cao Lãnh, quýt hồng Lai Vung, sen, nem Lai Vung... Tuy nhiên, phần lớn nông dân và hộ sản xuất nhỏ vẫn phụ thuộc vào thương lái, thiếu thông tin thị trường minh bạch, dẫn đến tình trạng bị ép giá hoặc bán không đúng thời điểm.

Bên cạnh đó, việc đánh giá chất lượng nông sản trước khi bán hiện chủ yếu dựa vào kinh nghiệm cá nhân, thiếu công cụ hỗ trợ khách quan. Trong bối cảnh chuyển đổi số và yêu cầu truy xuất nguồn gốc ngày càng cao — đặc biệt với các sản phẩm OCOP — việc xây dựng một nền tảng số kết hợp trí tuệ nhân tạo để hỗ trợ nông dân là hướng đi cấp thiết và phù hợp với định hướng phát triển bền vững của tỉnh.

# **2. MỤC TIÊU DỰ ÁN**

- Xây dựng nền tảng kết nối trực tiếp giữa nông dân/hộ sản xuất và người mua, giảm khâu trung gian.
- Ứng dụng AI hỗ trợ nông dân đánh giá chất lượng sản phẩm một cách khách quan, nhanh chóng thông qua hình ảnh.
- Cung cấp khuyến nghị thời điểm bán tối ưu dựa trên dữ liệu thời tiết và giá thị trường, giúp nông dân chủ động hơn trong quyết định kinh doanh.
- Góp phần thúc đẩy chuyển đổi số trong nông nghiệp và phát triển kinh tế nông thôn bền vững tại Đồng Tháp.

# **3. ĐỐI TƯỢNG VÀ PHẠM VI**

## **3.1. Đối tượng**

- Hộ nông dân, cơ sở sản xuất OCOP, hợp tác xã nông nghiệp tại Đồng Tháp.
- Người mua lẻ, tiểu thương, đơn vị thu mua có nhu cầu tìm nguồn nông sản chất lượng, minh bạch nguồn gốc.

## **3.2. Phạm vi thí điểm**

Trong giai đoạn đầu, dự án tập trung thí điểm với 2–3 loại nông sản chủ lực của tỉnh (xoài Cao Lãnh, quýt hồng Lai Vung, sen) nhằm đảm bảo chất lượng demo và tính khả thi trong thời gian ngắn, trước khi mở rộng sang các loại nông sản khác.

# **4. GIẢI PHÁP / MÔ TẢ SẢN PHẨM**

## **4.1. Module Marketplace (Sàn kết nối)**

- Đăng bán sản phẩm, tìm kiếm và lọc theo loại nông sản, khu vực, mức giá.
- Trang truy xuất nguồn gốc bằng mã QR, hiển thị thông tin hộ sản xuất và kết quả đánh giá chất lượng từ AI.
- Ba vai trò chính: Nông dân/hộ sản xuất – Người mua – Quản trị viên.

## **4.2. Module AI đánh giá chất lượng**

- Nông dân chụp ảnh sản phẩm (xoài, quýt...) qua điện thoại.
- Hệ thống AI (ứng dụng công nghệ thị giác máy tính) phân loại độ chín, phát hiện dấu hiệu hư hỏng/sâu bệnh, ước lượng kích cỡ.
- Kết quả được gắn trực tiếp vào tin đăng bán, tăng độ tin cậy với người mua.

## **4.3. Module khuyến nghị thời điểm bán**

- Kết hợp dữ liệu thời tiết (ảnh hưởng đến vận chuyển, bảo quản) và dữ liệu giá thị trường tham khảo.
- AI tổng hợp và đưa ra khuyến nghị bằng ngôn ngữ tự nhiên, dễ hiểu, ví dụ: “Chất lượng tốt, giá thị trường đang ở mức cao, thời tiết thuận lợi — nên bán trong 3–5 ngày tới”.

Điểm khác biệt cốt lõi của dự án là ba module trên không hoạt động rời rạc mà liên kết chặt chẽ: kết quả đánh giá AI được sử dụng ngay để đăng bán và đưa ra khuyến nghị, tạo thành một quy trình khép kín từ đánh giá sản phẩm đến giao dịch.

# **5. CÔNG NGHỆ SỬ DỤNG**

- Backend & cơ sở dữ liệu: Laravel, MySQL.
- Trí tuệ nhân tạo: mô hình thị giác máy tính để phân loại/đánh giá ảnh sản phẩm; AI ngôn ngữ tự nhiên để tổng hợp khuyến nghị.
- Dữ liệu đầu vào: API thời tiết và nguồn dữ liệu giá nông sản tham khảo.
- Nền tảng truy cập: ứng dụng web tiến bộ (PWA), tối ưu cho điện thoại di động — phù hợp thói quen sử dụng của nông dân.

# **6. TÍNH MỚI / TÍNH SÁNG TẠO**

- Kết nối trực tiếp giữa AI đánh giá chất lượng và hành động đăng bán, thay vì là hai hệ thống tách biệt như các nền tảng hiện có.
- Khuyến nghị được trình bày bằng ngôn ngữ tự nhiên, gần gũi, dễ hiểu với nông dân — thay vì bảng biểu số liệu phức tạp.
- Mô hình tập trung vào nông sản đặc trưng địa phương, gắn với truy xuất nguồn gốc và câu chuyện sản phẩm OCOP.

# **7. TÍNH KHẢ THI**

## **7.1. Về mặt kỹ thuật**

Kiến trúc hệ thống có thể kế thừa kinh nghiệm từ các dự án cá nhân đã triển khai, bao gồm mô hình đa vai trò người dùng, tích hợp AI tạo sinh cho tư vấn/gợi ý, và xử lý theo thời gian thực — giúp rút ngắn thời gian phát triển MVP.

## **7.2. Về mặt vận hành**

Mô hình đơn giản, chi phí đầu tư ban đầu thấp, không yêu cầu hạ tầng phức tạp, phù hợp để triển khai thí điểm ở quy mô nhỏ trước khi nhân rộng.

# **8. MÔ HÌNH KINH DOANH**

- Thu phí giao dịch theo tỷ lệ phần trăm trên mỗi đơn hàng thành công qua nền tảng.
- Gói thành viên trả phí cho cơ sở OCOP/hợp tác xã muốn sử dụng module AI đánh giá chất lượng ở quy mô lớn.
- Gói dữ liệu thị trường (giá cả, xu hướng) cung cấp cho hợp tác xã hoặc đơn vị thu mua có nhu cầu.

# **9. KẾ HOẠCH TRIỂN KHAI**

|               |                       |                                                                                                                     |
| ------------- | --------------------- | ------------------------------------------------------------------------------------------------------------------- |
| **Giai đoạn** | **Thời gian dự kiến** | **Nội dung chính**                                                                                                  |
| GĐ1           | Tháng 1 – 2           | Xây dựng MVP: module marketplace cơ bản (đăng bán, tìm kiếm, đặt hàng) + module AI đánh giá chất lượng qua ảnh chụp |
| GĐ2           | Tháng 3 – 4           | Tích hợp module khuyến nghị thời điểm bán (thời tiết + giá thị trường), hoàn thiện truy xuất nguồn gốc QR           |
| GĐ3           | Tháng 5 – 6           | Thí điểm với 10–20 hộ nông dân/OCOP tại 1–2 huyện, thu thập phản hồi, tối ưu mô hình AI                             |
| GĐ4           | Sau 6 tháng           | Nhân rộng ra các huyện khác trong tỉnh, bổ sung thêm loại nông sản                                                  |

# **10. KẾT LUẬN / TÁC ĐỘNG KỲ VỌNG**

Dự án hướng đến việc tăng thu nhập và vị thế đàm phán cho nông dân thông qua kết nối trực tiếp với người mua; minh bạch hóa chuỗi cung ứng nông sản bằng công nghệ truy xuất nguồn gốc; đồng thời góp phần thúc đẩy quá trình chuyển đổi số trong nông nghiệp tại tỉnh Đồng Tháp. Đây là bước khởi đầu phù hợp với định hướng phát triển nông nghiệp xanh, bền vững và ứng dụng công nghệ số của địa phương.
