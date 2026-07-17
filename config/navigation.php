<?php

/*
 * Toàn bộ liên kết điều hướng của khung giao diện public.
 * Nguồn: Test-Giao-Dien/agrisenai-home.html + assets/agrisen-chrome.js
 *
 * 'key'  khớp với biến $page mà trang truyền vào layout — quyết định mục nào active.
 * 'icon' khớp tên trong <x-icon name="...">.
 */
return [

    'top_links' => [
        ['key' => 'home',       'label' => 'Trang chủ',      'href' => '/'],
        ['key' => 'market',     'label' => 'Sàn thị trường', 'href' => '#'],
        ['key' => 'ai',         'label' => 'AI đánh giá',    'href' => '/danh-gia'],
        ['key' => 'khuyennghi', 'label' => 'Khuyến nghị',    'href' => '#'],
        ['key' => 'about',      'label' => 'Về chúng tôi',   'href' => '#'],
    ],

    /*
     * Drawer chia HAI NHÓM, ngăn bởi một đường kẻ (drawer-divider trong demo).
     * Đối chiếu: <nav class="drawer-nav"> trong agrisenai-home.html — 5 link, 1 divider,
     * rồi 3 link. Nhãn copy nguyên văn ("AI đánh giá chất lượng", không phải
     * "AI đánh giá" — nhãn ngắn đó là của top-nav).
     *
     * Lưu ý: agrisen-chrome.js có thêm link 'notif' (Thông báo) mà agrisenai-home.html
     * không có. Bám theo home.html vì đó là bản demo được chọn làm chuẩn.
     */
    'drawer_groups' => [
        [
            ['key' => 'home',       'label' => 'Trang chủ',             'href' => '/',         'icon' => 'home'],
            ['key' => 'market',     'label' => 'Sàn thị trường',        'href' => '#',         'icon' => 'market'],
            ['key' => 'ai',         'label' => 'AI đánh giá chất lượng','href' => '/danh-gia', 'icon' => 'ai'],
            ['key' => 'khuyennghi', 'label' => 'Khuyến nghị giá bán',   'href' => '#',         'icon' => 'chart'],
            ['key' => 'truyxuat',   'label' => 'Quét QR truy xuất',     'href' => '#',         'icon' => 'qr'],
        ],
        [
            ['key' => 'cart',     'label' => 'Giỏ hàng',  'href' => '#', 'icon' => 'cart'],
            ['key' => 'account',  'label' => 'Tài khoản', 'href' => '#', 'icon' => 'user'],
            ['key' => 'settings', 'label' => 'Cài đặt',   'href' => '#', 'icon' => 'settings'],
        ],
    ],

    'bottom_nav' => [
        'default' => [
            ['key' => 'home',    'label' => 'Trang chủ',  'href' => '/',         'icon' => 'home'],
            ['key' => 'market',  'label' => 'Thị trường', 'href' => '#',         'icon' => 'market'],
            ['key' => 'ai-scan', 'label' => 'AI Scan',    'href' => '/danh-gia', 'icon' => 'scan'],
            ['key' => 'cart',    'label' => 'Giỏ hàng',   'href' => '#',         'icon' => 'cart'],
            ['key' => 'account', 'label' => 'Tài khoản',  'href' => '#',         'icon' => 'user'],
        ],
        'advisory' => [
            ['key' => 'home',       'label' => 'Trang chủ',  'href' => '/',         'icon' => 'home'],
            ['key' => 'market',     'label' => 'Thị trường', 'href' => '#',         'icon' => 'market'],
            ['key' => 'ai-scan',    'label' => 'AI Scan',    'href' => '/danh-gia', 'icon' => 'scan'],
            ['key' => 'khuyennghi', 'label' => 'Khuyến nghị','href' => '#',         'icon' => 'chart'],
            ['key' => 'account',    'label' => 'Tài khoản',  'href' => '#',         'icon' => 'user'],
        ],
    ],

    'footer_columns' => [
        [
            'title' => 'Khám phá',
            'links' => [
                ['label' => 'Trang chủ',        'href' => '/'],
                ['label' => 'Nông sản',         'href' => '#'],
                ['label' => 'AI đánh giá',      'href' => '/danh-gia'],
                ['label' => 'Khuyến nghị giá',  'href' => '#'],
                ['label' => 'Quét QR truy xuất','href' => '#'],
            ],
        ],
        [
            'title' => 'Hỗ trợ',
            'links' => [
                ['label' => 'Hướng dẫn sử dụng',    'href' => '#'],
                ['label' => 'Câu hỏi thường gặp',   'href' => '#'],
                ['label' => 'Chính sách bảo mật',   'href' => '#'],
                ['label' => 'Điều khoản dịch vụ',   'href' => '#'],
                ['label' => 'Về chúng tôi',         'href' => '#'],
            ],
        ],
    ],

    'contact' => [
        ['icon' => 'pin',   'text' => 'TP. Cao Lãnh, Tỉnh Đồng Tháp'],
        ['icon' => 'mail',  'text' => 'hotro@agrisenai.vn'],
        ['icon' => 'phone', 'text' => '1900 1234'],
    ],

];
