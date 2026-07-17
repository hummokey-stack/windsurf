<?php
require_once 'includes/functions.php';
$pageTitle = 'Blog & Conseils';

$pdo = getDB();
$catFil = trim($_GET['cat'] ?? '');
$where  = ["actif=1"];
$params = [];
if ($catFil) {
    $where[] = "categorie=:cat";
    $params[':cat'] = $catFil;
}
$sql = "SELECT * FROM blog WHERE " . implode(' AND ', $where) . " ORDER BY date_creation DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Catégories blog disponibles
$blogCats = $pdo->query("SELECT DISTINCT categorie FROM blog WHERE actif=1 AND categorie IS NOT NULL AND categorie!='' ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb"><a href="index.php">Accueil</a><span>›</span><span>Blog</span></nav>
    <h1>📰 Blog & Conseils</h1>
    <p style="color:rgba(255,255,255,0.75);margin-top:8px;">Guides, spots, entretien et actus sports de glisse</p>
  </div>
</div>

<div class="container" style="padding:40px 0 60px;">

  <!-- Filtres catégories -->
  <?php if (!empty($blogCats)): ?>
  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:30px;">
    <a href="blog.php" class="badge <?= !$catFil ? 'badge-nouveau' : '' ?>" style="padding:8px 18px;font-size:0.88rem;<?= !$catFil ? '' : 'background:var(--gray-light);color:var(--dark);' ?>">
      Tous
    </a>
    <?php foreach ($blogCats as $bc): ?>
      <a href="blog.php?cat=<?= urlencode($bc) ?>"
         class="badge <?= $catFil === $bc ? 'badge-nouveau' : '' ?>"
         style="padding:8px 18px;font-size:0.88rem;<?= $catFil === $bc ? '' : 'background:var(--gray-light);color:var(--dark);' ?>">
        <?= h($bc) ?>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (empty($articles)): ?>
    <div class="cart-empty">
      <div class="icon">📰</div>
      <h2>Aucun article pour le moment</h2>
      <p>Revenez bientôt pour nos prochains articles !</p>
    </div>
  <?php else: ?>
    <div class="blog-grid">
      <?php foreach ($articles as $art): ?>
        <article class="blog-card">
          <div class="blog-card-img">
            <a href="article.php?slug=<?= h($art['slug']) ?>">
              <img src="<?= $art['image'] && file_exists($art['image']) ? h($art['image']) : 'assets/images/placeholder.jpg' ?>"
                   alt="<?= h($art['titre']) ?>" loading="lazy">
            </a>
          </div>
          <div class="blog-card-body">
            <?php if ($art['categorie']): ?>
              <span class="blog-cat-tag"><?= h($art['categorie']) ?></span>
            <?php endif; ?>
            <h2 class="blog-card-title">
              <a href="article.php?slug=<?= h($art['slug']) ?>"><?= h($art['titre']) ?></a>
            </h2>
            <p class="blog-card-excerpt">
              <?= h(substr($art['extrait'] ?: strip_tags($art['contenu']), 0, 130)) ?>…
            </p>
            <div class="blog-card-meta">
              <span><i class="fas fa-calendar" style="margin-right:4px;"></i><?= formatDate($art['date_creation']) ?></span>
              <a href="article.php?slug=<?= h($art['slug']) ?>" style="color:var(--primary);font-weight:600;font-size:0.82rem;">
                Lire l'article <i class="fas fa-arrow-right"></i>
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
