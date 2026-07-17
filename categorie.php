<?php
/**
 * Page catégorie — GlisseShop
 * categorie.php
 */
require_once 'includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) { redirect('index.php'); }

$categorie = getCategorieBySlug($slug);
if (!$categorie) { header('HTTP/1.0 404 Not Found'); redirect('index.php'); }

// Options de filtrage
$options = [];
if (!empty($_GET['marque']))   $options['marque']   = $_GET['marque'];
if (!empty($_GET['prix_min'])) $options['prix_min'] = (float)$_GET['prix_min'];
if (!empty($_GET['prix_max'])) $options['prix_max'] = (float)$_GET['prix_max'];
if (!empty($_GET['niveau']))   $options['niveau']   = $_GET['niveau'];
if (!empty($_GET['tri']))      $options['tri']      = $_GET['tri'];

$produits    = getProduitsByCategorie((int)$categorie['id'], $options);
$sousCategories = getSousCategories((int)$categorie['id']);
$marques     = getMarquesParCategorie((int)$categorie['id']);

$pageTitle = $categorie['nom'];
$pageDesc  = $categorie['description'] ?? '';

require_once 'includes/header.php';
?>

<!-- Page hero -->
<div class="page-hero">
  <div class="container">
    <?= breadcrumbCategorie($categorie) ?>
    <h1><?= h($categorie['nom']) ?></h1>
    <?php if ($categorie['description']): ?>
      <p style="color:rgba(255,255,255,0.75);margin-top:10px;max-width:600px;"><?= h($categorie['description']) ?></p>
    <?php endif; ?>
  </div>
</div>

<div class="container" style="padding-top:30px;padding-bottom:50px;">
  <div class="category-layout">

    <!-- Sidebar filtres -->
    <aside class="sidebar">

      <?php if (!empty($sousCategories)): ?>
      <div class="filter-block">
        <h3>Sous-catégories</h3>
        <?php foreach ($sousCategories as $sc): ?>
          <label>
            <a href="categorie.php?slug=<?= h($sc['slug']) ?>" style="color:var(--dark);">
              <?= h($sc['nom']) ?>
            </a>
          </label>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <form id="filterForm" method="get">
        <input type="hidden" name="slug" value="<?= h($slug) ?>">
        <input type="hidden" name="tri" id="triHidden" value="<?= h($_GET['tri'] ?? '') ?>">

        <?php if (!empty($marques)): ?>
        <div class="filter-block">
          <h3>Marque</h3>
          <?php foreach ($marques as $marque): ?>
            <label>
              <input type="radio" name="marque" value="<?= h($marque) ?>"
                <?= ($_GET['marque'] ?? '') === $marque ? 'checked' : '' ?>>
              <?= h($marque) ?>
            </label>
          <?php endforeach; ?>
          <?php if (!empty($_GET['marque'])): ?>
            <label><input type="radio" name="marque" value=""> Toutes les marques</label>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="filter-block">
          <h3>Prix</h3>
          <div class="price-range-wrap">
            <input type="range" name="prix_max" min="0" max="3000" step="50"
              value="<?= (int)($_GET['prix_max'] ?? 3000) ?>" id="priceRange"
              oninput="document.getElementById('priceVal').textContent=this.value">
            <div class="price-range-labels">
              <span>0 €</span>
              <span id="priceVal"><?= (int)($_GET['prix_max'] ?? 3000) ?> €</span>
            </div>
          </div>
        </div>

        <div class="filter-block">
          <h3>Niveau</h3>
          <?php $niveaux = ['debutant' => 'Débutant', 'intermediaire' => 'Intermédiaire', 'expert' => 'Expert', 'tous' => 'Tous niveaux']; ?>
          <?php foreach ($niveaux as $nk => $nl): ?>
            <label>
              <input type="radio" name="niveau" value="<?= $nk ?>"
                <?= ($_GET['niveau'] ?? '') === $nk ? 'checked' : '' ?>>
              <?= $nl ?>
            </label>
          <?php endforeach; ?>
          <?php if (!empty($_GET['niveau'])): ?>
            <label><input type="radio" name="niveau" value=""> Tous niveaux</label>
          <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
          <i class="fas fa-filter"></i> Appliquer les filtres
        </button>
        <?php if (!empty(array_filter([$options]))): ?>
          <a href="categorie.php?slug=<?= h($slug) ?>" class="btn btn-outline" style="width:100%;justify-content:center;margin-top:10px;">
            <i class="fas fa-times"></i> Réinitialiser
          </a>
        <?php endif; ?>
      </form>

    </aside>

    <!-- Produits -->
    <div>
      <!-- Toolbar -->
      <div class="products-toolbar">
        <span class="count">
          <strong><?= count($produits) ?></strong> produit(s) trouvé(s)
        </span>
        <div style="display:flex;align-items:center;gap:10px;">
          <label style="font-size:0.88rem;color:#666;">Trier par :</label>
          <select onchange="window.location.href='categorie.php?slug=<?= h($slug) ?>&tri='+this.value+'<?= !empty($_GET['marque']) ? '&marque='.urlencode($_GET['marque']) : '' ?><?= !empty($_GET['niveau']) ? '&niveau='.urlencode($_GET['niveau']) : '' ?>'">
            <option value="nouveaute" <?= ($_GET['tri'] ?? '') === 'nouveaute' ? 'selected' : '' ?>>Nouveautés</option>
            <option value="prix_asc"  <?= ($_GET['tri'] ?? '') === 'prix_asc'  ? 'selected' : '' ?>>Prix croissant</option>
            <option value="prix_desc" <?= ($_GET['tri'] ?? '') === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
            <option value="promo"     <?= ($_GET['tri'] ?? '') === 'promo'     ? 'selected' : '' ?>>Promotions</option>
          </select>
        </div>
      </div>

      <?php if (empty($produits)): ?>
        <div class="cart-empty" style="padding:60px 20px;">
          <div class="icon">🔍</div>
          <h2>Aucun produit trouvé</h2>
          <p>Essayez de modifier vos filtres.</p>
          <a href="categorie.php?slug=<?= h($slug) ?>" class="btn btn-primary mt-3">Réinitialiser les filtres</a>
        </div>
      <?php else: ?>
        <div class="products-grid">
          <?php foreach ($produits as $p): ?>
            <?php include 'includes/product-card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
