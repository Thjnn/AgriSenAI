@extends('layouts.app', ['page' => 'new'])

@section('title', 'Tạo lô mới')

@section('content')
    <div class="mx-auto max-w-lg">
        <a href="{{ route('lots.index') }}" class="inline-flex min-h-11 items-center text-sm font-bold text-ink-sec">← Lô hàng</a>
        <h1 class="text-2xl font-extrabold">Tạo lô mới</h1>
        <p class="mb-4 text-sm text-ink-sec">Hệ thống cấp mã lô duy nhất để in lên tem QR. Bước sau là chụp ảnh.</p>

        @include('lots._steps', ['current' => 1, 'linked' => false])

        <form method="post" action="{{ route('lots.store') }}" class="space-y-5 rounded-card bg-white p-5 shadow-sm" novalidate>
            @csrf
            @php
                $field = 'w-full rounded-card-sm border bg-white px-3.5 py-3 text-base transition focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10';
                $border = fn ($name) => $errors->has($name) ? 'border-red-400' : 'border-gray-200';
                $label = 'mb-1.5 block text-sm font-bold';
                $err = 'mt-1 block text-[13px] font-bold text-red-700';
            @endphp

            <label class="block">
                <span class="{{ $label }}">Nhà vườn / nguồn cung <span class="text-red-600">*</span></span>
                <input name="supplier" required maxlength="150" value="{{ old('supplier') }}" list="ds-nha-vuon" autocomplete="off"
                       class="{{ $field }} {{ $border('supplier') }}" placeholder="vd Vườn ông Sáu Tâm, Mỹ Xương">
                @error('supplier')<span class="{{ $err }}">{{ $message }}</span>@enderror
            </label>
            <datalist id="ds-nha-vuon">
                @foreach ($suppliers as $s)<option value="{{ $s }}"></option>@endforeach
            </datalist>

            <label class="block">
                <span class="{{ $label }}">Giống xoài <span class="font-normal text-ink-sec">(không bắt buộc)</span></span>
                <input name="variety" maxlength="100" value="{{ old('variety') }}" list="ds-giong" autocomplete="off"
                       class="{{ $field }} {{ $border('variety') }}" placeholder="vd Cát Hòa Lộc, Keo, Cát Chu">
                @error('variety')<span class="{{ $err }}">{{ $message }}</span>@enderror
            </label>
            <datalist id="ds-giong">
                @foreach ($varieties as $v)<option value="{{ $v }}"></option>@endforeach
            </datalist>

            <fieldset>
                <legend class="{{ $label }}">Mục đích bán <span class="text-red-600">*</span></legend>
                <div class="grid grid-cols-2 gap-2.5">
                    @foreach (config('agrisenai.muc_dich_ban') as $key => $text)
                        {{-- "Ban de an xanh (xoai xanh cham muoi ot)" -> tieu de + dong phu --}}
                        @php
                            [$title, $sub] = array_pad(explode(' (', $text, 2), 2, null);
                        @endphp
                        <label class="relative flex cursor-pointer flex-col gap-1 rounded-card-sm border-2 p-3 transition
                                      {{ $errors->has('purpose') ? 'border-red-300' : 'border-gray-100' }}
                                      has-[:checked]:border-brand has-[:checked]:bg-brand-bg has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-brand/20">
                            <input type="radio" name="purpose" value="{{ $key }}" required @checked(old('purpose') === $key) class="peer sr-only">
                            <span aria-hidden="true" class="absolute right-2.5 top-2.5 flex h-5 w-5 items-center justify-center rounded-full border-2 border-gray-200 text-[11px] font-black text-white peer-checked:border-brand peer-checked:bg-brand">✓</span>
                            <b class="pr-6 text-[15px] leading-tight">{{ $title }}</b>
                            @if ($sub)<span class="text-[12px] leading-snug text-ink-sec">{{ rtrim($sub, ')') }}</span>@endif
                        </label>
                    @endforeach
                </div>
                @error('purpose')
                    <span class="{{ $err }}">{{ $message }}</span>
                @else
                    <p class="mt-1.5 text-xs text-ink-sec">AI cần biết để không coi vỏ xanh là lỗi khi bán ăn xanh.</p>
                @enderror
            </fieldset>

            <div class="grid grid-cols-2 gap-3">
                <label class="block">
                    <span class="{{ $label }}">Khối lượng <span class="text-red-600">*</span></span>
                    <span class="relative block">
                        <input name="weight_kg" type="number" inputmode="decimal" step="0.1" min="0.1" required value="{{ old('weight_kg') }}"
                               class="{{ $field }} {{ $border('weight_kg') }} pr-10" placeholder="0">
                        <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-sm font-bold text-ink-sec">kg</span>
                    </span>
                    @error('weight_kg')<span class="{{ $err }}">{{ $message }}</span>@enderror
                </label>
                <label class="block">
                    <span class="{{ $label }}">Ngày thu hoạch <span class="text-red-600">*</span></span>
                    <input name="harvest_date" type="date" required max="{{ now()->toDateString() }}" value="{{ old('harvest_date', now()->toDateString()) }}"
                           class="{{ $field }} {{ $border('harvest_date') }} px-2.5">
                    @error('harvest_date')<span class="{{ $err }}">{{ $message }}</span>@enderror
                </label>
            </div>

            <button class="flex w-full items-center justify-center gap-2 rounded-card bg-brand py-3.5 text-base font-extrabold text-white shadow-[0_6px_16px_-6px_rgba(46,125,50,.5)] hover:bg-brand-medium">
                <x-icon name="scan" class="h-5 w-5" /> Tạo lô và chụp ảnh
            </button>
        </form>
    </div>
@endsection
