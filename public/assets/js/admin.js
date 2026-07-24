/**
 * GlisseShop — JavaScript Interface Admin
 * assets/js/admin.js
 */

'use strict';

// ============================================================
// 1. SIDEBAR TOGGLE (mobile)
// ============================================================
function initSidebarToggle() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar   = document.getElementById('adminSidebar');
    if (!toggleBtn || !sidebar) return;

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });

    // Fermer en cliquant à l'extérieur (mobile)
    document.addEventListener('click', e => {
        if (window.innerWidth < 768 &&
            sidebar.classList.contains('open') &&
            !sidebar.contains(e.target) &&
            e.target !== toggleBtn) {
            sidebar.classList.remove('open');
        }
    });
}

// ============================================================
// 2. CONFIRMATION SUPPRESSION
// ============================================================
function initDeleteConfirms() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm || 'Confirmer cette action ?')) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// ============================================================
// 3. ALERTS AUTO-DISMISS
// ============================================================
function initAlertsDismiss() {
    document.querySelectorAll('.alert-admin-success').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 4000);
    });
}

// ============================================================
// 4. DRAG & DROP UPLOAD ZONES
// ============================================================
function initUploadZones() {
    document.querySelectorAll('.upload-zone').forEach(zone => {
        zone.addEventListener('dragover', e => {
            e.preventDefault();
            zone.classList.add('dragover');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('dragover');
            const input = zone.querySelector('input[type="file"]');
            if (input && e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
    });
}

// ============================================================
// 5. MODAL HELPERS
// ============================================================
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

// Fermer modal en cliquant à l'extérieur
document.addEventListener('click', e => {
    document.querySelectorAll('.modal-overlay.active').forEach(modal => {
        if (e.target === modal) modal.classList.remove('active');
    });
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
    }
});

// ============================================================
// 6. TABLE SEARCH (filtrage côté client)
// ============================================================
function initTableSearch() {
    const searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(input => {
        const targetId = input.dataset.tableSearch;
        const table = document.getElementById(targetId);
        if (!table) return;
        input.addEventListener('input', () => {
            const q = input.value.toLowerCase();
            table.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    });
}

// ============================================================
// 7. SLUG AUTO-GÉNÉRATION
// ============================================================
function initSlugGeneration() {
    const titreInput = document.getElementById('titre') || document.getElementById('nom');
    const slugInput  = document.getElementById('slug');
    if (!titreInput || !slugInput) return;

    titreInput.addEventListener('input', () => {
        if (slugInput.dataset.manual) return; // Ne pas écraser si modifié manuellement
        slugInput.value = toSlug(titreInput.value);
    });

    slugInput.addEventListener('input', () => {
        slugInput.dataset.manual = '1';
    });
}

function toSlug(str) {
    const map = { 'à':'a','â':'a','ä':'a','é':'e','è':'e','ê':'e','ë':'e','î':'i','ï':'i','ô':'o','ö':'o','ù':'u','û':'u','ü':'u','ç':'c','ñ':'n','&':'et' };
    return str.toLowerCase()
        .replace(/[àâäáãéèêëîïíôöóòùûüúçñ&]/g, c => map[c] || c)
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-|-$/g, '');
}

// ============================================================
// 8. PRIX — Calcul réduction live
// ============================================================
function initPriceReduction() {
    const prixInput       = document.getElementById('prix');
    const ancienPrixInput = document.getElementById('ancien_prix');
    if (!prixInput || !ancienPrixInput) return;

    function updateReduction() {
        const prix       = parseFloat(prixInput.value) || 0;
        const ancienPrix = parseFloat(ancienPrixInput.value) || 0;
        let tag = document.getElementById('reductionTag');
        if (!tag) {
            tag = document.createElement('span');
            tag.id = 'reductionTag';
            tag.style.cssText = 'display:inline-block;background:#dc3545;color:#fff;padding:3px 10px;border-radius:4px;font-size:0.82rem;font-weight:700;margin-left:8px;';
            ancienPrixInput.parentNode.appendChild(tag);
        }
        if (ancienPrix > prix && prix > 0) {
            const reduc = Math.round(((ancienPrix - prix) / ancienPrix) * 100);
            tag.textContent = '-' + reduc + '%';
            tag.style.display = 'inline-block';
        } else {
            tag.style.display = 'none';
        }
    }

    prixInput.addEventListener('input', updateReduction);
    ancienPrixInput.addEventListener('input', updateReduction);
    updateReduction();
}

// ============================================================
// 9. COUNTER - Compteur de caractères
// ============================================================
function initCharCounters() {
    document.querySelectorAll('[data-maxlength]').forEach(el => {
        const max = parseInt(el.dataset.maxlength);
        const counter = document.createElement('small');
        counter.style.color = '#999';
        el.parentNode.appendChild(counter);
        const update = () => {
            const remaining = max - el.value.length;
            counter.textContent = `${el.value.length}/${max}`;
            counter.style.color = remaining < 20 ? '#dc3545' : '#999';
        };
        el.addEventListener('input', update);
        update();
    });
}

// ============================================================
// 10. TOAST ADMIN
// ============================================================
function showAdminToast(msg, type = 'success') {
    let container = document.getElementById('adminToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'adminToastContainer';
        container.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    const colors = { success: '#28a745', danger: '#dc3545', warning: '#ffc107', info: '#17a2b8' };
    toast.style.cssText = `background:#fff;border-left:4px solid ${colors[type] || colors.success};border-radius:6px;padding:12px 18px;box-shadow:0 4px 16px rgba(0,0,0,0.12);font-size:0.88rem;max-width:300px;`;
    toast.textContent = msg;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    initSidebarToggle();
    initDeleteConfirms();
    initAlertsDismiss();
    initUploadZones();
    initTableSearch();
    initSlugGeneration();
    initPriceReduction();
    initCharCounters();
});
