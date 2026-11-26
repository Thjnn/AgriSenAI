@extends('layouts.app', ['page' => 'stats'])

@section('title', 'Thống kê')

@section('content')
    @php
        $published = $byStatus['published'] ?? 0;
        $pctOf = fn ($n, $d) => $d ? (int) round($n / $d * 100) : 0;
        // Mau trang thai: cung bo mau voi nhan trang thai (lots._status), xam -> xanh dam theo tien do.
        $statusColor = ['draft' => 'bg-gray-300', 'analyzing' => 'bg-amber-400', 'analyzed' => 'bg-sky-400', 'confirmed' => 'bg-brand-medium', 'published' => 'bg-mango'];
        $decisionMeta = [
            'accept' => ['Đồng ý', 'bg-brand'],
            'edit' => ['Sửa', 'bg-mango'],
            'reject' => ['Bác bỏ', 'bg-red-400'],
            'manual' => ['Thủ công', 'bg-gray-300'],
        ];
        $agree = $decisions->sum(fn ($d) => $d['accept'] ?? 0);
        $withAiAll = $decisions->sum(fn ($d) => ($d['accept'] ?? 0) + ($d['edit'] ?? 0) + ($d['reject'] ?? 0));
        $card = 'rounded-card bg-white p-5 shadow-sm';
        $h2 = 'text-lg font-extrabold';
        $sub = 'mb-4 text-sm text-ink-sec';
        $maxLots = max(1, $trend->max('lots'));
        $maxScans = max(1, $trend->max('scans'));
    @endphp

    {{-- Dau trang: nen xanh dam nhu hero trang chu, tong so lo + thanh trang thai --}}
    <section class="hero-in relative isolate mb-4 overflow-hidden rounded-card-lg bg-brand-night p-5 text-white sm:p-6">
        <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-16 -z-10 h-52 w-52 rounded-full bg-mango/25 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(rgba(255,255,255,.07)_1px,transparent_1px)] [background-size:18px_18px]"></div>

        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black">Thống kê vận hành</h1>
                <p class="text-sm text-white/70">Số liệu thật trong hệ thống, kèm tử số / mẫu số.</p>
            </div>
            <a href="{{ route('stats.dataset') }}" title="Xuất dataset (ảnh + nhãn)"
               class="flex min-h-11 shrink-0 items-center gap-1.5 rounded-card bg-white/10 px-3 text-sm font-extrabold ring-1 ring-white/20 transition hover:bg-white/20">
                ⬇ <span class="max-sm:sr-only">Xuất dataset (ảnh + nhãn)</span><span class="sm:hidden">Dataset</span>
            </a>
        </div>

        <div class="mt-5 flex items-end gap-6">
            <div>
                <b class="block text-5xl font-black leading-none">{{ $total }}</b>
                <span class="text-sm text-white/70">lô đã tạo</span>
            </div>
            <div>
                <b class="block text-3xl font-black leading-none text-mango">{{ $published }}</b>
                <span class="text-sm text-white/70">đã công bố ({{ $pctOf($published, $total) }}%)</span>
            </div>
        </div>

        @if ($total)
            <div class="mt-4 flex h-3 gap-0.5 overflow-hidden rounded-full bg-white/10" role="img"
                 aria-label="Lô theo trạng thái: @foreach (\App\Models\Lot::STATUSES as $k => $label){{ $label }} {{ $byStatus[$k] ?? 0 }}{{ $loop->last ? '' : ', ' }}@endforeach">
                @foreach (\App\Models\Lot::STATUSES as $k => $label)
                    @if ($byStatus[$k] ?? 0)
                        <span class="{{ $statusColor[$k] }} h-full" style="width: {{ ($byStatus[$k] / $total) * 100 }}%" title="{{ $label }}: {{ $byStatus[$k] }}"></span>
                    @endif
                @endforeach
            </div>
        @endif
        <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-[13px]">
            @foreach (\App\Models\Lot::STATUSES as $k => $label)
                <li>
                    <a href="{{ route('lots.index', ['status' => $k]) }}" class="inline-flex items-center gap-1.5 text-white/80 hover:text-white">
                        <span class="{{ $statusColor[$k] }} h-2.5 w-2.5 rounded-full"></span>{{ $label }} <b class="text-white">{{ $byStatus[$k] ?? 0 }}</b>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- O so lieu nhanh --}}
    @php $tile = 'list-in rounded-card bg-white p-4 shadow-sm'; @endphp
    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="{{ $tile }}" style="--i: 0">
            <span class="text-[13px] font-bold text-ink-sec">Thời gian lập hồ sơ (TB)</span>
            <b class="mt-1 block text-2xl font-black">{{ $avgMinutes === null ? '—' : number_format($avgMinutes, 0).' phút' }}</b>
            <span class="text-[11px] text-ink-light">tạo lô → công bố</span>
        </div>
        <div class="{{ $tile }}" style="--i: 1">
            <span class="text-[13px] font-bold text-ink-sec">Lượt quét QR</span>
            <b class="mt-1 block text-2xl font-black">{{ (int) $scans->qr }}</b>
            <span class="text-[11px] text-ink-light">{{ (int) $scans->total }} lượt xem hồ sơ công khai</span>
        </div>
        <div class="{{ $tile }}" style="--i: 2">
            <span class="text-[13px] font-bold text-ink-sec">Người dùng đồng ý với AI</span>
            <b class="mt-1 block text-2xl font-black text-brand">{{ $withAiAll ? $pctOf($agree, $withAiAll).'%' : '—' }}</b>
            <span class="text-[11px] text-ink-light">{{ $agree }}/{{ $withAiAll }} tiêu chí có gợi ý AI</span>
        </div>
        <div class="{{ $tile }}" style="--i: 3">
            <span class="text-[13px] font-bold text-ink-sec">Lượt gọi AI</span>
            <b class="mt-1 block text-2xl font-black">{{ $aiCalls }}</b>
            <span class="text-[11px] text-ink-light">{{ $aiErrors }} lỗi · trung vị {{ $medianLatency === null ? '—' : number_format($medianLatency / 1000, 1, ',', '').' s' }}</span>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- 14 ngay qua: hai bieu do cot rieng (khong gop 2 truc) --}}
        <section class="{{ $card }}">
            <h2 class="{{ $h2 }}">Hoạt động 14 ngày qua</h2>
            <p class="{{ $sub }}">Chạm vào cột để xem số của ngày đó.</p>
            @foreach ([['lots', 'Lô tạo mới', $maxLots, 'bg-brand'], ['scans', 'Lượt quét QR', $maxScans, 'bg-mango']] as [$key, $title, $max, $color])
                <div class="{{ $loop->first ? '' : 'mt-5' }}">
                    <div class="mb-1.5 flex items-baseline justify-between text-sm">
                        <b>{{ $title }}</b>
                        <span class="text-ink-sec">tổng <b class="text-ink">{{ $trend->sum($key) }}</b></span>
                    </div>
                    <div class="flex h-20 items-end gap-[2px] border-b border-gray-200">
                        @foreach ($trend as $day)
                            <button type="button" class="group relative flex h-full flex-1 items-end focus:outline-none" aria-label="{{ $day['label'] }}: {{ $day[$key] }}">
                                <span class="{{ $day[$key] ? $color : 'bg-gray-100' }} block w-full rounded-t-[4px] transition group-hover:opacity-80"
                                      style="height: {{ $day[$key] ? max(6, $day[$key] / $max * 100) : 4 }}%"></span>
                                <span class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded-card-sm bg-ink px-2 py-1 text-[11px] font-bold text-white group-hover:block group-focus:block">
                                    {{ $day['label'] }} · {{ $day[$key] }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                    <div class="mt-1 flex justify-between text-[11px] text-ink-light">
                        <span>{{ $trend->first()['label'] }}</span><span>Hôm nay</span>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- KPI co tu so / mau so --}}
        <section class="{{ $card }}">
            <h2 class="{{ $h2 }}">Chỉ số vận hành</h2>
            <p class="{{ $sub }}">Đo trên dữ liệu thật trong hệ thống, luôn kèm tử số/mẫu số.</p>
            <dl class="space-y-4">
                @foreach ($kpis as $k)
                    <div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-sm font-bold">{{ $k['label'] }}</dt>
                            <dd class="shrink-0 text-sm">
                                @if ($k['den'] === 0)
                                    <span class="text-ink-light">Chưa có dữ liệu</span>
                                @else
                                    <b>{{ $k['num'] }}/{{ $k['den'] }}</b> <span class="text-ink-sec">({{ number_format($k['num'] / $k['den'] * 100, 0) }}%)</span>
                                @endif
                            </dd>
                        </div>
                        <div class="mt-1.5 h-2 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full bg-brand" style="width: {{ $pctOf($k['num'], $k['den']) }}%"></div>
                        </div>
                        <p class="mt-1 text-[11px] leading-snug text-ink-light">{{ $k['note'] }}</p>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- Quyet dinh theo tieu chi: thanh xep chong thay bang (bang van co trong <details>) --}}
        <section class="{{ $card }} lg:col-span-2">
            <h2 class="{{ $h2 }}">Gợi ý AI được người dùng chấp nhận</h2>
            <p class="{{ $sub }}">“AI đúng bao nhiêu % theo đánh giá của chính người dùng” — tỉ lệ chỉ tính các lần có gợi ý AI (bỏ nhập thủ công).</p>

            <ul class="mb-4 flex flex-wrap gap-x-4 gap-y-1 text-[13px] text-ink-sec">
                @foreach ($decisionMeta as [$label, $color])
                    <li class="inline-flex items-center gap-1.5"><span class="{{ $color }} h-2.5 w-2.5 rounded-full"></span>{{ $label }}</li>
                @endforeach
            </ul>

            <div class="grid gap-4 lg:grid-cols-2 lg:gap-x-8">
                @foreach (config('agrisenai.criteria') as $key => $c)
                    @php
                        $d = $decisions[$key] ?? collect();
                        $all = collect(array_keys($decisionMeta))->sum(fn ($x) => $d[$x] ?? 0);
                        $withAi = ($d['accept'] ?? 0) + ($d['edit'] ?? 0) + ($d['reject'] ?? 0);
                    @endphp
                    <div>
                        <div class="mb-1.5 flex items-baseline justify-between gap-3 text-sm">
                            <b>{{ $c['label'] }}</b>
                            <span class="shrink-0"><b class="text-brand">{{ $withAi ? $pctOf($d['accept'] ?? 0, $withAi).'%' : '—' }}</b> <span class="text-ink-light">đồng ý</span></span>
                        </div>
                        <div class="flex h-3 gap-0.5 overflow-hidden rounded-full bg-gray-100">
                            @if ($all)
                                @foreach ($decisionMeta as $dk => [$label, $color])
                                    @if ($d[$dk] ?? 0)
                                        <span class="{{ $color }} h-full" style="width: {{ $d[$dk] / $all * 100 }}%" title="{{ $label }}: {{ $d[$dk] }}"></span>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <p class="mt-1 text-[11px] text-ink-light">
                            @foreach ($decisionMeta as $dk => [$label])
                                {{ $label }} {{ $d[$dk] ?? 0 }}{{ $loop->last ? '' : ' · ' }}
                            @endforeach
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="{{ $card }}">
            <h2 class="{{ $h2 }}">Theo phiên bản prompt</h2>
            <p class="{{ $sub }}">Mỗi lần đổi prompt, tỉ lệ người đồng ý với AI có tăng không.</p>
            @forelse ($byVersion as $version => $d)
                @php
                    $n = ($d['accept'] ?? 0) + ($d['edit'] ?? 0) + ($d['reject'] ?? 0);
                    $pct = $n ? round(($d['accept'] ?? 0) / $n * 100) : 0;
                @endphp
                <div class="mb-3">
                    <div class="flex justify-between text-sm">
                        <code class="font-bold">{{ $version }}</code>
                        <span><b>{{ $pct }}%</b> <span class="text-ink-light">({{ $d['accept'] ?? 0 }}/{{ $n }})</span></span>
                    </div>
                    <div class="mt-1 h-2 rounded-full bg-gray-100"><div class="h-2 rounded-full bg-brand" style="width: {{ $pct }}%"></div></div>
                </div>
            @empty
                <p class="text-sm text-ink-light">Chưa có dữ liệu — cần ít nhất một lô có gợi ý AI được xác nhận.</p>
            @endforelse
        </section>

        <section class="{{ $card }}">
            <h2 class="{{ $h2 }}">AI hay bị sửa nhất</h2>
            <p class="{{ $sub }}">Gợi ý AI → người phụ trách sửa thành. Dùng để chỉnh prompt và ảnh mẫu.</p>
            @forelse ($topEdits as $e)
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2.5 text-sm last:border-0">
                    <span class="min-w-0"><span class="block text-[12px] font-bold text-ink-sec">{{ $e['criterion'] }}</span>{{ $e['from'] }} → <b>{{ $e['to'] }}</b></span>
                    <span class="shrink-0 rounded-full bg-mango/20 px-2.5 py-1 text-[12px] font-extrabold text-brand-night">{{ $e['n'] }} lần</span>
                </div>
            @empty
                <p class="text-sm text-ink-light">Chưa có lần sửa nào.</p>
            @endforelse
        </section>
    </div>
@endsection
