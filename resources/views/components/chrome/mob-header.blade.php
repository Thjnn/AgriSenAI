{{-- Header mobile, ẩn từ lg trở lên (chỗ đó dùng top-nav). --}}
<header class="sticky top-0 z-50 bg-white px-5 py-3.5 max-[360px]:px-4 max-[360px]:py-3 sm:px-7 sm:py-4 tab:px-8 lg:hidden print:hidden">

    <div class="mb-3.5 flex items-center gap-3">
        <button type="button" id="drawerOpen" aria-label="Mở menu"
                class="flex h-10 w-10 shrink-0 flex-col items-center justify-center gap-1 rounded-card-sm border-none bg-transparent active:bg-gray-50">
            <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
            <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
            <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
        </button>

        <a href="/" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[11px]">
            <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="AgriSenAi" class="block h-full w-full object-contain">
        </a>

        <div>
            <div class="text-lg font-extrabold leading-[1.15]">AgriSenAi</div>
            <div class="text-xs font-medium text-ink-sec">Hồ sơ lô xoài đáng tin</div>
        </div>
    </div>

    <form action="{{ route('public.lookup') }}" method="get"
          class="flex items-center gap-2.5 rounded-card border-[1.5px] border-gray-100 bg-gray-50 px-4 py-[11px] focus-within:border-brand">
        <x-icon name="search" class="h-[18px] w-[18px] shrink-0 text-gray-400" />
        <input type="text" name="ma" placeholder="Nhập mã lô để tra cứu hồ sơ" aria-label="Mã lô"
               class="w-full border-none bg-transparent text-sm font-medium text-ink outline-none placeholder:text-gray-300">
    </form>
</header>
