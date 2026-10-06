# AgriSen v2 — Khởi tạo dự án mới (kế thừa từ AgriSenAI v1)

> **Cách dùng:** chép file này vào thư mục gốc repo mới (đổi tên thành `PLAN.md`), rồi nói với Claude:
> *"đọc PLAN.md, làm Giai đoạn 1"*. Mọi đường dẫn dạng `v1:...` là file trong repo cũ
> `E:\laragon\www\AgriSenAI` — mở ra đọc/chép, **không** import trực tiếp.
>
> Căn cứ: góp ý cô Hằng 05/10/2026 (`v1:docs/Giao-Vien/Cô-Hằng/Agrisen-Update-051026.docx`),
> MVP mẫu của cô (`v1:docs/Giao-Vien/Cô-Hằng/agrisen-mvp.zip`), và toàn bộ kinh nghiệm đo đạc ở v1.

---

## 1. Định vị sản phẩm (chốt, không bàn lại)

**Không bán "AI nhận diện xoài". Bán "nền tảng chuẩn hóa hồ sơ lô xoài cho B2B".**

Câu thuyết trình chuẩn (dùng nguyên văn):

> "Chúng em xây dựng nền tảng B2B SaaS giúp chuẩn hóa quy trình kiểm định xoài bằng cơ chế
> Human-in-the-loop. Giai đoạn MVP, hệ thống giải quyết nỗi đau lưu trữ thông tin rời rạc của các
> điểm thu gom. Đồng thời, qua quá trình sử dụng hàng ngày của khách hàng, Data Pipeline của hệ thống
> tự động thu thập và gán nhãn dữ liệu thực tế. Khách hàng càng dùng, tập dữ liệu càng lớn — đó là lợi
> thế cạnh tranh dài hạn để tiến tới huấn luyện mô hình Computer Vision độc quyền ở giai đoạn 2."

**Không bao giờ nói:** "Chúng em làm app AI nhận diện xoài nhưng chưa có data train."

### Ba nguyên tắc sản phẩm

1. **AI gợi ý, con người quyết định và chịu trách nhiệm.** AI dùng Vision API có sẵn (Gemini) + xử lý ảnh cơ bản.
2. **Không có điểm tổng hợp, không có xếp hạng.** Không "7.8/10", không "Hạng I". Chỉ có quan sát ngoại quan theo từng tiêu chí.
3. **Mỗi lần xác nhận = một nhãn dữ liệu.** Cặp (ảnh + nhãn đã duyệt) là tài sản của dự án.

---

## 2. Phạm vi MVP

### LÀM (đúng 5 bước, theo sơ đồ quy trình của cô)

| # | Bước | Người dùng |
|---|---|---|
| 1 | Tạo lô hàng (nguồn cung, giống, khối lượng, ngày thu hoạch) → mã lô duy nhất | Điểm thu gom / HTX |
| 2 | Chụp ảnh chuẩn hóa: khung ngắm + kiểm tra mờ/tối/nhỏ ngay trên máy, không đạt thì chụp lại | Điểm thu gom |
| 3 | AI gợi ý ngoại quan theo tiêu chí (màu, bề mặt/khuyết tật, hình dạng, độ đồng đều, độ chín) | Hệ thống |
| 4 | Người phụ trách **Chấp nhận / Sửa / Từ chối** từng gợi ý → ghi audit log | Điểm thu gom |
| 5 | Công bố hồ sơ bất biến + QR. Người mua quét → thấy ảnh, ngoại quan đã duyệt, **ai xác nhận, lúc nào**, chuỗi audit còn nguyên hay không | Người mua B2B |

Kèm theo: in tem QR dán thùng, xuất dataset (ảnh + nhãn), báo cáo vận hành (số lô, thời gian xử lý/lô, tỉ lệ AI được chấp nhận nguyên văn).

### KHÔNG LÀM (cô yêu cầu bỏ — không đưa vào code, slide, đơn đăng ký)

- Tự train mô hình / "thuật toán độc quyền" (để Giai đoạn 2 sau thí điểm).
- Điểm tổng hợp, xếp hạng tự động.
- Chatbot, dự báo thời tiết, khuyến nghị giá & thời điểm bán.
- Sàn giao dịch, giỏ hàng, thanh toán, đơn hàng, đánh giá sao, thông báo.
- Loại nông sản khác ngoài xoài.

### ĐỂ SAU MVP (đã có hướng, chưa code)

- RAG tra cứu TCVN 9766:2013 + yêu cầu thu mua của từng siêu thị (pgvector).
- Train mô hình riêng từ dataset đã thu (notebook Kaggle của cô: `v1:docs/Giao-Vien/Cô-Hằng/agrisen_train.ipynb`).
- Phản hồi của người mua trên trang QR (đã xem / xin mẫu / xin báo giá).

---

## 3. Công nghệ

| Lớp | Chọn | Ghi chú |
|---|---|---|
| Backend | **FastAPI** (Python 3.12), SQLAlchemy 2 + Alembic, Pydantic v2 | Theo góp ý của cô |
| DB | **PostgreSQL** — Supabase (đã có sẵn pgvector cho RAG sau này) | Dev: Postgres local qua Docker hoặc dùng luôn Supabase |
| Lưu ảnh | Supabase Storage (bucket `lot-photos`) | Dev: thư mục `data/uploads/` |
| AI | Gemini Vision API (model đang dùng ở v1: `gemini-3.1-flash-lite`) | Đúng tinh thần "Pre-trained Vision API" |
| Xử lý ảnh | Pillow + numpy (server); Canvas API (client) | Không cần OpenCV.js cho MVP — Laplacian + độ sáng viết tay ~20 dòng |
| Frontend | **React + Vite + vite-plugin-pwa**, TypeScript | Chọn Vite thay Next.js: PWA đơn giản hơn, không cần SSR |
| QR | `qrcode` (Python) | |
| Đăng nhập | Bảng `users` + mật khẩu bcrypt + JWT (PyJWT) | Đủ để biết **ai** xác nhận. Không dùng mã nhân viên chung như MVP mẫu |
| Test | pytest + httpx (API), Postman collection (demo cho giám khảo), Playwright (E2E giao diện) | Cô ghi Selenium/TestNG; Playwright cùng vai trò, hợp với PWA hơn — nói rõ khi thuyết trình |
| Deploy | Render (backend), Vercel (frontend), Supabase (DB + ảnh) | Cả ba tự cấp HTTPS → camera PWA chạy được |

---

## 4. Kế thừa từ v1 — chép gì, sửa gì

### 4.1. Chép và giữ nguyên ý (port sang Python/React)

| Thứ | Nguồn v1 | Port thế nào |
|---|---|---|
| Gọi Gemini | `v1:app/Services/Quality/GeminiVisionClient.php` | `temperature: 0.1`, `responseMimeType: application/json`, `responseSchema`, **retry 4 lần, 3 s** khi lỗi kết nối/429/500/503. Dùng `httpx`. Thông báo lỗi 503 gợi ý đổi model |
| Chuẩn hóa ảnh | `v1:app/Services/Quality/ImageNormalizer.php` | Pillow: `ImageOps.exif_transpose` → cắt vuông (theo khung ngắm, nếu không thì cắt giữa) → 768×768 → JPEG q85, nền xám 128 khi có alpha. **Ảnh mẫu few-shot và ảnh người dùng phải đi qua cùng một hàm** |
| Luật trong prompt | `v1:app/Services/Quality/FewShotPromptBuilder.php` → `systemInstruction()` | Giữ: chỉ chấm cái nhìn thấy; không đoán khối lượng/Brix; **vỏ xanh không phải lỗi** (theo `muc_dich_ban` ăn xanh/ăn chín; giống Keo, Tứ Quý, Đài Loan chín vẫn xanh); ảnh không dùng được → `usable=false`, không bịa; mô tả phải nêu lý do. **Bỏ:** thang hạng, ngưỡng điểm |
| Danh mục khuyết tật | `v1:config/agrisenai.php` (`defect_types`, `defect_severities`) | Chuyển thành enum Python. Giữ nhãn tiếng Việt để hiện trên UI |
| 6 ảnh mẫu few-shot | `v1:storage/app/reference/xoai/` (+ `labels.json`, `source.json`) | Chép ảnh. **Viết lại** `labels.json` theo schema mới (mục 6): bỏ `grade`, `overall_score`, `scores`; giữ `defects`, độ chín, mô tả |
| Tập test 25 ảnh | `v1:storage/app/testset/xoai/` (+ `ground_truth.json`) | Chép. Dùng để đo **độ khớp khuyết tật/độ chín**, không đo hạng nữa |
| Kiểm tra chất lượng ảnh | MVP của cô: `assess_quality()` trong `agrisen-mvp.zip/backend/main.py` | Laplacian variance (mờ < 20), độ sáng trung bình (< 60 tối, > 220 cháy), cạnh ngắn < 320 px. Chạy **cả ở client (chặn sớm) lẫn server (không tin client)** |
| Audit log nối chuỗi băm | MVP của cô: `audit()` + `lot_view()` | `hash = sha256(prev_hash + payload_json_sorted)`, chỉ thêm, kiểm tra lại cả chuỗi khi hiển thị → `chain_ok`. Ý tưởng hash ảnh SHA-256 lấy từ `v1:app/Services/TraceabilityService.php` |
| Luồng xác nhận | MVP của cô: `ConfirmIn` (`accept` / `edit` / `reject` / `manual`) | Giữ 4 quyết định; làm ở mức **từng tiêu chí**, không chỉ một nhãn cho cả lô |
| Ảnh xoài thật để seed demo | `v1:public/images/products/xoai-mau1..12.jpg` | Chép vào seed 3–4 lô demo đã xác nhận |
| PWA | `v1:public/manifest.webmanifest`, icons, `v1:public/sw.js` | Lấy icon + màu thương hiệu; service worker để vite-plugin-pwa sinh |
| Tài liệu | `v1:docs/files-ke-hoach/` | Mang theo: `cau-hoi-giam-khao-ai-danh-gia.md` (sửa phần hạng), `kich-ban-phong-van-nong-dan.md`, `huong-dan-tao-link-tam-thoi.md` (Cloudflare Tunnel), `ket-qua-do-xoai-hong-2026-10-01.md` |

### 4.2. Bài học đã trả giá ở v1 — đừng lặp lại

- **EXIF:** 20/69 ảnh xoài chụp bằng điện thoại có `Orientation=6`. Không xoay → AI nhìn quả nằm ngang.
- **Tách quả bằng màu đã thất bại:** màu vỏ xoài vàng trùng màu mặt bàn gỗ. Cách rẻ và đúng là **khung ngắm** bắt người dùng đặt quả vào giữa, nền trơn.
- **Viết thêm luật vào prompt không thay được ảnh mẫu.** Đã thử "phân vân thì chọn thấp hơn": không cải thiện, còn sinh lỗi mới.
- **Thêm 2 ảnh xoài thối vào few-shot không đổi được gì đo được** (đo 170 lần, 01/10). AI vốn đã bắt được xoài thối rõ. Chỗ yếu là **vùng ranh giới** (thán thư 15–22% bề mặt, đốm đen 1–2% bị gọi là "thối nặng"). Đây chính là lý do HITL có giá trị: con người sửa đúng chỗ AI lưỡng lự.
- **Gemini chậm và thất thường:** trung vị 10 s/lần, khoảng 1/7 số lần > 30 s, có lần > 6 phút, 4/174 lỗi 503. → Gọi AI **bất đồng bộ** (job nền + UI "đang phân tích"), có timeout, và luôn cho phép **xác nhận thủ công** khi AI không trả lời.
- **Ngưỡng 1/10 và 1/4 diện tích chưa đối chiếu bản TCVN 9766:2013 gốc.** Không đưa con số TCVN lên giao diện cho tới khi có bản gốc.
- **Nhãn 6 ảnh mẫu do AI đề xuất, chưa có chuyên gia duyệt.** Trước khi thi, nhờ thầy/cán bộ khuyến nông/chủ vựa soát lại.
- **Bộ MangoFruitDDS chưa rõ giấy phép** → chỉ đo nội bộ, không commit, không đưa vào dataset.

### 4.3. KHÔNG mang sang

`GradeRuleEngine`, thang `grades`, `overall_score`; sàn/giỏ/thanh toán/đơn hàng; bảng `weather_data_cache`, `market_price_reference`, `price_recommendations`, `reviews`, `notifications`; trang `/khuyen-nghi`; chatbot; mọi dữ liệu cam/bưởi/nhãn/quýt.

---

## 5. Mô hình dữ liệu (PostgreSQL)

```sql
CREATE TABLE users (
  id            BIGSERIAL PRIMARY KEY,
  full_name     TEXT NOT NULL,
  phone         TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  role          TEXT NOT NULL CHECK (role IN ('admin','staff')),
  org_name      TEXT NOT NULL,              -- ten diem thu gom / HTX
  created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE lots (
  id            BIGSERIAL PRIMARY KEY,
  code          TEXT UNIQUE NOT NULL,       -- vd XL-20261006-0007, in tren tem QR
  supplier      TEXT NOT NULL,              -- nha vuon / nguon cung
  variety       TEXT,                       -- giong (Cat Hoa Loc, Keo...) - nguoi nhap
  purpose       TEXT NOT NULL CHECK (purpose IN ('an_xanh','an_chin')),
  weight_kg     NUMERIC(10,2) NOT NULL CHECK (weight_kg > 0),
  harvest_date  DATE NOT NULL,
  status        TEXT NOT NULL CHECK (status IN ('draft','analyzing','analyzed','confirmed','published')),
  created_by    BIGINT NOT NULL REFERENCES users(id),
  created_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
  published_at  TIMESTAMPTZ
);

CREATE TABLE photos (
  id            BIGSERIAL PRIMARY KEY,
  lot_id        BIGINT NOT NULL REFERENCES lots(id),
  storage_path  TEXT NOT NULL,              -- anh DA chuan hoa 768x768
  sha256        CHAR(64) NOT NULL,
  view          TEXT NOT NULL CHECK (view IN ('toan_qua','be_mat','cuong','khac')),
  quality       JSONB NOT NULL,             -- {ok, issues[], blur, brightness, width, height}
  created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Append-only: phan tich lai = them dong moi, khong UPDATE
CREATE TABLE ai_suggestions (
  id            BIGSERIAL PRIMARY KEY,
  lot_id        BIGINT NOT NULL REFERENCES lots(id),
  provider      TEXT NOT NULL,              -- 'gemini'
  model         TEXT NOT NULL,              -- 'gemini-3.1-flash-lite'
  prompt_version TEXT NOT NULL,             -- doi prompt -> doi version
  photo_ids     BIGINT[] NOT NULL,
  result        JSONB,                      -- schema muc 6; NULL neu loi
  error         TEXT,
  latency_ms    INT,
  created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Ket qua nguoi xac nhan, theo TUNG tieu chi = nhan du lieu
CREATE TABLE verified_labels (
  id             BIGSERIAL PRIMARY KEY,
  lot_id         BIGINT NOT NULL REFERENCES lots(id),
  suggestion_id  BIGINT REFERENCES ai_suggestions(id),   -- NULL = nhap thu cong
  criterion      TEXT NOT NULL,             -- color | surface | shape | uniformity | ripeness
  decision       TEXT NOT NULL CHECK (decision IN ('accept','edit','reject','manual')),
  ai_value       JSONB,
  final_value    JSONB,                     -- NULL khi reject
  note           TEXT,
  confirmed_by   BIGINT NOT NULL REFERENCES users(id),
  confirmed_at   TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Nhat ky chi them, noi chuoi bam theo tung lo
CREATE TABLE audit_log (
  id         BIGSERIAL PRIMARY KEY,
  lot_id     BIGINT NOT NULL REFERENCES lots(id),
  ts         TIMESTAMPTZ NOT NULL,
  actor_id   BIGINT REFERENCES users(id),   -- NULL = he thong
  action     TEXT NOT NULL,                 -- created | photo_added | ai_suggested | confirmed | reconfirmed | published
  payload    TEXT NOT NULL,                 -- JSON sort_keys, chinh xac chuoi da bam
  hash       CHAR(64) NOT NULL              -- sha256(prev_hash + payload)
);

-- Chan sua/xoa o muc DB (khong chi o code)
CREATE RULE audit_no_update AS ON UPDATE TO audit_log DO INSTEAD NOTHING;
CREATE RULE audit_no_delete AS ON DELETE TO audit_log DO INSTEAD NOTHING;
CREATE RULE sugg_no_update  AS ON UPDATE TO ai_suggestions DO INSTEAD NOTHING;
```

Ràng buộc nghiệp vụ:

- Lô `published` không sửa được nữa. Muốn đổi thì tạo lô mới, tham chiếu lô cũ.
- `payload` của audit phải gồm `photo sha256` + `final_value` + `confirmed_by` để trang QR chứng minh được ảnh và kết luận không bị đổi sau khi công bố.
- Kết quả AI **không bao giờ lấy từ client**. Client chỉ gửi `lot_code`, server tự đọc `ai_suggestions`.

---

## 6. Schema gợi ý của AI (thay cho điểm/hạng)

Gemini trả về JSON đúng dạng này (ép bằng `responseSchema`):

```json
{
  "usable": true,
  "unusable_reason": null,
  "detected_product": "Xoài",
  "color":      { "distribution": [{"name": "vàng", "percent": 80}, {"name": "xanh", "percent": 20}],
                  "note": "Vàng đều, vai quả còn xanh nhạt" },
  "ripeness":   { "stage": "chin_toi", "matches_purpose": true,
                  "note": "Đúng mục đích ăn chín" },
  "surface":    { "defects": [{"type": "xuoc_vo", "severity": "nhe", "location": "gần cuống",
                               "area_percent": 2, "into_flesh": false}],
                  "note": "Một vết xước nông gần cuống, chưa ăn vào thịt quả" },
  "shape":      { "status": "binh_thuong", "note": "Cân đối, không méo" },
  "uniformity": { "status": "dong_deu", "note": "Các quả trong ảnh cùng cỡ, cùng màu" },
  "confidence": 0.8
}
```

- Enum `ripeness.stage`: `xanh_gia`, `chin_toi`, `chin_ky`, `qua_chin`. Enum `shape.status`: `binh_thuong`, `meo_nhe`, `meo_nang`. Enum `uniformity.status`: `dong_deu`, `khong_deu`.
- **Không có** `grade`, `overall_score`, `scores`. Test phải fail nếu các trường này xuất hiện trong response API.
- `confidence < 0.6` hoặc `usable = false` → UI không cho "Chấp nhận nhanh", buộc người dùng xem từng tiêu chí.
- Lô nhiều ảnh: gửi tối đa 4 ảnh đạt chất lượng trong một lần gọi (ảnh mờ bị loại trước).

---

## 7. API

| Method | Path | Quyền | Việc |
|---|---|---|---|
| POST | `/api/auth/login` | — | SĐT + mật khẩu → JWT |
| POST | `/api/lots` | staff | Tạo lô → `code` |
| GET | `/api/lots?status=` | staff | Danh sách lô của tổ chức |
| GET | `/api/lots/{code}` | staff | Chi tiết + ảnh + gợi ý mới nhất + nhãn + audit |
| POST | `/api/lots/{code}/photos` | staff | Upload (multipart). Server chuẩn hóa → kiểm tra chất lượng → lưu. Trả về `quality` từng ảnh |
| POST | `/api/lots/{code}/analyze` | staff | Đưa job gọi Gemini vào hàng đợi → `202`. Dùng `BackgroundTasks` (đủ cho MVP) |
| GET | `/api/lots/{code}/analysis` | staff | Poll trạng thái: `pending` / `done` / `error` |
| POST | `/api/lots/{code}/confirm` | staff | Body: danh sách `{criterion, decision, value?, note?}`. Ghi `verified_labels` + audit |
| POST | `/api/lots/{code}/publish` | staff | Khóa lô, ghi audit `published` |
| GET | `/api/lots/{code}/qr.png` | staff | Ảnh QR trỏ tới trang công khai |
| GET | `/api/public/lots/{code}` | công khai | Chỉ lô `published`: ảnh, ngoại quan đã duyệt, người xác nhận, thời gian, audit, `chain_ok` |
| GET | `/api/export/dataset` | admin | Zip: `images/` + `labels.jsonl` (một dòng = 1 ảnh + nhãn đã duyệt + gợi ý AI gốc) |
| GET | `/api/stats` | admin | Số lô, thời gian từ tạo lô → công bố, tỉ lệ accept / edit / reject theo tiêu chí |
| GET | `/api/health` | — | `{db: ok, gemini_key: bool}` |

`/api/stats` là nơi sinh **số liệu ROI** cho thuyết trình: thời gian lập hồ sơ/lô, và tỉ lệ AI được chấp nhận nguyên văn ("AI đúng bao nhiêu % theo đánh giá của chính người dùng").

---

## 8. Màn hình frontend

1. **Đăng nhập.**
2. **Danh sách lô:** lọc theo trạng thái, nút "Tạo lô".
3. **Tạo lô:** form; mục đích bán `ăn xanh / ăn chín` là trường bắt buộc (prompt cần nó).
4. **Chụp ảnh có hướng dẫn:** `getUserMedia` + overlay khung tròn/vuông; chụp vào canvas → tính độ sáng + Laplacian trên ảnh thu nhỏ 320 px → không đạt thì hiện lý do ("Ảnh bị mờ — giữ máy chắc") và **không cho gửi**. Gợi ý góc chụp: toàn quả, cận bề mặt, cuống. Có nút chọn ảnh từ thư viện (vẫn qua bước kiểm tra).
5. **Gợi ý & xác nhận (HITL):** ảnh bên trái; bên phải 5 thẻ tiêu chí, mỗi thẻ có `Đồng ý / Sửa / Bác bỏ`. "Sửa" mở đúng các lựa chọn enum (không gõ tự do, để nhãn sạch). Thanh dưới: "Xác nhận hồ sơ" → hiện tên người xác nhận + giờ.
6. **Công bố & tem QR:** xem trước trang công khai; in tem (CSS `@media print`, khổ 50×30 mm: QR + mã lô + ngày thu hoạch).
7. **Trang hồ sơ công khai (mở từ QR, không cần đăng nhập):** ảnh đã duyệt; bảng ngoại quan; khối **"Người xác nhận: Nguyễn Văn A — Vựa X — 14:32 06/10/2026"**; lịch sử audit dạng timeline; dải xanh "Hồ sơ nguyên vẹn" hoặc dải đỏ khi `chain_ok = false`. Ghi rõ dòng nhỏ: "Gợi ý ngoại quan do AI hỗ trợ, đã được người phụ trách xác nhận".
8. **Thống kê (admin):** số liệu từ `/api/stats`, nút "Xuất dataset".

Chữ hiển thị: tiếng Việt có dấu. Giao diện dùng tốt trên điện thoại Android tầm trung, ngoài trời nắng (tương phản cao, nút to).

---

## 9. Cấu trúc repo

```
agrisen/
├── PLAN.md                     # file nay
├── backend/
│   ├── app/
│   │   ├── main.py
│   │   ├── config.py           # doc .env (pydantic-settings)
│   │   ├── db.py
│   │   ├── models.py           # SQLAlchemy
│   │   ├── schemas.py          # Pydantic, gom ca schema goi y AI (muc 6)
│   │   ├── auth.py
│   │   ├── routers/            # auth.py, lots.py, public.py, export.py, stats.py
│   │   └── services/
│   │       ├── image.py        # normalize + assess_quality (port tu v1 + MVP cua co)
│   │       ├── vision.py       # goi Gemini, few-shot, retry
│   │       ├── prompt.py       # system instruction + few-shot turns
│   │       ├── audit.py        # append + verify chain
│   │       └── storage.py      # local / Supabase
│   ├── reference/xoai/         # 6 anh mau + labels.json (schema moi)
│   ├── testset/xoai/           # 25 anh + ground_truth.json
│   ├── alembic/
│   ├── scripts/eval_vision.py  # do do khop tren testset (goi Gemini that)
│   ├── tests/
│   └── requirements.txt
├── frontend/                   # React + Vite + vite-plugin-pwa
│   └── src/{pages,components,lib/imageCheck.ts,api.ts}
├── tests-e2e/                  # Playwright
├── postman/agrisen.postman_collection.json
└── docs/                       # tai lieu mang tu v1 sang
```

`.env` (backend): `DATABASE_URL`, `JWT_SECRET`, `GEMINI_API_KEY`, `GEMINI_MODEL`, `GEMINI_TIMEOUT=60`, `PUBLIC_URL`, `STORAGE=local|supabase`, `SUPABASE_URL`, `SUPABASE_SERVICE_KEY`.

---

## 10. Lộ trình

Mỗi task: viết test → code → test xanh → commit. Tick `- [x]` khi xong.

### Giai đoạn 1 — Khung chạy được trọn luồng, chưa có AI

- [ ] 1.1 Khởi tạo repo, FastAPI + Alembic + Postgres, `/api/health`, pytest chạy được.
- [ ] 1.2 `users` + đăng nhập JWT + seed 1 admin, 1 staff.
- [ ] 1.3 Tạo lô / danh sách / chi tiết + audit `created`.
- [ ] 1.4 `services/audit.py`: append + verify chain. Test: sửa tay 1 dòng payload trong DB → `chain_ok = false`.
- [ ] 1.5 Upload ảnh: chuẩn hóa (test EXIF orientation 6) + `assess_quality` + sha256 + audit `photo_added`.
- [ ] 1.6 Xác nhận **thủ công** (`decision = manual`) theo 5 tiêu chí + công bố + QR + trang công khai.
- [ ] 1.7 Frontend: đăng nhập → tạo lô → chụp ảnh (chưa cần khung ngắm) → nhập tay → công bố → mở QR trên máy khác.

> Hết Giai đoạn 1 đã demo được câu chuyện "hồ sơ lô đáng tin" mà **không cần AI**. Đây là lưới an toàn khi Gemini sập lúc thi.

### Giai đoạn 2 — AI gợi ý + HITL

- [ ] 2.1 `prompt.py`: system instruction mới (mục 4.1, bỏ thang hạng) + viết lại `labels.json` 6 ảnh mẫu theo schema mục 6.
- [ ] 2.2 `vision.py`: gọi Gemini có retry/timeout; ghi `ai_suggestions` (cả khi lỗi). Test bằng client giả, không gọi mạng.
- [ ] 2.3 `/analyze` bất đồng bộ + `/analysis` poll; UI "đang phân tích" có nút "Tự nhập, không chờ AI".
- [ ] 2.4 Màn hình HITL 5 thẻ tiêu chí (Đồng ý / Sửa / Bác bỏ), ghi `verified_labels` có `ai_value`.
- [ ] 2.5 `scripts/eval_vision.py` trên 25 ảnh test: độ khớp có/không khuyết tật, độ khớp loại khuyết tật, độ khớp độ chín. Lưu báo cáo vào `docs/`.
- [ ] 2.6 Test chặn: response API không bao giờ chứa `grade` / `overall_score` / `scores`.

### Giai đoạn 3 — Chụp chuẩn, dữ liệu, QA, triển khai

- [ ] 3.1 Khung ngắm trên camera + kiểm tra mờ/tối phía client (`lib/imageCheck.ts`, có unit test với ảnh mẫu mờ/tối/đạt). Gửi kèm tọa độ khung để server cắt đúng vùng.
- [ ] 3.2 Tem QR in được.
- [ ] 3.3 `/api/export/dataset` + `/api/stats` + trang thống kê.
- [ ] 3.4 Postman collection đủ luồng (có test script kiểm tra status/field) + Playwright 1 kịch bản E2E: tạo lô → ảnh → xác nhận → công bố → mở trang QR.
- [ ] 3.5 Deploy Supabase + Render + Vercel; chạy thử camera trên điện thoại thật qua HTTPS.
- [ ] 3.6 Seed 3–4 lô demo đã công bố bằng ảnh `xoai-mau*.jpg` của v1.

### Giai đoạn 4 (sau thi / sau thí điểm) — không làm trước

- RAG TCVN 9766 + yêu cầu siêu thị (pgvector trên Supabase).
- Train mô hình riêng khi `verified_labels` đủ lớn (notebook Kaggle của cô; xuất ONNX; chạy song song với Gemini và so độ khớp với nhãn người dùng).
- Phản hồi của người mua trên trang QR.

---

## 11. Việc của nhóm (ngoài code)

- [ ] Xin lại cô Hằng phần **code mẫu Giai đoạn 1–4** trong file góp ý 05/10 (file Word chỉ còn tiêu đề, mất code).
- [ ] Hỏi cô: bắt buộc FastAPI/PostgreSQL hay chỉ cần thể hiện kiến trúc chuẩn? (Nếu chỉ cần kiến trúc, v1 Laravel vẫn là phương án dự phòng.)
- [ ] Tìm bản gốc **TCVN 9766:2013**; nhờ chuyên gia soát nhãn 6 ảnh mẫu.
- [ ] **Thí điểm tại một vựa ở Cao Lãnh**, ghi lại để có số liệu ROI:
  - thời gian lập hồ sơ một lô: cách cũ (sổ tay/Zalo) và AgriSen;
  - số lần người mua phải đến tận nơi xem hàng, trước và sau khi có trang QR;
  - tỉ lệ gợi ý AI được chấp nhận nguyên văn (lấy từ `/api/stats`).
- [ ] Tính lại mô hình thu phí **1,5 triệu đ/tháng/điểm**: chi phí Gemini + hạ tầng mỗi điểm, điểm hòa vốn.
- [ ] Sửa slide + đơn đăng ký: xóa mọi chỗ "AI tự phát triển", "chấm điểm", "xếp hạng", chatbot, thời tiết, giá, sàn giao dịch.
