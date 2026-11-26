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
                    @unless ($verify['lot_ok']) Thông tin lô (khối lượng, ngày thu hoạch…) khác bản đã công bố. @endunless
                    Liên hệ điểm thu gom trước khi nhận hàng.
                </span>
            </div>
        @endif

        @if ($correction)
            <div role="alert" class="mb-4 rounded-card bg-amber-50 px-4 py-3 text-sm font-extrabold text-amber-900">
                Hồ sơ này đã được đính chính ngày {{ $correction['at']->format('d/m/Y') }}
                <span class="block text-[13px] font-semibold">Lý do: {{ $correction['reason'] }}</span>
                @if ($correction['new_published'])
                    <a href="{{ route('public.show', $correction['new_code']) }}" class="mt-1 inline-block underline">Xem hồ sơ đính chính {{ $correction['new_code'] }}</a>
                @else
                    <span class="block text-[13px] font-semibold">Hồ sơ đính chính {{ $correction['new_code'] }} đang được lập.</span>
                @endif
            </div>
        @endif

        <section class="mb-4 rounded-card bg-white p-5 shadow-md">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-ink-light">Hồ sơ lô xoài</p>
                    <h1 class="font-mono text-2xl font-extrabold">{{ $lot->code }}</h1>
                    {{-- Trinh duyet "In -> Luu thanh PDF": khong can thu vien PDF phia server --}}
                    <button type="button" onclick="window.print()" class="mt-2 rounded-card-sm border border-brand px-3 py-1.5 text-sm font-extrabold text-brand print:hidden">⬇ Tải PDF / In hồ sơ</button>
                </div>
                <img src="{{ route('public.qr', $lot) }}" alt="Mã QR hồ sơ {{ $lot->code }}" class="size-24 shrink-0" width="96" height="96">
            </div>
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
            <p class="mt-2 text-[12px] text-ink-sec">{{ config('agrisenai.ai_disclaimer') }}</p>
        </section>

        <section id="phan-hoi" class="mb-4 rounded-card bg-white p-5 shadow-md">
            <h2 class="mb-1 text-lg font-extrabold">Phản hồi của bên nhận hàng</h2>
            <p class="mb-3 text-[12px] text-ink-sec">Ghi vào nhật ký hồ sơ, không sửa hay xoá được. Người gửi không cần đăng nhập nên danh tính chưa được xác thực.</p>

            @if (session('ok'))
                <p role="status" class="mb-3 rounded-card-sm bg-brand-light p-3 text-sm font-bold text-brand">{{ session('ok') }}</p>
            @endif

            @forelse ($feedback as $f)
                @php($d = $f->data())
                <div class="mb-2 rounded-card-sm p-3 text-sm {{ $d['result'] === 'dung' ? 'bg-brand-bg' : 'bg-red-50' }}">
                    <b class="{{ $d['result'] === 'dung' ? 'text-brand' : 'text-red-800' }}">{{ $d['result'] === 'dung' ? '✓' : '✗' }} {{ \App\Models\AuditLog::FEEDBACK[$d['result']] ?? $d['result'] }}</b>
                    <span class="text-[12px] text-ink-sec">· {{ $d['receiver'] ?: 'Không ghi tên' }} · {{ $f->created_at->format('H:i d/m/Y') }}</span>
                    @if ($d['note'])<p class="mt-1">{{ $d['note'] }}</p>@endif
                    @if ($d['photo'])
                        <img src="{{ asset('storage/'.$d['photo']['path']) }}" alt="Ảnh bên nhận hàng chụp" class="mt-2 max-h-40 rounded-card-sm" loading="lazy">
                    @endif
                </div>
            @empty
                <p class="mb-3 text-sm text-ink-light">Chưa có phản hồi.</p>
            @endforelse

            <details class="mt-3 print:hidden" @if ($errors->any()) open @endif>
                <summary class="cursor-pointer font-extrabold text-brand">Bạn đã nhận lô hàng này? Gửi phản hồi</summary>
                <form method="post" action="{{ route('public.feedback', $lot) }}#phan-hoi" enctype="multipart/form-data" class="mt-3 space-y-3">
                    @csrf
                    @if ($errors->any())
                        <ul role="alert" class="rounded-card-sm bg-red-50 p-3 text-sm font-bold text-red-800">
                            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    @endif
                    <fieldset class="flex flex-wrap gap-3">
                        <legend class="sr-only">Kết quả nhận hàng</legend>
                        @foreach (\App\Models\AuditLog::FEEDBACK as $k => $label)
                            <label class="flex items-center gap-2 rounded-card-sm border border-gray-200 px-3 py-2 text-sm font-bold">
                                <input type="radio" name="result" value="{{ $k }}" required @checked(old('result') === $k) class="accent-brand"> {{ $label }}
                            </label>
                        @endforeach
                    </fieldset>
                    <label class="block text-sm font-bold">Ghi chú <span class="font-normal text-ink-sec">(bắt buộc nếu không đúng)</span>
                        <textarea name="note" rows="2" maxlength="500" class="mt-1 w-full rounded-card-sm border border-gray-200 p-2 font-normal">{{ old('note') }}</textarea>
                    </label>
                    <label class="block text-sm font-bold">Đơn vị / người nhận <span class="font-normal text-ink-sec">(không bắt buộc)</span>
                        <input name="receiver" maxlength="100" value="{{ old('receiver') }}" class="mt-1 w-full rounded-card-sm border border-gray-200 p-2 font-normal">
                    </label>
                    <label class="block text-sm font-bold">Ảnh hàng nhận được <span class="font-normal text-ink-sec">(không bắt buộc)</span>
                        <input type="file" name="anh" accept="image/*" class="mt-1 block text-sm font-normal">
                    </label>
                    <button class="rounded-card bg-ink px-4 py-2.5 font-extrabold text-white">Gửi phản hồi</button>
                </form>
            </details>
        </section>

        <section class="mb-4 rounded-card bg-white p-5 shadow-md">
            <h2 class="mb-3 text-lg font-extrabold">Lịch sử hồ sơ</h2>
            @include('lots._timeline', ['logs' => $lot->auditLogs])
            <p class="mt-3 text-[12px] text-ink-sec">Mỗi bước được ký HMAC-SHA256 và nối chuỗi với bước trước. Sửa bất kỳ bước nào, dải xác minh phía trên sẽ chuyển đỏ.</p>
        </section>
    </div>
@endsection
