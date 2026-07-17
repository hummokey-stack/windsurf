<?php
/**
 * Formulaire article blog — GlisseShop Admin
 * admin/blog-form.php
 */
$artId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$adminTitle = $artId ? 'Modifier un article' : 'Nouvel article';
require_once '../includes/admin-header.php';

$pdo = getDB();
$errors = [];
$success = '';
$art = [];

if ($artId) {
    $stmt = $pdo->prepare("SELECT * FROM blog WHERE id=?");
    $stmt->execute([$artId]);
    $art = $stmt->fetch() ?: [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        $errors[] = 'Requête invalide.';
    } else {
        $titre     = trim($_POST['titre'] ?? '');
        $slug      = slugify($titre);
        $contenu   = $_POST['contenu'] ?? '';
        $extrait   = trim(substr($_POST['extrait'] ?? '', 0, 250));
        $categorie = trim($_POST['categorie'] ?? '');
        $actif     = isset($_POST['actif']) ? 1 : 0;

        if (empty($titre)) $errors[] = 'Le titre est obligatoire.';

        // Upload image
        $image = $art['image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $up = uploadImage($_FILES['image'], 'blog');
            if ($up) $image = $up;
            else $errors[] = 'Image invalide (JPG/PNG/WebP, max 2Mo).';
        }

        if (empty($errors)) {
            // Slug unique
            $slugBase = $slug;
            $i = 1;
            while (true) {
                $check = $pdo->prepare("SELECT id FROM blog WHERE slug=? AND id!=?");
                $check->execute([$slug, $artId]);
                if (!$check->fetch()) break;
                $slug = $slugBase . '-' . $i++;
            }

            if ($artId) {
                $pdo->prepare("UPDATE blog SET titre=?,slug=?,contenu=?,extrait=?,image=?,categorie=?,actif=? WHERE id=?")
                    ->execute([$titre, $slug, $contenu, $extrait, $image, $categorie, $actif, $artId]);
                $success = 'Article mis à jour.';
                $stmt = $pdo->prepare("SELECT * FROM blog WHERE id=?");
                $stmt->execute([$artId]);
                $art = $stmt->fetch();
            } else {
                $pdo->prepare("INSERT INTO blog (titre,slug,contenu,extrait,image,categorie,actif) VALUES (?,?,?,?,?,?,?)")
                    ->execute([$titre, $slug, $contenu, $extrait, $image, $categorie, $actif]);
                $newId = $pdo->lastInsertId();
                header('Location: blog-form.php?id=' . $newId . '&saved=1');
                exit;
            }
        }
    }
}

if (isset($_GET['saved'])) $success = 'Article créé avec succès.';
$csrf = csrfToken();
?>

<?php if (!empty($errors)): ?>
  <div class="alert-admin alert-admin-danger">
    <ul style="margin:0;padding-left:18px;">
      <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="alert-admin alert-admin-success"><i class="fas fa-check-circle"></i> <?= h($success) ?></div>
<?php endif; ?>

<div style="margin-bottom:16px;">
  <a href="blog-admin.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Retour au blog</a>
</div>

<form method="post" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

    <div>
      <div class="card">
        <div class="card-header"><h3>Contenu de l'article</h3></div>
        <div class="card-body">
          <div class="admin-form-group">
            <label>Titre <span class="req">*</span></label>
            <input type="text" name="titre" class="admin-form-control" value="<?= h($art['titre'] ?? '') ?>" required>
          </div>
          <div class="admin-form-group">
            <label>Extrait (max 250 caractères)</label>
            <textarea name="extrait" class="admin-form-control" rows="2" maxlength="250"><?= h($art['extrait'] ?? '') ?></textarea>
          </div>
          <div class="admin-form-group">
            <label>Contenu complet (HTML autorisé)</label>
            <textarea name="contenu" class="admin-form-control" rows="18"><?= h($art['contenu'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="card">
        <div class="card-header"><h3>Options</h3></div>
        <div class="card-body">
          <div class="admin-form-group">
            <label>Catégorie</label>
            <input type="text" name="categorie" class="admin-form-control"
              value="<?= h($art['categorie'] ?? '') ?>" placeholder="Wingsurf, Conseils, Spots...">
          </div>
          <label class="form-check">
            <input type="checkbox" name="actif" value="1" <?= ($art ? ($art['actif'] ?? 1) : 1) ? 'checked' : '' ?>>
            <span>Publier l'article</span>
          </label>
        </div>
      </div>

      <div class="card" style="margin-top:16px;">
        <div class="card-header"><h3>Image de couverture</h3></div>
        <div class="card-body">
          <?php if (!empty($art['image']) && file_exists('../' . $art['image'])): ?>
            <img src="../<?= h($art['image']) ?>" style="max-height:140px;border-radius:8px;margin-bottom:12px;width:100%;object-fit:cover;">
          <?php endif; ?>
          <label class="upload-zone">
            <div class="upload-icon"><i class="fas fa-image"></i></div>
            <p>Choisir une image<br><small>JPG, PNG, WebP — max 2Mo</small></p>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" id="blogImgInput"
              onchange="previewBlogImg(this)">
          </label>
          <img id="blogImgPreview" src="" style="display:none;max-height:120px;margin-top:10px;border-radius:6px;width:100%;object-fit:cover;">
        </div>
      </div>

      <button type="submit" class="btn-admin btn-admin-primary btn-admin-lg" style="width:100%;justify-content:center;margin-top:0;">
        <i class="fas fa-save"></i> <?= $artId ? 'Mettre à jour' : 'Publier' ?>
      </button>
    </div>

  </div>
</form>

<?php
echo '</div></div></div>';
echo '<script>
function previewBlogImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const prev = document.getElementById("blogImgPreview");
            prev.src = e.target.result;
            prev.style.display = "block";
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>';
echo '<script src="../assets/js/admin.js"></script>';
echo '</body></html>';
?>
