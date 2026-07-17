<?php
/**
 * En-tête site public — GlisseShop
 * includes/header.php
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';
$settings   = getSettings();
$categories = getCategoriesParentes();
$siteName   = h($settings['site_name'] ?? 'GlisseShop');
$slogan     = h($settings['slogan'] ?? 'Votre spécialiste wingsurf & sports de glisse');
$logo       = h($settings['logo'] ?? 'assets/images/logo.png');
$livraisonSeuil = $settings['livraison_gratuite_montant'] ?? 100;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' — ' . $siteName : $siteName . ' — ' . $slogan ?></title>
<meta name="description" content="<?= isset($pageDesc) ? h($pageDesc) : h($settings['meta_description'] ?? '') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= isset($baseUrl) ? $baseUrl : '' ?>assets/css/style.css">
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Barre top livraison -->
<div class="header-top">
  <span>🚚 Livraison gratuite dès <?= number_format($livraisonSeuil, 0, ',', ' ') ?> € d'achats</span>
  <span>•</span>
  <span>📦 Retours sous 30 jours</span>
  <span>•</span>
  <span>🔒 Paiement sécurisé</span>
</div>

<header id="header">
  <div class="container">
    <div class="header-main">

      <!-- Logo -->
      <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>index.php" class="logo">
        <?php if (!empty($settings['logo']) && file_exists(__DIR__ . '/../' . $settings['logo'])): ?>
          <img src="<?= isset($baseUrl) ? $baseUrl : '' ?><?= $logo ?>" alt="<?= $siteName ?>">
        <?php else: ?>
          <div class="logo-text">
            <div class="name"><?= $siteName ?></div>
            <div class="slogan"><?= $slogan ?></div>
          </div>
        <?php endif; ?>
      </a>

      <!-- Navigation principale -->
      <nav class="nav-main" id="navMain">
        <ul>
          <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>index.php">Accueil</a></li>
          <?php foreach ($categories as $cat): ?>
            <?php $subs = getSousCategories($cat['id']); ?>
            <li>
              <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>categorie.php?slug=<?= h($cat['slug']) ?>"><?= h($cat['nom']) ?></a>
              <?php if (!empty($subs)): ?>
                <ul class="dropdown-menu">
                  <?php foreach ($subs as $sub): ?>
                    <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>categorie.php?slug=<?= h($sub['slug']) ?>"><?= h($sub['nom']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
          <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>promotions.php">Promos 🔥</a></li>
          <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>occasion.php">Occasion</a></li>
        </ul>
      </nav>

      <!-- Actions -->
      <div class="header-actions">
        <button class="btn-icon" id="btnSearch" title="Rechercher">
          <i class="fas fa-search"></i>
        </button>
        <a href="<?= isset($baseUrl) ? $baseUrl : '' ?>panier.php" class="btn-icon" title="Panier">
          <i class="fas fa-shopping-cart"></i>
          <span class="badge-count" id="cartCount">0</span>
        </a>
        <button class="btn-hamburger" id="btnHamburger" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
      </div>

    </div>
  </div>
</header>

<!-- Menu mobile -->
<div class="nav-mobile-overlay" id="mobileOverlay"></div>
<nav class="nav-mobile" id="navMobile">
  <button class="nav-mobile-close" id="closeMobile">&times;</button>
  <ul>
    <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>index.php">🏠 Accueil</a></li>
    <?php foreach ($categories as $cat): ?>
      <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>categorie.php?slug=<?= h($cat['slug']) ?>"><?= h($cat['nom']) ?></a></li>
    <?php endforeach; ?>
    <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>promotions.php">🔥 Promotions</a></li>
    <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>occasion.php">♻️ Occasion</a></li>
    <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>blog.php">📰 Blog</a></li>
    <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>contact.php">📞 Contact</a></li>
  </ul>
</nav>

<!-- Overlay recherche -->
<div class="search-overlay" id="searchOverlay">
  <div class="search-box">
    <form action="<?= isset($baseUrl) ? $baseUrl : '' ?>categorie.php" method="get" role="search">
      <input type="text" name="q" id="searchInput" placeholder="Rechercher un produit, une marque..." autocomplete="off" autofocus>
      <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>
    <div class="search-results" id="searchResults"></div>
    <div style="text-align:right;margin-top:8px;">
      <button id="closeSearch" style="background:none;border:none;cursor:pointer;color:#999;font-size:0.85rem;">✕ Fermer</button>
    </div>
  </div>
</div>

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Bouton scroll top -->
<button class="scroll-top" id="scrollTop" title="Haut de page"><i class="fas fa-chevron-up"></i></button>

<main>
