/**
 * GlisseShop — JavaScript principal
 * assets/js/main.js
 */

'use strict';

// ============================================================
// 1. PANIER (localStorage)
// ============================================================
const CART_KEY = 'glisse_cart';

function getCart() {
    try {
        return JSON.parse(localStorage.getItem(CART_KEY) || '[]');
    } catch (e) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartBadge();
}

/**
 * Ajouter un article au panier
 * @param {number} id
 * @param {string} nom
 * @param {number} prix
 * @param {string} image
 * @param {string} variante
 */
function addToCart(id, nom, prix, image, variante) {
    const cart = getCart();
    const key  = id + '_' + (variante || '');
    const idx  = cart.findIndex(i => i.key === key);

    if (idx !== -1) {
        cart[idx].quantite += 1;
    } else {
        cart.push({ key, id, nom, prix, image: image || 'assets/images/placeholder.jpg', variante: variante || '', quantite: 1 });
    }

    saveCart(cart);
    showToast('✅ ' + nom + ' ajouté au panier !', 'success');
    updateCartBadge();
}

/**
 * Retirer un article par son index
 */
function removeFromCartByKey(key) {
    const cart = getCart().filter(i => i.key !== key);
    saveCart(cart);
    updateCartBadge();
}

/**
 * Mettre à jour la quantité par clé
 */
function updateQuantityByKey(key, qty) {
    const cart = getCart();
    const idx  = cart.findIndex(i => i.key === key);
    if (idx !== -1) {
        cart[idx].quantite = Math.max(1, parseInt(qty) || 1);
    }
    saveCart(cart);
}

/**
 * Calcule le total du panier
 */
function getCartTotal() {
    return getCart().reduce((sum, item) => sum + item.prix * item.quantite, 0);
}

/**
 * Nombre total d'articles
 */
function getCartCount() {
    return getCart().reduce((sum, item) => sum + item.quantite, 0);
}

/**
 * Met à jour le badge du panier dans le header
 */
function updateCartBadge() {
    const badge = document.getElementById('cartCount');
    if (!badge) return;
    const count = getCartCount();
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
}

// ============================================================
// 2. MENU MOBILE
// ============================================================
function initMobileMenu() {
    const btnHamburger = document.getElementById('btnHamburger');
    const navMobile    = document.getElementById('navMobile');
    const overlay      = document.getElementById('mobileOverlay');
    const closeBtn     = document.getElementById('closeMobile');

    if (!btnHamburger) return;

    function openMenu() {
        navMobile.classList.add('open');
        overlay.classList.add('open');
        btnHamburger.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        navMobile.classList.remove('open');
        overlay.classList.remove('open');
        btnHamburger.classList.remove('open');
        document.body.style.overflow = '';
    }

    btnHamburger.addEventListener('click', () => {
        if (navMobile.classList.contains('open')) closeMenu();
        else openMenu();
    });

    overlay.addEventListener('click', closeMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
}

// ============================================================
// 3. RECHERCHE
// ============================================================
function initSearch() {
    const btnSearch    = document.getElementById('btnSearch');
    const closeSearch  = document.getElementById('closeSearch');
    const overlay      = document.getElementById('searchOverlay');
    const searchInput  = document.getElementById('searchInput');
    const resultsEl    = document.getElementById('searchResults');

    if (!btnSearch) return;

    function openSearch() {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        if (searchInput) setTimeout(() => searchInput.focus(), 100);
    }

    function closeSearchFn() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        if (resultsEl) resultsEl.innerHTML = '';
        if (searchInput) searchInput.value = '';
    }

    btnSearch.addEventListener('click', openSearch);
    if (closeSearch) closeSearch.addEventListener('click', closeSearchFn);

    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeSearchFn();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeSearchFn();
    });

    // Auto-complétion AJAX
    if (searchInput && resultsEl) {
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const q = searchInput.value.trim();
            if (q.length < 2) { resultsEl.innerHTML = ''; return; }
            debounceTimer = setTimeout(() => {
                fetch('includes/search-ajax.php?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        if (!data.length) { resultsEl.innerHTML = '<p style="color:#999;padding:10px;">Aucun résultat</p>'; return; }
                        resultsEl.innerHTML = data.map(p => `
                            <a href="produit.php?id=${p.id}" class="search-result-item" style="text-decoration:none;color:inherit;">
                                <img src="${p.image || 'assets/images/placeholder.jpg'}" alt="${p.nom}"
                                    onerror="this.src='assets/images/placeholder.jpg'">
                                <div>
                                    <div style="font-weight:700;font-size:0.9rem;">${p.nom}</div>
                                    <div style="color:var(--primary);font-weight:700;font-size:0.85rem;">${parseFloat(p.prix).toFixed(2).replace('.',',')} €</div>
                                </div>
                            </a>`).join('');
                    })
                    .catch(() => {});
            }, 300);
        });
    }
}

// ============================================================
// 4. GALERIE PRODUIT
// ============================================================
function switchMainImage(src, el) {
    const main = document.getElementById('mainImg');
    if (main) main.src = src;
    document.querySelectorAll('.product-thumbnails img').forEach(i => i.classList.remove('active'));
    if (el) el.classList.add('active');
}

// ============================================================
// 5. TOAST NOTIFICATIONS
// ============================================================
function showToast(msg, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML = msg;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ============================================================
// 6. SCROLL TO TOP
// ============================================================
function initScrollTop() {
    const btn = document.getElementById('scrollTop');
    if (!btn) return;
    window.addEventListener('scroll', () => {
        btn.classList.toggle('visible', window.scrollY > 300);
    });
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

// ============================================================
// 7. FILTRES PRODUITS (client-side optionnel)
// ============================================================
function initProductFilters() {
    // Les filtres sont gérés côté serveur via PHP
    // Cette fonction peut être étendue pour le filtrage client-side si besoin
}

// ============================================================
// 8. STICKY HEADER
// ============================================================
function initStickyHeader() {
    const header = document.getElementById('header');
    if (!header) return;
    // Le sticky est géré par CSS (position:sticky), on ajoute juste la classe scrolled
    window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 50);
    });
}

// ============================================================
// 9. ANIMATIONS au scroll (Intersection Observer)
// ============================================================
function initScrollAnimations() {
    const cards = document.querySelectorAll('.product-card, .category-card, .blog-card, .stat-card');
    if (!('IntersectionObserver' in window)) return;
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.opacity = '1';
                e.target.style.transform = 'translateY(0)';
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        obs.observe(card);
    });
}

// ============================================================
// 10. PANIER PAGE — Fonctions spécifiques
// ============================================================
// Ces fonctions sont disponibles globalement pour panier.php
window.removeFromCart = function(idx) {
    let cart = getCart();
    cart.splice(idx, 1);
    saveCart(cart);
};

window.updateQty = function(idx, qty) {
    let cart = getCart();
    if (cart[idx]) cart[idx].quantite = Math.max(1, parseInt(qty) || 1);
    saveCart(cart);
};

window.clearCart = function() {
    if (confirm('Vider entièrement le panier ?')) {
        saveCart([]);
    }
};

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    updateCartBadge();
    initMobileMenu();
    initSearch();
    initScrollTop();
    initStickyHeader();

    // Animation au scroll (desktop uniquement pour perf)
    if (window.innerWidth > 768) {
        initScrollAnimations();
    }
});
