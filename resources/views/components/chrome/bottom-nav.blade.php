@props(['page' => ''])

@php($items = config('navigation.bottom_nav'))

<nav aria-label="Điều hướng dưới" class="fixed inset-x-0 bottom-0 z-[100] flex h-[var(--nav-h)] items-stretch justify-around border-t border-gray-100 bg-white pb-[var(--safe-b)] lg:hidden print:hidden">
    @foreach ($items as $item)
        @php($isActive = $item['key'] === $page)

        @if ($item['key'] === 'new')
            {{-- Nút tròn nổi lên khỏi thanh nav: việc làm nhiều nhất là tạo lô + chụp ảnh --}}
            <a href="{{ $item['href'] }}"
               data-bnav="{{ $item['key'] }}"
               @if ($isActive) data-bnav-active="1" @endif
               class="relative flex flex-1 flex-col items-center justify-end overflow-visible pb-[7px]">
                <span class="absolute -top-6 left-1/2 flex h-[54px] w-[54px] -translate-x-1/2 items-center justify-center rounded-full border-4 border-white bg-brand shadow-[0_4px_14px_rgba(46,125,50,0.35)] transition-transform active:scale-[0.92]">
                    <x-icon name="scan" class="h-6 w-6 text-white" />
                </span>
                <span class="mt-auto block text-[10px] font-bold leading-none text-brand">{{ $item['label'] }}</span>
            </a>
        @else
            <a href="{{ $item['href'] }}"
               data-bnav="{{ $item['key'] }}"
               @if ($isActive) data-bnav-active="1" @endif
               class="flex flex-1 flex-col items-center justify-center gap-0.5 text-[10px] font-semibold {{ $isActive ? 'text-brand' : 'text-gray-400' }}">
                <x-icon :name="$item['icon']" class="h-[22px] w-[22px]" :stroke-width="$isActive ? '2.5' : '1.8'" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endif
    @endforeach
</nav>
