{{--
    Storefront Promo Popup (mobile theme — same logic as default)
    Include once on the homepage:
        @include('theme::partials.promo-popup')
--}}

<div id="promoPopupRoot" aria-hidden="true"></div>

<style>
    #promoPopupRoot.show { display: flex !important; }
    #promoPopupRoot {
        display: none;
        position: fixed; inset: 0;
        background: rgba(15, 23, 42, .75);
        z-index: 1080;
        align-items: center; justify-content: center;
        padding: 16px;
        animation: ppFade .25s ease;
    }
    @keyframes ppFade { from { opacity: 0 } to { opacity: 1 } }
    .pp-card {
        position: relative;
        max-width: 420px;
        width: 100%;
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(0,0,0,.35);
        animation: ppPop .3s cubic-bezier(.2,.9,.3,1.2);
    }
    @keyframes ppPop { from { transform: scale(.92); opacity: .4 } to { transform: scale(1); opacity: 1 } }
    .pp-card img { display: block; width: 100%; height: auto; cursor: pointer; }
    .pp-close {
        position: absolute; top: 10px; right: 10px;
        width: 36px; height: 36px; border-radius: 50%;
        background: rgba(0,0,0,.55); color: #fff;
        border: none; display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; cursor: pointer;
    }
    .pp-cta { padding: 12px 14px; text-align: center; border-top: 1px solid #f1f5f9; }
    .pp-cta a {
        display: inline-block; padding: 9px 22px; border-radius: 999px;
        background: #2563eb; color: #fff; text-decoration: none; font-weight: 600; font-size: .85rem;
    }
</style>

<script>
(function () {
    'use strict';
    const STORAGE_KEY = 'pp_seen_v1';
    function getSeenIds() {
        try { return JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]'); }
        catch (e) { return []; }
    }
    function markSeen(id) {
        const ids = getSeenIds();
        if (!ids.includes(id)) { ids.push(id); sessionStorage.setItem(STORAGE_KEY, JSON.stringify(ids)); }
    }
    function build(popup) {
        const root = document.getElementById('promoPopupRoot');
        if (!root) return;
        const linkOpenAttrs = popup.link_url ? `href="${popup.link_url}" target="_blank" rel="noopener"` : '';
        const ctaHtml = popup.link_url ? `<div class="pp-cta"><a ${linkOpenAttrs}>${popup.button_label || 'Lihat Promo'}</a></div>` : '';
        const imgClickable = popup.link_url ? `onclick="window.open('${popup.link_url}', '_blank')"` : '';
        root.innerHTML = `
            <div class="pp-card" role="dialog" aria-modal="true" aria-label="${popup.title || 'Promo'}">
                <button type="button" class="pp-close" aria-label="Tutup">&times;</button>
                <img src="${popup.image_url}" alt="${popup.title || 'Promo'}" ${imgClickable}>
                ${ctaHtml}
            </div>`;
        const close = () => {
            root.classList.remove('show');
            root.setAttribute('aria-hidden', 'true');
            if (popup.show_once_per_session) markSeen(popup.id);
        };
        root.querySelector('.pp-close').addEventListener('click', close);
        root.addEventListener('click', e => { if (e.target === root) close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && root.classList.contains('show')) close(); }, { once: true });
        root.classList.add('show');
        root.setAttribute('aria-hidden', 'false');
    }
    function init() {
        fetch('{{ route('promo-popup.active') }}', { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(json => {
                const popup = json && json.data;
                if (!popup) return;
                if (popup.show_once_per_session && getSeenIds().includes(popup.id)) return;
                const delayMs = (parseInt(popup.display_delay, 10) || 0) * 1000;
                setTimeout(() => build(popup), delayMs);
            })
            .catch(() => {});
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
