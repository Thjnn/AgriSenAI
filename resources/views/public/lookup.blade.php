@extends('layouts.app', ['page' => 'lookup'])

@section('title', 'Tra cứu hồ sơ')

@section('content')
    <div class="mx-auto max-w-md py-6">
        <h1 class="mb-1 text-2xl font-extrabold">Tra cứu hồ sơ lô</h1>
        <p class="mb-5 text-sm text-ink-sec">Quét QR trên thùng hàng, hoặc nhập mã lô in trên tem.</p>

        <form method="get" action="{{ route('public.lookup') }}" class="flex gap-2">
            <input name="ma" required value="{{ $code ?? '' }}" placeholder="vd XL-261022-AB12" aria-label="Mã lô"
                   class="flex-1 rounded-card border border-gray-200 px-4 py-3 font-mono uppercase focus:border-brand focus:outline-none">
            <button class="rounded-card bg-brand px-5 font-extrabold text-white">Tra cứu</button>
        </form>

        @if ($notFound ?? false)
            <p role="alert" class="mt-4 rounded-card bg-amber-50 p-3 text-sm font-bold text-amber-900">
                Không tìm thấy hồ sơ đã công bố với mã <span class="font-mono">{{ $code }}</span>. Kiểm tra lại mã trên tem.
            </p>
        @endif
    </div>
@endsection
