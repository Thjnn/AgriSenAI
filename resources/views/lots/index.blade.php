@extends('layouts.app', ['page' => 'lots'])

@section('title', 'Lô hàng')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">Lô hàng</h1>
            <p class="text-sm text-ink-sec">{{ auth()->user()->isAdmin() ? 'Tất cả tổ chức' : auth()->user()->org_name }}</p>
        </div>
        <a href="{{ route('lots.create') }}" class="rounded-card bg-brand px-4 py-2.5 font-extrabold text-white hover:bg-brand-medium">+ Tạo lô</a>
    </div>

    <nav class="mb-4 flex flex-wrap gap-2" aria-label="Lọc theo trạng thái">
        <a href="{{ route('lots.index') }}" class="rounded-full px-3 py-1.5 text-xs font-bold {{ ! $status ? 'bg-brand text-white' : 'bg-gray-100 text-ink-sec' }}">Tất cả</a>
        @foreach (\App\Models\Lot::STATUSES as $key => $label)
            <a href="{{ route('lots.index', ['status' => $key]) }}" class="rounded-full px-3 py-1.5 text-xs font-bold {{ $status === $key ? 'bg-brand text-white' : 'bg-gray-100 text-ink-sec' }}">{{ $label }}</a>
        @endforeach
    </nav>

    @if ($lots->isEmpty())
        <div class="rounded-card bg-white p-8 text-center text-ink-sec shadow-sm">
            Chưa có lô nào. <a href="{{ route('lots.create') }}" class="font-bold text-brand">Tạo lô đầu tiên</a>.
        </div>
    @else
        <ul class="space-y-2">
            @foreach ($lots as $lot)
                <li>
                    <a href="{{ route('lots.show', $lot) }}" class="flex items-center justify-between gap-3 rounded-card bg-white p-4 shadow-sm hover:shadow-md">
                        <span>
                            <b class="block font-mono">{{ $lot->code }}</b>
                            <span class="text-[13px] text-ink-sec">
                                {{ $lot->supplier }}{{ $lot->variety ? ' · '.$lot->variety : '' }} ·
                                {{ rtrim(rtrim(number_format($lot->weight_kg, 2, ',', '.'), '0'), ',') }} kg ·
                                {{ $lot->photos_count }} ảnh · thu hoạch {{ $lot->harvest_date->format('d/m') }}
                            </span>
                        </span>
                        @include('lots._status', ['status' => $lot->status])
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $lots->links() }}</div>
    @endif
@endsection
