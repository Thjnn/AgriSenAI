@extends('layouts.app', ['page' => 'lots'])

@section('title', 'Lô '.$lot->code)

@php
    $criteria = config('agrisenai.criteria');
    $ai = $sugg?->hasResult() ? $sugg->result : null;
    $lowConfidence = $ai && ($ai['confidence'] ?? 0) < config('agrisenai.min_confidence');
    $editable = ! $lot->isPublished();
    $usablePhotos = $lot->photos->filter->isUsable()->count();
    $badPhotos = $sugg?->badPhotos() ?? [];
    $lastConfirm = $lot->auditLogs->where('action', 'confirmed')->last();
    $card = 'mb-4 rounded-card bg-white p-4 shadow-sm sm:p-5';
    $h2 = 'mb-3 flex items-center gap-2 text-lg font-extrabold';
    $step = 'flex h-7 w-7 items-center justify-center rounded-full bg-brand text-sm text-white';
@endphp

@section('content')
    {{-- Dau trang --}}
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('lots.index') }}" class="text-sm font-bold text-ink-sec">← Lô hàng</a>
            <h1 class="font-mono text-2xl font-extrabold">{{ $lot->code }}</h1>
            <p class="text-sm text-ink-sec">
                {{ $lot->supplier }}{{ $lot->variety ? ' · '.$lot->variety : '' }} ·
                {{ config('agrisenai.muc_dich_ban.'.$lot->purpose) }} ·
                {{ rtrim(rtrim(number_format($lot->weight_kg, 2, ',', '.'), '0'), ',') }} kg ·
                thu hoạch {{ $lot->harvest_date->format('d/m/Y') }}
            </p>
        </div>
        @include('lots._status', ['status' => $lot->status])
    </div>

    @foreach (['ok' => 'bg-brand-light text-brand', 'warn' => 'bg-amber-100 text-amber-900'] as $k => $cls)
        @if (session($k))
            <div role="status" class="mb-4 rounded-card px-4 py-3 text-sm font-bold {{ $cls }}">{{ session($k) }}</div>
        @endif
    @endforeach
    @if ($errors->any())
        <ul role="alert" class="mb-4 list-disc rounded-card bg-red-50 py-3 pl-8 pr-4 text-sm font-bold text-red-800">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    @endif

    <div class="grid gap-4 lg:grid-cols-[1fr_340px]">
        <div>
            {{-- ── Buoc 2: anh ─────────────────────────────── --}}
            <section class="{{ $card }}">
                <h2 class="{{ $h2 }}"><span class="{{ $step }}">2</span> Ảnh chuẩn hoá</h2>

                @if ($lot->photos->isNotEmpty())
                    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($lot->photos as $p)
                            @php $aiBad = $badPhotos[$p->id] ?? null; @endphp
                            <figure class="flex flex-col overflow-hidden rounded-card-sm border {{ $p->isUsable() && ! $aiBad ? 'border-gray-100' : 'border-red-300' }}">
                                <img src="{{ $p->url() }}" alt="{{ \App\Models\LotPhoto::VIEWS[$p->view] }}" class="aspect-square w-full object-cover" loading="lazy">
                                <figcaption class="flex-1 p-2 text-[11px]">
                                    <b>{{ \App\Models\LotPhoto::VIEWS[$p->view] }}</b>
                                    @if (! $p->isUsable())
                                        <span class="block text-red-700">{{ implode(' ', $p->quality['issues'] ?? []) }}</span>
                                    @elseif ($aiBad)
                                        <span class="block font-bold text-red-700">AI: {{ $aiBad }}</span>
                                    @else
                                        {{-- Chi kiem tra ky thuat (net, du sang, du lon); noi dung anh do AI xet --}}
                                        <span class="text-brand" title="Nét, đủ sáng, đủ lớn">· đạt kỹ thuật</span>
                                    @endif
                                </figcaption>
                                @if ($editable)
                                    <form method="post" action="{{ route('lots.photos.destroy', [$lot, $p]) }}"
                                          onsubmit="return confirm('Xoá ảnh này? Lô sẽ phải cho AI gợi ý và xác nhận lại.')">
                                        @csrf @method('DELETE')
                                        <button class="w-full border-t border-gray-100 py-1.5 text-[12px] font-bold {{ $aiBad || ! $p->isUsable() ? 'bg-red-50 text-red-700' : 'text-ink-sec' }} hover:bg-red-50 hover:text-red-700">Xoá ảnh</button>
                                    </form>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                @endif

                @if ($editable)
                    <form method="post" action="{{ route('lots.photos.store', $lot) }}" enctype="multipart/form-data" data-photo-capture
                          @foreach (config('agrisenai.photo_check') as $k => $v) data-{{ str_replace('_', '-', $k) }}="{{ $v }}" @endforeach
                          class="space-y-3 rounded-card-sm bg-gray-50 p-3">
                        @csrf
                        <input type="file" name="anh" hidden>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-open-camera class="rounded-card bg-brand px-4 py-3 font-extrabold text-white">📷 Mở camera</button>
                            <label class="cursor-pointer rounded-card border border-gray-200 bg-white px-4 py-3 font-bold">
                                Chọn ảnh <input type="file" accept="image/*" data-pick class="sr-only">
                            </label>
                            <select name="view" aria-label="Góc chụp" class="rounded-card border border-gray-200 bg-white px-3 py-3 font-bold">
                                @foreach (\App\Models\LotPhoto::VIEWS as $k => $label)
                                    <option value="{{ $k }}" @selected($k === ($lot->photos->isEmpty() ? 'toan_qua' : 'be_mat'))>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Khung ngam: video vuong object-cover, khung 80% o giua = dung vung JS cat --}}
                        <div data-camera hidden class="relative mx-auto aspect-square w-full max-w-sm overflow-hidden rounded-card bg-black">
                            <video autoplay playsinline muted class="h-full w-full object-cover"></video>
                            <div class="pointer-events-none absolute inset-[10%] rounded-card border-4 border-white/90 shadow-[0_0_0_999px_rgba(0,0,0,0.45)]"></div>
                            <p class="absolute inset-x-0 top-2 text-center text-xs font-bold text-white">Đặt quả vào giữa khung, nền trơn</p>
                            <div class="absolute inset-x-0 bottom-3 flex justify-center gap-3">
                                <button type="button" data-shoot class="h-14 w-14 rounded-full border-4 border-white bg-brand" aria-label="Chụp"></button>
                                <button type="button" data-close-camera class="rounded-card bg-white/90 px-3 text-sm font-bold">Đóng</button>
                            </div>
                        </div>

                        <img data-preview hidden alt="Xem trước" class="mx-auto max-h-56 rounded-card-sm">
                        <p data-msg class="text-[13px] text-ink-sec">Nên chụp 3 góc: toàn quả, cận bề mặt, cuống.</p>
                        <button type="submit" disabled class="rounded-card bg-ink px-4 py-2.5 font-extrabold text-white disabled:opacity-40">Lưu ảnh</button>
                    </form>
                @endif
            </section>

            {{-- ── Buoc 3: AI goi y ─────────────────────────── --}}
            <section class="{{ $card }}">
                <h2 class="{{ $h2 }}"><span class="{{ $step }}">3</span> AI gợi ý ngoại quan</h2>

                @if ($lot->status === 'analyzing')
                    <p id="aiWaiting" class="rounded-card-sm bg-amber-50 p-3 text-sm font-bold text-amber-900">
                        ⏳ AI đang phân tích… trang tự cập nhật khi xong. Không muốn chờ? Nhập thủ công ở bước 4 ngay bên dưới.
                    </p>
                @elseif ($sugg?->error)
                    <p class="rounded-card-sm bg-red-50 p-3 text-sm text-red-800"><b>AI không trả lời được:</b> {{ $sugg->error }} — hãy nhập thủ công.</p>
                @elseif ($sugg && ! $ai)
                    <p class="rounded-card-sm bg-amber-50 p-3 text-sm text-amber-900"><b>AI không dùng được ảnh:</b> {{ $sugg->result['unusable_reason'] ?? 'không rõ lý do' }}. Xoá các ảnh viền đỏ ở bước 2, chụp lại rồi phân tích lại — hoặc nhập thủ công.</p>
                @elseif ($ai)
                    <p class="text-sm text-ink-sec">
                        {{ $sugg->model }} · {{ number_format($sugg->latency_ms / 1000, 1, ',', '') }} s ·
                        độ tự tin <b class="{{ $lowConfidence ? 'text-red-700' : 'text-ink' }}">{{ number_format(($ai['confidence'] ?? 0) * 100) }}%</b>
                        @if ($lowConfidence) — thấp, hãy xem kỹ từng tiêu chí. @endif
                    </p>
                    @if ($badPhotos)
                        <p class="mt-2 rounded-card-sm bg-amber-50 p-2.5 text-[13px] text-amber-900">AI đã bỏ qua {{ count($badPhotos) }} ảnh không dùng được (viền đỏ ở bước 2) — nên xoá đi trước khi công bố.</p>
                    @endif
                @else
                    <p class="text-sm text-ink-sec">Chưa phân tích. AI chỉ xem các ảnh <b>đạt</b> chất lượng ({{ $usablePhotos }} ảnh).</p>
                @endif

                @if ($editable && $lot->status !== 'analyzing' && $usablePhotos > 0)
                    <form method="post" action="{{ route('lots.analyze', $lot) }}" class="mt-3">
                        @csrf
                        <button class="rounded-card bg-brand px-4 py-2.5 font-extrabold text-white hover:bg-brand-medium">
                            {{ $sugg ? 'Phân tích lại' : 'AI gợi ý' }}
                        </button>
                    </form>
                @endif
            </section>

            {{-- ── Buoc 4: xac nhan (HITL) ─────────────────── --}}
            <section class="{{ $card }}">
                <h2 class="{{ $h2 }}"><span class="{{ $step }}">4</span> Người phụ trách xác nhận</h2>

                @if ($lastConfirm)
                    <p class="mb-3 rounded-card-sm bg-brand-light p-3 text-sm">
                        Đã xác nhận bởi <b>{{ $lastConfirm->actor?->name }}</b> — {{ $lastConfirm->data()['org'] ?? '' }} — {{ $lastConfirm->created_at->format('H:i d/m/Y') }}
                    </p>
                @endif

                @if (! $editable)
                    <dl class="divide-y divide-gray-100">
                        @foreach ($criteria as $key => $c)
                            <div class="grid grid-cols-[120px_1fr] gap-2 py-2 text-sm">
                                <dt class="font-bold">{{ $c['label'] }}</dt>
                                <dd>@if (isset($labels[$key])) @include('lots._value', ['key' => $key, 'final' => $labels[$key]->final_value]) @endif</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <form method="post" action="{{ route('lots.confirm', $lot) }}" id="confirmForm" class="space-y-3">
                        @csrf
                        @if ($ai && ! $lowConfidence)
                            <button type="button" id="acceptAll" class="rounded-card border border-brand px-3 py-2 text-sm font-bold text-brand">✓ Đồng ý tất cả gợi ý AI</button>
                        @endif

                        @foreach ($criteria as $key => $c)
                            @php
                                $aiVal = $ai[$key] ?? null;
                                $cur = $labels[$key] ?? null;
                                $decision = old("items.$key.decision", $cur?->decision ?? ($aiVal ? null : 'manual'));
                                $value = old("items.$key.value", $cur?->final_value['value'] ?? $aiVal['value'] ?? null);
                                $types = old("items.$key.defect_types", $cur?->final_value['defect_types'] ?? collect($aiVal['defects'] ?? [])->pluck('type')->unique()->values()->all());
                            @endphp
                            <fieldset data-criterion class="rounded-card-sm border border-gray-100 p-3">
                                <legend class="px-1 font-extrabold">{{ $c['label'] }}</legend>

                                @if ($aiVal)
                                    <div class="mb-2 rounded-card-sm bg-gray-50 p-2.5 text-sm">
                                        <span class="text-[11px] font-extrabold uppercase text-ink-light">AI gợi ý</span>
                                        <b class="block">{{ $c['options'][$aiVal['value']] ?? $aiVal['value'] }}</b>
                                        <span class="text-ink-sec">{{ $aiVal['note'] ?? '' }}</span>
                                        @if ($key === 'color' && ! empty($aiVal['distribution']))
                                            <span class="block text-[12px] text-ink-sec">{{ collect($aiVal['distribution'])->map(fn ($d) => $d['name'].' '.$d['percent'].'%')->join(' · ') }}</span>
                                        @endif
                                        @if ($key === 'ripeness' && isset($aiVal['matches_purpose']))
                                            <span class="block text-[12px] {{ $aiVal['matches_purpose'] ? 'text-brand' : 'text-red-700' }}">{{ $aiVal['matches_purpose'] ? 'Đúng mục đích bán' : 'Chưa đúng mục đích bán' }}</span>
                                        @endif
                                        @if ($key === 'surface')
                                            @foreach ($aiVal['defects'] ?? [] as $d)
                                                <span class="block text-[12px]">• {{ config('agrisenai.defect_types.'.$d['type'], $d['type']) }} ({{ $d['severity'] }}, ~{{ $d['area_percent'] }}%{{ ! empty($d['location']) ? ', '.$d['location'] : '' }}){{ ! empty($d['into_flesh']) ? ' — ăn vào thịt quả' : '' }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="mb-2 flex flex-wrap gap-2">
                                        @foreach (['accept' => 'Đồng ý', 'edit' => 'Sửa', 'reject' => 'Bác bỏ'] as $d => $dl)
                                            <label class="cursor-pointer rounded-full border border-gray-200 px-3 py-1.5 text-sm font-bold has-[:checked]:border-brand has-[:checked]:bg-brand has-[:checked]:text-white">
                                                <input type="radio" name="items[{{ $key }}][decision]" value="{{ $d }}" @checked($decision === $d) class="sr-only"> {{ $dl }}
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <input type="hidden" name="items[{{ $key }}][decision]" value="manual">
                                @endif

                                <div data-edit @if ($aiVal && ! in_array($decision, ['edit', 'manual'], true)) hidden @endif class="space-y-2">
                                    <select name="items[{{ $key }}][value]" aria-label="{{ $c['label'] }}" class="w-full rounded-card-sm border border-gray-200 bg-white px-3 py-2.5">
                                        <option value="">— Chọn —</option>
                                        @foreach ($c['options'] as $ok => $ol)
                                            <option value="{{ $ok }}" @selected($value === $ok)>{{ $ol }}</option>
                                        @endforeach
                                    </select>
                                    @if ($key === 'surface')
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach (config('agrisenai.defect_types') as $tk => $tl)
                                                <label class="cursor-pointer rounded-full border border-gray-200 px-2.5 py-1 text-xs font-bold has-[:checked]:border-red-400 has-[:checked]:bg-red-50">
                                                    <input type="checkbox" name="items[{{ $key }}][defect_types][]" value="{{ $tk }}" @checked(in_array($tk, (array) $types, true)) class="sr-only"> {{ $tl }}
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <input name="items[{{ $key }}][note]" maxlength="500" value="{{ old("items.$key.note", $cur?->note) }}" placeholder="Ghi chú (không bắt buộc)"
                                       class="mt-2 w-full rounded-card-sm border border-gray-200 px-3 py-2 text-sm">
                            </fieldset>
                        @endforeach

                        <button class="w-full rounded-card bg-brand py-3 font-extrabold text-white hover:bg-brand-medium">Xác nhận hồ sơ</button>
                        <p class="text-center text-xs text-ink-sec">Ghi tên <b>{{ auth()->user()->name }}</b> và thời điểm vào nhật ký. Mỗi tiêu chí là một nhãn dữ liệu.</p>
                    </form>
                @endif
            </section>
        </div>

        <aside>
            {{-- ── Buoc 5: cong bo ──────────────────────────── --}}
            <section class="{{ $card }}">
                <h2 class="{{ $h2 }}"><span class="{{ $step }}">5</span> Công bố & tem QR</h2>
                @if ($lot->isPublished())
                    <div class="mb-3 rounded-card-sm p-3 text-sm font-extrabold {{ $verify['ok'] ? 'bg-brand-light text-brand' : 'bg-red-50 text-red-800' }}">
                        {{ $verify['ok'] ? '✓ Hồ sơ nguyên vẹn' : '✗ Hồ sơ đã bị thay đổi sau khi công bố' }}
                    </div>
                    <img src="{{ route('public.qr', $lot) }}" alt="QR hồ sơ {{ $lot->code }}" class="mx-auto mb-3 w-40">
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('public.show', $lot) }}" target="_blank" class="rounded-card bg-brand py-2.5 text-center font-extrabold text-white">Xem trang công khai</a>
                        <a href="{{ route('lots.label', $lot) }}" target="_blank" class="rounded-card border border-gray-200 py-2.5 text-center font-bold">In tem QR</a>
                    </div>
                @elseif ($lot->status === 'confirmed')
                    <p class="mb-3 text-sm text-ink-sec">Sau khi công bố, hồ sơ bị khoá — không sửa được nữa.</p>
                    <form method="post" action="{{ route('lots.publish', $lot) }}">
                        @csrf
                        <button class="w-full rounded-card bg-brand py-3 font-extrabold text-white hover:bg-brand-medium">Công bố hồ sơ</button>
                    </form>
                @else
                    <p class="text-sm text-ink-sec">Xác nhận đủ 5 tiêu chí ở bước 4 để công bố.</p>
                @endif
            </section>

            <section class="{{ $card }}">
                <h2 class="{{ $h2 }}">Nhật ký</h2>
                @include('lots._timeline', ['logs' => $lot->auditLogs])
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/photo-capture.js')
    <script>
        // Sua / Nhap thu cong -> hien o chon gia tri.
        document.querySelectorAll('[data-criterion]').forEach((fs) => {
            const edit = fs.querySelector('[data-edit]');
            fs.querySelectorAll('input[type=radio]').forEach((r) => r.addEventListener('change', () => {
                edit.hidden = !['edit', 'manual'].includes(r.value);
            }));
        });
        document.getElementById('acceptAll')?.addEventListener('click', () => {
            document.querySelectorAll('#confirmForm input[value=accept]').forEach((r) => {
                r.checked = true;
                r.dispatchEvent(new Event('change'));
            });
        });
        // AI dang chay -> hoi trang thai moi 3 s, xong thi tai lai trang
        // (tru khi nguoi dung dang tu nhap — khong xoa cong cua ho).
        const waiting = document.getElementById('aiWaiting');
        if (waiting) {
            let dirty = false;
            document.getElementById('confirmForm')?.addEventListener('input', () => { dirty = true; });
            const poll = setInterval(async () => {
                const res = await fetch(@json(route('lots.analysis', $lot)), { headers: { Accept: 'application/json' } });
                if (res.ok && (await res.json()).status !== 'analyzing') {
                    clearInterval(poll);
                    if (dirty) waiting.innerHTML = 'AI đã xong. <a href="" class="underline">Tải lại trang</a> để xem gợi ý (phần đang nhập sẽ mất).';
                    else location.reload();
                }
            }, 3000);
        }
    </script>
@endpush
