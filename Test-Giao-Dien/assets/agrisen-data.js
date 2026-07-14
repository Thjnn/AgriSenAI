/*!
 * AgriData — nguồn dữ liệu mẫu dùng chung cho các trang demo AgriSenAi.
 *
 * Các trường đặt tên bám theo schema thật để khi chuyển sang Blade chỉ việc
 * thay mảng này bằng dữ liệu Eloquent, không phải sửa giao diện:
 *   products              → id, name, price, unit, quantity_available, qr_code, status
 *   ai_quality_assessments→ ai.{ripeness_level, quality_score, defect_detected, defect_note}
 *   farmer_profiles       → farmer.{farm_name, region, ocop_star_rating}
 *   traceability_logs     → trace[].{stage, description, location, happened_at}
 *   reviews               → reviews[].{rating, comment, created_at}
 */
(function (global) {
    "use strict";

    const IMG = "../public/images/products/";
    const FARM_IMG = "../public/images/farmers/";
    const BASE_URL = "https://agrisenai.vn/truy-xuat/";

    // Chỉ có 4 ảnh sản phẩm thật; các loại còn lại tạm mượn ảnh xoài.
    const CAT_IMG = {
        xoai: IMG + "anh-xoai-cat-hoa-loc.jpg",
        quyt: IMG + "quyt-hong-lai-vung.jpg",
        nhan: IMG + "nhan-chau-thanh.jpg",
        buoi: IMG + "buoi-phong-hoa.jpg",
        sau: IMG + "anh-xoai-cat-hoa-loc.jpg",
        sen: IMG + "anh-xoai-cat-hoa-loc.jpg",
        gao: IMG + "anh-xoai-cat-hoa-loc.jpg",
    };

    const CAT_NAME = {
        xoai: "Xoài",
        quyt: "Quýt",
        nhan: "Nhãn",
        buoi: "Bưởi",
        sau: "Sầu riêng",
        sen: "Sen",
        gao: "Gạo",
    };

    const CAT_BG = {
        xoai: "bg-1",
        quyt: "bg-2",
        nhan: "bg-3",
        buoi: "bg-5",
        sau: "bg-4",
        sen: "bg-6",
        gao: "bg-4",
    };

    const RIPENESS_LABEL = {
        xanh: "Còn xanh",
        vua_chin: "Vừa chín tới",
        chin: "Chín",
        qua_chin: "Quá chín",
    };

    /* ---------- Hồ sơ nông dân / HTX ---------- */
    const FARMERS = {
        f1: {
            name: "Nguyễn Văn Sáu",
            farm_name: "HTX Xoài Cao Lãnh",
            region: "Xã Mỹ Xương, Cao Lãnh",
            ocop_star_rating: 4,
            years: 18,
            avatar: FARM_IMG + "nong-trai-1.jpg",
            note: "Canh tác xoài theo hướng VietGAP, bao trái từ khi đậu quả.",
        },
        f2: {
            name: "Trần Thị Hồng",
            farm_name: "HTX Quýt hồng Lai Vung",
            region: "Xã Long Hậu, Lai Vung",
            ocop_star_rating: 4,
            years: 22,
            avatar: FARM_IMG + "nong-trai-2.jpg",
            note: "Vườn quýt hồng truyền thống hơn 20 năm, thu hoạch đúng độ chín.",
        },
        f3: {
            name: "Lê Hoàng Nam",
            farm_name: "HTX Nhãn Châu Thành",
            region: "Xã An Nhơn, Châu Thành",
            ocop_star_rating: 3,
            years: 12,
            avatar: FARM_IMG + "nong-trai-3.jpg",
            note: "Nhãn Ido trồng theo tiêu chuẩn xuất khẩu, có mã số vùng trồng.",
        },
        f4: {
            name: "Phạm Văn Đức",
            farm_name: "Nhà vườn Đức Thành",
            region: "Xã Định Yên, Lấp Vò",
            ocop_star_rating: 3,
            years: 15,
            avatar: FARM_IMG + "nong-trai-4.jpg",
            note: "Chuyên bưởi da xanh và cây có múi, tưới nhỏ giọt tiết kiệm nước.",
        },
        f5: {
            name: "Võ Thị Lan",
            farm_name: "HTX Sen Tháp Mười",
            region: "Xã Mỹ Hoà, Tháp Mười",
            ocop_star_rating: 5,
            years: 10,
            avatar: FARM_IMG + "nong-trai-5.jpg",
            note: "Sen và gạo hữu cơ vùng Đồng Tháp Mười, đạt OCOP 5 sao.",
        },
    };

    /* ---------- Danh sách sản phẩm ---------- */
    // prettier-ignore
    const RAW = [
        { name: "Xoài cát Hòa Lộc",       cat: "xoai", origin: "Cao Lãnh",   price: 45000,  unit: "kg",   change:  5.2, qty: 850,  score: 9.2, ripeness: "vua_chin", farmer: "f1", harvest: "2026-07-11" },
        { name: "Xoài Đài Loan",          cat: "xoai", origin: "Cao Lãnh",   price: 38000,  unit: "kg",   change:  2.1, qty: 1200, score: 8.6, ripeness: "chin",     farmer: "f1", harvest: "2026-07-10" },
        { name: "Xoài keo",               cat: "xoai", origin: "Thanh Bình", price: 32000,  unit: "kg",   change: -1.5, qty: 640,  score: 7.8, ripeness: "xanh",     farmer: "f1", harvest: "2026-07-12" },
        { name: "Xoài tượng da xanh",     cat: "xoai", origin: "Cao Lãnh",   price: 28000,  unit: "kg",   change:  0,   qty: 500,  score: 8.1, ripeness: "xanh",     farmer: "f1", harvest: "2026-07-12" },
        { name: "Quýt hồng Lai Vung",     cat: "quyt", origin: "Lai Vung",   price: 35000,  unit: "kg",   change:  8.0, qty: 920,  score: 8.8, ripeness: "chin",     farmer: "f2", harvest: "2026-07-10" },
        { name: "Quýt đường",             cat: "quyt", origin: "Lai Vung",   price: 28000,  unit: "kg",   change: -2.3, qty: 700,  score: 8.0, ripeness: "chin",     farmer: "f2", harvest: "2026-07-09" },
        { name: "Quýt tiều",              cat: "quyt", origin: "Lai Vung",   price: 33000,  unit: "kg",   change:  1.2, qty: 410,  score: 7.9, ripeness: "vua_chin", farmer: "f2", harvest: "2026-07-11" },
        { name: "Nhãn xuồng cơm vàng",    cat: "nhan", origin: "Châu Thành", price: 55000,  unit: "kg",   change:  3.4, qty: 380,  score: 9.0, ripeness: "chin",     farmer: "f3", harvest: "2026-07-11" },
        { name: "Nhãn Ido",               cat: "nhan", origin: "Châu Thành", price: 62000,  unit: "kg",   change:  4.1, qty: 260,  score: 9.4, ripeness: "vua_chin", farmer: "f3", harvest: "2026-07-12" },
        { name: "Nhãn tiêu da bò",        cat: "nhan", origin: "Châu Thành", price: 48000,  unit: "kg",   change: -0.8, qty: 540,  score: 8.3, ripeness: "chin",     farmer: "f3", harvest: "2026-07-09", defect: true, defectNote: "Một số quả có vết nứt nhẹ ở cuống do mưa lớn, nên tiêu thụ trong 2–3 ngày." },
        { name: "Bưởi da xanh",           cat: "buoi", origin: "Lấp Vò",     price: 30000,  unit: "trái", change:  2.7, qty: 1500, score: 8.9, ripeness: "chin",     farmer: "f4", harvest: "2026-07-08" },
        { name: "Bưởi năm roi",           cat: "buoi", origin: "Lấp Vò",     price: 26000,  unit: "trái", change: -1.1, qty: 1100, score: 8.2, ripeness: "chin",     farmer: "f4", harvest: "2026-07-08" },
        { name: "Bưởi Phong Hòa",         cat: "buoi", origin: "Lai Vung",   price: 32000,  unit: "trái", change:  0,   qty: 760,  score: 8.5, ripeness: "vua_chin", farmer: "f4", harvest: "2026-07-10" },
        { name: "Sầu riêng Ri6",          cat: "sau",  origin: "Cù lao",     price: 85000,  unit: "kg",   change:  6.5, qty: 320,  score: 9.1, ripeness: "vua_chin", farmer: "f4", harvest: "2026-07-12" },
        { name: "Sầu riêng Musang King",  cat: "sau",  origin: "Cù lao",     price: 120000, unit: "kg",   change:  9.2, qty: 180,  score: 9.5, ripeness: "vua_chin", farmer: "f4", harvest: "2026-07-12" },
        { name: "Sen Tháp Mười",          cat: "sen",  origin: "Tháp Mười",  price: 60000,  unit: "kg",   change:  1.9, qty: 240,  score: 9.3, ripeness: "chin",     farmer: "f5", harvest: "2026-07-11" },
        { name: "Gạo ST25",               cat: "gao",  origin: "Tháp Mười",  price: 22000,  unit: "kg",   change:  0.5, qty: 5000, score: 9.0, ripeness: "chin",     farmer: "f5", harvest: "2026-06-28" },
        { name: "Gạo Nàng Hoa",           cat: "gao",  origin: "Tháp Mười",  price: 18000,  unit: "kg",   change: -0.6, qty: 4200, score: 8.4, ripeness: "chin",     farmer: "f5", harvest: "2026-06-25" },
        { name: "Xoài cát chu",           cat: "xoai", origin: "Cao Lãnh",   price: 40000,  unit: "kg",   change:  3.0, qty: 680,  score: 8.7, ripeness: "chin",     farmer: "f1", harvest: "2026-07-10" },
        { name: "Quýt đường loại 1",      cat: "quyt", origin: "Lai Vung",   price: 42000,  unit: "kg",   change:  5.5, qty: 350,  score: 9.0, ripeness: "vua_chin", farmer: "f2", harvest: "2026-07-11" },
    ];

    const DESC = {
        xoai: "Trái to đều, vỏ căng bóng, thịt vàng cam, ít xơ, ngọt thanh. Được bao trái từ nhỏ nên vỏ sạch, không dùng thuốc bảo vệ thực vật giai đoạn cận thu hoạch.",
        quyt: "Vỏ mỏng bóng, múi mọng nước, vị ngọt dịu hơi chua nhẹ đặc trưng vùng Lai Vung. Thu hái đúng độ chín, đóng thùng ngay trong ngày.",
        nhan: "Cùi dày, hạt nhỏ, giòn và thơm. Trồng theo tiêu chuẩn có mã số vùng trồng, đủ điều kiện xuất khẩu.",
        buoi: "Tép mọng, vị ngọt thanh, không the đắng. Bưởi được để đủ ngày trên cây trước khi cắt nên độ đường ổn định.",
        sau: "Cơm vàng, hạt lép, béo ngậy. Cắt đúng độ già, không nhúng thuốc chín ép.",
        sen: "Hạt sen tươi vùng Đồng Tháp Mười, bùi và thơm, thu hoạch trong ngày.",
        gao: "Gạo canh tác theo hướng hữu cơ, hạt dài, cơm dẻo, thơm tự nhiên, không chất bảo quản.",
    };

    const REVIEW_POOL = [
        { name: "Nguyễn Minh Tuấn", rating: 5, comment: "Hàng tươi, đóng gói kỹ, giao đúng hẹn. Sẽ đặt tiếp cho quán.", days: 2 },
        { name: "Trần Thu Hà", rating: 5, comment: "Quét mã QR thấy rõ ngày thu hoạch nên rất yên tâm. Chất lượng đúng như mô tả.", days: 5 },
        { name: "Công ty TNHH Thực phẩm An Bình", rating: 4, comment: "Chất lượng ổn định qua 3 đợt lấy hàng. Mong có thêm size lớn cho đơn sỉ.", days: 9 },
        { name: "Lê Quốc Việt", rating: 5, comment: "Giá tốt hơn thương lái tại chợ, lại biết được vườn nào trồng.", days: 14 },
        { name: "Phạm Thị Kim", rating: 4, comment: "Ngon, ngọt. Vài trái hơi nhỏ so với hình nhưng nhìn chung đáng tiền.", days: 21 },
    ];

    /* ---------- Tiện ích ---------- */
    const slugify = (s) =>
        s
            .normalize("NFD")
            .replace(/\p{Diacritic}/gu, "")
            .replace(/đ/g, "d")
            .replace(/Đ/g, "D")
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/^-|-$/g, "");

    // Mã lô: DT-<viết tắt tên>-<năm>-<số thứ tự>, ví dụ DT-XCHL-2026-0142.
    function makeQr(name, id) {
        const initials = name
            .normalize("NFD")
            .replace(/\p{Diacritic}/gu, "")
            .replace(/đ/gi, "d")
            .split(/\s+/)
            .map((w) => w[0])
            .join("")
            .toUpperCase()
            .slice(0, 4);
        return `DT-${initials}-2026-${String(100 + id * 7).padStart(4, "0")}`;
    }

    const addDays = (iso, n) => {
        const d = new Date(iso + "T06:00:00");
        d.setDate(d.getDate() + n);
        return d;
    };

    const fmtDateTime = (d) =>
        `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()} · ${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}`;

    const money = (n) => n.toLocaleString("vi-VN");

    /* ---------- Dựng nhật ký truy xuất từ ngày thu hoạch ---------- */
    function buildTrace(p) {
        const f = p.farmer;
        const at = (dayOffset, h, m) => {
            const d = addDays(p.harvest, dayOffset);
            d.setHours(h, m, 0, 0);
            return d;
        };
        return [
            {
                stage: "thu_hoach",
                label: "Thu hoạch",
                icon: "🌾",
                description: `${f.name} thu hoạch tại ${f.farm_name}. Hái thủ công, chọn lọc trái đạt kích cỡ thương phẩm.`,
                location: f.region + ", Đồng Tháp",
                happened_at: at(0, 5, 30),
            },
            {
                stage: "kiem_dinh_ai",
                label: "AI kiểm định chất lượng",
                icon: "🤖",
                description: `AgriSenAi chấm ${p.ai.quality_score.toFixed(1)}/10 · độ chín: ${RIPENESS_LABEL[p.ai.ripeness_level].toLowerCase()}${
                    p.ai.defect_detected ? " · phát hiện khuyết tật nhẹ" : " · không phát hiện khuyết tật"
                }.`,
                location: "Ảnh chụp tại vườn, phân tích bằng Gemini Vision",
                happened_at: at(0, 9, 15),
            },
            {
                stage: "dong_goi",
                label: "Phân loại & đóng gói",
                icon: "📦",
                description: "Rửa sạch, phân loại theo kích cỡ, đóng thùng carton có lót giấy chống dập.",
                location: `Nhà sơ chế ${f.farm_name}`,
                happened_at: at(0, 15, 0),
            },
            {
                stage: "van_chuyen",
                label: "Vận chuyển",
                icon: "🚚",
                description: "Xe lạnh chuyển về kho trung chuyển AgriSenAi, giữ nhiệt độ 12–14°C.",
                location: "Kho trung chuyển TP. Cao Lãnh",
                happened_at: at(1, 6, 45),
            },
            {
                stage: "dang_ban",
                label: "Đang bán trên sàn",
                icon: "🛒",
                description: "Sản phẩm được niêm yết công khai, giá cập nhật theo bảng giá thị trường mỗi ngày.",
                location: "Sàn AgriSenAi",
                happened_at: at(1, 8, 0),
            },
        ];
    }

    /* ---------- Dựng danh sách sản phẩm hoàn chỉnh ---------- */
    const PRODUCTS = RAW.map((r, i) => {
        const id = i + 1;
        const farmer = FARMERS[r.farmer];
        const reviews = REVIEW_POOL.filter((_, k) => (k + id) % 5 !== 0).slice(0, 3 + (id % 2));
        const p = {
            id,
            slug: slugify(r.name),
            name: r.name,
            cat: r.cat,
            cat_name: CAT_NAME[r.cat],
            bg: CAT_BG[r.cat],
            origin: r.origin,
            price: r.price,
            unit: r.unit,
            quantity_available: r.qty,
            status: "active",
            qr_code: makeQr(r.name, id),
            change: r.change,
            harvest: r.harvest,
            description: DESC[r.cat],
            image: CAT_IMG[r.cat],
            gallery: [CAT_IMG[r.cat], farmer.avatar, FARM_IMG + "nong-trai-2.jpg"],
            farmer,
            ai: {
                quality_score: r.score,
                ripeness_level: r.ripeness,
                ripeness_label: RIPENESS_LABEL[r.ripeness],
                defect_detected: !!r.defect,
                defect_note: r.defectNote || "Không phát hiện dấu hiệu sâu bệnh hay hư hỏng.",
            },
            reviews: reviews.map((rv) => ({
                ...rv,
                created_at: fmtDateTime(addDays("2026-07-13", -rv.days)),
            })),
        };
        p.rating_avg = p.reviews.reduce((s, r2) => s + r2.rating, 0) / p.reviews.length;
        p.trace = buildTrace(p);
        p.trace_url = BASE_URL + p.qr_code;
        return p;
    });

    global.AgriData = {
        products: PRODUCTS,
        farmers: FARMERS,
        catName: CAT_NAME,
        ripenessLabel: RIPENESS_LABEL,
        traceBaseUrl: BASE_URL,
        bySlug: (slug) => PRODUCTS.find((p) => p.slug === slug) || null,
        byQr: (code) =>
            PRODUCTS.find((p) => p.qr_code.toLowerCase() === String(code || "").toLowerCase()) || null,
        related: (p, n) =>
            PRODUCTS.filter((x) => x.cat === p.cat && x.id !== p.id).slice(0, n || 4),
        money,
        fmtDateTime,
        detailUrl: (p) => `agrisenai-chi-tiet-san-pham.html?sp=${p.slug}`,
        traceUrl: (p) => `agrisenai-truy-xuat.html?ma=${p.qr_code}`,

        // Nội dung nhúng vào mã QR. Khi demo chạy qua web server (Laragon, LAN) thì
        // mã trỏ thẳng vào trang truy xuất đang chạy nên quét bằng điện thoại là mở
        // được ngay; khi mở file HTML trực tiếp thì dùng URL sản phẩm cho đẹp.
        qrPayload(p) {
            const isServed = /^https?:$/.test(global.location ? global.location.protocol : "");
            return isServed
                ? new URL(this.traceUrl(p), global.location.href).href
                : p.trace_url;
        },
    };
})(typeof window !== "undefined" ? window : globalThis);
