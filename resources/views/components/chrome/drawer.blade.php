@props(['page' => ''])

@php($groups = config('navigation.drawer_groups'))

{{-- Overlay + drawer. Class 'open' do resources/js/app.js gắn/gỡ. --}}
<div id="drawerOverlay"
     class="invisible fixed inset-0 z-[998] bg-black/40 opacity-0 transition-[opacity,visibility] duration-[250ms] lg:hidden"></div>

<aside id="drawer"
       class="fixed inset-y-0 left-0 z-[999] flex w-[78%] max-w-[300px] -translate-x-full flex-col bg-white shadow-[4px_0_24px_rgba(0,0,0,0.1)] transition-transform duration-[280ms] ease-[cubic-bezier(0.4,0,0.2,1)] lg:hidden">

    <div class="flex items-center gap-3 border-b border-gray-100 px-5 pb-5 pt-6">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl">
            <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="AgriSenAi" class="block h-full w-full object-contain">
        </span>
        <div class="flex-1">
            <div class="text-[19px] font-extrabold">Agri<span class="text-brand">SenAi</span></div>
            <div class="text-[11px] font-semibold text-ink-sec">Sàn nông sản Đồng Tháp</div>
        </div>
        <button type="button" id="drawerClose" aria-label="Đóng menu"
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-50 active:bg-gray-100">
            <x-icon name="close" class="h-4 w-4 text-ink" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-3">
        @foreach ($groups as $group)
            {{-- Đường kẻ ngăn giữa các nhóm (drawer-divider trong demo), không kẻ trước nhóm đầu. --}}
            @unless ($loop->first)
                <div class="mx-5 my-2.5 h-px bg-gray-100"></div>
            @endunless

            @foreach ($group as $link)
                @php($isActive = $link['key'] === $page)
                @php($href = $link['key'] === 'account' && auth()->check() ? auth()->user()->accountHref() : $link['href'])
                <a href="{{ $href }}"
                   data-drawer="{{ $link['key'] }}"
                   @if ($isActive) data-drawer-active="1" @endif
                   class="flex items-center gap-3.5 px-5 py-[13px] text-[15px] font-bold transition-colors active:bg-gray-50 {{ $isActive ? 'bg-brand-light text-brand' : 'text-ink' }}">
                    <x-icon :name="$link['icon']" class="h-[21px] w-[21px] shrink-0" />
                    {{ $link['label'] }}
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="border-t border-gray-100 px-5 pb-[22px] pt-4">
        @auth
            <a href="{{ auth()->user()->accountHref() }}" data-drawer-user
               class="flex items-center gap-3 rounded-card bg-gray-50 p-2.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-light">
                    <x-icon name="user" class="h-5 w-5 text-brand" />
                </span>
                <span>
                    <span class="block text-sm font-bold">{{ auth()->user()->name }}</span>
                    <span class="block text-[11px] font-semibold text-ink-sec">
                        {{ auth()->user()->isFarmer() ? 'Nông dân' : 'Người mua' }}
                    </span>
                </span>
            </a>
        @endauth

        @guest
            <a href="#" data-drawer-guest
               class="flex items-center justify-center rounded-card bg-brand px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-brand-medium">
                Đăng nhập
            </a>
        @endguest
    </div>
</aside>
