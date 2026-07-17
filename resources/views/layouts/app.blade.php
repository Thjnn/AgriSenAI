{{--
    Khung giao diện public dùng chung.

    Cách dùng:
        @extends('layouts.app', ['page' => 'home'])
        @section('title', 'Trang chủ')
        @section('content') ... @endsection

    $page: home | market | ai | khuyennghi | cart | account | notif
           Quyết định mục nào active và bottom-nav dùng bộ nào.

    Tái hiện Test-Giao-Dien/agrisenai-home.html. Mobile (< lg) không có footer — đúng demo.
--}}
@php($page = $page ?? '')

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@hasSection('title')@yield('title') — AgriSenAi @else AgriSenAi — Sàn nông sản Đồng Tháp @endif</title>
    <meta name="description" content="@yield('description', 'Sàn nông sản Đồng Tháp ứng dụng AI đánh giá chất lượng và khuyến nghị thời điểm bán tối ưu, kết nối nông dân với người mua.')">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

{{-- pb chừa chỗ cho bottom-nav; từ lg trở lên bottom-nav ẩn nên bỏ pb --}}
<body class="bg-surface font-sans text-ink antialiased pb-[calc(var(--nav-h)+var(--safe-b)+8px)] lg:pb-0">

    <x-chrome.top-nav :page="$page" />
    <x-chrome.mob-header />
    <x-chrome.drawer :page="$page" />

    <main class="mx-auto max-w-[1200px] px-5 py-4 max-[360px]:px-4 max-[360px]:py-3.5 sm:px-7 sm:py-5 tab:px-8 tab:py-6 lg:py-7 xl:max-w-[1320px] xl:px-12 xl:py-8">
        @yield('content')
    </main>

    <x-chrome.footer />
    <x-chrome.bottom-nav :page="$page" />
    <x-chrome.chat />

    @stack('scripts')
</body>

</html>
