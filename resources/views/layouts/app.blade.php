{{--
    Khung giao diện public dùng chung.

    Cách dùng:
        @extends('layouts.app', ['page' => 'home'])
        @section('title', 'Trang chủ')
        @section('content') ... @endsection

    $page: home | lots | new | lookup | stats — quyết định mục nào active.
    $bare: true = bỏ nav (trang in tem).

    Tái hiện Test-Giao-Dien/agrisenai-home.html.
--}}
@php($page = $page ?? '')
@php($bare = $bare ?? false)

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@hasSection('title')@yield('title') — AgriSenAi @else AgriSenAi — Hồ sơ lô xoài đáng tin @endif</title>
    <meta name="description" content="@yield('description', 'Chuẩn hóa hồ sơ lô xoài: chụp ảnh chuẩn, AI gợi ý ngoại quan, người phụ trách xác nhận, người mua quét QR kiểm chứng.')">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    @include('partials.pwa-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

{{-- pb chừa chỗ cho bottom-nav; từ lg trở lên bottom-nav ẩn nên bỏ pb --}}

<body class="bg-surface font-sans text-ink antialiased pb-[calc(var(--nav-h)+var(--safe-b)+8px)] lg:pb-0">

    @unless ($bare)
        <x-chrome.top-nav :page="$page" />
        <x-chrome.mob-header />
        <x-chrome.drawer :page="$page" />
    @endunless

    <main class="mx-auto max-w-[1200px] px-5 py-4 max-[360px]:px-4 max-[360px]:py-3.5 sm:px-7 sm:py-5 tab:px-8 tab:py-6 lg:py-7 xl:max-w-[1320px] xl:px-12 xl:py-8">
        @yield('content')
    </main>

    @unless ($bare)
        <x-chrome.bottom-nav :page="$page" />
    @endunless

    @stack('scripts')
    @include('partials.pwa-register')
</body>

</html>
