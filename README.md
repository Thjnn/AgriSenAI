# Thuyết Minh Dự Án: Nền Tảng Số Hóa Nông Sản Đồng Tháp

## 1. Tổng quan dự án

- [cite_start]**Tên dự án:** AgriSenAI[cite: 5].
- [cite_start]**Mô tả:** Sàn thương mại điện tử kết nối trực tiếp nông dân, hộ OCOP với người mua, tích hợp AI đánh giá chất lượng và khuyến nghị thời điểm bán[cite: 8].
- **Vấn đề giải quyết:**
    - [cite_start]Phụ thuộc thương lái, bị ép giá[cite: 9].
    - [cite_start]Thiếu công cụ đánh giá chất lượng khách quan[cite: 10].
    - [cite_start]Thiếu thông tin thị trường/thời tiết để tối ưu thời điểm bán[cite: 11].

## 2. Tính đổi mới sáng tạo

- [cite_start]**Công nghệ:** Sử dụng Computer Vision và Gemini AI để tự động đánh giá chất lượng qua hình ảnh (độ chín, hư hỏng, chấm điểm)[cite: 14].
- [cite_start]**Trải nghiệm:** Tích hợp trực tiếp kết quả AI vào luồng đăng bán và khuyến nghị thời điểm bán bằng ngôn ngữ tự nhiên[cite: 15, 16].
- [cite_start]**Nền tảng:** Ứng dụng web tiến bộ (PWA), không cần cài đặt qua store, tích hợp QR truy xuất nguồn gốc[cite: 17].

## 3. Mô hình kinh doanh (Rút gọn)

| Đối tác chính                  | Hoạt động chính                            | Giá trị mang lại                                                                 |
| :----------------------------- | :----------------------------------------- | :------------------------------------------------------------------------------- |
| HTX, hộ OCOP, API Thời tiết/AI | Vận hành sàn, đánh giá AI, hỗ trợ đăng bán | [cite_start]Bán trực tiếp, minh bạch chất lượng, tối ưu thời điểm bán [cite: 43] |

| Dòng doanh thu                                                                      | Cơ cấu chi phí                                                                |
| :---------------------------------------------------------------------------------- | :---------------------------------------------------------------------------- |
| [cite_start]Phí giao dịch (3–5%), gói AI trả phí, gói dữ liệu thị trường [cite: 44] | [cite_start]Hạ tầng server, phí API, vận hành & bảo trì, marketing [cite: 44] |

## 4. Kỹ thuật & Phát triển

- [cite_start]**Tech Stack:** Laravel, MySQL, Gemini Vision API, API dữ liệu thời tiết, PWA[cite: 34].
- [cite_start]**Hiện trạng:** Đã có thuyết minh, thiết kế CSDL (15 bảng/5 nhóm), giao diện demo (Marketplace, AI module, QR)[cite: 46].
- **Lộ trình:**
    - [cite_start]6 tháng: Hoàn thiện MVP, thí điểm 10-20 hộ[cite: 47].
    - [cite_start]1 năm: Nhân rộng toàn tỉnh, bắt đầu thu phí[cite: 47].
    - [cite_start]3 năm: Mở rộng ra ĐBSCL, phát triển gói dữ liệu chuyên sâu[cite: 47].

## 5. Tác động xã hội (SDGs)

- [cite_start]**SDG 1:** Xóa nghèo (tăng thu nhập trực tiếp)[cite: 50].
- [cite_start]**SDG 8:** Việc làm bền vững và tăng trưởng kinh tế[cite: 51].
- [cite_start]**SDG 9:** Đổi mới sáng tạo trong nông nghiệp[cite: 52].
- [cite_start]**SDG 12:** Tiêu dùng và sản xuất có trách nhiệm (truy xuất nguồn gốc)[cite: 53].
- [cite_start]**SDG 17:** Quan hệ đối tác phát triển[cite: 54].
