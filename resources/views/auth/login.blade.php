@extends('layouts.app', ['page' => 'login'])

@section('title', 'Đăng nhập')

@section('content')
    <div class="mx-auto max-w-sm py-6">
        <h1 class="mb-1 text-2xl font-extrabold">Đăng nhập</h1>
        <p class="mb-6 text-sm text-ink-sec">Dành cho nhân viên điểm thu gom / HTX. Người mua không cần đăng nhập — chỉ cần quét QR trên thùng hàng.</p>

        <form method="post" action="{{ route('login') }}" class="space-y-4 rounded-card bg-white p-5 shadow-md">
            @csrf
            <label class="block">
                <span class="mb-1 block text-sm font-bold">Số điện thoại</span>
                <input name="phone" type="tel" inputmode="tel" autocomplete="username" required value="{{ old('phone') }}"
                       class="w-full rounded-card-sm border border-gray-200 px-3 py-3 text-base focus:border-brand focus:outline-none">
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-bold">Mật khẩu</span>
                <input name="password" type="password" autocomplete="current-password" required
                       class="w-full rounded-card-sm border border-gray-200 px-3 py-3 text-base focus:border-brand focus:outline-none">
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 accent-brand"> Ghi nhớ đăng nhập
            </label>

            @error('phone')
                <p class="text-sm font-bold text-red-700">{{ $message }}</p>
            @enderror

            <button class="w-full rounded-card bg-brand py-3 text-base font-extrabold text-white hover:bg-brand-medium">Đăng nhập</button>
        </form>
    </div>
@endsection
