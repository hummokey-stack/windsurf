<?php
require_once 'includes/functions.php';
$slug = trim($_GET['slug'] ?? '');
if (!$slug) redirect('blog.php');

$article = getBlogArticleBySlug($slug);
if (!$article) { header('HTTP/1.0 404 Not Found'); redirect('blog.php'); }

$pageTitle = $article['titre'];
$pageDesc  = $article['extrait'] ?? substr(strip_tags($article['contenu']), 0, 160);

// Articles récents
$recents = getDerniersBlogArticles(3);

require_once 'includes/header.php';
?>

<div class="page-hero" style="padding:50px 0 30px;">
  <div class="container">
    <nav class="breadcrumb">
      <a href="index.php">Accueil</a><span>›</span>
      <a href="blog.php">Blog</a><span>›</span>
      <?php if ($article['categorie']): ?>
        <a href="blog.php?cat=<?= urlencode($article['categorie']) ?>"><?= h($article['categorie']) ?></a><span>›</span>
      <?php endif; ?>
      <span><?= h(substr($article['titre'], 0, 40)) ?>…</span>
    </nav>
    <?php if ($article['categorie']): ?>
      <span class="hero-tag" style="margin-bottom:14px;"><?= h($article['categorie']) ?></span>
    <?php endif; ?>
    <h1 style="font-size:clamp(1.8rem,4vw,3rem);max-width:800px;"><?= h($article['titre']) ?></h1>
    <div class="article-meta">
      <span><i class="fas fa-calendar" style="margin-right:4px;"></i><?= formatDate($article['date_creation']) ?></span>
      <span><i class="fas fa-tag" style="margin-right:4px;"></i><?= h($article['categorie'] ?? 'Blog') ?></span>
    </div>
  </div>
</div>

<div class="container" style="padding:40px 0 60px;">
  <div style="display:grid;grid-template-columns:1fr 300px;gap:50px;align-items:start;">

    <!-- Article -->
    <article>
      <?php if ($article['image'] && file_exists($article['image'])): ?>
        <img src="<?= h($article['image']) ?>" alt="<?= h($article['titre']) ?>"
          style="width:100%;max-height:420px;object-fit:cover;border-radius:12px;margin-bottom:30px;">
      <?php endif; ?>

      <div class="article-content">
        <?= $article['contenu'] ?>
      </div>

      <!-- Partage réseaux sociaux -->
      <div style="margin-top:40px;padding-top:24px;border-top:1px solid var(--gray-light);">
        <p style="font-weight:700;margin-bottom:12px;font-family:Rajdhani,sans-serif;">Partager cet article :</p>
        <div style="display:flex;gap:10px;">
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . ($_SERVER['HTTP_HOST'] ?? '') . $_SERVER['REQUEST_URI']) ?>"
             target="_blank" rel="noopener"
             class="btn btn-primary btn-sm"><i class="fab fa-facebook-f"></i> Facebook</a>
          <a href="https://twitter.com/intent/tweet?url=<?= urlencode('https://' . ($_SERVER['HTTP_HOST'] ?? '') . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($article['titre']) ?>"
             target="_blank" rel="noopener"
             class="btn btn-outline btn-sm"><i class="fab fa-x-twitter"></i> X / Twitter</a>
        </div>
      </div>

      <!-- Navigation entre articles -->
      <div style="margin-top:32px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <a href="blog.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Tous les articles</a>
      </div>
    </article>

    <!-- Sidebar -->
    <aside>
      <!-- Articles récents -->
      <div style="background:var(--light);border-radius:12px;padding:22px;">
        <h3 style="font-family:Rajdhani,sans-serif;font-size:1.1rem;margin-bottom:16px;">Articles récents</h3>
        <?php foreach ($recents as $r): ?>
          <?php if ($r['slug'] !== $article['slug']): ?>
          <div style="margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid #ddd;display:flex;gap:12px;">
            <img src="<?= $r['image'] && file_exists($r['image']) ? h($r['image']) : 'assets/images/placeholder.jpg' ?>"
              style="width:60px;height:60px;object-fit:cover;border-radius:8px;flex-shrink:0;">
            <div>
              <a href="article.php?slug=<?= h($r['slug']) ?>"
                style="font-size:0.88rem;font-weight:700;color:var(--dark);line-height:1.3;display:block;">
                <?= h(substr($r['titre'], 0, 55)) ?>
              </a>
              <span style="font-size:0.76rem;color:#aaa;"><?= formatDate($r['date_creation']) ?></span>
            </div>
          </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <!-- CTA produits -->
      <div style="background:linear-gradient(135deg,var(--primary),var(--secondary));border-radius:12px;padding:24px;margin-top:20px;color:white;text-align:center;">
        <div style="font-size:2rem;margin-bottom:10px;">🏄</div>
        <h3 style="font-family:Rajdhani,sans-serif;font-size:1.2rem;margin-bottom:10px;">Équipez-vous !</h3>
        <p style="font-size:0.85rem;opacity:0.9;margin-bottom:16px;">Découvrez notre sélection de matériel de wingsurf.</p>
        <a href="categorie.php?slug=wing" class="btn btn-white btn-sm">Voir les produits</a>
      </div>
    </aside>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
