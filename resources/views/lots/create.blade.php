@extends('layouts.app', ['page' => 'new'])

@section('title', 'Tạo lô mới')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="mb-1 text-2xl font-extrabold">Tạo lô mới</h1>
        <p class="mb-5 text-sm text-ink-sec">Bước 1/5. Hệ thống cấp mã lô duy nhất để in lên tem QR.</p>

        <form method="post" action="{{ route('lots.store') }}" class="space-y-4 rounded-card bg-white p-5 shadow-md">
            @csrf
            @php($field = 'w-full rounded-card-sm border border-gray-200 px-3 py-3 text-base focus:border-brand focus:outline-none')

            <label class="block">
                <span class="mb-1 block text-sm font-bold">Nhà vườn / nguồn cung *</span>
                <input name="supplier" required maxlength="150" value="{{ old('supplier') }}" class="{{ $field }}" placeholder="vd Vườn ông Sáu Tâm, Mỹ Xương">
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-bold">Giống xoài</span>
                <input name="variety" maxlength="100" value="{{ old('variety') }}" class="{{ $field }}" placeholder="vd Cát Hòa Lộc, Keo, Cát Chu">
            </label>

            <fieldset>
                <legend class="mb-1 text-sm font-bold">Mục đích bán *</legend>
                <div class="grid grid-cols-2 gap-2">
                    @foreach (config('agrisenai.muc_dich_ban') as $key => $label)
                        <label class="flex cursor-pointer items-start gap-2 rounded-card-sm border border-gray-200 p-3 has-[:checked]:border-brand has-[:checked]:bg-brand-light">
                            <input type="radio" name="purpose" value="{{ $key }}" required @checked(old('purpose') === $key) class="mt-1 accent-brand">
                            <span class="text-sm font-semibold">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-ink-sec">AI cần biết để không coi vỏ xanh là lỗi khi bán ăn xanh.</p>
            </fieldset>

            <div class="grid grid-cols-2 gap-3">
                <label class="block">
                    <span class="mb-1 block text-sm font-bold">Khối lượng (kg) *</span>
                    <input name="weight_kg" type="number" inputmode="decimal" step="0.1" min="0.1" required value="{{ old('weight_kg') }}" class="{{ $field }}">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-bold">Ngày thu hoạch *</span>
                    <input name="harvest_date" type="date" required max="{{ now()->toDateString() }}" value="{{ old('harvest_date', now()->toDateString()) }}" class="{{ $field }}">
                </label>
            </div>

            @if ($errors->any())
                <ul class="list-disc pl-5 text-sm font-bold text-red-700">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            @endif

            <button class="w-full rounded-card bg-brand py-3 text-base font-extrabold text-white hover:bg-brand-medium">Tạo lô → chụp ảnh</button>
        </form>
    </div>
@endsection
