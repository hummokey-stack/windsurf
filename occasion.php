<?php
require_once 'includes/functions.php';
$pageTitle = 'Matériel d\'occasion';
$occasions = getProduitsOccasion(24);
require_once 'includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb"><a href="index.php">Accueil</a><span>›</span><span>Occasion</span></nav>
    <h1>♻️ Matériel d'occasion</h1>
    <p style="color:rgba(255,255,255,0.75);margin-top:8px;">Matériel contrôlé et certifié par nos experts</p>
  </div>
</div>

<!-- Garanties occasion -->
<div class="bandeau-livraison" style="background:linear-gradient(135deg,#FF6B35,#ff9a6c);">
  <span>♻️ Chaque article contrôlé et noté</span>
  <span>•</span>
  <span>💯 Descriptions honnêtes</span>
  <span>•</span>
  <span>🔒 Même garantie retours 30j</span>
</div>

<div class="container" style="padding:40px 0 60px;">
  <?php if (empty($occasions)): ?>
    <div class="cart-empty">
      <div class="icon">♻️</div>
      <h2>Aucun produit d'occasion disponible</h2>
      <p>Nous renouvelons régulièrement notre stock d'occasion. Revenez bientôt !</p>
      <a href="index.php" class="btn btn-primary mt-3">Voir les produits neufs</a>
    </div>
  <?php else: ?>
    <div style="background:rgba(255,107,53,0.08);border-radius:10px;padding:20px;margin-bottom:30px;display:flex;gap:20px;flex-wrap:wrap;">
      <div style="flex:1;min-width:200px;">
        <strong>🔍 Notre contrôle qualité</strong><br>
        <span style="color:#666;font-size:0.88rem;">Chaque article est inspecté, nettoyé et décrit avec honnêteté (état, rayures, défauts éventuels).</span>
      </div>
      <div style="flex:1;min-width:200px;">
        <strong>💰 Économies garanties</strong><br>
        <span style="color:#666;font-size:0.88rem;">Jusqu'à -60% par rapport au prix neuf sur du matériel de qualité.</span>
      </div>
    </div>
    <p style="color:#666;margin-bottom:30px;"><strong><?= count($occasions) ?></strong> produit(s) d'occasion disponible(s)</p>
    <div class="products-grid">
      <?php foreach ($occasions as $p): ?>
        <?php include 'includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
