@extends('layouts.app', ['page' => 'login'])

@section('title', 'Đăng nhập')

@section('content')
    <div class="mx-auto max-w-sm py-2 sm:py-6">
        {{-- Dau trang: cung nen xanh dam + quang vang xoai nhu hero trang chu --}}
        <div class="hero-in relative isolate overflow-hidden rounded-t-card-lg bg-brand-night px-5 pb-7 pt-6 text-white">
            <div aria-hidden="true" class="pointer-events-none absolute -right-14 -top-14 -z-10 h-44 w-44 rounded-full bg-mango/30 blur-3xl"></div>
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(rgba(255,255,255,.07)_1px,transparent_1px)] [background-size:18px_18px]"></div>
            <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="" class="mb-3 h-12 w-12 rounded-card-sm bg-white p-1">
            <h1 class="text-2xl font-black">Đăng nhập nhân viên</h1>
            <p class="mt-1 text-sm leading-snug text-white/75">Dành cho nhân viên điểm thu gom / HTX để lập hồ sơ lô.</p>
        </div>

        <form method="post" action="{{ route('login') }}" class="-mt-3 space-y-4 rounded-card-lg bg-white p-5 shadow-md">
            @csrf
            @php
                $field = 'w-full rounded-card-sm border bg-white px-3.5 py-3 text-base transition focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10';
                $border = $errors->has('phone') ? 'border-red-400' : 'border-gray-200';
            @endphp

            @error('phone')
                <p role="alert" class="rounded-card-sm bg-red-50 px-3 py-2.5 text-sm font-bold text-red-800">{{ $message }}</p>
            @enderror

            <label class="block">
                <span class="mb-1.5 block text-sm font-bold">Số điện thoại</span>
                <input name="phone" type="tel" inputmode="tel" autocomplete="username" required value="{{ old('phone') }}" placeholder="09xx xxx xxx"
                       class="{{ $field }} {{ $border }}">
            </label>
            <label class="block">
                <span class="mb-1.5 block text-sm font-bold">Mật khẩu</span>
                <span class="relative block">
                    <input id="matKhau" name="password" type="password" autocomplete="current-password" required
                           class="{{ $field }} {{ $border }} pr-16">
                    {{-- Hien/an mat khau: go tren dien thoai de sai --}}
                    <button type="button" aria-controls="matKhau" aria-pressed="false"
                            onclick="const i=document.getElementById('matKhau'),s=i.type==='password';i.type=s?'text':'password';this.setAttribute('aria-pressed',s);this.textContent=s?'Ẩn':'Hiện'"
                            class="absolute right-1 top-1/2 min-h-11 -translate-y-1/2 rounded-card-sm px-3 text-sm font-bold text-brand hover:bg-brand-bg">Hiện</button>
                </span>
            </label>
            <label class="flex min-h-11 items-center gap-2.5 text-sm">
                <input type="checkbox" name="remember" value="1" class="h-5 w-5 accent-brand"> Ghi nhớ đăng nhập trên máy này
            </label>

            <button class="w-full rounded-card bg-brand py-3.5 text-base font-extrabold text-white shadow-[0_6px_16px_-6px_rgba(46,125,50,.5)] hover:bg-brand-medium">Đăng nhập</button>
        </form>

        {{-- Nguoi mua lac vao day: chi duong sang tra cuu --}}
        <a href="{{ route('public.lookup') }}" class="mt-4 flex items-center gap-3 rounded-card bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-mango/20 text-brand-night">
                <x-icon name="qr" class="h-5 w-5" />
            </span>
            <span class="text-sm">
                <b class="block">Bạn là người mua?</b>
                <span class="text-ink-sec">Không cần đăng nhập — quét QR trên thùng hoặc nhập mã lô.</span>
            </span>
        </a>
    </div>
@endsection
