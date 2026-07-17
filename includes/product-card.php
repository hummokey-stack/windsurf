<?php
/**
 * Carte produit réutilisable — GlisseShop
 * includes/product-card.php
 * Variable requise : $p (array produit)
 */
$_imgSrc = imageProduit($p['image_principale'] ?? null);
$_reduc  = ($p['ancien_prix'] && $p['ancien_prix'] > $p['prix']) ? calculReduction((float)$p['ancien_prix'], (float)$p['prix']) : 0;
?>
<div class="product-card">
  <div class="product-card-img">
    <a href="produit.php?id=<?= (int)$p['id'] ?>">
      <img src="<?= h($_imgSrc) ?>" alt="<?= h($p['nom']) ?>" loading="lazy">
    </a>
    <div class="product-card-badges">
      <?php if ($p['est_nouveaute'] ?? 0): ?><span class="badge badge-nouveau">Nouveau</span><?php endif; ?>
      <?php if ($p['est_promo'] ?? 0): ?><span class="badge badge-promo">-<?= $_reduc ?>%</span><?php endif; ?>
      <?php if ($p['est_occasion'] ?? 0): ?><span class="badge badge-occasion">Occasion</span><?php endif; ?>
      <?php if (($p['stock'] ?? 0) == 0): ?><span class="badge badge-rupture">Rupture</span><?php endif; ?>
    </div>
    <div class="product-card-actions">
      <button class="btn-icon"
        onclick='addToCart(<?= (int)$p["id"] ?>, <?= htmlspecialchars(json_encode($p["nom"]), ENT_QUOTES) ?>, <?= (float)$p["prix"] ?>, <?= htmlspecialchars(json_encode($_imgSrc), ENT_QUOTES) ?>, "")'
        title="Ajouter au panier"
        <?= ($p['stock'] ?? 0) == 0 ? 'disabled' : '' ?>>
        <i class="fas fa-shopping-cart"></i>
      </button>
    </div>
  </div>
  <div class="product-card-body">
    <?php if (!empty($p['marque'])): ?>
      <div class="product-brand"><?= h($p['marque']) ?></div>
    <?php endif; ?>
    <h3 class="product-name">
      <a href="produit.php?id=<?= (int)$p['id'] ?>"><?= h($p['nom']) ?></a>
    </h3>
    <div class="product-price">
      <span class="price-new"><?= formatPrix((float)$p['prix']) ?></span>
      <?php if ($p['ancien_prix'] && $p['ancien_prix'] > $p['prix']): ?>
        <span class="price-old"><?= formatPrix((float)$p['ancien_prix']) ?></span>
        <?php if ($_reduc > 0): ?><span class="price-discount">-<?= $_reduc ?>%</span><?php endif; ?>
      <?php endif; ?>
    </div>
    <?php if (($p['stock'] ?? 0) > 5): ?>
      <div class="product-stock stock-ok"><i class="fas fa-circle" style="font-size:0.55rem;margin-right:4px;"></i>En stock</div>
    <?php elseif (($p['stock'] ?? 0) > 0): ?>
      <div class="product-stock stock-low"><i class="fas fa-circle" style="font-size:0.55rem;margin-right:4px;"></i>Dernières pièces</div>
    <?php else: ?>
      <div class="product-stock stock-none"><i class="fas fa-times-circle" style="font-size:0.8rem;margin-right:4px;"></i>Rupture de stock</div>
    <?php endif; ?>
    <button class="btn btn-primary btn-add"
      onclick='addToCart(<?= (int)$p["id"] ?>, <?= htmlspecialchars(json_encode($p["nom"]), ENT_QUOTES) ?>, <?= (float)$p["prix"] ?>, <?= htmlspecialchars(json_encode($_imgSrc), ENT_QUOTES) ?>, "")'
      <?= ($p['stock'] ?? 0) == 0 ? 'disabled' : '' ?>>
      <i class="fas fa-cart-plus"></i>
      <?= ($p['stock'] ?? 0) == 0 ? 'Rupture' : 'Ajouter au panier' ?>
    </button>
  </div>
</div>
