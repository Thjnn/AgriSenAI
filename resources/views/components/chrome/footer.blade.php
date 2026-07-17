{{-- Footer chỉ hiện từ lg trở lên — đúng demo, mobile dùng bottom-nav thay thế. --}}
<footer class="mt-12 hidden bg-brand-footer text-white/[0.82] lg:block">

    <div class="mx-auto grid max-w-[1200px] grid-cols-[1.7fr_1fr_1fr_1.5fr] gap-10 px-8 pb-9 pt-[52px]">

        <div>
            <div class="mb-4 flex items-center gap-[11px]">
                <span class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-xl bg-white p-1">
                    <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="AgriSenAi" class="block h-full w-full object-contain">
                </span>
                <span class="text-[21px] font-extrabold text-white">Agri<span class="text-brand-mint">SenAi</span></span>
            </div>

            <p class="mb-5 max-w-[300px] text-[13px] leading-[1.65]">
                Sàn nông sản Đồng Tháp ứng dụng AI đánh giá chất lượng và khuyến nghị thời điểm bán tối ưu, kết nối nông dân với người mua.
            </p>

            <div class="flex gap-2.5">
                @foreach ([['fb', 'Facebook'], ['yt', 'YouTube'], ['zalo', 'Zalo']] as [$icon, $label])
                    <a href="#" aria-label="{{ $label }}"
                       class="flex h-[38px] w-[38px] items-center justify-center rounded-full bg-white/10 transition-colors hover:bg-white/[0.22]">
                        <x-icon :name="$icon" class="h-[18px] w-[18px] text-white" />
                    </a>
                @endforeach
            </div>
        </div>

        @foreach (config('navigation.footer_columns') as $col)
            <div>
                <h4 class="mb-4 text-[13px] font-extrabold uppercase tracking-[0.6px] text-white">{{ $col['title'] }}</h4>
                @foreach ($col['links'] as $link)
                    <a href="{{ $link['href'] }}"
                       class="block py-1.5 text-[13px] font-medium text-white/[0.78] transition-colors hover:text-white">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        @endforeach

        <div>
            <h4 class="mb-4 text-[13px] font-extrabold uppercase tracking-[0.6px] text-white">Liên hệ</h4>
            @foreach (config('navigation.contact') as $item)
                <div class="mb-[13px] flex items-start gap-2.5 text-[13px] leading-[1.5]">
                    <x-icon :name="$item['icon']" class="mt-0.5 h-4 w-4 shrink-0 text-brand-mint" />
                    <span>{{ $item['text'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="border-t border-white/[0.13] p-5 text-center text-xs text-white/60">
        © {{ date('Y') }} AgriSenAi · Sàn nông sản Đồng Tháp. Mọi quyền được bảo lưu.
    </div>
</footer>
