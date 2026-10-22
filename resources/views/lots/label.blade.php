@extends('layouts.app', ['bare' => true])

@section('title', 'Tem '.$lot->code)

@push('head')
    <style>
        @page { size: 50mm 30mm; margin: 0; }
        @media print {
            body { padding: 0 !important; background: #fff !important; }
            main { padding: 0 !important; max-width: none !important; }
            .no-print { display: none !important; }
        }
        .tem { width: 50mm; height: 30mm; display: flex; gap: 2mm; padding: 2mm; box-sizing: border-box; background: #fff; overflow: hidden; }
        .tem svg { width: 26mm; height: 26mm; flex-shrink: 0; }
    </style>
@endpush

@section('content')
    <div class="no-print mb-4 flex gap-2">
        <button onclick="print()" class="rounded-card bg-brand px-4 py-2 font-extrabold text-white">In tem</button>
        <a href="{{ route('lots.show', $lot) }}" class="rounded-card border border-gray-200 px-4 py-2 font-bold">Quay lại</a>
        <span class="self-center text-xs text-ink-sec">Khổ 50 × 30 mm. Chọn đúng khổ giấy máy in tem.</span>
    </div>

    <div class="tem border border-dashed border-gray-300 print:border-0">
        {!! $qr !!}
        <div class="flex min-w-0 flex-col justify-center leading-tight">
            <b class="text-[7pt]">AgriSenAi · Hồ sơ lô</b>
            <b class="break-all font-mono text-[8.5pt]">{{ $lot->code }}</b>
            <span class="text-[6.5pt]">Thu hoạch {{ $lot->harvest_date->format('d/m/Y') }}</span>
            <span class="truncate text-[6.5pt]">{{ $lot->supplier }}</span>
            <span class="mt-0.5 text-[6pt] text-ink-sec">Quét QR để kiểm chứng</span>
        </div>
    </div>
@endsection
