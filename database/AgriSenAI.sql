-- =====================================================
-- CSDL: NỀN TẢNG SỐ HÓA NÔNG SẢN ĐỒNG THÁP
-- 15 bảng | MySQL 8+
-- =====================================================

CREATE DATABASE IF NOT EXISTS dongthap_nongsan
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dongthap_nongsan;

-- =====================================================
-- NHÓM 1: NGƯỜI DÙNG & VAI TRÒ
-- =====================================================

CREATE TABLE roles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE COMMENT 'nong_dan / nguoi_mua / admin',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    role_id     INT NOT NULL,
    name        VARCHAR(150) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    phone       VARCHAR(15),
    password    VARCHAR(255) NOT NULL,
    avatar      VARCHAR(255),
    address     VARCHAR(255),
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE farmer_profiles (
    id                  BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT NOT NULL UNIQUE,
    farm_name           VARCHAR(200) NOT NULL,
    ocop_certificate    VARCHAR(255) COMMENT 'Mã/ảnh chứng nhận OCOP nếu có',
    ocop_star_rating    TINYINT COMMENT 'Số sao OCOP (3-5), NULL nếu chưa có',
    region              VARCHAR(150) COMMENT 'Huyện/xã tại Đồng Tháp',
    description         TEXT,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- NHÓM 2: SẢN PHẨM
-- =====================================================

CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL COMMENT 'Xoài, Quýt hồng, Sen, Nem...',
    description TEXT
);

CREATE TABLE products (
    id                  BIGINT AUTO_INCREMENT PRIMARY KEY,
    farmer_id           BIGINT NOT NULL,
    category_id         INT NOT NULL,
    name                VARCHAR(200) NOT NULL,
    description         TEXT,
    price               DECIMAL(12,2) NOT NULL,
    unit                VARCHAR(30) DEFAULT 'kg' COMMENT 'kg, thùng, kg...',
    quantity_available  DECIMAL(10,2) DEFAULT 0,
    qr_code             VARCHAR(255) UNIQUE COMMENT 'Mã QR truy xuất nguồn gốc',
    status              ENUM('draft','active','sold_out','hidden') DEFAULT 'draft',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE product_images (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id  BIGINT NOT NULL,
    image_url   VARCHAR(255) NOT NULL,
    is_primary  BOOLEAN DEFAULT FALSE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =====================================================
-- NHÓM 3: AI - ĐÁNH GIÁ CHẤT LƯỢNG & KHUYẾN NGHỊ BÁN
-- =====================================================

CREATE TABLE ai_quality_assessments (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id      BIGINT NOT NULL,
    image_url       VARCHAR(255) NOT NULL COMMENT 'Ảnh nông dân chụp để AI quét',
    ripeness_level  ENUM('xanh','vua_chin','chin','qua_chin') COMMENT 'Kết quả AI phân loại độ chín',
    quality_score   DECIMAL(5,2) COMMENT 'Điểm chất lượng 0-100 do AI chấm',
    defect_detected BOOLEAN DEFAULT FALSE COMMENT 'AI phát hiện dấu hiệu hư hỏng/sâu bệnh',
    defect_note     TEXT COMMENT 'Mô tả lỗi nếu có, do AI sinh ra',
    raw_ai_response JSON COMMENT 'Toàn bộ phản hồi gốc từ Gemini Vision để truy vết',
    assessed_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE weather_data_cache (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    region          VARCHAR(150) NOT NULL,
    date            DATE NOT NULL,
    temperature     DECIMAL(5,2) COMMENT 'Độ C',
    humidity        DECIMAL(5,2) COMMENT '% độ ẩm',
    rain_forecast   VARCHAR(100) COMMENT 'Mô tả dự báo mưa',
    fetched_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_region_date (region, date)
);

CREATE TABLE market_price_reference (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    region      VARCHAR(150),
    price       DECIMAL(12,2) NOT NULL COMMENT 'Giá tham khảo trên thị trường',
    price_date  DATE NOT NULL,
    source      VARCHAR(255) COMMENT 'Nguồn dữ liệu giá',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE price_recommendations (
    id                  BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id          BIGINT NOT NULL,
    weather_ref_id      BIGINT COMMENT 'Liên kết dữ liệu thời tiết dùng để tính toán',
    market_price_ref_id BIGINT COMMENT 'Liên kết giá thị trường dùng để tính toán',
    recommended_action  ENUM('ban_ngay','cho_them','giam_gia') NOT NULL,
    reasoning_text       TEXT COMMENT 'Giải thích khuyến nghị bằng ngôn ngữ tự nhiên (AI sinh ra)',
    valid_until         DATE COMMENT 'Khuyến nghị có hiệu lực đến ngày nào',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (weather_ref_id) REFERENCES weather_data_cache(id),
    FOREIGN KEY (market_price_ref_id) REFERENCES market_price_reference(id)
);

-- =====================================================
-- NHÓM 4: GIAO DỊCH
-- =====================================================

CREATE TABLE orders (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    buyer_id        BIGINT NOT NULL,
    order_code      VARCHAR(30) NOT NULL UNIQUE,
    total_amount    DECIMAL(14,2) NOT NULL,
    status          ENUM('pending','confirmed','shipping','completed','cancelled') DEFAULT 'pending',
    shipping_address VARCHAR(255),
    note            TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id    BIGINT NOT NULL,
    product_id  BIGINT NOT NULL,
    quantity    DECIMAL(10,2) NOT NULL,
    unit_price  DECIMAL(12,2) NOT NULL,
    subtotal    DECIMAL(14,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =====================================================
-- NHÓM 5: TRUY XUẤT NGUỒN GỐC & TƯƠNG TÁC
-- =====================================================

CREATE TABLE traceability_logs (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id  BIGINT NOT NULL,
    stage       VARCHAR(100) NOT NULL COMMENT 'thu_hoach / dong_goi / van_chuyen / dang_ban...',
    description TEXT,
    location    VARCHAR(255),
    happened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id  BIGINT NOT NULL,
    buyer_id    BIGINT NOT NULL,
    rating      TINYINT NOT NULL COMMENT '1-5 sao',
    comment     TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id)
);

CREATE TABLE notifications (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT NOT NULL,
    message     VARCHAR(500) NOT NULL,
    type        VARCHAR(50) COMMENT 'order / ai_result / price_alert...',
    is_read     BOOLEAN DEFAULT FALSE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- DỮ LIỆU MẪU CHO ROLES (bắt buộc phải có trước users)
-- =====================================================

INSERT INTO roles (name) VALUES ('nong_dan'), ('nguoi_mua'), ('admin');
