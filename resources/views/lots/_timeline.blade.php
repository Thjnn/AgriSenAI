{{-- Nhat ky audit dang timeline: $logs = AuditLog collection --}}
<ol class="relative space-y-3 border-l-2 border-gray-100 pl-5">
    @foreach ($logs as $log)
        <li class="relative">
            <span class="absolute -left-[27px] top-1 h-3 w-3 rounded-full border-2 border-white bg-brand"></span>
            <b class="block text-sm">{{ \App\Models\AuditLog::ACTIONS[$log->action] ?? $log->action }}</b>
            <span class="text-[12px] text-ink-sec">
                {{ $log->created_at->format('H:i d/m/Y') }} · {{ $log->actor?->name ?? 'Hệ thống' }}
                @if ($log->action === 'ai_suggested' && ($log->data()['error'] ?? null)) · <span class="text-red-700">AI lỗi</span>@endif
            </span>
            <code class="block truncate text-[10.5px] text-ink-light" title="{{ $log->hash }}">#{{ substr($log->hash, 0, 16) }}…</code>
        </li>
    @endforeach
</ol>
