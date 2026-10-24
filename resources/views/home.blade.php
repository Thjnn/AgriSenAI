@extends('layouts.app', ['page' => 'home'])

@section('content')
    {{-- Hero --}}
    <section class="hero relative isolate overflow-hidden rounded-card-lg bg-brand-night px-6 py-10 text-white sm:px-10 lg:px-12 lg:py-16">
        {{-- Quầng sáng vàng xoài + lưới chấm mờ làm nền --}}
        <div aria-hidden="true" class="pointer-events-none absolute -right-24 -top-24 -z-10 h-96 w-96 rounded-full bg-mango/30 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-20 -z-10 h-80 w-80 rounded-full bg-brand-medium/40 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(rgba(255,255,255,.07)_1px,transparent_1px)] [background-size:22px_22px]"></div>

        <div class="grid items-center gap-10 lg:grid-cols-[1.15fr_1fr] lg:gap-14">
            <div class="hero-in">
                <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3.5 py-1.5 text-xs font-bold text-white/85">
                    <span class="h-2 w-2 rounded-full bg-mango shadow-[0_0_0_4px_rgba(255,193,7,.25)]"></span>
                    Cho điểm thu gom &amp; HTX xoài Đồng Tháp
                </p>

                <h1 class="text-[34px] font-black leading-[1.08] tracking-tight sm:text-[44px] lg:text-[56px]">
                    Mỗi lô xoài<br>
                    một <span class="relative whitespace-nowrap text-mango">hồ sơ đáng tin<svg aria-hidden="true" viewBox="0 0 300 12" preserveAspectRatio="none" class="absolute -bottom-2 left-0 h-3 w-full text-mango/70"><path d="M2 9C80 2 220 2 298 8" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg></span>.
                </h1>

                <p class="mt-6 max-w-xl text-[15px] leading-relaxed text-white/75 lg:text-base">
                    Chụp ảnh chuẩn, AI gợi ý ngoại quan, người phụ trách xác nhận. Người mua quét QR trên thùng là thấy
                    <b class="text-white">ai xác nhận, lúc nào</b>, và hồ sơ có còn nguyên vẹn không.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('lots.create') }}" class="group inline-flex items-center gap-2 rounded-card bg-mango px-6 py-3.5 font-extrabold text-brand-night shadow-[0_8px_24px_-6px_rgba(255,193,7,.6)] transition hover:-translate-y-0.5 hover:bg-mango-light">
                            Tạo lô mới <span class="transition-transform group-hover:translate-x-1">→</span>
                        </a>
                        <a href="{{ route('lots.index') }}" class="rounded-card border border-white/25 px-6 py-3.5 font-extrabold text-white transition hover:bg-white/10">Danh sách lô</a>
                    @else
                        <a href="{{ route('login') }}" class="group inline-flex items-center gap-2 rounded-card bg-mango px-6 py-3.5 font-extrabold text-brand-night shadow-[0_8px_24px_-6px_rgba(255,193,7,.6)] transition hover:-translate-y-0.5 hover:bg-mango-light">
                            Đăng nhập nhân viên <span class="transition-transform group-hover:translate-x-1">→</span>
                        </a>
                        <a href="{{ route('public.lookup') }}" class="inline-flex items-center gap-2 rounded-card border border-white/25 px-6 py-3.5 font-extrabold text-white transition hover:bg-white/10">
                            <x-icon name="qr" class="h-5 w-5" /> Tra cứu hồ sơ
                        </a>
                    @endauth
                </div>

                <dl class="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-white/10 pt-6">
                    @foreach ([['5', 'tiêu chí ngoại quan'], ['100%', 'con người xác nhận'], ['1 QR', 'cho mỗi thùng hàng']] as [$num, $label])
                        <div>
                            <dt class="text-2xl font-black text-mango lg:text-[28px]">{{ $num }}</dt>
                            <dd class="mt-0.5 text-xs leading-snug text-white/60">{{ $label }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Ảnh thật + thẻ nổi mô phỏng đúng luồng: AI gợi ý → người xác nhận → QR --}}
            <div class="hero-in relative mx-auto w-full max-w-[420px] [animation-delay:.15s] lg:max-w-none">
                <div class="relative overflow-hidden rounded-[22px] shadow-2xl ring-1 ring-white/10">
                    <img src="{{ asset('images/products/xoai-mau1.jpg') }}" alt="Chùm xoài cát chín trên cây"
                         class="aspect-square w-full object-cover sm:aspect-[4/5] lg:aspect-[5/6]" fetchpriority="high">
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-night/70 via-transparent to-transparent"></div>
                    {{-- khung ngắm như màn chụp ảnh chuẩn --}}
                    <div aria-hidden="true" class="absolute inset-[14%] rounded-2xl border-2 border-dashed border-white/50"></div>
                    <span class="absolute right-4 top-4 rounded-full bg-black/40 px-3 py-1 font-mono text-[11px] font-bold text-white backdrop-blur">XL-261024-A7K2</span>
                </div>

                <div class="hero-float absolute -left-3 top-8 w-[210px] rounded-card bg-white p-3.5 text-ink shadow-xl sm:-left-8">
                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wide text-brand">
                        <x-icon name="ai" class="h-4 w-4" /> AI gợi ý
                    </p>
                    @foreach ([['Màu vỏ', 'Vàng đều', 92], ['Độ chín', 'Chín tới', 87], ['Bề mặt', 'Ít vết', 78]] as [$k, $v, $p])
                        <div class="mb-1.5 last:mb-0">
                            <div class="flex justify-between text-[12px]"><span class="text-ink-sec">{{ $k }}</span><b>{{ $v }}</b></div>
                            <div class="mt-1 h-1.5 rounded-full bg-gray-100"><div class="h-full rounded-full bg-brand-medium" style="width: {{ $p }}%"></div></div>
                        </div>
                    @endforeach
                </div>

                <div class="hero-float absolute -right-3 bottom-16 flex items-center gap-2.5 rounded-card bg-white py-2.5 pl-2.5 pr-4 text-ink shadow-xl [animation-delay:-2s] sm:-right-6">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand text-white">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                    </span>
                    <span class="leading-tight">
                        <b class="block text-[13px]">Đã xác nhận</b>
                        <span class="text-[11px] text-ink-sec">Người phụ trách · 07:42</span>
                    </span>
                </div>

                <div class="hero-float absolute -bottom-5 left-6 flex items-center gap-2 rounded-card bg-mango px-3.5 py-2.5 font-extrabold text-brand-night shadow-xl [animation-delay:-4s]">
                    <x-icon name="qr" class="h-6 w-6" />
                    <span class="text-[12px] leading-tight">Quét để<br>kiểm chứng</span>
                </div>
            </div>
        </div>
    </section>

    {{-- 5 buoc --}}
    <section id="quy-trinh" class="mt-10">
        <h2 class="mb-4 text-xl font-extrabold">Quy trình 5 bước</h2>
        <ol class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ([
                ['Tạo lô', 'Nguồn cung, giống, mục đích bán, khối lượng, ngày thu hoạch → mã lô duy nhất.'],
                ['Chụp ảnh chuẩn', 'Khung ngắm + kiểm tra mờ/tối ngay trên máy. Không đạt thì chụp lại.'],
                ['AI gợi ý', 'Gợi ý ngoại quan theo 5 tiêu chí: màu, độ chín, bề mặt, hình dạng, độ đồng đều.'],
                ['Người xác nhận', 'Đồng ý / Sửa / Bác bỏ từng gợi ý. Mỗi xác nhận là một nhãn dữ liệu.'],
                ['Công bố + QR', 'Hồ sơ khoá lại, nhật ký nối chuỗi băm. In tem QR dán thùng.'],
            ] as $i => [$t, $d])
                <li class="rounded-card bg-white p-4 shadow-sm">
                    <span class="mb-2 flex h-8 w-8 items-center justify-center rounded-full bg-brand text-sm font-extrabold text-white">{{ $i + 1 }}</span>
                    <b class="block">{{ $t }}</b>
                    <span class="text-[13px] text-ink-sec">{{ $d }}</span>
                </li>
            @endforeach
        </ol>
    </section>

    <section id="chup-anh" class="mt-10 grid gap-4 lg:grid-cols-2">
        <div class="rounded-card bg-white p-5 shadow-sm">
            <h2 class="mb-2 text-lg font-extrabold">AI gợi ý, con người quyết định</h2>
            <p class="text-sm leading-relaxed text-ink-sec">
                AI dùng Vision API có sẵn (Gemini) để gợi ý quan sát. Không có điểm tổng hợp, không có xếp hạng —
                người phụ trách chịu trách nhiệm kết luận cuối cùng. Khi AI chậm hoặc lỗi, vẫn nhập tay được.
            </p>
        </div>
        <div class="rounded-card bg-white p-5 shadow-sm">
            <h2 class="mb-2 text-lg font-extrabold">Chụp ảnh chuẩn</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-ink-sec">
                <li>Đặt quả vào giữa khung, nền trơn, đủ sáng, không nắng gắt.</li>
                <li>Nên chụp 3 góc: toàn quả, cận bề mặt, cuống.</li>
                <li>Ảnh mờ, tối hoặc quá nhỏ sẽ bị chặn ngay trên máy.</li>
            </ul>
        </div>
    </section>

    @if ($recent->isNotEmpty())
        <section class="mt-10">
            <h2 class="mb-4 text-xl font-extrabold">Hồ sơ mới công bố</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($recent as $lot)
                    <a href="{{ route('public.show', $lot) }}" class="overflow-hidden rounded-card bg-white shadow-sm transition-shadow hover:shadow-md">
                        @if ($lot->photos->first())
                            <img src="{{ $lot->photos->first()->url() }}" alt="Ảnh lô {{ $lot->code }}" class="aspect-square w-full object-cover" loading="lazy">
                        @endif
                        <div class="p-3">
                            <b class="block font-mono text-sm">{{ $lot->code }}</b>
                            <span class="text-[13px] text-ink-sec">{{ $lot->creator?->org_name }} · {{ $lot->harvest_date->format('d/m/Y') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section id="ve-chung-toi" class="mt-10 rounded-card bg-white p-5 text-sm leading-relaxed text-ink-sec shadow-sm">
        <h2 class="mb-2 text-lg font-extrabold text-ink">Về chúng tôi</h2>
        AgriSenAi là dự án khởi nghiệp sinh viên tại Đồng Tháp. Giai đoạn MVP giải quyết việc lưu trữ thông tin lô rời rạc
        ở điểm thu gom; dữ liệu ảnh + nhãn đã được con người xác nhận là nền tảng để huấn luyện mô hình riêng ở giai đoạn sau.
    </section>
@endsection
