/**
 * Khung giao diện: đóng/mở drawer (menu mobile) và panel trợ lý chat.
 * Cả hai đều khoá scroll nền khi mở và đóng được bằng phím Esc.
 */

/**
 * Nối một panel trượt với nút mở, overlay và nút đóng.
 *
 * @param {object} refs
 * @param {string} refs.panel   id của panel
 * @param {string} refs.overlay id của lớp phủ
 * @param {string} refs.open    id của nút mở
 * @param {string} refs.close   id của nút đóng
 */
function bindSlidePanel({ panel, overlay, open, close }) {
    const panelEl = document.getElementById(panel);
    const overlayEl = document.getElementById(overlay);
    const openEl = document.getElementById(open);
    const closeEl = document.getElementById(close);

    if (!panelEl || !overlayEl) return;

    const setOpen = (isOpen) => {
        panelEl.classList.toggle('open', isOpen);
        overlayEl.classList.toggle('open', isOpen);
        panelEl.setAttribute('aria-hidden', String(!isOpen));
        // Khoá scroll nền để không cuộn trang phía sau khi panel đang mở.
        document.body.style.overflow = isOpen ? 'hidden' : '';
    };

    openEl?.addEventListener('click', () => setOpen(true));
    closeEl?.addEventListener('click', () => setOpen(false));
    overlayEl.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && panelEl.classList.contains('open')) {
            setOpen(false);
        }
    });
}

bindSlidePanel({
    panel: 'drawer',
    overlay: 'drawerOverlay',
    open: 'drawerOpen',
    close: 'drawerClose',
});

bindSlidePanel({
    panel: 'chatPanel',
    overlay: 'chatOverlay',
    open: 'chatFab',
    close: 'chatClose',
});
