@extends('layouts.app', ['page' => 'stats'])

@section('title', 'Thống kê')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-extrabold">Thống kê vận hành</h1>
        <a href="{{ route('stats.dataset') }}" class="rounded-card bg-brand px-4 py-2.5 font-extrabold text-white">⬇ Xuất dataset (ảnh + nhãn)</a>
    </div>

    @php $tile = 'rounded-card bg-white p-4 shadow-sm'; @endphp
    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="{{ $tile }}"><span class="text-sm text-ink-sec">Tổng số lô</span><b class="block text-3xl">{{ $total }}</b></div>
        <div class="{{ $tile }}"><span class="text-sm text-ink-sec">Đã công bố</span><b class="block text-3xl">{{ $byStatus['published'] ?? 0 }}</b></div>
        <div class="{{ $tile }}">
            <span class="text-sm text-ink-sec">Thời gian lập hồ sơ (TB)</span>
            <b class="block text-3xl">{{ $avgMinutes === null ? '—' : number_format($avgMinutes, 0).' phút' }}</b>
            <span class="text-[11px] text-ink-light">tạo lô → công bố</span>
        </div>
        <div class="{{ $tile }}">
            <span class="text-sm text-ink-sec">Lượt gọi AI</span>
            <b class="block text-3xl">{{ $aiCalls }}</b>
            <span class="text-[11px] text-ink-light">{{ $aiErrors }} lỗi · trung vị {{ $medianLatency === null ? '—' : number_format($medianLatency / 1000, 1, ',', '').' s' }}</span>
        </div>
    </div>

    <section class="mb-6 rounded-card bg-white p-5 shadow-sm">
        <h2 class="mb-1 text-lg font-extrabold">Gợi ý AI được người dùng chấp nhận</h2>
        <p class="mb-4 text-sm text-ink-sec">“AI đúng bao nhiêu % theo đánh giá của chính người dùng” — chỉ tính các lần có gợi ý AI (bỏ nhập thủ công).</p>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[480px] text-sm">
                <thead class="text-left text-ink-sec">
                    <tr><th class="py-2">Tiêu chí</th><th>Đồng ý</th><th>Sửa</th><th>Bác bỏ</th><th>Thủ công</th><th>Tỉ lệ đồng ý</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach (config('agrisenai.criteria') as $key => $c)
                        @php
                            $d = $decisions[$key] ?? collect();
                            $withAi = ($d['accept'] ?? 0) + ($d['edit'] ?? 0) + ($d['reject'] ?? 0);
                        @endphp
                        <tr>
                            <td class="py-2 font-bold">{{ $c['label'] }}</td>
                            <td>{{ $d['accept'] ?? 0 }}</td>
                            <td>{{ $d['edit'] ?? 0 }}</td>
                            <td>{{ $d['reject'] ?? 0 }}</td>
                            <td>{{ $d['manual'] ?? 0 }}</td>
                            <td class="font-extrabold">{{ $withAi ? number_format(($d['accept'] ?? 0) / $withAi * 100, 0).'%' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-card bg-white p-5 shadow-sm">
        <h2 class="mb-3 text-lg font-extrabold">Lô theo trạng thái</h2>
        <div class="flex flex-wrap gap-3">
            @foreach (\App\Models\Lot::STATUSES as $k => $label)
                <a href="{{ route('lots.index', ['status' => $k]) }}" class="rounded-card bg-gray-50 px-4 py-2 text-sm">
                    {{ $label }}: <b>{{ $byStatus[$k] ?? 0 }}</b>
                </a>
            @endforeach
        </div>
    </section>
@endsection
