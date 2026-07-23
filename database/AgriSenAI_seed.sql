-- =====================================================
-- DỮ LIỆU MẪU (SEED DATA) - AGRISENAI
-- CSDL: agrisenai | MySQL 8+  (khớp DB_DATABASE trong .env)
-- Chạy SAU khi đã chạy migration (php artisan migrate)
-- Thứ tự insert tuân theo phụ thuộc khóa ngoại (FK)
-- Mật khẩu mẫu (đã hash bcrypt cost 12) = "password"
-- =====================================================

USE agrisenai;

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- =====================================================
-- 0. ROLES  (bắt buộc: users.role_id trỏ tới đây)
-- =====================================================
INSERT INTO roles (id, name) VALUES
(1, 'nong_dan'),
(2, 'nguoi_mua'),
(3, 'admin');

-- =====================================================
-- 1. USERS  (role_id: 1=nong_dan, 2=nguoi_mua, 3=admin)
-- Mật khẩu hash mẫu là bcrypt của chuỗi "password"
-- =====================================================
INSERT INTO users (id, role_id, name, email, phone, password, avatar, address, is_active) VALUES
-- Nông dân (role 1)
(1, 1, N'Nguyễn Văn Bảy',   'bay.xoai@agrisenai.vn',    '0913456701', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u1.jpg',  N'Ấp 2, xã Mỹ Xương, TP. Cao Lãnh, Đồng Tháp', TRUE),
(2, 1, N'Trần Thị Hồng',    'hong.quyt@agrisenai.vn',   '0913456702', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u2.jpg',  N'Ấp Long Hưng, xã Long Hậu, H. Lai Vung, Đồng Tháp', TRUE),
(3, 1, N'Lê Văn Tâm',       'tam.nhan@agrisenai.vn',    '0913456703', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u3.jpg',  N'Xã An Nhơn, H. Châu Thành, Đồng Tháp', TRUE),
(4, 1, N'Phạm Thị Loan',    'loan.sen@agrisenai.vn',    '0913456704', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u4.jpg',  N'Xã Tân Hội Trung, H. Cao Lãnh, Đồng Tháp', TRUE),
(5, 1, N'Võ Minh Đức',      'duc.saurieng@agrisenai.vn','0913456705', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u5.jpg',  N'Xã Tân Thuận Tây, TP. Cao Lãnh, Đồng Tháp', TRUE),
-- Người mua (role 2)
(6, 2, N'Đặng Quốc Huy',    'huy.buyer@gmail.com',      '0987654301', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u6.jpg',  N'123 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh', TRUE),
(7, 2, N'Bùi Thị Mai',      'mai.buyer@gmail.com',      '0987654302', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u7.jpg',  N'45 Lê Lợi, Q. Ninh Kiều, TP. Cần Thơ', TRUE),
(8, 2, N'Coop Food Miền Tây','coopfood@business.vn',    '0987654303', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u8.jpg',  N'Khu CN Sa Đéc, Đồng Tháp', TRUE),
-- Admin (role 3)
(9, 3, N'Quản trị AgriSenAI','admin@agrisenai.vn',      '0900000000', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u9.jpg',  N'Tiền Giang University', TRUE);

-- =====================================================
-- 2. FARMER_PROFILES  (user_id trỏ tới users role nong_dan)
-- =====================================================
INSERT INTO farmer_profiles (id, user_id, farm_name, ocop_certificate, ocop_star_rating, region, description) VALUES
(1, 1, N'Vườn xoài Cát Chu Mỹ Xương',   'OCOP-2023-DT-0157', 4,    N'Xã Mỹ Xương, TP. Cao Lãnh',   N'Vườn xoài Cát Chu & Cát Hòa Lộc canh tác theo VietGAP, 3 ha, thu hoạch quanh năm.'),
(2, 2, N'HTX Quýt hồng Lai Vung',        'OCOP-2022-DT-0089', 5,    N'Xã Long Hậu, H. Lai Vung',    N'Hợp tác xã quýt hồng đặc sản Tết, đạt OCOP 5 sao, canh tác truyền thống hơn 20 năm.'),
(3, 3, N'Nhà vườn nhãn Châu Thành',      'OCOP-2023-DT-0201', 3,    N'Xã An Nhơn, H. Châu Thành',   N'Nhãn Idor & nhãn xuồng cơm vàng, 2 ha, có mã số vùng trồng xuất khẩu.'),
(4, 4, N'Đồng Sen Tân Hội Trung',        NULL,                NULL, N'Xã Tân Hội Trung, H. Cao Lãnh',N'Trồng sen lấy hạt, ngó và gương sen; đang hoàn thiện hồ sơ OCOP.'),
(5, 5, N'Vườn sầu riêng Tân Thuận Tây',  'OCOP-2024-DT-0312', 4,    N'Xã Tân Thuận Tây, TP. Cao Lãnh',N'Sầu riêng Ri6 & Monthong, 1.5 ha, tưới nhỏ giọt tự động.');

-- =====================================================
-- 3. CATEGORIES
-- =====================================================
INSERT INTO categories (id, name, slug, description) VALUES
(1, N'Xoài',       'xoai',      N'Xoài Cát Chu, Cát Hòa Lộc - đặc sản Cao Lãnh, Đồng Tháp'),
(2, N'Quýt hồng',  'quyt-hong', N'Quýt hồng Lai Vung - đặc sản dịp Tết Nguyên Đán'),
(3, N'Nhãn',       'nhan',      N'Nhãn Idor, nhãn xuồng cơm vàng - Châu Thành'),
(4, N'Sen',        'sen',       N'Sản phẩm từ sen: hạt sen, ngó sen, gương sen'),
(5, N'Sầu riêng',  'sau-rieng', N'Sầu riêng Ri6, Monthong'),
(6, N'Bưởi',       'buoi',      N'Bưởi da xanh, bưởi năm roi');

-- =====================================================
-- 4. PRODUCTS  (farmer_id = users.id của nông dân)
-- =====================================================
INSERT INTO products (id, farmer_id, category_id, name, description, price, unit, quantity_available, qr_code, status) VALUES
(1, 1, 1, N'Xoài Cát Chu loại 1 Mỹ Xương',  N'Xoài Cát Chu chín cây, ngọt thanh, trọng lượng 350-450g/trái. Canh tác VietGAP.', 45000.00,  'kg',    500.00, 'QR-XCC-MX-0001', 'active'),
(2, 1, 1, N'Xoài Cát Hòa Lộc đặc biệt',      N'Xoài Cát Hòa Lộc size lớn 500-600g/trái, thịt vàng, hạt lép. Hàng tuyển xuất khẩu.', 75000.00, 'kg',    200.00, 'QR-XHL-MX-0002', 'active'),
(3, 2, 2, N'Quýt hồng Lai Vung loại tuyển',  N'Quýt hồng vỏ mỏng, mọng nước, màu cam đỏ đẹp. Đặt trước dịp Tết.', 60000.00, 'kg',    800.00, 'QR-QH-LV-0003', 'active'),
(4, 2, 2, N'Quýt hồng chưng Tết (nguyên cây)',N'Cây quýt hồng bonsai trĩu trái, cao 0.8-1.2m, dùng chưng Tết.', 1500000.00,'cây',   30.00,  'QR-QH-LV-0004', 'active'),
(5, 3, 3, N'Nhãn Idor Châu Thành',           N'Nhãn Idor cơm dày, hạt nhỏ, độ ngọt cao. Vùng trồng có mã số xuất khẩu.', 55000.00, 'kg',    350.00, 'QR-NH-CT-0005', 'active'),
(6, 3, 3, N'Nhãn xuồng cơm vàng',            N'Nhãn xuồng cơm vàng đặc sản, cùi giòn thơm.', 90000.00, 'kg',    120.00, 'QR-NH-CT-0006', 'sold_out'),
(7, 4, 4, N'Hạt sen tươi Tháp Mười',         N'Hạt sen tươi mới tách, bùi ngọt, dùng nấu chè, hầm.', 80000.00, 'kg',    150.00, 'QR-SEN-TM-0007', 'active'),
(8, 4, 4, N'Ngó sen tươi',                   N'Ngó sen non giòn, dùng làm gỏi.', 65000.00, 'kg',    90.00,  'QR-SEN-TM-0008', 'active'),
(9, 5, 5, N'Sầu riêng Ri6 loại 1',           N'Sầu riêng Ri6 cơm vàng, hạt lép, béo ngọt. Trọng lượng 2-3.5kg/trái.', 85000.00, 'kg',    400.00, 'QR-SR-TT-0009', 'active'),
(10,5, 5, N'Sầu riêng Monthong',             N'Sầu riêng Monthong cơm ráo, ngọt vừa, ít xơ.', 95000.00, 'kg',    180.00, 'QR-SR-TT-0010', 'active'),
(11,1, 6, N'Bưởi da xanh ruột hồng',         N'Bưởi da xanh trồng xen canh, ngọt thanh không hạt.', 40000.00, 'kg',    250.00, 'QR-BUOI-0011', 'draft'),
(12,2, 2, N'Quýt hồng loại 2 (giá sỉ)',      N'Quýt hồng size nhỏ hơn, phù hợp ép nước, bán sỉ số lượng lớn.', 42000.00, 'kg',    600.00, 'QR-QH-LV-0012', 'active');

-- =====================================================
-- 5. PRODUCT_IMAGES
-- =====================================================
INSERT INTO product_images (id, product_id, image_url, is_primary) VALUES
(1,  1, 'products/xoai-catchu-01.jpg', TRUE),
(2,  1, 'products/xoai-catchu-02.jpg', FALSE),
(3,  2, 'products/xoai-hoaloc-01.jpg', TRUE),
(4,  3, 'products/quyt-hong-01.jpg',   TRUE),
(5,  3, 'products/quyt-hong-02.jpg',   FALSE),
(6,  4, 'products/quyt-bonsai-01.jpg', TRUE),
(7,  5, 'products/nhan-idor-01.jpg',   TRUE),
(8,  6, 'products/nhan-xuong-01.jpg',  TRUE),
(9,  7, 'products/hat-sen-01.jpg',     TRUE),
(10, 8, 'products/ngo-sen-01.jpg',     TRUE),
(11, 9, 'products/saurieng-ri6-01.jpg',TRUE),
(12, 9, 'products/saurieng-ri6-02.jpg',FALSE),
(13, 10,'products/saurieng-mon-01.jpg',TRUE),
(14, 11,'products/buoi-daxanh-01.jpg', TRUE),
(15, 12,'products/quyt-loai2-01.jpg',  TRUE);

-- =====================================================
-- 6. AI_QUALITY_ASSESSMENTS  (kết quả Gemini Vision)
-- ripeness_level: xanh / vua_chin / chin / qua_chin
-- =====================================================
-- image_url trỏ tới ảnh thật trên public disk (storage/app/public/products/N.jpg)
-- image_sha256 = SHA-256 nội dung ảnh đó -> verify() trả "verified" (không phải mismatch)
INSERT INTO ai_quality_assessments (id, product_id, image_url, image_sha256, ripeness_level, quality_score, defect_detected, defect_note, raw_ai_response, assessed_at) VALUES
(1, 1, 'products/1.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'chin',     92.50, FALSE, NULL,
 '{"grade":"Đặc biệt","ripeness":"chin","quality_score":92.5,"usable":true,"confidence":0.94,"defects":[],"tcvn_override":null,"notes":"Vỏ vàng đều, không đốm đen, đạt Hạng Đặc biệt theo TCVN 9766:2013"}', '2026-07-15 08:12:00'),
(2, 1, 'products/1.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'vua_chin', 85.00, FALSE, NULL,
 '{"grade":"Hạng I","ripeness":"vua_chin","quality_score":85.0,"usable":true,"confidence":0.88,"defects":[],"tcvn_override":null,"notes":"Vừa chín tới, phù hợp vận chuyển xa"}', '2026-07-16 09:30:00'),
(3, 2, 'products/2.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'chin',    96.00, FALSE, NULL,
 '{"grade":"Đặc biệt","ripeness":"chin","quality_score":96.0,"usable":true,"confidence":0.97,"defects":[],"tcvn_override":null,"notes":"Size lớn, thịt vàng, hạt lép - hàng tuyển"}', '2026-07-16 10:05:00'),
(4, 3, 'products/3.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'chin',     88.75, TRUE,  N'Phát hiện 1-2 vết nám nhẹ trên vỏ, không ảnh hưởng ruột',
 '{"grade":"Hạng I","ripeness":"chin","quality_score":88.75,"usable":true,"confidence":0.85,"defects":["nám vỏ nhẹ"],"tcvn_override":"Hạng I do khuyết tật vỏ dưới 5%","notes":"Màu cam đỏ đẹp, vỏ có vài đốm nám nhỏ"}', '2026-07-17 07:45:00'),
(5, 5, 'products/5.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'vua_chin', 90.20, FALSE, NULL,
 '{"grade":"Hạng I","ripeness":"vua_chin","quality_score":90.2,"usable":true,"confidence":0.91,"defects":[],"tcvn_override":null,"notes":"Chùm đều, quả căng, cơm dày"}', '2026-07-18 08:00:00'),
(6, 6, 'products/6.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'qua_chin',68.00, TRUE, N'Một số quả nứt vỏ do quá chín, cần bán nhanh',
 '{"grade":"Hạng II","ripeness":"qua_chin","quality_score":68.0,"usable":true,"confidence":0.82,"defects":["nứt vỏ","quá chín"],"tcvn_override":"Hạng II","notes":"Cần tiêu thụ trong 1-2 ngày"}', '2026-07-19 14:20:00'),
(7, 9, 'products/9.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'chin',   94.00, FALSE, NULL,
 '{"grade":"Đặc biệt","ripeness":"chin","quality_score":94.0,"usable":true,"confidence":0.93,"defects":[],"tcvn_override":null,"notes":"Cơm vàng, hạt lép, độ chín lý tưởng để giao"}', '2026-07-20 09:10:00'),
(8, 2, 'products/2.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'xanh',     45.00, FALSE, N'Còn xanh, chưa đạt độ chín thu hoạch tối ưu',
 '{"grade":"Chưa đạt","ripeness":"xanh","quality_score":45.0,"usable":false,"confidence":0.89,"defects":["chưa chín"],"tcvn_override":null,"notes":"Nên chờ thêm 4-6 ngày trước khi thu hoạch"}', '2026-07-21 11:00:00');

-- Tính integrity_hash = SHA-256 chuỗi canonical (khớp App\Services\TraceabilityService::computeHash)
-- Thứ tự: qr_code | product_id | quality_score | ripeness_level | defect(1/0) | image_sha256 | farmer_id | assessed_at(ISO8601 UTC)
-- Dùng chính giá trị cột nên chuỗi khớp tuyệt đối với PHP -> verify() trả "verified".
UPDATE ai_quality_assessments a
JOIN products p ON p.id = a.product_id
SET a.integrity_hash = SHA2(CONCAT_WS('|',
        p.qr_code,
        a.product_id,
        a.quality_score,
        a.ripeness_level,
        IF(a.defect_detected, '1', '0'),
        a.image_sha256,
        p.farmer_id,
        DATE_FORMAT(a.assessed_at, '%Y-%m-%dT%H:%i:%s+00:00')
    ), 256);

-- =====================================================
-- 7. WEATHER_DATA_CACHE
-- =====================================================
INSERT INTO weather_data_cache (id, region, date, temperature, humidity, rain_forecast, fetched_at) VALUES
(1, N'TP. Cao Lãnh',   '2026-07-22', 33.50, 78.00, N'Chiều có mưa rào rải rác',           '2026-07-22 06:00:00'),
(2, N'TP. Cao Lãnh',   '2026-07-23', 34.00, 75.00, N'Ngày nắng, không mưa',               '2026-07-23 06:00:00'),
(3, N'H. Lai Vung',    '2026-07-22', 32.80, 80.00, N'Mưa dông về chiều tối',              '2026-07-22 06:05:00'),
(4, N'H. Lai Vung',    '2026-07-23', 33.20, 77.00, N'Nắng gián đoạn',                     '2026-07-23 06:05:00'),
(5, N'H. Châu Thành',  '2026-07-23', 33.00, 76.00, N'Ngày nắng nóng',                     '2026-07-23 06:10:00'),
(6, N'H. Cao Lãnh',    '2026-07-23', 33.80, 74.00, N'Không mưa, độ ẩm giảm',              '2026-07-23 06:15:00');

-- =====================================================
-- 8. MARKET_PRICE_REFERENCE  (giá thị trường tham khảo)
-- =====================================================
INSERT INTO market_price_reference (id, category_id, region, price, price_date, source) VALUES
(1, 1, N'TP. Cao Lãnh',  43000.00, '2026-07-22', N'Chợ đầu mối nông sản Cao Lãnh'),
(2, 1, N'TP. Hồ Chí Minh',52000.00, '2026-07-22', N'Chợ đầu mối Thủ Đức'),
(3, 2, N'H. Lai Vung',   58000.00, '2026-07-22', N'Thương lái địa phương Lai Vung'),
(4, 3, N'H. Châu Thành', 53000.00, '2026-07-22', N'Vựa nhãn Châu Thành'),
(5, 5, N'TP. Cao Lãnh',  82000.00, '2026-07-22', N'Chợ đầu mối nông sản Cao Lãnh'),
(6, 4, N'H. Cao Lãnh',   78000.00, '2026-07-22', N'HTX Sen Tháp Mười');

-- =====================================================
-- 9. PRICE_RECOMMENDATIONS
-- recommended_action: ban_ngay / cho_them / giam_gia
-- =====================================================
INSERT INTO price_recommendations (id, product_id, weather_ref_id, market_price_ref_id, recommended_action, reasoning_text, valid_until, created_at) VALUES
(1, 1, 2, 1, 'ban_ngay',
 N'Giá thị trường Cao Lãnh đang ở 43.000đ/kg, giá bán của bạn 45.000đ/kg vẫn cạnh tranh. Thời tiết 2 ngày tới nắng ráo, xoài đã chín tới (điểm AI 92.5) - nên bán ngay để tránh chín quá.', '2026-07-25', '2026-07-23 07:00:00'),
(2, 3, 4, 3, 'cho_them',
 N'Quýt hồng đang vào vụ nhưng cầu tăng mạnh dịp cận Tết. Giá thị trường 58.000đ/kg và có xu hướng tăng. Sản phẩm còn tươi (điểm AI 88.75), khuyến nghị chờ thêm 5-7 ngày để bán được giá cao hơn.', '2026-07-30', '2026-07-23 07:05:00'),
(3, 6, NULL, NULL, 'giam_gia',
 N'Nhãn xuồng đã quá chín (điểm AI 68, có nứt vỏ), cần tiêu thụ gấp trong 1-2 ngày. Khuyến nghị giảm giá 15-20% để đẩy hàng nhanh, tránh hư hỏng toàn bộ lô.', '2026-07-24', '2026-07-23 07:10:00'),
(4, 9, 6, 5, 'ban_ngay',
 N'Sầu riêng Ri6 đạt độ chín lý tưởng (điểm AI 94). Giá thị trường 82.000đ/kg, giá bán 85.000đ/kg hợp lý cho hàng loại 1. Nắng ráo thuận lợi vận chuyển - nên chốt đơn và giao ngay.', '2026-07-26', '2026-07-23 07:15:00');

-- =====================================================
-- 10. ORDERS  (buyer_id = users.id của người mua)
-- =====================================================
INSERT INTO orders (id, buyer_id, order_code, total_amount, status, shipping_address, note, created_at) VALUES
(1, 6, 'AGS-20260718-0001', 900000.00,  'completed', N'123 Nguyễn Huệ, Quận 1, TP. HCM',   N'Giao giờ hành chính', '2026-07-18 10:30:00'),
(2, 7, 'AGS-20260719-0002', 1275000.00, 'shipping',  N'45 Lê Lợi, Q. Ninh Kiều, Cần Thơ',  N'Gọi trước khi giao',  '2026-07-19 14:00:00'),
(3, 8, 'AGS-20260720-0003', 12600000.00,'confirmed', N'Khu CN Sa Đéc, Đồng Tháp',           N'Đơn sỉ, xuất hóa đơn VAT', '2026-07-20 09:15:00'),
(4, 6, 'AGS-20260722-0004', 340000.00,  'pending',   N'123 Nguyễn Huệ, Quận 1, TP. HCM',   NULL, '2026-07-22 16:45:00'),
(5, 7, 'AGS-20260723-0005', 475000.00,  'cancelled', N'45 Lê Lợi, Q. Ninh Kiều, Cần Thơ',  N'Khách đổi ý huỷ đơn',  '2026-07-23 08:20:00');

-- =====================================================
-- 11. ORDER_ITEMS
-- =====================================================
INSERT INTO order_items (id, order_id, product_id, quantity, unit_price, subtotal) VALUES
-- Đơn 1: 20kg xoài Cát Chu
(1,  1, 1,  20.00, 45000.00,  900000.00),
-- Đơn 2: 15kg quýt hồng + 5kg nhãn Idor
(2,  2, 3,  15.00, 60000.00,  900000.00),
(3,  2, 5,  5.00,  55000.00,  275000.00),
(4,  2, 7,  1.25,  80000.00,  100000.00),
-- Đơn 3 (sỉ): 200kg sầu riêng Ri6 (giá sỉ 63k)
(5,  3, 9,  200.00,63000.00,  12600000.00),
-- Đơn 4: 5kg xoài Hòa Lộc + 1kg hạt sen (đang chờ)
(6,  4, 2,  4.00,  75000.00,  300000.00),
(7,  4, 7,  0.50,  80000.00,  40000.00),
-- Đơn 5 (đã huỷ): 5kg sầu riêng Monthong
(8,  5, 10, 5.00,  95000.00,  475000.00);

-- =====================================================
-- 12. TRACEABILITY_LOGS  (truy xuất nguồn gốc theo QR)
-- =====================================================
INSERT INTO traceability_logs (id, product_id, stage, description, location, happened_at) VALUES
(1, 1, 'thu_hoach',   N'Thu hoạch xoài Cát Chu chín cây, chọn trái đạt chuẩn size', N'Vườn Mỹ Xương, Cao Lãnh', '2026-07-14 06:30:00'),
(2, 1, 'dong_goi',    N'Phân loại, lau sạch, đóng thùng carton 10kg',              N'Xưởng sơ chế Mỹ Xương',   '2026-07-14 15:00:00'),
(3, 1, 'dang_ban',    N'Đăng bán trên nền tảng AgriSenAI',                          N'Online',                  '2026-07-15 08:00:00'),
(4, 3, 'thu_hoach',   N'Thu hoạch quýt hồng, cắt cuống giữ tươi',                   N'HTX Long Hậu, Lai Vung',  '2026-07-16 07:00:00'),
(5, 3, 'dong_goi',    N'Đóng gói thùng lưới 5kg, dán tem truy xuất',                N'Kho HTX Lai Vung',        '2026-07-16 16:30:00'),
(6, 9, 'thu_hoach',   N'Thu hoạch sầu riêng Ri6 đạt độ chín, buộc cuống đánh dấu',  N'Vườn Tân Thuận Tây',      '2026-07-19 06:00:00'),
(7, 9, 'van_chuyen',  N'Vận chuyển lạnh về kho phân phối',                          N'Xe lạnh - QL30',          '2026-07-20 05:00:00'),
(8, 9, 'dang_ban',    N'Sẵn sàng giao cho đơn sỉ AGS-20260720-0003',               N'Kho Sa Đéc',              '2026-07-20 10:00:00');

-- =====================================================
-- 13. REVIEWS  (chỉ buyer đã mua mới đánh giá)
-- =====================================================
INSERT INTO reviews (id, product_id, buyer_id, rating, comment, created_at) VALUES
(1, 1, 6, 5, N'Xoài ngọt, chín đều, đóng gói cẩn thận. Sẽ ủng hộ tiếp!',       '2026-07-19 20:15:00'),
(2, 3, 7, 4, N'Quýt tươi ngon, có vài trái vỏ hơi nám nhưng ruột ngọt.',        '2026-07-21 19:00:00'),
(3, 5, 7, 5, N'Nhãn Idor cơm dày hạt nhỏ, đúng như mô tả.',                     '2026-07-21 19:05:00'),
(4, 9, 8, 5, N'Sầu riêng loại 1 chuẩn, cơm vàng béo. Giao đúng hẹn cho đơn sỉ.', '2026-07-22 11:30:00'),
(5, 7, 7, 4, N'Hạt sen tươi, hạt to bùi, nấu chè rất thơm. Đóng gói kỹ.',        '2026-07-21 20:30:00');

-- =====================================================
-- 14. NOTIFICATIONS
-- type: order / ai_result / price_alert
-- =====================================================
INSERT INTO notifications (id, user_id, message, type, is_read) VALUES
(1, 1, N'Xoài Cát Chu của bạn được AI chấm 92.5 điểm - Hạng Đặc biệt theo TCVN.', 'ai_result',  TRUE),
(2, 1, N'Khuyến nghị: Nên BÁN NGAY xoài Cát Chu, giá thị trường đang thuận lợi.',  'price_alert',FALSE),
(3, 2, N'Khuyến nghị: Nên CHỜ THÊM 5-7 ngày để bán quýt hồng được giá cao hơn.',   'price_alert',FALSE),
(4, 1, N'Đơn hàng AGS-20260718-0001 đã hoàn thành. Bạn nhận được đánh giá 5 sao!', 'order',      TRUE),
(5, 5, N'Sầu riêng Ri6 được AI chấm 94 điểm. Đơn sỉ AGS-20260720-0003 đã xác nhận.','order',     FALSE),
(6, 3, N'Cảnh báo: Nhãn xuồng quá chín, nên GIẢM GIÁ để tiêu thụ nhanh.',          'price_alert',FALSE),
(7, 6, N'Đơn hàng AGS-20260722-0004 của bạn đang chờ xác nhận từ người bán.',      'order',      FALSE),
(8, 8, N'Đơn sỉ AGS-20260720-0003 (12.6 triệu) đã được xác nhận.',                 'order',      TRUE);

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- CẬP NHẬT AUTO_INCREMENT (tránh trùng ID khi thêm mới)
-- =====================================================
ALTER TABLE roles                  AUTO_INCREMENT = 4;
ALTER TABLE users                  AUTO_INCREMENT = 10;
ALTER TABLE farmer_profiles        AUTO_INCREMENT = 6;
ALTER TABLE categories             AUTO_INCREMENT = 7;
ALTER TABLE products               AUTO_INCREMENT = 13;
ALTER TABLE product_images         AUTO_INCREMENT = 16;
ALTER TABLE ai_quality_assessments AUTO_INCREMENT = 9;
ALTER TABLE weather_data_cache     AUTO_INCREMENT = 7;
ALTER TABLE market_price_reference AUTO_INCREMENT = 7;
ALTER TABLE price_recommendations  AUTO_INCREMENT = 5;
ALTER TABLE orders                 AUTO_INCREMENT = 6;
ALTER TABLE order_items            AUTO_INCREMENT = 9;
ALTER TABLE traceability_logs      AUTO_INCREMENT = 9;
ALTER TABLE reviews                AUTO_INCREMENT = 6;
ALTER TABLE notifications          AUTO_INCREMENT = 9;

-- HẾT SEED DATA
