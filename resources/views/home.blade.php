@extends('layouts.app', ['page' => 'home'])

@section('content')
    {{-- Hero --}}
    <section class="rounded-card-lg bg-gradient-to-br from-brand-banner-1 to-brand-banner-2 px-6 py-10 lg:px-12 lg:py-14">
        <p class="mb-2 text-xs font-extrabold uppercase tracking-wider text-brand">B2B SaaS cho điểm thu gom & HTX xoài</p>
        <h1 class="max-w-2xl text-3xl font-extrabold leading-tight lg:text-[40px]">Hồ sơ lô xoài đáng tin — chụp chuẩn, AI gợi ý, con người xác nhận.</h1>
        <p class="mt-4 max-w-xl text-[15px] leading-relaxed text-ink">
            Thay sổ tay và tin nhắn Zalo rời rạc bằng một hồ sơ lô thống nhất. Người mua quét QR trên thùng hàng
            là thấy ảnh, ngoại quan đã duyệt, <b>ai xác nhận, lúc nào</b>, và hồ sơ có còn nguyên vẹn không.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
            @auth
                <a href="{{ route('lots.create') }}" class="rounded-card bg-brand px-5 py-3 font-extrabold text-white hover:bg-brand-medium">Tạo lô mới</a>
                <a href="{{ route('lots.index') }}" class="rounded-card bg-white px-5 py-3 font-extrabold text-brand">Danh sách lô</a>
            @else
                <a href="{{ route('login') }}" class="rounded-card bg-brand px-5 py-3 font-extrabold text-white hover:bg-brand-medium">Đăng nhập nhân viên</a>
                <a href="{{ route('public.lookup') }}" class="rounded-card bg-white px-5 py-3 font-extrabold text-brand">Tra cứu hồ sơ</a>
            @endauth
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
