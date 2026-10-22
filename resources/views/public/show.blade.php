@extends('layouts.app', ['page' => 'lookup'])

@section('title', 'Hồ sơ lô '.$lot->code)
@section('description', 'Hồ sơ ngoại quan lô xoài '.$lot->code.', đã được người phụ trách xác nhận.')

@php($pub = $published->data())

@section('content')
    <div class="mx-auto max-w-3xl">
        {{-- Dai xac minh: chuoi bam + anh + nhan phai khop ban da cong bo --}}
        @if ($verify['ok'])
            <div role="status" class="mb-4 flex items-center gap-2 rounded-card bg-brand-light px-4 py-3 text-sm font-extrabold text-brand">
                ✓ Hồ sơ nguyên vẹn — ảnh, kết luận và nhật ký khớp với bản đã công bố
            </div>
        @else
            <div role="alert" class="mb-4 rounded-card bg-red-50 px-4 py-3 text-sm font-extrabold text-red-800">
                ✗ Cảnh báo: hồ sơ đã bị thay đổi sau khi công bố
                <span class="block text-[12px] font-semibold">
                    @unless ($verify['chain_ok']) Nhật ký bị sửa. @endunless
                    @unless ($verify['photos_ok']) Ảnh không khớp mã băm. @endunless
                    @unless ($verify['labels_ok']) Kết luận trong hệ thống khác bản đã công bố. @endunless
                    Liên hệ điểm thu gom trước khi nhận hàng.
                </span>
            </div>
        @endif

        <section class="mb-4 rounded-card bg-white p-5 shadow-md">
            <p class="text-[11px] font-extrabold uppercase tracking-wider text-ink-light">Hồ sơ lô xoài</p>
            <h1 class="font-mono text-2xl font-extrabold">{{ $lot->code }}</h1>
            <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm sm:grid-cols-3">
                {{-- Khong hien ten nha vuon / nong ho tren trang cong khai (bao ve du lieu ca nhan) --}}
                <div><dt class="text-ink-sec">Điểm thu gom</dt><dd class="font-bold">{{ $lot->creator?->org_name }}</dd></div>
                <div><dt class="text-ink-sec">Giống (người nhập)</dt><dd class="font-bold">{{ $lot->variety ?: '—' }}</dd></div>
                <div><dt class="text-ink-sec">Mục đích bán</dt><dd class="font-bold">{{ config('agrisenai.muc_dich_ban.'.$lot->purpose) }}</dd></div>
                <div><dt class="text-ink-sec">Khối lượng</dt><dd class="font-bold">{{ rtrim(rtrim(number_format($lot->weight_kg, 2, ',', '.'), '0'), ',') }} kg</dd></div>
                <div><dt class="text-ink-sec">Thu hoạch</dt><dd class="font-bold">{{ $lot->harvest_date->format('d/m/Y') }}</dd></div>
                <div><dt class="text-ink-sec">Công bố</dt><dd class="font-bold">{{ $lot->published_at->format('H:i d/m/Y') }}</dd></div>
            </dl>
        </section>

        <section class="mb-4 rounded-card bg-white p-5 shadow-md">
            <h2 class="mb-3 text-lg font-extrabold">Ảnh đã duyệt</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($lot->photos as $p)
                    <figure>
                        <img src="{{ $p->url() }}" alt="{{ \App\Models\LotPhoto::VIEWS[$p->view] }}" class="aspect-square w-full rounded-card-sm object-cover" loading="lazy">
                        <figcaption class="mt-1 text-[11px] text-ink-sec">
                            {{ \App\Models\LotPhoto::VIEWS[$p->view] }}
                            <code class="block truncate" title="SHA-256 {{ $p->sha256 }}">SHA-256 {{ substr($p->sha256, 0, 12) }}…</code>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>

        <section class="mb-4 rounded-card bg-white p-5 shadow-md">
            <h2 class="mb-3 text-lg font-extrabold">Ngoại quan đã xác nhận</h2>
            <dl class="divide-y divide-gray-100">
                @foreach (config('agrisenai.criteria') as $key => $c)
                    <div class="grid grid-cols-[120px_1fr] gap-3 py-2.5 text-sm sm:grid-cols-[170px_1fr]">
                        <dt class="font-bold">{{ $c['label'] }}</dt>
                        <dd>
                            @if (isset($labels[$key]))
                                @include('lots._value', ['key' => $key, 'final' => $labels[$key]['final_value']])
                                <span class="block text-[11px] text-ink-light">{{ \App\Models\VerifiedLabel::DECISIONS[$labels[$key]['decision']] ?? '' }}</span>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-4 rounded-card-sm bg-brand-bg p-3 text-sm">
                Người xác nhận: <b>{{ $pub['by'] ?? '—' }}</b> — {{ $pub['org'] ?? '' }} — {{ $published->created_at->format('H:i d/m/Y') }}
            </div>
            <p class="mt-2 text-[12px] text-ink-sec">Gợi ý ngoại quan do AI hỗ trợ, đã được người phụ trách xác nhận. Đây là quan sát qua ảnh, không thay thế kiểm tra trực tiếp.</p>
        </section>

        <section class="mb-4 rounded-card bg-white p-5 shadow-md">
            <h2 class="mb-3 text-lg font-extrabold">Lịch sử hồ sơ</h2>
            @include('lots._timeline', ['logs' => $lot->auditLogs])
            <p class="mt-3 text-[12px] text-ink-sec">Mỗi bước được nối chuỗi băm SHA-256 với bước trước. Sửa bất kỳ bước nào, dải xác minh phía trên sẽ chuyển đỏ.</p>
        </section>
    </div>
@endsection
