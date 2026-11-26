@extends('layouts.app', ['page' => 'lots'])

@section('title', 'Lô hàng')

@section('content')
    <div class="mb-4 flex items-end justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold">Lô hàng</h1>
            <p class="truncate text-sm text-ink-sec">{{ auth()->user()->isAdmin() ? 'Tất cả tổ chức' : auth()->user()->org_name }}</p>
        </div>
        <a href="{{ route('lots.create') }}" class="shrink-0 rounded-card bg-brand px-4 py-2.5 font-extrabold text-white transition hover:bg-brand-medium active:scale-[0.97]">+ Tạo lô</a>
    </div>

    {{-- Mobile: mot hang cuon ngang thay vi rot dong --}}
    <nav aria-label="Lọc theo trạng thái"
         class="-mx-5 mb-4 flex gap-2 overflow-x-auto px-5 pb-1 max-[360px]:-mx-4 max-[360px]:px-4 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0 [&::-webkit-scrollbar]:hidden">
        @php($chip = 'shrink-0 rounded-full px-3.5 py-2 text-[13px] font-bold transition')
        <a href="{{ route('lots.index') }}" class="{{ $chip }} {{ ! $status ? 'bg-brand text-white' : 'bg-white text-ink-sec ring-1 ring-gray-100' }}">Tất cả</a>
        @foreach (\App\Models\Lot::STATUSES as $key => $label)
            <a href="{{ route('lots.index', ['status' => $key]) }}" class="{{ $chip }} {{ $status === $key ? 'bg-brand text-white' : 'bg-white text-ink-sec ring-1 ring-gray-100' }}">{{ $label }}</a>
        @endforeach
    </nav>

    @if ($lots->isEmpty())
        <div class="rounded-card bg-white p-8 text-center text-ink-sec shadow-sm">
            Chưa có lô nào. <a href="{{ route('lots.create') }}" class="font-bold text-brand">Tạo lô đầu tiên</a>.
        </div>
    @else
        <ul class="grid gap-2.5 lg:grid-cols-2">
            @foreach ($lots as $lot)
                @php($thumb = $lot->photos->first())
                <li class="list-in" style="--i: {{ $loop->index }}">
                    <a href="{{ route('lots.show', $lot) }}" class="flex items-center gap-3 rounded-card bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        @if ($thumb)
                            <img src="{{ $thumb->url() }}" alt="" class="h-16 w-16 shrink-0 rounded-card-sm object-cover" loading="lazy">
                        @else
                            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-card-sm bg-brand-bg text-brand">
                                <x-icon name="scan" class="h-6 w-6" />
                            </span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-2">
                                <b class="truncate font-mono text-[15px]">{{ $lot->code }}</b>
                                @include('lots._status', ['status' => $lot->status])
                            </span>
                            <span class="mt-0.5 block truncate text-[13px] text-ink">{{ $lot->supplier }}{{ $lot->variety ? ' · '.$lot->variety : '' }}</span>
                            <span class="block text-[12px] text-ink-sec">
                                {{ rtrim(rtrim(number_format($lot->weight_kg, 2, ',', '.'), '0'), ',') }} kg ·
                                {{ $lot->photos_count }} ảnh · thu hoạch {{ $lot->harvest_date->format('d/m') }}
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $lots->links() }}</div>
    @endif
@endsection
