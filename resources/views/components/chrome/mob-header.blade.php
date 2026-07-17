{{-- Header mobile, ẩn từ lg trở lên (chỗ đó dùng top-nav). --}}
<header class="sticky top-0 z-50 bg-white px-5 py-3.5 max-[360px]:px-4 max-[360px]:py-3 sm:px-7 sm:py-4 tab:px-8 lg:hidden">

    <div class="mb-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button type="button" id="drawerOpen" aria-label="Mở menu"
                    class="flex h-10 w-10 shrink-0 flex-col items-center justify-center gap-1 rounded-card-sm border-none bg-transparent active:bg-gray-50">
                <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
                <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
                <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
            </button>

            {{-- Logo demo là <div>, không phải link — giữ đúng vậy. --}}
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[11px]">
                <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="AgriSenAi" class="block h-full w-full object-contain">
            </div>

            {{-- Brand mob-header liền, KHÔNG tách span (khác drawer/footer). Sub là "Nông sản Đồng Tháp". --}}
            <div>
                <div class="text-lg font-extrabold leading-[1.15]">AgriSenAi</div>
                <div class="text-xs font-medium text-ink-sec">Nông sản Đồng Tháp</div>
            </div>
        </div>

        {{-- Demo chỉ có ĐÚNG MỘT nút chuông trong .mob-icons (không giỏ, không AI-scan —
             giỏ/AI-scan/tài khoản đã nằm ở bottom-nav). Chuông là @auth vì khách chưa có thông báo. --}}
        <div class="flex gap-1.5">
            @auth
                <a href="#" aria-label="Thông báo"
                   class="relative flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 active:bg-gray-100">
                    <x-icon name="bell" class="h-5 w-5 text-ink" />
                    <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full border-2 border-white bg-danger"></span>
                </a>
            @endauth
        </div>
    </div>

    {{-- Demo .mob-search không có :focus-within (chỉ .top-nav-search có) — bám đúng demo. --}}
    <div class="flex items-center gap-2.5 rounded-card border-[1.5px] border-gray-100 bg-gray-50 px-4 py-[11px]">
        <x-icon name="search" class="h-[18px] w-[18px] shrink-0 text-gray-400" />
        <input type="text" placeholder="Tìm nông sản..."
               class="w-full border-none bg-transparent text-sm font-medium text-ink outline-none placeholder:text-gray-300">
    </div>
</header>
