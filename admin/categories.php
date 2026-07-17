<?php
/**
 * Gestion des catégories — GlisseShop Admin
 * admin/categories.php
 */
$adminTitle = 'Catégories';
require_once '../includes/admin-header.php';

$pdo = getDB();
$errors = [];
$success = '';
$editCat = null;

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!empty($_GET['csrf']) && verifyCsrf($_GET['csrf'])) {
        $catId = (int)$_GET['delete'];
        // Vérifier qu'aucun produit n'est lié
        $nb = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE categorie_id=?");
        $nb->execute([$catId]);
        if ($nb->fetchColumn() > 0) {
            $errors[] = 'Impossible de supprimer : des produits sont liés à cette catégorie.';
        } else {
            $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$catId]);
            $success = 'Catégorie supprimée.';
        }
    }
}

// Charger pour édition
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editCat = $stmt->fetch();
}

// Enregistrement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        $errors[] = 'Requête invalide.';
    } else {
        $nom       = trim($_POST['nom'] ?? '');
        $slug      = slugify($nom);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $desc      = trim($_POST['description'] ?? '');
        $ordre     = (int)($_POST['ordre'] ?? 0);
        $actif     = isset($_POST['actif']) ? 1 : 0;
        $catId     = (int)($_POST['cat_id'] ?? 0);

        if (empty($nom)) $errors[] = 'Le nom est obligatoire.';

        if (empty($errors)) {
            // Slug unique
            $slugBase = $slug;
            $i = 1;
            while (true) {
                $check = $pdo->prepare("SELECT id FROM categories WHERE slug=? AND id!=?");
                $check->execute([$slug, $catId]);
                if (!$check->fetch()) break;
                $slug = $slugBase . '-' . $i++;
            }

            if ($catId) {
                $pdo->prepare("UPDATE categories SET nom=?,slug=?,parent_id=?,description=?,ordre=?,actif=? WHERE id=?")
                    ->execute([$nom, $slug, $parent_id, $desc, $ordre, $actif, $catId]);
                $success = 'Catégorie mise à jour.';
            } else {
                $pdo->prepare("INSERT INTO categories (nom,slug,parent_id,description,ordre,actif) VALUES (?,?,?,?,?,?)")
                    ->execute([$nom, $slug, $parent_id, $desc, $ordre, $actif]);
                $success = 'Catégorie créée.';
            }
            $editCat = null;
        }
    }
}

$csrf = csrfToken();
$allCats = $pdo->query("SELECT c.*, p.nom as parent_nom,
    (SELECT COUNT(*) FROM produits WHERE categorie_id=c.id) as nb_produits
    FROM categories c LEFT JOIN categories p ON c.parent_id=p.id
    ORDER BY c.parent_id NULLS FIRST, c.ordre")->fetchAll();
$parentes = array_filter($allCats, fn($c) => $c['parent_id'] === null);
?>

<?php foreach ($errors as $e): ?>
  <div class="alert-admin alert-admin-danger"><i class="fas fa-exclamation-circle"></i> <?= h($e) ?></div>
<?php endforeach; ?>
<?php if ($success): ?>
  <div class="alert-admin alert-admin-success"><i class="fas fa-check-circle"></i> <?= h($success) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;align-items:start;">

  <!-- Formulaire -->
  <div class="card">
    <div class="card-header">
      <h3><?= $editCat ? 'Modifier la catégorie' : 'Nouvelle catégorie' ?></h3>
      <?php if ($editCat): ?>
        <a href="categories.php" class="btn-admin btn-admin-outline btn-admin-sm">Annuler</a>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
        <input type="hidden" name="cat_id" value="<?= $editCat ? (int)$editCat['id'] : 0 ?>">

        <div class="admin-form-group">
          <label>Nom <span class="req">*</span></label>
          <input type="text" name="nom" class="admin-form-control" value="<?= h($editCat['nom'] ?? '') ?>" required>
        </div>

        <div class="admin-form-group">
          <label>Catégorie parente</label>
          <select name="parent_id" class="admin-form-control">
            <option value="">— Aucune (catégorie principale) —</option>
            <?php foreach ($parentes as $p): ?>
              <?php if (!$editCat || $p['id'] != $editCat['id']): ?>
                <option value="<?= $p['id'] ?>"
                  <?= ($editCat['parent_id'] ?? null) == $p['id'] ? 'selected' : '' ?>>
                  <?= h($p['nom']) ?>
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="admin-form-group">
          <label>Description</label>
          <textarea name="description" class="admin-form-control" rows="3"><?= h($editCat['description'] ?? '') ?></textarea>
        </div>

        <div class="admin-form-row">
          <div class="admin-form-group">
            <label>Ordre d'affichage</label>
            <input type="number" name="ordre" class="admin-form-control" value="<?= h($editCat['ordre'] ?? 0) ?>" min="0">
          </div>
          <div class="admin-form-group" style="display:flex;align-items:flex-end;padding-bottom:4px;">
            <label class="form-check">
              <input type="checkbox" name="actif" value="1" <?= ($editCat ? $editCat['actif'] : 1) ? 'checked' : '' ?>>
              <span>Catégorie active</span>
            </label>
          </div>
        </div>

        <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;justify-content:center;">
          <i class="fas fa-save"></i> <?= $editCat ? 'Mettre à jour' : 'Créer' ?>
        </button>
      </form>
    </div>
  </div>

  <!-- Liste -->
  <div class="card">
    <div class="card-header">
      <h3><?= count($allCats) ?> catégorie(s)</h3>
    </div>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Parente</th>
            <th>Produits</th>
            <th>Ordre</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allCats as $cat): ?>
            <tr>
              <td>
                <?php if ($cat['parent_id']): ?>
                  <span style="color:#aaa;margin-right:6px;">↳</span>
                <?php endif; ?>
                <strong><?= h($cat['nom']) ?></strong>
                <div style="font-size:0.75rem;color:#aaa;"><?= h($cat['slug']) ?></div>
              </td>
              <td><?= h($cat['parent_nom'] ?? '—') ?></td>
              <td><span class="badge-admin badge-primary"><?= (int)$cat['nb_produits'] ?></span></td>
              <td><?= (int)$cat['ordre'] ?></td>
              <td>
                <?php if ($cat['actif']): ?>
                  <span class="badge-admin badge-success">Active</span>
                <?php else: ?>
                  <span class="badge-admin badge-secondary">Inactive</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="action-btns">
                  <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm">
                    <i class="fas fa-edit"></i>
                  </a>
                  <?php if ($cat['nb_produits'] == 0): ?>
                    <a href="categories.php?delete=<?= $cat['id'] ?>&csrf=<?= $csrf ?>"
                       class="btn-admin btn-admin-danger btn-admin-sm"
                       onclick="return confirm('Supprimer cette catégorie ?')">
                      <i class="fas fa-trash"></i>
                    </a>
                  <?php else: ?>
                    <span class="btn-admin btn-admin-sm" style="opacity:0.3;cursor:not-allowed;" title="Des produits sont liés">
                      <i class="fas fa-trash"></i>
                    </span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php
echo '</div></div></div>';
echo '<script src="../assets/js/admin.js"></script>';
echo '</body></html>';
?>
