<?php
/**
 * Dashboard Admin — GlisseShop
 * admin/index.php
 */
$adminTitle = 'Dashboard';
require_once '../includes/admin-header.php';

$pdo = getDB();

// Stats
$nbProduits     = $pdo->query("SELECT COUNT(*) FROM produits WHERE est_actif=1")->fetchColumn();
$nbCommandes    = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
$nbEnAttente    = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut='en_attente'")->fetchColumn();
$caMois         = $pdo->query("SELECT SUM(total) FROM commandes WHERE strftime('%Y-%m', date_commande)=strftime('%Y-%m','now') AND statut!='annulee'")->fetchColumn() ?? 0;
$nbBlog         = $pdo->query("SELECT COUNT(*) FROM blog WHERE actif=1")->fetchColumn();
$nbCategories   = $pdo->query("SELECT COUNT(*) FROM categories WHERE actif=1")->fetchColumn();

// Dernières commandes
$dernieresCommandes = $pdo->query("SELECT * FROM commandes ORDER BY date_commande DESC LIMIT 8")->fetchAll();

// Produits en rupture
$ruptures = $pdo->query("SELECT * FROM produits WHERE est_actif=1 AND stock=0 LIMIT 5")->fetchAll();
// Produits stock faible
$stockFaible = $pdo->query("SELECT * FROM produits WHERE est_actif=1 AND stock>0 AND stock<=3 LIMIT 5")->fetchAll();
?>

<!-- Stats principales -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-box"></i></div>
    <div>
      <div class="stat-num"><?= (int)$nbProduits ?></div>
      <div class="stat-label">Produits actifs</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-shopping-bag"></i></div>
    <div>
      <div class="stat-num"><?= (int)$nbCommandes ?></div>
      <div class="stat-label">Commandes totales</div>
      <?php if ($nbEnAttente > 0): ?>
        <div class="stat-change up"><i class="fas fa-clock"></i> <?= (int)$nbEnAttente ?> en attente</div>
      <?php endif; ?>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-euro-sign"></i></div>
    <div>
      <div class="stat-num"><?= number_format((float)$caMois, 0, ',', ' ') ?> €</div>
      <div class="stat-label">CA ce mois</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fas fa-newspaper"></i></div>
    <div>
      <div class="stat-num"><?= (int)$nbBlog ?></div>
      <div class="stat-label">Articles publiés</div>
    </div>
  </div>
</div>

<!-- Raccourcis -->
<div class="quick-links">
  <a href="produit-form.php" class="quick-link"><span class="icon">➕</span> Nouveau produit</a>
  <a href="commandes.php" class="quick-link"><span class="icon">📦</span> Gérer commandes <?php if ($nbEnAttente > 0): ?><span class="badge-admin badge-danger"><?= (int)$nbEnAttente ?></span><?php endif; ?></a>
  <a href="blog-form.php" class="quick-link"><span class="icon">✍️</span> Écrire un article</a>
  <a href="parametres.php" class="quick-link"><span class="icon">⚙️</span> Paramètres</a>
  <a href="categories.php" class="quick-link"><span class="icon">🏷️</span> Catégories</a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

  <!-- Dernières commandes -->
  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-shopping-bag" style="margin-right:8px;color:var(--admin-primary);"></i>Dernières commandes</h3>
      <a href="commandes.php" class="btn-admin btn-admin-outline btn-admin-sm">Tout voir</a>
    </div>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Réf.</th>
            <th>Client</th>
            <th>Total</th>
            <th>Statut</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($dernieresCommandes)): ?>
            <tr><td colspan="5" style="text-align:center;color:#aaa;padding:30px;">Aucune commande</td></tr>
          <?php else: ?>
            <?php foreach ($dernieresCommandes as $cmd): ?>
              <?php $s = labelStatut($cmd['statut']); ?>
              <tr>
                <td><strong><?= h($cmd['reference']) ?></strong></td>
                <td><?= h($cmd['client_nom']) ?></td>
                <td><strong><?= formatPrix((float)$cmd['total']) ?></strong></td>
                <td><span class="badge-admin <?= h($s['class']) ?>"><?= h($s['label']) ?></span></td>
                <td style="color:#999;font-size:0.8rem;"><?= formatDate($cmd['date_commande']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Alertes stock -->
  <div style="display:flex;flex-direction:column;gap:24px;">

    <?php if (!empty($ruptures)): ?>
    <div class="card">
      <div class="card-header">
        <h3><i class="fas fa-exclamation-triangle" style="margin-right:8px;color:var(--admin-danger);"></i>Ruptures de stock</h3>
        <a href="produits.php" class="btn-admin btn-admin-outline btn-admin-sm">Gérer</a>
      </div>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead><tr><th>Produit</th><th>Marque</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($ruptures as $p): ?>
              <tr>
                <td><?= h($p['nom']) ?></td>
                <td><span class="badge-admin badge-secondary"><?= h($p['marque']) ?></span></td>
                <td><a href="produit-form.php?id=<?= $p['id'] ?>" class="btn-admin btn-admin-sm btn-admin-warning"><i class="fas fa-edit"></i></a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($stockFaible)): ?>
    <div class="card">
      <div class="card-header">
        <h3><i class="fas fa-battery-quarter" style="margin-right:8px;color:var(--admin-warning);"></i>Stock faible (≤3)</h3>
      </div>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead><tr><th>Produit</th><th>Stock</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($stockFaible as $p): ?>
              <tr>
                <td><?= h($p['nom']) ?></td>
                <td><span class="badge-admin badge-warning"><?= (int)$p['stock'] ?> restant(s)</span></td>
                <td><a href="produit-form.php?id=<?= $p['id'] ?>" class="btn-admin btn-admin-sm btn-admin-outline"><i class="fas fa-edit"></i></a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <?php if (empty($ruptures) && empty($stockFaible)): ?>
    <div class="card">
      <div class="card-body" style="text-align:center;padding:40px;color:#aaa;">
        <i class="fas fa-check-circle" style="font-size:2.5rem;color:var(--admin-success);margin-bottom:12px;display:block;"></i>
        <strong>Tout va bien !</strong><br>Aucune alerte de stock.
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php
// Footer admin
echo '</div></div></div>';
echo '<script src="../assets/js/admin.js"></script>';
echo '</body></html>';
?>
