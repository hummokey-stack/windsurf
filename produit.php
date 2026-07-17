<?php
/**
 * Fiche produit — GlisseShop
 * produit.php
 */
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { redirect('index.php'); }

$produit = getProduitById($id);
if (!$produit) { header('HTTP/1.0 404 Not Found'); redirect('index.php'); }

$variantes = [];
if (!empty($produit['variantes'])) {
    $v = json_decode($produit['variantes'], true);
    if (is_array($v)) $variantes = $v;
}

$imagesSecondaires = [];
if (!empty($produit['images_secondaires'])) {
    $imgs = json_decode($produit['images_secondaires'], true);
    if (is_array($imgs)) $imagesSecondaires = $imgs;
}

$similaires = getProduitsSimilaires($id, (int)$produit['categorie_id'], 4);
$imgPrincipale = imageProduit($produit['image_principale']);
$reduc = ($produit['ancien_prix'] && $produit['ancien_prix'] > $produit['prix']) ?
         calculReduction((float)$produit['ancien_prix'], (float)$produit['prix']) : 0;

$pageTitle = $produit['nom'];
$pageDesc  = $produit['description_courte'] ?? '';

require_once 'includes/header.php';
?>

<div style="background:var(--light);padding:6px 0;">
  <div class="container">
    <nav class="breadcrumb">
      <a href="index.php">Accueil</a>
      <?php if ($produit['categorie_slug']): ?>
        <span>›</span>
        <a href="categorie.php?slug=<?= h($produit['categorie_slug']) ?>"><?= h($produit['categorie_nom']) ?></a>
      <?php endif; ?>
      <span>›</span>
      <span><?= h($produit['nom']) ?></span>
    </nav>
  </div>
</div>

<div class="container">
  <div class="product-layout">

    <!-- Galerie -->
    <div class="product-gallery">
      <img id="mainImg" class="main-img"
        src="<?= h($imgPrincipale) ?>" alt="<?= h($produit['nom']) ?>">
      <?php if (!empty($imagesSecondaires)): ?>
        <div class="product-thumbnails">
          <img src="<?= h($imgPrincipale) ?>" class="active"
            onclick="switchMainImage('<?= h($imgPrincipale) ?>', this)" alt="<?= h($produit['nom']) ?>">
          <?php foreach ($imagesSecondaires as $img): ?>
            <?php if (file_exists($img)): ?>
              <img src="<?= h($img) ?>" onclick="switchMainImage('<?= h($img) ?>', this)" alt="<?= h($produit['nom']) ?>">
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Infos produit -->
    <div class="product-info">
      <!-- Badges -->
      <div class="product-meta">
        <?php if ($produit['marque']): ?>
          <span class="product-brand-tag"><?= h($produit['marque']) ?></span>
        <?php endif; ?>
        <?php if ($produit['est_nouveaute']): ?><span class="badge badge-nouveau">Nouveau</span><?php endif; ?>
        <?php if ($produit['est_promo']): ?><span class="badge badge-promo">Promo -<?= $reduc ?>%</span><?php endif; ?>
        <?php if ($produit['est_occasion']): ?><span class="badge badge-occasion">Occasion</span><?php endif; ?>
      </div>

      <h1><?= h($produit['nom']) ?></h1>

      <!-- Prix -->
      <div class="product-price-block">
        <span class="price-main"><?= formatPrix((float)$produit['prix']) ?></span>
        <?php if ($produit['ancien_prix'] && $produit['ancien_prix'] > $produit['prix']): ?>
          <span class="price-old"><?= formatPrix((float)$produit['ancien_prix']) ?></span>
          <?php if ($reduc > 0): ?><span class="discount-tag">-<?= $reduc ?>%</span><?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- Description courte -->
      <?php if ($produit['description_courte']): ?>
        <p class="product-desc-short"><?= h($produit['description_courte']) ?></p>
      <?php endif; ?>

      <!-- Variantes -->
      <?php if (!empty($variantes)): ?>
        <div class="product-variantes">
          <label>Taille / Volume / Surface :</label>
          <div class="variante-options" id="varianteOptions">
            <?php foreach ($variantes as $v): ?>
              <button type="button" class="variante-btn" onclick="selectVariante(this, '<?= h($v) ?>')"><?= h($v) ?></button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Stock -->
      <div class="product-stock-indicator">
        <?php if ($produit['stock'] > 5): ?>
          <div class="stock-dot ok"></div>
          <span class="stock-ok">En stock (<?= (int)$produit['stock'] ?> disponibles)</span>
        <?php elseif ($produit['stock'] > 0): ?>
          <div class="stock-dot low"></div>
          <span class="stock-low">Dernières pièces (<?= (int)$produit['stock'] ?> restant<?= $produit['stock'] > 1 ? 's' : '' ?>)</span>
        <?php else: ?>
          <div class="stock-dot none"></div>
          <span class="stock-none">Rupture de stock</span>
        <?php endif; ?>
      </div>

      <!-- Niveau -->
      <?php if ($produit['niveau'] && $produit['niveau'] !== 'tous'): ?>
        <div style="margin-bottom:16px;font-size:0.9rem;">
          <strong>Niveau :</strong> <?= labelNiveau($produit['niveau']) ?>
        </div>
      <?php endif; ?>

      <!-- Actions -->
      <div class="product-actions">
        <button class="btn btn-accent btn-add-cart"
          id="btnAddCart"
          <?= $produit['stock'] == 0 ? 'disabled' : '' ?>
          onclick="addToCartFromPage()">
          <i class="fas fa-cart-plus"></i>
          <?= $produit['stock'] == 0 ? 'Rupture de stock' : 'Ajouter au panier' ?>
        </button>
      </div>

      <!-- Livraison rapide -->
      <?php
      $settings = getSettings();
      $seuil = $settings['livraison_gratuite_montant'] ?? 100;
      ?>
      <?php if ($produit['prix'] >= $seuil): ?>
        <div class="livraison-gratuite-msg">
          <i class="fas fa-truck"></i> <strong>Livraison gratuite</strong> pour ce produit !
        </div>
      <?php else: ?>
        <div style="font-size:0.85rem;color:#666;margin-bottom:16px;">
          <i class="fas fa-truck" style="color:var(--primary);margin-right:6px;"></i>
          Livraison gratuite dès <?= formatPrix($seuil) ?> d'achat.
          Plus que <?= formatPrix($seuil - $produit['prix']) ?> pour en bénéficier !
        </div>
      <?php endif; ?>

    </div><!-- /product-info -->
  </div><!-- /product-layout -->

  <!-- Onglets description -->
  <div class="product-tabs">
    <div class="tabs-nav">
      <button class="tab-btn active" onclick="switchTab(this, 'tabDesc')">Description</button>
      <button class="tab-btn" onclick="switchTab(this, 'tabCaract')">Caractéristiques</button>
      <button class="tab-btn" onclick="switchTab(this, 'tabLivraison')">Livraison & Retours</button>
    </div>
    <div id="tabDesc" class="tab-content active article-content">
      <?php if ($produit['description']): ?>
        <?= $produit['description'] ?>
      <?php else: ?>
        <p>Description détaillée non disponible pour le moment.</p>
      <?php endif; ?>
    </div>
    <div id="tabCaract" class="tab-content">
      <ul>
        <?php if ($produit['marque']): ?><li><strong>Marque :</strong> <?= h($produit['marque']) ?></li><?php endif; ?>
        <?php if ($produit['niveau']): ?><li><strong>Niveau :</strong> <?= labelNiveau($produit['niveau']) ?></li><?php endif; ?>
        <?php if (!empty($variantes)): ?><li><strong>Tailles disponibles :</strong> <?= h(implode(', ', $variantes)) ?></li><?php endif; ?>
        <?php if ($produit['est_occasion']): ?><li><strong>État :</strong> Occasion (reconditionné)</li><?php endif; ?>
      </ul>
    </div>
    <div id="tabLivraison" class="tab-content">
      <h3>Livraison</h3>
      <ul>
        <li>Livraison standard en 3-5 jours ouvrés</li>
        <li>Livraison express 24h disponible (supplément)</li>
        <li>Livraison gratuite dès <?= formatPrix($seuil) ?> d'achat</li>
        <li>Retrait en magasin gratuit (Nice)</li>
      </ul>
      <h3>Retours</h3>
      <ul>
        <li>30 jours pour retourner votre commande</li>
        <li>Article en état d'origine et non utilisé</li>
        <li>Remboursement sous 5 jours ouvrés</li>
      </ul>
    </div>
  </div>

  <!-- Produits similaires -->
  <?php if (!empty($similaires)): ?>
    <div style="margin-top:60px;margin-bottom:50px;">
      <h2 style="font-size:1.8rem;margin-bottom:30px;">Produits similaires</h2>
      <div class="products-grid">
        <?php foreach ($similaires as $p): ?>
          <?php include 'includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
const _produit = {
    id: <?= (int)$produit['id'] ?>,
    nom: <?= json_encode($produit['nom']) ?>,
    prix: <?= (float)$produit['prix'] ?>,
    image: <?= json_encode($imgPrincipale) ?>
};
let _varianteSelectionnee = '';

function switchMainImage(src, el) {
    document.getElementById('mainImg').src = src;
    document.querySelectorAll('.product-thumbnails img').forEach(i => i.classList.remove('active'));
    el.classList.add('active');
}

function selectVariante(btn, val) {
    document.querySelectorAll('.variante-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    _varianteSelectionnee = val;
}

function addToCartFromPage() {
    addToCart(_produit.id, _produit.nom, _produit.prix, _produit.image, _varianteSelectionnee);
}

function switchTab(btn, tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}
</script>

<?php require_once 'includes/footer.php'; ?>
