@extends('layouts.app', ['page' => $page])

@section('title', 'Thử layout')

@section('content')
    <h1 class="mb-2 text-2xl font-extrabold text-ink">Trang thử layout</h1>
    <p class="mb-6 text-sm text-ink-sec">
        Trang này chỉ để đối chiếu khung nav/footer với
        <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">Test-Giao-Dien/agrisenai-home.html</code>.
        Đổi <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">?page=</code> để xem bottom-nav đổi bộ.
    </p>

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach (['home', 'market', 'ai', 'khuyennghi', 'cart', 'account'] as $key)
            <a href="{{ url('/layout-demo?page=' . $key) }}"
               class="rounded-card px-3 py-1.5 text-xs font-bold {{ $page === $key ? 'bg-brand text-white' : 'bg-gray-100 text-ink-sec hover:bg-gray-200' }}">
                {{ $key }}
            </a>
        @endforeach
    </div>

    {{-- Khối cao để kiểm tra sticky nav và chỗ chừa cho bottom-nav. --}}
    @for ($i = 1; $i <= 12; $i++)
        <div class="mb-3 rounded-card border border-gray-100 bg-white p-5">
            <div class="text-sm font-bold text-ink">Khối nội dung {{ $i }}</div>
            <div class="text-[13px] text-ink-sec">Cuộn xuống để kiểm tra nav dính trên và bottom-nav dưới.</div>
        </div>
    @endfor
@endsection
