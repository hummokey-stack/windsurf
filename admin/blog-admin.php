<?php
/**
 * Gestion du blog — GlisseShop Admin
 * admin/blog-admin.php
 */
$adminTitle = 'Blog';
require_once '../includes/admin-header.php';

$pdo = getDB();
$success = '';

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!empty($_GET['csrf']) && verifyCsrf($_GET['csrf'])) {
        $pdo->prepare("DELETE FROM blog WHERE id=?")->execute([(int)$_GET['delete']]);
        $success = 'Article supprimé.';
    }
}
// Toggle actif
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    if (!empty($_GET['csrf']) && verifyCsrf($_GET['csrf'])) {
        $pdo->prepare("UPDATE blog SET actif=CASE WHEN actif=1 THEN 0 ELSE 1 END WHERE id=?")
            ->execute([(int)$_GET['toggle']]);
    }
}

$articles = $pdo->query("SELECT * FROM blog ORDER BY date_creation DESC")->fetchAll();
$csrf = csrfToken();
?>

<?php if ($success): ?>
  <div class="alert-admin alert-admin-success"><i class="fas fa-check-circle"></i> <?= h($success) ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:flex-end;margin-bottom:18px;">
  <a href="blog-form.php" class="btn-admin btn-admin-primary">
    <i class="fas fa-plus"></i> Nouvel article
  </a>
</div>

<div class="card">
  <div style="overflow-x:auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Titre</th>
          <th>Catégorie</th>
          <th>Statut</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($articles)): ?>
          <tr><td colspan="6" style="text-align:center;padding:40px;color:#aaa;">Aucun article</td></tr>
        <?php else: ?>
          <?php foreach ($articles as $art): ?>
            <tr>
              <td>
                <?php if ($art['image'] && file_exists('../' . $art['image'])): ?>
                  <img src="../<?= h($art['image']) ?>" class="img-mini">
                <?php else: ?>
                  <div style="width:44px;height:44px;background:#f4f6f9;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#aaa;"><i class="fas fa-image"></i></div>
                <?php endif; ?>
              </td>
              <td>
                <strong><?= h($art['titre']) ?></strong>
                <div style="font-size:0.75rem;color:#aaa;"><?= h($art['slug']) ?></div>
              </td>
              <td><?= h($art['categorie'] ?? '—') ?></td>
              <td>
                <?php if ($art['actif']): ?>
                  <span class="badge-admin badge-success">Publié</span>
                <?php else: ?>
                  <span class="badge-admin badge-secondary">Brouillon</span>
                <?php endif; ?>
              </td>
              <td style="font-size:0.82rem;color:#999;"><?= formatDate($art['date_creation']) ?></td>
              <td>
                <div class="action-btns">
                  <a href="blog-form.php?id=<?= $art['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="blog-admin.php?toggle=<?= $art['id'] ?>&csrf=<?= $csrf ?>"
                     class="btn-admin btn-admin-sm <?= $art['actif'] ? 'btn-admin-warning' : 'btn-admin-success' ?>">
                    <i class="fas <?= $art['actif'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                  </a>
                  <a href="../article.php?slug=<?= h($art['slug']) ?>" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm">
                    <i class="fas fa-external-link-alt"></i>
                  </a>
                  <a href="blog-admin.php?delete=<?= $art['id'] ?>&csrf=<?= $csrf ?>"
                     class="btn-admin btn-admin-danger btn-admin-sm"
                     onclick="return confirm('Supprimer cet article ?')">
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
