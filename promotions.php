<?php
require_once 'includes/functions.php';
$pageTitle = 'Promotions';
$promos = getProduitsPromo(20);
require_once 'includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb"><a href="index.php">Accueil</a><span>›</span><span>Promotions</span></nav>
    <h1>🔥 Promotions</h1>
    <p style="color:rgba(255,255,255,0.75);margin-top:8px;">Les meilleures affaires du moment</p>
  </div>
</div>

<div class="container" style="padding:40px 0 60px;">
  <?php if (empty($promos)): ?>
    <div class="cart-empty">
      <div class="icon">🔥</div>
      <h2>Aucune promotion en cours</h2>
      <p>Revenez bientôt, de nouvelles promotions arrivent régulièrement !</p>
      <a href="index.php" class="btn btn-primary mt-3">Retour à l'accueil</a>
    </div>
  <?php else: ?>
    <p style="color:#666;margin-bottom:30px;"><strong><?= count($promos) ?></strong> produit(s) en promotion</p>
    <div class="products-grid">
      <?php foreach ($promos as $p): ?>
        <?php include 'includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
