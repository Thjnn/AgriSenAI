{{-- PWA: đăng ký service worker (dùng chung). Root-relative '/sw.js' để scope = '/'. --}}
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js')
                .then(function (reg) { console.log('[PWA] SW scope:', reg.scope); })
                .catch(function (err) { console.warn('[PWA] SW lỗi:', err); });
        });
    }
</script>
