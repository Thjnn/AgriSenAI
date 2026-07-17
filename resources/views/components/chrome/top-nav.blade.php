@props(['page' => ''])

@php($links = config('navigation.top_links'))

<nav aria-label="Điều hướng chính" class="sticky top-0 z-[100] hidden border-b border-gray-100 bg-white lg:block">
    <div class="mx-auto flex h-16 max-w-[1200px] items-center justify-between px-8 xl:max-w-[1320px] xl:px-12">

        <a href="/" class="flex items-center gap-2.5 text-xl font-extrabold text-ink">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-card-sm">
                <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="AgriSenAi" class="block h-full w-full object-contain">
            </span>
            AgriSenAi
        </a>

        <ul class="flex items-center gap-8">
            @foreach ($links as $link)
                @php($isActive = $link['key'] === $page)
                <li>
                    <a href="{{ $link['href'] }}"
                       data-topnav="{{ $link['key'] }}"
                       @if ($isActive) data-topnav-active="1" @endif
                       class="text-sm font-bold transition-colors hover:text-brand {{ $isActive ? 'text-brand' : 'text-ink-sec' }}">
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-3">
            <div class="flex w-[260px] items-center gap-2 rounded-card border-[1.5px] border-gray-100 bg-gray-50 px-4 py-[9px] transition-colors focus-within:border-brand">
                <x-icon name="search" class="h-4 w-4 shrink-0 text-gray-400" />
                <input type="text" placeholder="Tìm nông sản..."
                       class="w-full border-none bg-transparent text-[13px] font-medium text-ink outline-none placeholder:text-gray-300">
            </div>

            @auth
                <span data-topnav-auth class="flex items-center gap-3">
                    <a href="#" aria-label="Thông báo"
                       class="relative flex h-[38px] w-[38px] items-center justify-center rounded-full bg-gray-50 transition-colors hover:bg-gray-100">
                        <x-icon name="bell" class="h-[18px] w-[18px] text-ink" />
                        <span class="absolute right-[7px] top-[7px] h-2 w-2 rounded-full border-2 border-white bg-danger"></span>
                    </a>

                    <a href="#" aria-label="Giỏ hàng"
                       class="flex h-[38px] w-[38px] items-center justify-center rounded-full bg-gray-50 transition-colors hover:bg-gray-100">
                        <x-icon name="cart" class="h-[18px] w-[18px] text-ink" />
                    </a>

                    <a href="{{ auth()->user()->accountHref() }}" aria-label="Tài khoản"
                       class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-light">
                        <x-icon name="user" class="h-5 w-5 text-brand" />
                    </a>
                </span>
            @endauth

            @guest
                <span data-topnav-guest class="flex items-center gap-2">
                    <a href="#" class="px-4 py-2 text-sm font-bold text-ink-sec transition-colors hover:text-brand">Đăng nhập</a>
                    <a href="#" class="rounded-card bg-brand px-4 py-2 text-sm font-bold text-white transition-colors hover:bg-brand-medium">Đăng ký</a>
                </span>
            @endguest
        </div>
    </div>
</nav>
