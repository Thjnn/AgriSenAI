<?php

/*
 * Toàn bộ liên kết điều hướng của khung giao diện.
 *
 * 'key'  khớp với biến $page mà trang truyền vào layout — quyết định mục nào active.
 * 'icon' khớp tên trong <x-icon name="...">.
 * 'auth' => true: chỉ hiện khi đã đăng nhập (màn hình của nhân viên điểm thu gom).
 * 'admin' => true: chỉ hiện cho admin (trang Thống kê trả 403 với nhân viên).
 */
return [

    'top_links' => [
        ['key' => 'home',   'label' => 'Trang chủ',      'href' => '/'],
        ['key' => 'lots',   'label' => 'Lô hàng',        'href' => '/lo',       'auth' => true],
        ['key' => 'lookup', 'label' => 'Tra cứu hồ sơ',  'href' => '/tra-cuu'],
        ['key' => 'stats',  'label' => 'Thống kê',       'href' => '/thong-ke', 'admin' => true],
    ],

    'drawer_groups' => [
        [
            ['key' => 'home',   'label' => 'Trang chủ',        'href' => '/',         'icon' => 'home'],
            ['key' => 'lots',   'label' => 'Lô hàng',          'href' => '/lo',       'icon' => 'market', 'auth' => true],
            ['key' => 'new',    'label' => 'Tạo lô mới',       'href' => '/lo/tao',   'icon' => 'scan',   'auth' => true],
            ['key' => 'lookup', 'label' => 'Tra cứu hồ sơ QR', 'href' => '/tra-cuu',  'icon' => 'qr'],
        ],
        [
            ['key' => 'stats',  'label' => 'Thống kê & dataset', 'href' => '/thong-ke', 'icon' => 'chart', 'admin' => true],
        ],
    ],

    'bottom_nav' => [
        ['key' => 'home',   'label' => 'Trang chủ', 'href' => '/',         'icon' => 'home'],
        ['key' => 'lots',   'label' => 'Lô hàng',   'href' => '/lo',       'icon' => 'market'],
        ['key' => 'new',    'label' => 'Tạo lô',    'href' => '/lo/tao',   'icon' => 'scan'],
        ['key' => 'lookup', 'label' => 'Tra cứu',   'href' => '/tra-cuu',  'icon' => 'qr'],
        ['key' => 'stats',  'label' => 'Thống kê',  'href' => '/thong-ke', 'icon' => 'chart', 'admin' => true],
    ],

];
