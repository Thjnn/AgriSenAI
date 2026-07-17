/*!
 * AgriSenAi — khung giao diện dùng chung (nav desktop, drawer, header mobile,
 * footer, bottom-nav, trợ lý chat) cho các trang demo.
 *
 * Cách dùng: đặt trên <body> hai thuộc tính rồi nạp file này trước agrisen-data.js:
 *   <body data-page="cart" data-role="nguoi-mua">
 *   <script src="assets/agrisen-chrome.js"></script>
 *
 * data-page: home | market | ai | khuyennghi | cart | account | notif
 * data-role: nguoi-mua (mặc định) | nong-dan   → quyết định trang tài khoản + hồ sơ ở drawer
 *
 * Giữ nguyên class/CSS của agrisen-ui.css nên trông y hệt các trang cũ.
 */
(function () {
    "use strict";

    var body = document.body;
    var page = body.dataset.page || "";
    var role = body.dataset.role === "nong-dan" ? "nong-dan" : "nguoi-mua";

    var accountHref =
        role === "nong-dan"
            ? "agrisenai-tai-khoan-nong-dan.html"
            : "agrisenai-tai-khoan-nguoi-mua.html";
    var cartHref = "agrisenai-gio-hang.html";
    var notifHref = "agrisenai-thong-bao.html";

    var user =
        role === "nong-dan"
            ? { avatar: "👨‍🌾", name: "Nguyễn Văn Sáu", role: "HTX Xoài Cao Lãnh · Cao Lãnh" }
            : { avatar: "🛒", name: "Trần Thu Hà", role: "Người mua · TP. Cao Lãnh" };

    /* ---------- SVG icon dùng lại ---------- */
    var I = {
        home: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>',
        market: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/></svg>',
        ai: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>',
        chart: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>',
        qr: '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path fill="currentColor" d="M3 4.5v5c0 .83.67 1.5 1.5 1.5h5c.83 0 1.5-.67 1.5-1.5v-5c0-.83-.67-1.5-1.5-1.5h-5C3.67 3 3 3.67 3 4.5M5 5h4v4H5zM3 19.5c0 .83.67 1.5 1.5 1.5h5c.83 0 1.5-.67 1.5-1.5v-5c0-.83-.67-1.5-1.5-1.5h-5c-.83 0-1.5.67-1.5 1.5zM5 15h4v4H5zM19.5 3h-5c-.83 0-1.5.67-1.5 1.5v5c0 .83.67 1.5 1.5 1.5h5c.83 0 1.5-.67 1.5-1.5v-5c0-.83-.67-1.5-1.5-1.5M19 9h-4V5h4zm-6 4h2v2h-2zm2 2h2v2h-2zm-2 2h2v2h-2zm4 0h2v2h-2zm2 2h2v2h-2zm-4 0h2v2h-2zm2-6h2v2h-2zm2 2h2v2h-2z"/></svg>',
        cart: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>',
        user: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>',
        bell: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>',
        search: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>',
        settings: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        chat: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M14.343 3A6 6 0 0 0 22 10.657V18a1 1 0 0 1-1 1h-6.55L12 22.5L9.55 19H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm5.186-1.68a.507.507 0 0 1 .942 0l.253.61a4.37 4.37 0 0 0 2.25 2.327l.717.32a.53.53 0 0 1 0 .962l-.758.338a4.36 4.36 0 0 0-2.22 2.25l-.246.566a.506.506 0 0 1-.934 0l-.247-.565a4.36 4.36 0 0 0-2.219-2.251l-.76-.338a.53.53 0 0 1 0-.963l.718-.32a4.37 4.37 0 0 0 2.251-2.325z"/></svg>',
        close: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        send: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>',
        fb: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.55-1.5H17V3.6c-.3-.04-1.3-.13-2.47-.13-2.45 0-4.13 1.5-4.13 4.25v2.37H7.7V13h2.7v8h3.1z"/></svg>',
        yt: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.6 7.2a2.5 2.5 0 00-1.75-1.77C18.3 5 12 5 12 5s-6.3 0-7.85.43A2.5 2.5 0 002.4 7.2 26 26 0 002 12a26 26 0 00.4 4.8 2.5 2.5 0 001.75 1.77C5.7 19 12 19 12 19s6.3 0 7.85-.43a2.5 2.5 0 001.75-1.77A26 26 0 0022 12a26 26 0 00-.4-4.8zM10 15V9l5.2 3-5.2 3z"/></svg>',
        pin: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>',
        mail: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>',
        phone: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>',
        scan: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>',
    };

    var LOGO_MARK = "../public/images/logo-agrisenai-mark.png";

    function on(cond, cls) {
        return cond ? cls : "";
    }

    /* ---------- Liên kết điều hướng chính ---------- */
    var TOP_LINKS = [
        { key: "home", href: "agrisenai-home.html", label: "Trang chủ" },
        { key: "market", href: "agrisenai-thi-truong.html", label: "Sàn thị trường" },
        { key: "ai", href: "agrisenai-ai-danh-gia.html", label: "AI đánh giá" },
        { key: "khuyennghi", href: "agrisenai-khuyen-nghi.html", label: "Khuyến nghị" },
        { key: "about", href: "#", label: "Về chúng tôi" },
    ];

    function topNav() {
        var links = TOP_LINKS.map(function (l) {
            return (
                '<li><a href="' +
                l.href +
                '" class="' +
                on(page === l.key, "active") +
                '">' +
                l.label +
                "</a></li>"
            );
        }).join("");

        return (
            '<nav class="top-nav"><div class="top-nav-inner">' +
            '<a href="agrisenai-home.html" class="top-nav-logo">' +
            '<div class="top-nav-logo-icon"><img src="' +
            LOGO_MARK +
            '" alt="AgriSenAi" /></div>AgriSenAi</a>' +
            '<ul class="top-nav-links">' +
            links +
            "</ul>" +
            '<div class="top-nav-right">' +
            '<div class="top-nav-search">' +
            I.search +
            '<input type="text" placeholder="Tìm nông sản..." /></div>' +
            '<a class="top-nav-icon" href="' +
            notifHref +
            '" aria-label="Thông báo">' +
            I.bell +
            '<span class="dot"></span></a>' +
            '<a class="top-nav-icon" href="' +
            cartHref +
            '" aria-label="Giỏ hàng">' +
            I.cart +
            "</a>" +
            '<a class="top-nav-avatar" href="' +
            accountHref +
            '" aria-label="Tài khoản">' +
            I.user +
            "</a>" +
            "</div></div></nav>"
        );
    }

    function drawerLink(key, href, icon, label) {
        var active = on(page === key, "active");
        var h = href ? ' href="' + href + '"' : "";
        return (
            '<a class="drawer-link ' + active + '"' + h + ">" + icon + label + "</a>"
        );
    }

    function drawer() {
        return (
            '<div class="drawer-overlay" id="drawerOverlay"></div>' +
            '<aside class="drawer" id="drawer">' +
            '<div class="drawer-head">' +
            '<div class="drawer-logo"><img src="' +
            LOGO_MARK +
            '" alt="AgriSenAi" /></div>' +
            "<div><div class=\"drawer-brand\">Agri<span>SenAi</span></div>" +
            '<div class="drawer-tag">Sàn nông sản Đồng Tháp</div></div></div>' +
            '<nav class="drawer-nav">' +
            drawerLink("home", "agrisenai-home.html", I.home, "Trang chủ") +
            drawerLink("market", "agrisenai-thi-truong.html", I.market, "Sàn thị trường") +
            drawerLink("ai", "agrisenai-ai-danh-gia.html", I.ai, "AI đánh giá chất lượng") +
            drawerLink("khuyennghi", "agrisenai-khuyen-nghi.html", I.chart, "Khuyến nghị giá bán") +
            drawerLink("truyxuat", "agrisenai-truy-xuat.html", I.qr, "Quét QR truy xuất") +
            '<div class="drawer-divider"></div>' +
            drawerLink("cart", cartHref, I.cart, "Giỏ hàng") +
            drawerLink("account", accountHref, I.user, "Tài khoản") +
            drawerLink("notif", notifHref, I.bell, "Thông báo") +
            drawerLink("settings", "#", I.settings, "Cài đặt") +
            "</nav>" +
            '<div class="drawer-foot"><div class="drawer-user">' +
            '<div class="drawer-user-avatar">' +
            user.avatar +
            "</div><div>" +
            '<div class="drawer-user-name">' +
            user.name +
            "</div>" +
            '<div class="drawer-user-role">' +
            user.role +
            "</div></div></div></div></aside>"
        );
    }

    function mobHeader() {
        return (
            '<header class="mob-header"><div class="mob-header-row">' +
            '<div class="mob-header-left">' +
            '<button class="mob-menu-btn" id="menuBtn" aria-label="Menu"><span></span><span></span><span></span></button>' +
            '<div class="mob-logo"><img src="' +
            LOGO_MARK +
            '" alt="AgriSenAi" /></div>' +
            '<div><div class="mob-brand">AgriSenAi</div>' +
            '<div class="mob-sub">Nông sản Đồng Tháp</div></div></div>' +
            '<div class="mob-icons">' +
            '<a class="mob-icon-btn" href="' +
            cartHref +
            '" aria-label="Giỏ hàng">' +
            I.cart +
            "</a>" +
            '<a class="mob-icon-btn" href="' +
            notifHref +
            '" aria-label="Thông báo">' +
            I.bell +
            '<span class="dot"></span></a>' +
            "</div></div>" +
            '<div class="mob-search">' +
            I.search +
            '<input type="text" placeholder="Tìm nông sản..." /></div></header>'
        );
    }

    function footer() {
        return (
            '<footer class="site-footer"><div class="footer-inner">' +
            '<div class="footer-col"><div class="footer-logo-row">' +
            '<div class="footer-logo"><img src="' +
            LOGO_MARK +
            '" alt="AgriSenAi" /></div>' +
            '<div class="footer-brand-name">Agri<span>SenAi</span></div></div>' +
            '<p class="footer-desc">Sàn nông sản Đồng Tháp ứng dụng AI đánh giá chất lượng và khuyến nghị thời điểm bán tối ưu, kết nối nông dân với người mua.</p>' +
            '<div class="footer-socials">' +
            '<a class="footer-social" aria-label="Facebook">' +
            I.fb +
            '</a><a class="footer-social" aria-label="YouTube">' +
            I.yt +
            "</a></div></div>" +
            '<div class="footer-col"><h4 class="footer-title">Khám phá</h4>' +
            '<a href="agrisenai-home.html">Trang chủ</a>' +
            '<a href="agrisenai-thi-truong.html">Nông sản</a>' +
            '<a href="agrisenai-ai-danh-gia.html">AI đánh giá</a>' +
            '<a href="agrisenai-khuyen-nghi.html">Khuyến nghị giá</a>' +
            '<a href="agrisenai-truy-xuat.html">Quét QR truy xuất</a></div>' +
            '<div class="footer-col"><h4 class="footer-title">Hỗ trợ</h4>' +
            '<a href="#">Hướng dẫn sử dụng</a><a href="#">Câu hỏi thường gặp</a>' +
            '<a href="#">Chính sách bảo mật</a><a href="#">Điều khoản dịch vụ</a>' +
            '<a href="#">Về chúng tôi</a></div>' +
            '<div class="footer-col"><h4 class="footer-title">Liên hệ</h4>' +
            '<div class="footer-contact">' +
            I.pin +
            "<span>TP. Cao Lãnh, Tỉnh Đồng Tháp</span></div>" +
            '<div class="footer-contact">' +
            I.mail +
            "<span>hotro@agrisenai.vn</span></div>" +
            '<div class="footer-contact">' +
            I.phone +
            "<span>1900 1234</span></div></div>" +
            "</div>" +
            '<div class="footer-bottom">© 2026 AgriSenAi · Sàn nông sản Đồng Tháp. Mọi quyền được bảo lưu.</div></footer>'
        );
    }

    function bottomNav() {
        return (
            '<nav class="bottom-nav">' +
            '<a class="bnav ' +
            on(page === "home", "active") +
            '" href="agrisenai-home.html">' +
            I.home +
            "<span>Trang chủ</span></a>" +
            '<a class="bnav ' +
            on(page === "market", "active") +
            '" href="agrisenai-thi-truong.html">' +
            I.market +
            "<span>Thị trường</span></a>" +
            '<a class="bnav bnav-scan" href="agrisenai-ai-danh-gia.html">' +
            '<div class="bnav-scan-circle">' +
            I.scan +
            '</div><span class="bnav-scan-label">AI Scan</span></a>' +
            '<a class="bnav ' +
            on(page === "cart", "active") +
            '" href="' +
            cartHref +
            '">' +
            I.cart +
            "<span>Giỏ hàng</span></a>" +
            '<a class="bnav ' +
            on(page === "account", "active") +
            '" href="' +
            accountHref +
            '">' +
            I.user +
            "<span>Tài khoản</span></a>" +
            "</nav>"
        );
    }

    function chat() {
        return (
            '<button class="chat-fab" id="chatFab" aria-label="Trợ lý AI giá bán">' +
            I.chat +
            "</button>" +
            '<div class="chat-overlay" id="chatOverlay"></div>' +
            '<aside class="chat-panel" id="chatPanel" aria-hidden="true">' +
            '<div class="chat-head"><div class="chat-head-info">' +
            '<div class="chat-avatar">🤖</div><div>' +
            '<div class="chat-title">Trợ lý giá AgriSenAi</div>' +
            '<div class="chat-status"><span class="dot-online"></span> Tư vấn giá bán & thời điểm bán</div>' +
            "</div></div>" +
            '<button class="chat-close" id="chatClose" aria-label="Đóng">' +
            I.close +
            "</button></div>" +
            '<div class="chat-body" id="chatBody">' +
            '<div class="msg ai"><div class="msg-bubble">Chào anh/chị 👋 Em là trợ lý AI của AgriSenAi. Anh/chị muốn tư vấn <b>giá bán</b> hay <b>thời điểm bán</b> loại nông sản nào ạ?</div></div>' +
            "</div>" +
            '<div class="chat-suggests" id="chatSuggests">' +
            '<button class="chat-chip">Xoài đang được giá không?</button>' +
            '<button class="chat-chip">Quýt hồng nên bán khi nào?</button>' +
            '<button class="chat-chip">Dự báo giá sầu riêng Ri6</button></div>' +
            '<div class="chat-input">' +
            '<input type="text" id="chatInput" placeholder="Hỏi về giá & thời điểm bán..." autocomplete="off" />' +
            '<button class="chat-send" id="chatSend" aria-label="Gửi">' +
            I.send +
            "</button></div></aside>"
        );
    }

    /* ---------- Tiêm vào trang ---------- */
    body.insertAdjacentHTML(
        "afterbegin",
        topNav() + drawer() + mobHeader(),
    );
    body.insertAdjacentHTML("beforeend", footer() + bottomNav() + chat());

    /* ---------- Drawer ---------- */
    var drawerEl = document.getElementById("drawer");
    var overlay = document.getElementById("drawerOverlay");
    var menuBtn = document.getElementById("menuBtn");

    function openDrawer() {
        drawerEl.classList.add("open");
        overlay.classList.add("open");
        document.body.style.overflow = "hidden";
    }
    function closeDrawer() {
        drawerEl.classList.remove("open");
        overlay.classList.remove("open");
        document.body.style.overflow = "";
    }
    menuBtn.addEventListener("click", openDrawer);
    overlay.addEventListener("click", closeDrawer);
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") closeDrawer();
    });

    /* ---------- Trợ lý chat (hardcode — sẽ ghép API sau) ---------- */
    (function () {
        var ov = document.getElementById("chatOverlay");
        var panel = document.getElementById("chatPanel");
        var chatBody = document.getElementById("chatBody");
        var chatInput = document.getElementById("chatInput");
        var sendBtn = document.getElementById("chatSend");
        var closeBtn = document.getElementById("chatClose");
        var fab = document.getElementById("chatFab");

        function openChat() {
            panel.classList.add("open");
            ov.classList.add("open");
            panel.setAttribute("aria-hidden", "false");
            document.body.style.overflow = "hidden";
            setTimeout(function () {
                chatInput && chatInput.focus();
            }, 300);
        }
        function closeChat() {
            panel.classList.remove("open");
            ov.classList.remove("open");
            panel.setAttribute("aria-hidden", "true");
            document.body.style.overflow = "";
        }
        fab.addEventListener("click", openChat);
        closeBtn.addEventListener("click", closeChat);
        ov.addEventListener("click", closeChat);
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && panel.classList.contains("open")) closeChat();
        });

        function addMsg(html, who) {
            var el = document.createElement("div");
            el.className = "msg " + who;
            el.innerHTML = '<div class="msg-bubble">' + html + "</div>";
            chatBody.appendChild(el);
            chatBody.scrollTop = chatBody.scrollHeight;
        }
        function showTyping() {
            var el = document.createElement("div");
            el.className = "msg ai chat-typing";
            el.innerHTML =
                '<div class="msg-bubble"><span></span><span></span><span></span></div>';
            chatBody.appendChild(el);
            chatBody.scrollTop = chatBody.scrollHeight;
            return el;
        }
        function answer(q) {
            var t = q.toLowerCase();
            if (t.indexOf("xoài") >= 0)
                return "Xoài cát Hòa Lộc đang ~<b>45.000đ/kg</b> (＋5,2%), vùng giá cao nhất 6 tháng.<br>👉 Nên bán trong <b>2–3 ngày tới</b> trước khi vào chính vụ.";
            if (t.indexOf("quýt") >= 0)
                return "Quýt hồng Lai Vung ~<b>35.000đ/kg</b>, đang ở vùng đáy nhưng cầu dịp Tết dự kiến tăng.<br>👉 Nếu chưa cần vốn gấp, có thể <b>chờ 1–2 tuần</b> để giá phục hồi 8–10%.";
            if (t.indexOf("sầu riêng") >= 0 || t.indexOf("ri6") >= 0)
                return "Sầu riêng Ri6 ~<b>85.000đ/kg</b> (＋6,5%), đang vào vụ chính, nhu cầu xuất khẩu tốt.<br>👉 Khuyến nghị <b>bán ngay</b> để tránh rớt giá cuối vụ.";
            if (t.indexOf("nhãn") >= 0)
                return "Nhãn Ido ~<b>62.000đ/kg</b> (＋4,1%), cầu vượt cung mùa này.<br>👉 Có thể <b>bán ngay</b>, giá đang thuận lợi.";
            if (t.indexOf("được giá") >= 0 || t.indexOf("loại nào") >= 0)
                return "Hôm nay được giá nhất: <b>Sầu riêng (＋9,2%)</b>, <b>Quýt đường loại 1 (＋5,5%)</b> và <b>Xoài cát Hòa Lộc (＋5,2%)</b> 🌟.";
            return "Cảm ơn câu hỏi của anh/chị 🌱 Trợ lý AI thời gian thực đang được hoàn thiện. Tạm thời anh/chị tham khảo mục <b>Bảng giá hôm nay</b> và <b>AI dự báo giá</b> để chọn thời điểm bán tối ưu nhé!";
        }
        function sendMsg(text) {
            text = (text || chatInput.value).trim();
            if (!text) return;
            var safe = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;");
            addMsg(safe, "user");
            chatInput.value = "";
            var typingEl = showTyping();
            setTimeout(function () {
                typingEl.remove();
                addMsg(answer(text), "ai");
            }, 900);
        }
        sendBtn.addEventListener("click", function () {
            sendMsg();
        });
        chatInput.addEventListener("keydown", function (e) {
            if (e.key === "Enter") sendMsg();
        });
        document
            .querySelectorAll("#chatSuggests .chat-chip")
            .forEach(function (c) {
                c.addEventListener("click", function () {
                    sendMsg(c.textContent);
                });
            });
    })();
})();
