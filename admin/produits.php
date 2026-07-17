<?php
/**
 * Gestion des produits — GlisseShop Admin
 * admin/produits.php
 */
$adminTitle = 'Produits';
require_once '../includes/admin-header.php';

$pdo = getDB();
$msg = '';

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!empty($_GET['csrf']) && verifyCsrf($_GET['csrf'])) {
        $stmt = $pdo->prepare("DELETE FROM produits WHERE id=?");
        $stmt->execute([(int)$_GET['delete']]);
        $msg = '<div class="alert-admin alert-admin-success"><i class="fas fa-check"></i> Produit supprimé.</div>';
    }
}

// Toggle actif
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    if (!empty($_GET['csrf']) && verifyCsrf($_GET['csrf'])) {
        $stmt = $pdo->prepare("UPDATE produits SET est_actif = CASE WHEN est_actif=1 THEN 0 ELSE 1 END WHERE id=?");
        $stmt->execute([(int)$_GET['toggle']]);
    }
}

// Filtres
$search   = trim($_GET['q'] ?? '');
$catFil   = (int)($_GET['cat'] ?? 0);
$where    = [];
$params   = [];

if ($search !== '') {
    $where[] = "(nom LIKE :q OR marque LIKE :q)";
    $params[':q'] = '%' . $search . '%';
}
if ($catFil > 0) {
    $where[] = "categorie_id=:cat";
    $params[':cat'] = $catFil;
}

$sql = "SELECT p.*, c.nom as cat_nom FROM produits p
        LEFT JOIN categories c ON p.categorie_id=c.id"
     . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY p.date_creation DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
$csrf = csrfToken();
?>

<?= $msg ?>

<div class="admin-search-bar">
  <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;">
    <div class="search-input-wrap">
      <i class="fas fa-search search-icon"></i>
      <input type="text" name="q" value="<?= h($search) ?>" placeholder="Rechercher un produit...">
    </div>
    <select name="cat" class="admin-form-control" style="width:auto;">
      <option value="0">Toutes catégories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= $catFil == $cat['id'] ? 'selected' : '' ?>><?= h($cat['nom']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-admin btn-admin-outline"><i class="fas fa-filter"></i> Filtrer</button>
    <?php if ($search || $catFil): ?>
      <a href="produits.php" class="btn-admin btn-admin-outline"><i class="fas fa-times"></i> Réinitialiser</a>
    <?php endif; ?>
  </form>
  <a href="produit-form.php" class="btn-admin btn-admin-primary" style="margin-left:auto;">
    <i class="fas fa-plus"></i> Nouveau produit
  </a>
</div>

<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-box" style="margin-right:8px;"></i><?= count($produits) ?> produit(s)</h3>
  </div>
  <div style="overflow-x:auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Nom</th>
          <th>Catégorie</th>
          <th>Marque</th>
          <th>Prix</th>
          <th>Stock</th>
          <th>Statut</th>
          <th>Tags</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($produits)): ?>
          <tr><td colspan="9" style="text-align:center;padding:40px;color:#aaa;">
            <i class="fas fa-box-open" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
            Aucun produit trouvé.
            <br><a href="produit-form.php" class="btn-admin btn-admin-primary" style="margin-top:14px;display:inline-flex;">Ajouter le premier</a>
          </td></tr>
        <?php else: ?>
          <?php foreach ($produits as $p): ?>
            <tr>
              <td>
                <?php $img = imageProduit($p['image_principale']); ?>
                <img src="../<?= h($img) ?>" class="img-mini" alt="<?= h($p['nom']) ?>">
              </td>
              <td>
                <strong><?= h($p['nom']) ?></strong>
                <div style="font-size:0.75rem;color:#999;"><?= h($p['slug']) ?></div>
              </td>
              <td><?= h($p['cat_nom'] ?? '—') ?></td>
              <td><?= h($p['marque'] ?? '—') ?></td>
              <td>
                <strong><?= formatPrix((float)$p['prix']) ?></strong>
                <?php if ($p['ancien_prix']): ?>
                  <div style="font-size:0.8rem;color:#aaa;text-decoration:line-through;"><?= formatPrix((float)$p['ancien_prix']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($p['stock'] == 0): ?>
                  <span class="badge-admin badge-danger">Rupture</span>
                <?php elseif ($p['stock'] <= 3): ?>
                  <span class="badge-admin badge-warning"><?= (int)$p['stock'] ?> restant(s)</span>
                <?php else: ?>
                  <span class="badge-admin badge-success"><?= (int)$p['stock'] ?></span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($p['est_actif']): ?>
                  <span class="badge-admin badge-success">Actif</span>
                <?php else: ?>
                  <span class="badge-admin badge-secondary">Inactif</span>
                <?php endif; ?>
              </td>
              <td style="white-space:nowrap;">
                <?php if ($p['est_promo']): ?>     <span class="badge-admin badge-danger">Promo</span> <?php endif; ?>
                <?php if ($p['est_nouveaute']): ?> <span class="badge-admin badge-info">Nouveau</span> <?php endif; ?>
                <?php if ($p['est_occasion']): ?>  <span class="badge-admin badge-warning">Occasion</span> <?php endif; ?>
              </td>
              <td>
                <div class="action-btns">
                  <a href="produit-form.php?id=<?= $p['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm" title="Modifier">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="produits.php?toggle=<?= $p['id'] ?>&csrf=<?= $csrf ?>"
                     class="btn-admin btn-admin-sm <?= $p['est_actif'] ? 'btn-admin-warning' : 'btn-admin-success' ?>"
                     title="<?= $p['est_actif'] ? 'Désactiver' : 'Activer' ?>">
                    <i class="fas <?= $p['est_actif'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                  </a>
                  <a href="../produit.php?id=<?= $p['id'] ?>" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm" title="Voir">
                    <i class="fas fa-external-link-alt"></i>
                  </a>
                  <a href="produits.php?delete=<?= $p['id'] ?>&csrf=<?= $csrf ?>"
                     class="btn-admin btn-admin-danger btn-admin-sm"
                     onclick="return confirm('Supprimer ce produit définitivement ?')"
                     title="Supprimer">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
echo '</div></div></div>';
echo '<script src="../assets/js/admin.js"></script>';
echo '</body></html>';
?>
