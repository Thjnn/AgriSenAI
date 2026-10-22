@props(['page' => ''])

@php($links = \App\Providers\AppServiceProvider::visibleLinks(config('navigation.top_links')))

<nav aria-label="Điều hướng chính" class="sticky top-0 z-[100] hidden border-b border-gray-100 bg-white lg:block print:hidden">
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
            {{-- Ô tìm = tra cứu hồ sơ theo mã lô in trên tem --}}
            <form action="{{ route('public.lookup') }}" method="get"
                  class="flex w-[230px] items-center gap-2 rounded-card border-[1.5px] border-gray-100 bg-gray-50 px-4 py-[9px] transition-colors focus-within:border-brand">
                <x-icon name="search" class="h-4 w-4 shrink-0 text-gray-400" />
                <input type="text" name="ma" placeholder="Nhập mã lô, vd XL-…" aria-label="Mã lô"
                       class="w-full border-none bg-transparent text-[13px] font-medium text-ink outline-none placeholder:text-gray-300">
            </form>

            @auth
                <span data-topnav-auth class="flex items-center gap-3">
                    <span class="text-right leading-tight">
                        <span class="block text-[13px] font-bold">{{ auth()->user()->name }}</span>
                        <span class="block text-[11px] text-ink-sec">{{ auth()->user()->org_name }}</span>
                    </span>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-card border border-gray-200 px-3 py-2 text-[13px] font-bold text-ink-sec hover:text-brand">Đăng xuất</button>
                    </form>
                </span>
            @endauth

            @guest
                <a data-topnav-guest href="{{ route('login') }}" class="rounded-card bg-brand px-4 py-2 text-sm font-bold text-white transition-colors hover:bg-brand-medium">Đăng nhập</a>
            @endguest
        </div>
    </div>
</nav>
