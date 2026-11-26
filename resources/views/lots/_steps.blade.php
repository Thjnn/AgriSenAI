{{-- Thanh tien do 5 buoc ho so lo. $current = buoc dang lam (6 = xong het);
     $linked = cham vao buoc de nhay toi #buoc-N (trang chi tiet lo). --}}
<ol aria-label="Tiến độ hồ sơ" class="mb-4 grid grid-cols-5 rounded-card bg-white px-2 py-3 shadow-sm">
    @foreach (['Tạo lô', 'Ảnh', 'AI gợi ý', 'Xác nhận', 'Công bố'] as $i => $name)
        @php
            $n = $i + 1;
            $tag = $linked && $n > 1 ? 'a' : 'span';
        @endphp
        <li class="relative flex flex-col items-center gap-1 text-center">
            @if ($n > 1)
                <span aria-hidden="true" class="absolute -left-1/2 top-[15px] h-0.5 w-full {{ $n <= $current ? 'bg-brand' : 'bg-gray-100' }}"></span>
            @endif
            <{{ $tag }} @if ($tag === 'a') href="#buoc-{{ $n }}" @endif @if ($n === $current) aria-current="step" @endif
               class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full text-sm font-extrabold
                      {{ $n < $current ? 'bg-brand text-white' : ($n === $current ? 'bg-white text-brand ring-2 ring-brand' : 'bg-gray-100 text-ink-light') }}">
                {{ $n < $current ? '✓' : $n }}
            </{{ $tag }}>
            <span class="text-[11px] font-bold leading-tight {{ $n === $current ? 'text-brand' : ($n < $current ? 'text-ink' : 'text-ink-light') }}">{{ $name }}</span>
        </li>
    @endforeach
</ol>
