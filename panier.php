<?php
/**
 * Page panier — GlisseShop
 * panier.php
 */
require_once 'includes/functions.php';
$pageTitle = 'Mon panier';
$settings = getSettings();
$livraisonSeuil = $settings['livraison_gratuite_montant'] ?? 100;
require_once 'includes/header.php';
?>

<div class="page-hero" style="padding:40px 0;">
  <div class="container">
    <h1>🛒 Mon panier</h1>
    <nav class="breadcrumb"><a href="index.php">Accueil</a><span>›</span><span>Panier</span></nav>
  </div>
</div>

<div class="container" style="padding:40px 0 60px;">

  <!-- Panier vide -->
  <div id="cartEmpty" class="cart-empty" style="display:none;">
    <div class="icon">🛒</div>
    <h2>Votre panier est vide</h2>
    <p>Découvrez notre sélection de matériel de glisse et ajoutez des articles à votre panier.</p>
    <a href="index.php" class="btn btn-primary mt-3">Continuer mes achats</a>
  </div>

  <!-- Panier avec articles -->
  <div id="cartFull" style="display:none;">
    <div class="cart-layout">

      <!-- Articles -->
      <div>
        <table class="cart-table" id="cartTable">
          <thead>
            <tr>
              <th colspan="2">Produit</th>
              <th>Prix unitaire</th>
              <th>Quantité</th>
              <th>Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="cartBody">
            <!-- Rempli par JS -->
          </tbody>
        </table>
        <div style="margin-top:16px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;">
          <a href="index.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Continuer mes achats
          </a>
          <button class="btn btn-outline" onclick="clearCart()" style="color:var(--danger);border-color:var(--danger);">
            <i class="fas fa-trash"></i> Vider le panier
          </button>
        </div>
      </div>

      <!-- Récapitulatif -->
      <div class="cart-summary">
        <h3>Récapitulatif</h3>
        <div class="summary-row">
          <span>Sous-total</span>
          <span id="summarySubtotal">0,00 €</span>
        </div>
        <div class="summary-row">
          <span>Livraison</span>
          <span id="summaryLivraison">—</span>
        </div>
        <div id="livraisonMsg" class="livraison-gratuite-msg" style="display:none;">
          <i class="fas fa-truck"></i> <strong>Livraison gratuite</strong> appliquée !
        </div>
        <div id="livraisonProgress" style="display:none;font-size:0.85rem;color:#666;margin:8px 0;"></div>
        <div class="summary-row total">
          <span>Total</span>
          <span id="summaryTotal">0,00 €</span>
        </div>
        <a href="commande.php" class="btn btn-accent btn-lg" style="width:100%;justify-content:center;margin-top:16px;">
          <i class="fas fa-lock"></i> Commander
        </a>
        <div style="text-align:center;margin-top:12px;font-size:0.8rem;color:#999;">
          <i class="fas fa-shield-alt" style="margin-right:4px;"></i>Paiement 100% sécurisé
        </div>
      </div>

    </div>
  </div>

</div>

<script>
const LIVRAISON_SEUIL = <?= (float)$livraisonSeuil ?>;
const LIVRAISON_FRAIS = 9.90;

function formatEur(n) {
    return n.toFixed(2).replace('.', ',') + ' €';
}

function renderCart() {
    const cart = JSON.parse(localStorage.getItem('glisse_cart') || '[]');
    const emptyEl = document.getElementById('cartEmpty');
    const fullEl  = document.getElementById('cartFull');
    const tbody   = document.getElementById('cartBody');

    if (cart.length === 0) {
        emptyEl.style.display = 'block';
        fullEl.style.display  = 'none';
        updateCartBadge();
        return;
    }

    emptyEl.style.display = 'none';
    fullEl.style.display  = 'block';

    let subtotal = 0;
    tbody.innerHTML = '';

    cart.forEach((item, idx) => {
        const lineTotal = item.prix * item.quantite;
        subtotal += lineTotal;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><img src="${item.image}" class="cart-item-img" alt="${item.nom}" onerror="this.src='assets/images/placeholder.jpg'"></td>
            <td>
                <div class="cart-item-name">${item.nom}</div>
                ${item.variante ? `<div class="cart-item-variante">${item.variante}</div>` : ''}
            </td>
            <td><strong>${formatEur(item.prix)}</strong></td>
            <td>
                <input type="number" class="cart-qty-input" value="${item.quantite}" min="1" max="99"
                    onchange="updateQty(${idx}, this.value)">
            </td>
            <td><strong>${formatEur(lineTotal)}</strong></td>
            <td>
                <button class="cart-remove" onclick="removeFromCart(${idx})" title="Supprimer">
                    <i class="fas fa-times-circle"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);
    });

    // Livraison
    let livraison = subtotal >= LIVRAISON_SEUIL ? 0 : LIVRAISON_FRAIS;
    const total = subtotal + livraison;

    document.getElementById('summarySubtotal').textContent = formatEur(subtotal);
    document.getElementById('summaryLivraison').textContent = livraison === 0 ? 'Gratuite' : formatEur(livraison);
    document.getElementById('summaryTotal').textContent = formatEur(total);

    const livrMsg = document.getElementById('livraisonMsg');
    const livrProg = document.getElementById('livraisonProgress');
    if (livraison === 0) {
        livrMsg.style.display = 'block';
        livrProg.style.display = 'none';
    } else {
        livrMsg.style.display = 'none';
        livrProg.style.display = 'block';
        const manque = LIVRAISON_SEUIL - subtotal;
        livrProg.innerHTML = `<i class="fas fa-info-circle" style="color:var(--primary);margin-right:4px;"></i>Plus que <strong>${formatEur(manque)}</strong> pour la livraison gratuite !`;
    }

    updateCartBadge();
}

function updateQty(idx, qty) {
    qty = Math.max(1, parseInt(qty) || 1);
    let cart = JSON.parse(localStorage.getItem('glisse_cart') || '[]');
    if (cart[idx]) { cart[idx].quantite = qty; }
    localStorage.setItem('glisse_cart', JSON.stringify(cart));
    renderCart();
}

function removeFromCart(idx) {
    let cart = JSON.parse(localStorage.getItem('glisse_cart') || '[]');
    cart.splice(idx, 1);
    localStorage.setItem('glisse_cart', JSON.stringify(cart));
    renderCart();
    showToast('Article retiré du panier', 'error');
}

function clearCart() {
    if (confirm('Vider entièrement le panier ?')) {
        localStorage.setItem('glisse_cart', '[]');
        renderCart();
    }
}

document.addEventListener('DOMContentLoaded', renderCart);
</script>

<?php require_once 'includes/footer.php'; ?>
