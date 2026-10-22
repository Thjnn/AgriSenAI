@php($color = [
    'draft' => 'bg-gray-100 text-ink-sec',
    'analyzing' => 'bg-amber-100 text-amber-800',
    'analyzed' => 'bg-sky-100 text-sky-800',
    'confirmed' => 'bg-brand-light text-brand',
    'published' => 'bg-brand text-white',
][$status] ?? 'bg-gray-100')
<span class="inline-block shrink-0 whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-extrabold {{ $color }}">{{ \App\Models\Lot::STATUSES[$status] ?? $status }}</span>
