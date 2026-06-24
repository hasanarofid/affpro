<div id="promoPopupRoot" aria-hidden="true"></div>

<style>
    #promoPopupRoot.show { display:flex !important; }
    #promoPopupRoot { display:none; position:fixed; inset:0; background:rgba(15,23,42,.78); z-index:1080; align-items:center; justify-content:center; padding:18px; backdrop-filter: blur(6px); }
    .pp-card { position:relative; max-width:520px; width:100%; background:#fff; border-radius:28px; overflow:hidden; box-shadow:0 30px 80px rgba(0,0,0,.35); border:1px solid rgba(255,255,255,.6); animation: ppPop .35s cubic-bezier(.2,.9,.3,1.2); }
    @keyframes ppPop { from { transform: scale(.94); opacity:0 } to { transform: scale(1); opacity:1 } }
    .pp-card img { display:block; width:100%; height:auto; cursor:pointer; }
    .pp-close { position:absolute; top:14px; right:14px; width:38px; height:38px; border-radius:999px; background:rgba(17,24,39,.7); color:#fff; border:0; display:flex; align-items:center; justify-content:center; font-size:1.1rem; cursor:pointer; transition:.2s ease; }
    .pp-close:hover { background:#111827; transform: rotate(90deg); }
    .pp-cta { padding:18px; text-align:center; border-top:1px solid #f1ede6; background: linear-gradient(180deg,#fff,#fbfaf6); }
    .pp-cta a { display:inline-flex; align-items:center; gap:8px; padding:12px 26px; border-radius:999px; background:#111827; color:#fff; text-decoration:none; font-weight:700; font-size:.9rem; }
    .pp-cta a:hover { background:#000; }
</style>

<script>
(function () {
    'use strict';
    const STORAGE_KEY = 'pp_seen_v1';
    function getSeenIds() { try { return JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]'); } catch (e) { return []; } }
    function markSeen(id) { const ids = getSeenIds(); if (!ids.includes(id)) { ids.push(id); sessionStorage.setItem(STORAGE_KEY, JSON.stringify(ids)); } }
    function build(popup) {
        const root = document.getElementById('promoPopupRoot'); if (!root) return;
        const linkOpenAttrs = popup.link_url ? `href="${popup.link_url}" target="_blank" rel="noopener"` : '';
        const ctaHtml = popup.link_url ? `<div class="pp-cta"><a ${linkOpenAttrs}>${popup.button_label || 'Lihat Promo'} <i class="bi bi-arrow-right"></i></a></div>` : '';
        const imgClickable = popup.link_url ? `onclick="window.open('${popup.link_url}', '_blank')"` : '';
        root.innerHTML = `
            <div class="pp-card" role="dialog" aria-modal="true" aria-label="${popup.title || 'Promo'}">
                <button type="button" class="pp-close" aria-label="Tutup">&times;</button>
                <img src="${popup.image_url}" alt="${popup.title || 'Promo'}" ${imgClickable}>
                ${ctaHtml}
            </div>`;
        const close = () => { root.classList.remove('show'); root.setAttribute('aria-hidden', 'true'); if (popup.show_once_per_session) markSeen(popup.id); };
        root.querySelector('.pp-close').addEventListener('click', close);
        root.addEventListener('click', e => { if (e.target === root) close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && root.classList.contains('show')) close(); }, { once: true });
        root.classList.add('show'); root.setAttribute('aria-hidden', 'false');
    }
    function init() {
        fetch('{{ route('promo-popup.active') }}', { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(json => {
                const popup = json && json.data; if (!popup) return;
                if (popup.show_once_per_session && getSeenIds().includes(popup.id)) return;
                const delayMs = (parseInt(popup.display_delay, 10) || 0) * 1000;
                setTimeout(() => build(popup), delayMs);
            }).catch(() => {});
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
