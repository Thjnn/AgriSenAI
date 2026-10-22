{{-- Hien mot ket luan da xac nhan: $key = tieu chi, $final = final_value (null = bac bo) --}}
@if ($final === null)
    <span class="text-ink-sec italic">Đã bác bỏ gợi ý, không kết luận</span>
@else
    <b>{{ config("agrisenai.criteria.{$key}.options.".($final['value'] ?? '')) ?? ($final['value'] ?? '—') }}</b>
    @if (! empty($final['defect_types']))
        <span class="text-ink-sec">— {{ collect($final['defect_types'])->map(fn ($t) => config("agrisenai.defect_types.{$t}", $t))->join(', ') }}</span>
    @endif
    @if (! empty($final['note']))
        <span class="block text-[13px] text-ink-sec">{{ $final['note'] }}</span>
    @endif
@endif
