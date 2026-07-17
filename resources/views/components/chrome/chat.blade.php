{{--
    Vỏ trợ lý AI giá bán. Chỉ có phần nhìn — chưa nối AI, chưa có hội thoại.
    Class 'open' do resources/js/app.js gắn/gỡ.
--}}

<button type="button" id="chatFab" aria-label="Trợ lý AI giá bán"
        class="fixed right-5 bottom-[calc(var(--nav-h)+var(--safe-b)+16px)] z-[900] flex h-14 w-14 items-center justify-center rounded-full border-none bg-brand shadow-[0_6px_20px_rgba(46,125,50,0.42)] transition-transform active:scale-[0.92] hover:shadow-[0_8px_26px_rgba(46,125,50,0.52)] lg:bottom-6 lg:right-6">
    <x-icon name="chat" class="h-[26px] w-[26px] text-white" />
</button>

<div id="chatOverlay"
     class="invisible fixed inset-0 z-[1000] bg-black/40 opacity-0 transition-[opacity,visibility] duration-[250ms]"></div>

<aside id="chatPanel" aria-hidden="true"
       class="fixed inset-y-0 right-0 z-[1001] flex w-full max-w-[420px] translate-x-full flex-col bg-surface shadow-[-4px_0_24px_rgba(0,0,0,0.12)] transition-transform duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]">

    <div class="flex shrink-0 items-center justify-between gap-3 bg-[linear-gradient(120deg,var(--color-brand-deep),var(--color-brand-medium))] px-[18px] py-4 text-white">
        <div class="flex items-center gap-[11px]">
            <span class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-full bg-white/[0.18]">
                <x-icon name="chat" class="h-5 w-5 text-white" />
            </span>
            <div>
                <div class="text-[15px] font-extrabold">Trợ lý giá bán</div>
                <div class="mt-0.5 flex items-center gap-[5px] text-[11px] font-semibold text-white/85">
                    <span class="h-[7px] w-[7px] rounded-full bg-online shadow-[0_0_0_2px_rgba(124,252,154,0.3)]"></span>
                    Đang hoạt động
                </div>
            </div>
        </div>

        <button type="button" id="chatClose" aria-label="Đóng trợ lý"
                class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-full border-none bg-white/[0.16] text-white">
            <x-icon name="close" class="h-[18px] w-[18px]" />
        </button>
    </div>

    {{-- Khung tin nhắn — sẽ do feature chat đổ dữ liệu vào sau. --}}
    <div id="chatBody" class="flex flex-1 flex-col gap-3 overflow-y-auto p-[18px]"></div>

    <div class="flex shrink-0 items-center gap-2.5 border-t border-gray-100 bg-white px-3.5 pt-3 pb-[calc(12px+env(safe-area-inset-bottom,0px))]">
        <input type="text" placeholder="Hỏi về giá bán..."
               class="flex-1 rounded-[22px] border-[1.5px] border-gray-200 bg-white px-4 py-[11px] text-sm text-ink outline-none focus:border-brand">
        <button type="button" aria-label="Gửi"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-none bg-brand transition-colors active:bg-brand-deep">
            <x-icon name="send" class="h-5 w-5 text-white" />
        </button>
    </div>
</aside>
