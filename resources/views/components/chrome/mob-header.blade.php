@props(['page' => ''])

{{-- Header mobile, ẩn từ lg trở lên (chỗ đó dùng top-nav).
     Một hàng gọn; ô tra cứu chỉ mở sẵn ở trang chủ + tra cứu, trang khác là nút kính lúp
     để nhường chiều cao cho nội dung (màn làm việc ở điểm thu gom). --}}
@php($showSearch = in_array($page, ['home', 'lookup'], true))

<header class="sticky top-0 z-50 border-b border-gray-100 bg-white/95 px-4 py-2.5 backdrop-blur sm:px-7 tab:px-8 lg:hidden print:hidden">
    <div class="flex items-center gap-2.5">
        <button type="button" id="drawerOpen" aria-label="Mở menu"
                class="-ml-1.5 flex h-11 w-11 shrink-0 flex-col items-center justify-center gap-1 rounded-card-sm active:bg-gray-50">
            <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
            <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
            <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
        </button>

        <a href="/" class="flex min-w-0 items-center gap-2">
            <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="" class="h-9 w-9 shrink-0 object-contain">
            <span class="min-w-0">
                <span class="block text-[17px] font-extrabold leading-tight">AgriSen</span>
                <span class="block truncate text-[11px] font-medium text-ink-sec">Hồ sơ lô xoài đáng tin</span>
            </span>
        </a>

        @unless ($showSearch)
            <a href="{{ route('public.lookup') }}" aria-label="Tra cứu hồ sơ"
               class="ml-auto flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gray-50 text-ink-sec active:bg-gray-100">
                <x-icon name="search" class="h-5 w-5" />
            </a>
        @endunless
    </div>

    <form action="{{ route('public.lookup') }}" method="get" @unless ($showSearch) hidden @endunless
          class="mt-2.5 flex items-center gap-2.5 rounded-card border-[1.5px] border-gray-100 bg-gray-50 px-4 py-2.5 focus-within:border-brand">
        <x-icon name="search" class="h-[18px] w-[18px] shrink-0 text-gray-400" />
        <input type="text" name="ma" placeholder="Nhập mã lô để tra cứu hồ sơ" aria-label="Mã lô" autocapitalize="characters"
               class="w-full border-none bg-transparent text-[15px] font-medium text-ink outline-none placeholder:text-gray-300">
    </form>
</header>
