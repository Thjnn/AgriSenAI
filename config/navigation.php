<?php

/*
 * Toàn bộ liên kết điều hướng của khung giao diện.
 *
 * 'key'  khớp với biến $page mà trang truyền vào layout — quyết định mục nào active.
 * 'icon' khớp tên trong <x-icon name="...">.
 * 'auth' => true: chỉ hiện khi đã đăng nhập (màn hình của nhân viên điểm thu gom).
 */
return [

    'top_links' => [
        ['key' => 'home',   'label' => 'Trang chủ',      'href' => '/'],
        ['key' => 'lots',   'label' => 'Lô hàng',        'href' => '/lo',       'auth' => true],
        ['key' => 'lookup', 'label' => 'Tra cứu hồ sơ',  'href' => '/tra-cuu'],
        ['key' => 'stats',  'label' => 'Thống kê',       'href' => '/thong-ke', 'auth' => true],
    ],

    'drawer_groups' => [
        [
            ['key' => 'home',   'label' => 'Trang chủ',        'href' => '/',         'icon' => 'home'],
            ['key' => 'lots',   'label' => 'Lô hàng',          'href' => '/lo',       'icon' => 'market', 'auth' => true],
            ['key' => 'new',    'label' => 'Tạo lô mới',       'href' => '/lo/tao',   'icon' => 'scan',   'auth' => true],
            ['key' => 'lookup', 'label' => 'Tra cứu hồ sơ QR', 'href' => '/tra-cuu',  'icon' => 'qr'],
        ],
        [
            ['key' => 'stats',  'label' => 'Thống kê & dataset', 'href' => '/thong-ke', 'icon' => 'chart', 'auth' => true],
        ],
    ],

    'bottom_nav' => [
        ['key' => 'home',   'label' => 'Trang chủ', 'href' => '/',         'icon' => 'home'],
        ['key' => 'lots',   'label' => 'Lô hàng',   'href' => '/lo',       'icon' => 'market'],
        ['key' => 'new',    'label' => 'Tạo lô',    'href' => '/lo/tao',   'icon' => 'scan'],
        ['key' => 'lookup', 'label' => 'Tra cứu',   'href' => '/tra-cuu',  'icon' => 'qr'],
        ['key' => 'stats',  'label' => 'Thống kê',  'href' => '/thong-ke', 'icon' => 'chart'],
    ],

    'footer_columns' => [
        [
            'title' => 'Khám phá',
            'links' => [
                ['label' => 'Trang chủ',          'href' => '/'],
                ['label' => 'Quy trình 5 bước',   'href' => '/#quy-trinh'],
                ['label' => 'Tra cứu hồ sơ QR',   'href' => '/tra-cuu'],
                ['label' => 'Đăng nhập',          'href' => '/dang-nhap'],
            ],
        ],
        [
            'title' => 'Hỗ trợ',
            'links' => [
                ['label' => 'Hướng dẫn chụp ảnh chuẩn', 'href' => '/#chup-anh'],
                ['label' => 'Về chúng tôi',             'href' => '/#ve-chung-toi'],
            ],
        ],
    ],

    'contact' => [
        ['icon' => 'pin',   'text' => 'TP. Cao Lãnh, Tỉnh Đồng Tháp'],
        ['icon' => 'mail',  'text' => 'hotro@agrisenai.vn'],
        ['icon' => 'phone', 'text' => '1900 1234'],
    ],

];
