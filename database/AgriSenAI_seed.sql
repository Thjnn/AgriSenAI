-- =====================================================
-- DỮ LIỆU MẪU (SEED DATA) - AGRISENAI
-- CSDL: agrisenai | MySQL 8+  (khớp DB_DATABASE trong .env)
-- Chạy SAU khi đã chạy migration (php artisan migrate)
-- Thứ tự insert tuân theo phụ thuộc khóa ngoại (FK)
-- Phạm vi: CHỈ XOÀI (chốt 30/09/2026) — 5 nhà vườn/HTX xoài, 3 danh mục, 12 lô
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
-- Nông dân (role 1) — dự án chỉ tập trung XOÀI (chốt 30/09/2026)
(1, 1, N'Nguyễn Văn Bảy',   'bay.xoai@agrisenai.vn',       '0913456701', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u1.jpg',  N'Ấp 2, xã Mỹ Xương, H. Cao Lãnh, Đồng Tháp', TRUE),
(2, 1, N'Trần Thị Hồng',    'hong.xoaikeo@agrisenai.vn',   '0913456702', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u2.jpg',  N'Ấp Tịnh Mỹ, xã Tịnh Thới, TP. Cao Lãnh, Đồng Tháp', TRUE),
(3, 1, N'Lê Văn Tâm',       'tam.xoaituquy@agrisenai.vn',  '0913456703', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u3.jpg',  N'Xã Tân Thuận Đông, TP. Cao Lãnh, Đồng Tháp', TRUE),
(4, 1, N'Phạm Thị Loan',    'loan.xoaisay@agrisenai.vn',   '0913456704', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u4.jpg',  N'Phường Hòa An, TP. Cao Lãnh, Đồng Tháp', TRUE),
(5, 1, N'Võ Minh Đức',      'duc.htxmyxuong@agrisenai.vn', '0913456705', '$2y$12$m5KZ5SeprRibr5W1Gym0..W5IHskkNNKLtEbr921obAB31pNvaRue', 'avatars/u5.jpg',  N'Xã Mỹ Xương, H. Cao Lãnh, Đồng Tháp', TRUE),
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
(1, 1, N'Vườn xoài Cát Chu Mỹ Xương',      'OCOP-2023-DT-0157', 4,    N'Xã Mỹ Xương, H. Cao Lãnh',        N'Vườn xoài Cát Chu & Cát Hòa Lộc canh tác theo VietGAP, 3 ha, thu hoạch quanh năm.'),
(2, 2, N'Vườn xoài Keo Tịnh Thới',         NULL,                NULL, N'Xã Tịnh Thới, TP. Cao Lãnh',      N'Xoài Keo, xoài tượng, Thanh Ca chuyên bán ăn xanh cho chợ đầu mối và bếp ăn, 2.5 ha.'),
(3, 3, N'Vườn xoài Tứ Quý Tân Thuận Đông', 'OCOP-2023-DT-0201', 3,    N'Xã Tân Thuận Đông, TP. Cao Lãnh', N'Xoài Tứ Quý ra trái nghịch vụ, 2 ha, bao trái từ sớm để giảm sâu bệnh.'),
(4, 4, N'Cơ sở xoài sấy dẻo Hòa An',       'OCOP-2024-DT-0277', 4,    N'Phường Hòa An, TP. Cao Lãnh',     N'Chế biến xoài sấy dẻo, mứt xoài từ xoài loại 2 của các vườn lân cận — giảm hao hụt sau thu hoạch.'),
(5, 5, N'HTX Xoài Mỹ Xương',               'OCOP-2022-DT-0089', 5,    N'Xã Mỹ Xương, H. Cao Lãnh',        N'Hợp tác xã xoài Cát Hòa Lộc, 45 thành viên, có mã số vùng trồng xuất khẩu, xe lạnh riêng.');

-- =====================================================
-- 3. CATEGORIES
-- =====================================================
INSERT INTO categories (id, name, slug, description) VALUES
(1, N'Xoài ăn chín',  'xoai',          N'Xoài Cát Chu, Cát Hòa Lộc, Tứ Quý, Đài Loan - đặc sản Cao Lãnh, Đồng Tháp'),
(2, N'Xoài ăn xanh',  'xoai-xanh',     N'Xoài Keo, xoài tượng, Thanh Ca - bán để ăn xanh, chấm muối ớt'),
(3, N'Xoài chế biến', 'xoai-che-bien', N'Xoài sấy dẻo, mứt xoài từ xoài Đồng Tháp');

-- =====================================================
-- 4. PRODUCTS  (farmer_id = users.id của nông dân)
-- =====================================================
INSERT INTO products (id, farmer_id, category_id, name, description, price, unit, quantity_available, qr_code, status) VALUES
(1, 1, 1, N'Xoài Cát Chu loại 1 Mỹ Xương',     N'Xoài Cát Chu chín cây, ngọt thanh, trọng lượng 350-450g/trái. Canh tác VietGAP.', 45000.00,  'kg',  500.00, 'QR-XCC-MX-0001',  'active'),
(2, 1, 1, N'Xoài Cát Hòa Lộc đặc biệt',         N'Xoài Cát Hòa Lộc size lớn 500-600g/trái, thịt vàng, hạt lép. Hàng tuyển.', 75000.00,  'kg',  200.00, 'QR-XHL-MX-0002',  'active'),
(3, 2, 2, N'Xoài Keo ăn xanh Tịnh Thới',        N'Xoài Keo già, giòn, chua nhẹ, chuyên ăn xanh chấm muối ớt. 300-400g/trái.', 22000.00,  'kg',  800.00, 'QR-XK-TT-0003',   'active'),
(4, 2, 2, N'Xoài Keo loại 2 (giá sỉ)',          N'Xoài Keo size nhỏ, vỏ có nám nhẹ, phù hợp bếp ăn / làm gỏi, bán sỉ số lượng lớn.', 15000.00, 'kg', 1500.00, 'QR-XK-TT-0004', 'active'),
(5, 3, 1, N'Xoài Tứ Quý Tân Thuận Đông',        N'Xoài Tứ Quý nghịch vụ, vỏ xanh-vàng khi chín, thịt dẻo thơm.', 35000.00,  'kg',  350.00, 'QR-XTQ-TD-0005',  'active'),
(6, 3, 1, N'Xoài Tứ Quý chín cây (bán nhanh)',  N'Lô chín cây, một số trái đã quá chín — giá giảm để tiêu thụ trong ngày.', 25000.00,  'kg',  120.00, 'QR-XTQ-TD-0006',  'sold_out'),
(7, 4, 3, N'Xoài sấy dẻo Hòa An',               N'Sấy dẻo từ xoài Cát Chu chín, không đường hóa học, túi zip 500g.', 180000.00, 'kg',  150.00, 'QR-XSD-HA-0007',  'active'),
(8, 4, 3, N'Mứt xoài Cao Lãnh',                 N'Mứt xoài sên thủ công, vị chua ngọt, hộp 300g.', 120000.00, 'kg',   90.00, 'QR-MX-HA-0008',   'active'),
(9, 5, 1, N'Xoài Cát Hòa Lộc xuất khẩu',        N'Hàng tuyển xuất khẩu của HTX: 450-600g/trái, bao trái, có mã số vùng trồng, vận chuyển lạnh.', 85000.00, 'kg', 400.00, 'QR-XHL-HTX-0009', 'active'),
(10,5, 1, N'Xoài Đài Loan HTX Mỹ Xương',        N'Xoài Đài Loan trái to 700-900g, thịt dày, ít xơ.', 30000.00,  'kg',  180.00, 'QR-XDL-HTX-0010', 'active'),
(11,1, 2, N'Xoài tượng da xanh',                N'Xoài tượng trái to, da xanh, giòn, dùng làm gỏi.', 28000.00,  'kg',  250.00, 'QR-XT-MX-0011',   'draft'),
(12,2, 2, N'Xoài Thanh Ca ăn xanh',             N'Xoài Thanh Ca giòn ngọt nhẹ khi còn xanh, trái thon dài.', 20000.00,  'kg',  600.00, 'QR-XTC-TT-0012',  'active');

-- =====================================================
-- 5. PRODUCT_IMAGES
-- =====================================================
INSERT INTO product_images (id, product_id, image_url, is_primary) VALUES
(1,  1, 'products/xoai-catchu-01.jpg',    TRUE),
(2,  1, 'products/xoai-catchu-02.jpg',    FALSE),
(3,  2, 'products/xoai-hoaloc-01.jpg',    TRUE),
(4,  3, 'products/xoai-keo-01.jpg',       TRUE),
(5,  3, 'products/xoai-keo-02.jpg',       FALSE),
(6,  4, 'products/xoai-keo-loai2-01.jpg', TRUE),
(7,  5, 'products/xoai-tuquy-01.jpg',     TRUE),
(8,  6, 'products/xoai-tuquy-02.jpg',     TRUE),
(9,  7, 'products/xoai-say-deo-01.jpg',   TRUE),
(10, 8, 'products/mut-xoai-01.jpg',       TRUE),
(11, 9, 'products/xoai-hoaloc-xk-01.jpg', TRUE),
(12, 9, 'products/xoai-hoaloc-xk-02.jpg', FALSE),
(13, 10,'products/xoai-dailoan-01.jpg',   TRUE),
(14, 11,'products/xoai-tuong-01.jpg',     TRUE),
(15, 12,'products/xoai-thanhca-01.jpg',   TRUE);

-- =====================================================
-- 6. AI_QUALITY_ASSESSMENTS  (kết quả Gemini Vision)
-- ripeness_level: xanh / vua_chin / chin / qua_chin
-- =====================================================
-- image_url trỏ tới ảnh thật trên public disk (storage/app/public/products/N.jpg)
-- image_sha256 = SHA-256 nội dung ảnh đó -> verify() trả "verified" (không phải mismatch)
-- raw_ai_response theo đúng shape QualityAssessor trả về (grade = key trong config agrisenai.grades).
-- raw_ai_response KHÔNG nằm trong integrity_hash -> đổi nội dung ở đây không làm hỏng verify().
INSERT INTO ai_quality_assessments (id, product_id, image_url, image_sha256, ripeness_level, quality_score, defect_detected, defect_note, raw_ai_response, assessed_at) VALUES
(1, 1, 'products/1.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'chin', 92.50, FALSE, NULL,
 '{"usable":true,"detected_product":"Xoài","grade":"dac_biet","overall_score":9.25,"confidence":0.94,"muc_dich_ban":"an_chin","defects":[],"reasoning":"Vỏ vàng đều, không đốm đen, dáng cân đối — đạt hạng đặc biệt theo TCVN 9766:2013."}', '2026-07-15 08:12:00'),
(2, 1, 'products/1.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'vua_chin', 85.00, FALSE, NULL,
 '{"usable":true,"detected_product":"Xoài","grade":"hang_1","overall_score":8.5,"confidence":0.88,"muc_dich_ban":"an_chin","defects":[],"reasoning":"Vừa chín tới, vỏ sạch — hạng I, phù hợp vận chuyển xa."}', '2026-07-16 09:30:00'),
(3, 2, 'products/2.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'chin', 96.00, FALSE, NULL,
 '{"usable":true,"detected_product":"Xoài","grade":"dac_biet","overall_score":9.6,"confidence":0.97,"muc_dich_ban":"an_chin","defects":[],"reasoning":"Size lớn, vỏ vàng sáng, không khuyết tật — hàng tuyển."}', '2026-07-16 10:05:00'),
(4, 3, 'products/3.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'xanh', 88.75, TRUE, N'nam_nang ~4%',
 '{"usable":true,"detected_product":"Xoài","grade":"hang_1","overall_score":8.875,"confidence":0.85,"muc_dich_ban":"an_xanh","defects":[{"type":"nam_nang","severity":"nhe","area_percent":4,"note":"Vài đốm nám nắng nhỏ trên vai trái"}],"reasoning":"Bán ăn xanh: vỏ xanh đậm, căng, thịt chắc là đúng yêu cầu. Nám nắng ~4% bề mặt, chỉ ở vỏ — hạng I."}', '2026-07-17 07:45:00'),
(5, 5, 'products/5.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'vua_chin', 90.20, FALSE, NULL,
 '{"usable":true,"detected_product":"Xoài","grade":"dac_biet","overall_score":9.02,"confidence":0.91,"muc_dich_ban":"an_chin","defects":[],"reasoning":"Tứ Quý chín tới vẫn còn vỏ xanh-vàng — đặc trưng giống, không trừ điểm độ chín. Vỏ sạch, trái đều."}', '2026-07-18 08:00:00'),
(6, 6, 'products/6.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'qua_chin', 68.00, TRUE, N'nut ~3%; tham ~8%',
 '{"usable":true,"detected_product":"Xoài","grade":"hang_2","overall_score":6.8,"confidence":0.82,"muc_dich_ban":"an_chin","defects":[{"type":"nut","severity":"nhe","area_percent":3,"note":"Nứt nhỏ ở cuống do quá chín"},{"type":"tham","severity":"trung_binh","area_percent":8,"note":"Vết thâm do va chạm khi thu hoạch"}],"reasoning":"Quá chín, có thâm ~8% và nứt nhỏ ở cuống — hạng II, cần tiêu thụ trong 1-2 ngày."}', '2026-07-19 14:20:00'),
(7, 9, 'products/9.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'chin', 94.00, FALSE, NULL,
 '{"usable":true,"detected_product":"Xoài","grade":"dac_biet","overall_score":9.4,"confidence":0.93,"muc_dich_ban":"an_chin","defects":[],"reasoning":"Bao trái nên vỏ sạch, vàng đều, dáng chuẩn — đạt hạng đặc biệt, phù hợp xuất khẩu."}', '2026-07-20 09:10:00'),
(8, 2, 'products/2.jpg', '61ae917b4310d94114cce773a42a687bf1078f287496072a72cf230dab7eeb6f', 'xanh', 45.00, FALSE, N'Còn xanh, chưa đạt độ chín thu hoạch tối ưu',
 '{"usable":true,"detected_product":"Xoài","grade":"khong_dat","overall_score":4.5,"confidence":0.89,"muc_dich_ban":"an_chin","defects":[],"reasoning":"Lô đăng bán để ăn chín nhưng trái còn xanh non — chưa đạt, nên chờ thêm 4-6 ngày trước khi thu hoạch."}', '2026-07-21 11:00:00');

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
(1, N'TP. Cao Lãnh', '2026-07-22', 33.50, 78.00, N'Chiều có mưa rào rải rác', '2026-07-22 06:00:00'),
(2, N'TP. Cao Lãnh', '2026-07-23', 34.00, 75.00, N'Ngày nắng, không mưa',     '2026-07-23 06:00:00'),
(3, N'H. Cao Lãnh',  '2026-07-22', 32.80, 80.00, N'Mưa dông về chiều tối',    '2026-07-22 06:05:00'),
(4, N'H. Cao Lãnh',  '2026-07-23', 33.80, 74.00, N'Không mưa, độ ẩm giảm',    '2026-07-23 06:05:00'),
(5, N'H. Tháp Mười', '2026-07-23', 33.00, 76.00, N'Ngày nắng nóng',           '2026-07-23 06:10:00'),
(6, N'TP. Sa Đéc',   '2026-07-23', 33.20, 77.00, N'Nắng gián đoạn',           '2026-07-23 06:15:00');

-- =====================================================
-- 8. MARKET_PRICE_REFERENCE  (giá thị trường tham khảo)
-- =====================================================
INSERT INTO market_price_reference (id, category_id, region, price, price_date, source) VALUES
(1, 1, N'TP. Cao Lãnh',    43000.00, '2026-07-22', N'Chợ đầu mối nông sản Cao Lãnh'),
(2, 1, N'TP. Hồ Chí Minh', 52000.00, '2026-07-22', N'Chợ đầu mối Thủ Đức'),
(3, 2, N'TP. Cao Lãnh',    20000.00, '2026-07-22', N'Thương lái xoài ăn xanh Tịnh Thới'),
(4, 2, N'TP. Hồ Chí Minh', 26000.00, '2026-07-22', N'Chợ Bình Điền'),
(5, 1, N'H. Cao Lãnh',     80000.00, '2026-07-22', N'Giá thu mua Cát Hòa Lộc xuất khẩu - doanh nghiệp thu mua'),
(6, 3, N'TP. Cao Lãnh',   170000.00, '2026-07-22', N'Cửa hàng OCOP Đồng Tháp');

-- =====================================================
-- 9. PRICE_RECOMMENDATIONS
-- recommended_action: ban_ngay / cho_them / giam_gia
-- =====================================================
INSERT INTO price_recommendations (id, product_id, weather_ref_id, market_price_ref_id, recommended_action, reasoning_text, valid_until, created_at) VALUES
(1, 1, 2, 1, 'ban_ngay',
 N'Giá thị trường Cao Lãnh đang ở 43.000đ/kg, giá bán của bạn 45.000đ/kg vẫn cạnh tranh. Thời tiết 2 ngày tới nắng ráo, xoài đã chín tới (điểm AI 92.5) - nên bán ngay để tránh chín quá.', '2026-07-25', '2026-07-23 07:00:00'),
(2, 3, 2, 3, 'cho_them',
 N'Xoài Keo ăn xanh đang được giá 20.000đ/kg tại vườn và 26.000đ/kg ở chợ Bình Điền, nhu cầu tăng dần. Trái còn xanh chắc (điểm AI 88.75) nên giữ được lâu - có thể chờ thêm 3-5 ngày để chốt giá tốt hơn.', '2026-07-28', '2026-07-23 07:05:00'),
(3, 6, NULL, NULL, 'giam_gia',
 N'Xoài Tứ Quý đã quá chín (điểm AI 68, có thâm và nứt nhỏ ở cuống), cần tiêu thụ gấp trong 1-2 ngày. Khuyến nghị giảm giá 15-20% hoặc chuyển cho cơ sở sấy dẻo để tránh hư hỏng toàn bộ lô.', '2026-07-24', '2026-07-23 07:10:00'),
(4, 9, 4, 5, 'ban_ngay',
 N'Xoài Cát Hòa Lộc đạt hạng đặc biệt (điểm AI 94). Giá thu mua xuất khẩu 80.000đ/kg, giá bán 85.000đ/kg hợp lý cho hàng tuyển có mã số vùng trồng. Trời không mưa, thuận lợi vận chuyển lạnh - nên chốt đơn và giao ngay.', '2026-07-26', '2026-07-23 07:15:00');

-- =====================================================
-- 10. ORDERS  (buyer_id = users.id của người mua)
-- =====================================================
INSERT INTO orders (id, buyer_id, order_code, total_amount, status, shipping_address, note, created_at) VALUES
(1, 6, 'AGS-20260718-0001', 900000.00,   'completed', N'123 Nguyễn Huệ, Quận 1, TP. HCM',  N'Giao giờ hành chính',       '2026-07-18 10:30:00'),
(2, 7, 'AGS-20260719-0002', 865000.00,   'shipping',  N'45 Lê Lợi, Q. Ninh Kiều, Cần Thơ', N'Gọi trước khi giao',        '2026-07-19 14:00:00'),
(3, 8, 'AGS-20260720-0003', 12600000.00, 'confirmed', N'Khu CN Sa Đéc, Đồng Tháp',          N'Đơn sỉ, xuất hóa đơn VAT', '2026-07-20 09:15:00'),
(4, 6, 'AGS-20260722-0004', 390000.00,   'pending',   N'123 Nguyễn Huệ, Quận 1, TP. HCM',  NULL,                         '2026-07-22 16:45:00'),
(5, 7, 'AGS-20260723-0005', 150000.00,   'cancelled', N'45 Lê Lợi, Q. Ninh Kiều, Cần Thơ', N'Khách đổi ý huỷ đơn',       '2026-07-23 08:20:00');

-- =====================================================
-- 11. ORDER_ITEMS
-- =====================================================
INSERT INTO order_items (id, order_id, product_id, quantity, unit_price, subtotal) VALUES
-- Đơn 1: 20kg xoài Cát Chu
(1,  1, 1,  20.00,  45000.00,  900000.00),
-- Đơn 2: 15kg xoài Keo + 5kg xoài Tứ Quý + 2kg xoài sấy dẻo = 865.000
(2,  2, 3,  15.00,  22000.00,  330000.00),
(3,  2, 5,  5.00,   35000.00,  175000.00),
(4,  2, 7,  2.00,   180000.00, 360000.00),
-- Đơn 3 (sỉ): 200kg xoài Cát Hòa Lộc xuất khẩu (giá sỉ 63k)
(5,  3, 9,  200.00, 63000.00,  12600000.00),
-- Đơn 4: 4kg xoài Hòa Lộc + 0.5kg xoài sấy dẻo = 390.000 (đang chờ)
(6,  4, 2,  4.00,   75000.00,  300000.00),
(7,  4, 7,  0.50,   180000.00, 90000.00),
-- Đơn 5 (đã huỷ): 5kg xoài Đài Loan
(8,  5, 10, 5.00,   30000.00,  150000.00);

-- =====================================================
-- 12. TRACEABILITY_LOGS  (truy xuất nguồn gốc theo QR)
-- =====================================================
INSERT INTO traceability_logs (id, product_id, stage, description, location, happened_at) VALUES
(1, 1, 'thu_hoach',  N'Thu hoạch xoài Cát Chu chín cây, chọn trái đạt chuẩn size',      N'Vườn Mỹ Xương, Cao Lãnh',  '2026-07-14 06:30:00'),
(2, 1, 'dong_goi',   N'Phân loại, lau sạch, đóng thùng carton 10kg',                    N'Xưởng sơ chế Mỹ Xương',    '2026-07-14 15:00:00'),
(3, 1, 'dang_ban',   N'Đăng bán trên nền tảng AgriSenAI',                               N'Online',                   '2026-07-15 08:00:00'),
(4, 3, 'thu_hoach',  N'Thu hoạch xoài Keo già, cắt cuống chừa 2cm tránh chảy nhựa',     N'Vườn Tịnh Thới, Cao Lãnh', '2026-07-16 07:00:00'),
(5, 3, 'dong_goi',   N'Đóng sọt nhựa 20kg, dán tem truy xuất',                          N'Vườn Tịnh Thới',           '2026-07-16 16:30:00'),
(6, 9, 'thu_hoach',  N'Thu hoạch Cát Hòa Lộc bao trái, phân loại theo size xuất khẩu',  N'HTX Xoài Mỹ Xương',        '2026-07-19 06:00:00'),
(7, 9, 'van_chuyen', N'Vận chuyển lạnh 12-13°C về kho phân phối',                       N'Xe lạnh - QL30',           '2026-07-20 05:00:00'),
(8, 9, 'dang_ban',   N'Sẵn sàng giao cho đơn sỉ AGS-20260720-0003',                     N'Kho Sa Đéc',               '2026-07-20 10:00:00');

-- =====================================================
-- 13. REVIEWS  (chỉ buyer đã mua mới đánh giá)
-- =====================================================
INSERT INTO reviews (id, product_id, buyer_id, rating, comment, created_at) VALUES
(1, 1, 6, 5, N'Xoài ngọt, chín đều, đóng gói cẩn thận. Sẽ ủng hộ tiếp!',             '2026-07-19 20:15:00'),
(2, 3, 7, 4, N'Xoài Keo giòn, chua nhẹ, chấm muối ớt rất ngon; vài trái vỏ hơi nám.', '2026-07-21 19:00:00'),
(3, 5, 7, 5, N'Tứ Quý thơm, thịt dẻo, vỏ còn xanh-vàng nhưng ăn đã ngọt.',           '2026-07-21 19:05:00'),
(4, 9, 8, 5, N'Cát Hòa Lộc hàng tuyển chuẩn, size đều. Giao đúng hẹn cho đơn sỉ.',   '2026-07-22 11:30:00'),
(5, 7, 7, 4, N'Xoài sấy dẻo vừa ngọt, không quá khô. Đóng gói kỹ.',                 '2026-07-21 20:30:00');

-- =====================================================
-- 14. NOTIFICATIONS
-- type: order / ai_result / price_alert
-- =====================================================
INSERT INTO notifications (id, user_id, message, type, is_read) VALUES
(1, 1, N'Xoài Cát Chu của bạn được AI chấm 92.5 điểm - Hạng Đặc biệt theo TCVN.',      'ai_result',   TRUE),
(2, 1, N'Khuyến nghị: Nên BÁN NGAY xoài Cát Chu, giá thị trường đang thuận lợi.',       'price_alert', FALSE),
(3, 2, N'Khuyến nghị: Có thể CHỜ THÊM 3-5 ngày để bán xoài Keo được giá cao hơn.',      'price_alert', FALSE),
(4, 1, N'Đơn hàng AGS-20260718-0001 đã hoàn thành. Bạn nhận được đánh giá 5 sao!',      'order',       TRUE),
(5, 5, N'Xoài Cát Hòa Lộc được AI chấm 94 điểm. Đơn sỉ AGS-20260720-0003 đã xác nhận.', 'order',       FALSE),
(6, 3, N'Cảnh báo: Xoài Tứ Quý quá chín, nên GIẢM GIÁ để tiêu thụ nhanh.',              'price_alert', FALSE),
(7, 6, N'Đơn hàng AGS-20260722-0004 của bạn đang chờ xác nhận từ người bán.',           'order',       FALSE),
(8, 8, N'Đơn sỉ AGS-20260720-0003 (12.6 triệu) đã được xác nhận.',                      'order',       TRUE);

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- CẬP NHẬT AUTO_INCREMENT (tránh trùng ID khi thêm mới)
-- =====================================================
ALTER TABLE roles                  AUTO_INCREMENT = 4;
ALTER TABLE users                  AUTO_INCREMENT = 10;
ALTER TABLE farmer_profiles        AUTO_INCREMENT = 6;
ALTER TABLE categories             AUTO_INCREMENT = 4;
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
