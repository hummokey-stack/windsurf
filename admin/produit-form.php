<?php
/**
 * Formulaire ajout/modification produit — GlisseShop Admin
 * admin/produit-form.php
 */
$produitId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$adminTitle = $produitId ? 'Modifier un produit' : 'Nouveau produit';
require_once '../includes/admin-header.php';

$pdo = getDB();
$errors = [];
$success = '';

// Chargement produit existant
$produit = [];
if ($produitId) {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id=?");
    $stmt->execute([$produitId]);
    $produit = $stmt->fetch() ?: [];
    if (empty($produit)) {
        echo '<div class="alert-admin alert-admin-danger">Produit introuvable.</div>';
        echo '</div></div></div><script src="../assets/js/admin.js"></script></body></html>';
        exit;
    }
}

// Traitement formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        $errors[] = 'Requête invalide.';
    } else {
        $nom              = trim($_POST['nom'] ?? '');
        $slug             = slugify($nom);
        $categorie_id     = (int)($_POST['categorie_id'] ?? 0);
        $marque           = trim($_POST['marque'] ?? '');
        $prix             = (float)str_replace(',', '.', $_POST['prix'] ?? '0');
        $ancien_prix      = trim($_POST['ancien_prix'] ?? '') !== '' ? (float)str_replace(',', '.', $_POST['ancien_prix']) : null;
        $stock            = (int)($_POST['stock'] ?? 0);
        $niveau           = trim($_POST['niveau'] ?? 'tous');
        $description      = $_POST['description'] ?? '';
        $description_courte = trim(substr($_POST['description_courte'] ?? '', 0, 160));
        $variantes        = trim($_POST['variantes'] ?? '');
        $est_nouveaute    = isset($_POST['est_nouveaute']) ? 1 : 0;
        $est_promo        = isset($_POST['est_promo']) ? 1 : 0;
        $est_occasion     = isset($_POST['est_occasion']) ? 1 : 0;
        $est_actif        = isset($_POST['est_actif']) ? 1 : 0;

        // Validation
        if (empty($nom)) $errors[] = 'Le nom est obligatoire.';
        if ($prix <= 0)  $errors[] = 'Le prix doit être supérieur à 0.';

        // Valider JSON variantes
        if (!empty($variantes)) {
            $varTest = json_decode($variantes, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Convertir liste ligne par ligne en JSON
                $lines = array_filter(array_map('trim', explode("\n", $variantes)));
                $variantes = json_encode(array_values($lines), JSON_UNESCAPED_UNICODE);
            }
        }

        // Upload image principale
        $image_principale = $produit['image_principale'] ?? null;
        if (!empty($_FILES['image_principale']['name'])) {
            $uploaded = uploadImage($_FILES['image_principale'], 'prod');
            if ($uploaded) {
                $image_principale = $uploaded;
            } else {
                $errors[] = 'Image principale invalide (JPG/PNG/WebP, max 2Mo).';
            }
        }

        // Upload images secondaires
        $images_secondaires = $produit['images_secondaires'] ?? '[]';
        if (!empty($_FILES['images_secondaires']['name'][0])) {
            $existingImgs = json_decode($images_secondaires, true) ?: [];
            foreach ($_FILES['images_secondaires']['tmp_name'] as $k => $tmp) {
                if ($_FILES['images_secondaires']['error'][$k] === UPLOAD_ERR_OK && count($existingImgs) < 5) {
                    $file = [
                        'name'     => $_FILES['images_secondaires']['name'][$k],
                        'tmp_name' => $tmp,
                        'size'     => $_FILES['images_secondaires']['size'][$k],
                        'error'    => $_FILES['images_secondaires']['error'][$k],
                    ];
                    $up = uploadImage($file, 'prod_sec');
                    if ($up) $existingImgs[] = $up;
                }
            }
            $images_secondaires = json_encode($existingImgs, JSON_UNESCAPED_UNICODE);
        }

        if (empty($errors)) {
            // Slug unique
            $slugBase = $slug;
            $i = 1;
            while (true) {
                $checkStmt = $pdo->prepare("SELECT id FROM produits WHERE slug=? AND id!=?");
                $checkStmt->execute([$slug, $produitId]);
                if (!$checkStmt->fetch()) break;
                $slug = $slugBase . '-' . $i++;
            }

            if ($produitId) {
                $stmt = $pdo->prepare("UPDATE produits SET
                    nom=?, slug=?, categorie_id=?, marque=?, prix=?, ancien_prix=?,
                    description=?, description_courte=?, stock=?, image_principale=?,
                    images_secondaires=?, variantes=?, niveau=?,
                    est_occasion=?, est_nouveaute=?, est_promo=?, est_actif=?
                    WHERE id=?");
                $stmt->execute([
                    $nom, $slug, $categorie_id ?: null, $marque, $prix, $ancien_prix,
                    $description, $description_courte, $stock, $image_principale,
                    $images_secondaires, $variantes ?: null, $niveau,
                    $est_occasion, $est_nouveaute, $est_promo, $est_actif, $produitId
                ]);
                $success = 'Produit mis à jour avec succès.';
                // Recharger
                $stmt2 = $pdo->prepare("SELECT * FROM produits WHERE id=?");
                $stmt2->execute([$produitId]);
                $produit = $stmt2->fetch();
            } else {
                $stmt = $pdo->prepare("INSERT INTO produits
                    (nom, slug, categorie_id, marque, prix, ancien_prix, description, description_courte,
                     stock, image_principale, images_secondaires, variantes, niveau,
                     est_occasion, est_nouveaute, est_promo, est_actif)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $nom, $slug, $categorie_id ?: null, $marque, $prix, $ancien_prix,
                    $description, $description_courte, $stock, $image_principale,
                    $images_secondaires, $variantes ?: null, $niveau,
                    $est_occasion, $est_nouveaute, $est_promo, $est_actif
                ]);
                $newId = $pdo->lastInsertId();
                header('Location: produit-form.php?id=' . $newId . '&saved=1');
                exit;
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $success = 'Produit enregistré avec succès.';
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY parent_id NULLS FIRST, nom")->fetchAll();
$csrf = csrfToken();
?>

<?php if (!empty($errors)): ?>
  <div class="alert-admin alert-admin-danger">
    <i class="fas fa-exclamation-circle"></i>
    <ul style="margin:0;padding-left:18px;">
      <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert-admin alert-admin-success"><i class="fas fa-check-circle"></i> <?= h($success) ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
  <a href="produits.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
  <?php if ($produitId && !empty($produit)): ?>
    <a href="../produit.php?id=<?= $produitId ?>" target="_blank" class="btn-admin btn-admin-outline">
      <i class="fas fa-external-link-alt"></i> Voir la fiche produit
    </a>
  <?php endif; ?>
</div>

<form method="post" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

    <!-- Colonne principale -->
    <div>

      <!-- Informations de base -->
      <div class="card">
        <div class="card-header"><h3>Informations principales</h3></div>
        <div class="card-body">
          <div class="admin-form-group">
            <label for="nom">Nom du produit <span class="req">*</span></label>
            <input type="text" id="nom" name="nom" class="admin-form-control"
              value="<?= h($produit['nom'] ?? '') ?>" required placeholder="Ex: Aile Wing F-One 5m">
          </div>

          <div class="admin-form-row">
            <div class="admin-form-group">
              <label for="marque">Marque</label>
              <input type="text" id="marque" name="marque" class="admin-form-control"
                value="<?= h($produit['marque'] ?? '') ?>" placeholder="F-One, Duotone, Naish...">
            </div>
            <div class="admin-form-group">
              <label for="categorie_id">Catégorie</label>
              <select id="categorie_id" name="categorie_id" class="admin-form-control">
                <option value="">— Sélectionner —</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"
                    <?= ($produit['categorie_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                    <?= $cat['parent_id'] ? '&nbsp;&nbsp;↳ ' : '' ?><?= h($cat['nom']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="admin-form-group">
            <label for="description_courte">Description courte (max 160 caractères)</label>
            <textarea id="description_courte" name="description_courte" class="admin-form-control"
              rows="2" maxlength="160"
              placeholder="Résumé court pour les listings..."><?= h($produit['description_courte'] ?? '') ?></textarea>
            <div class="form-hint"><span id="descCount">0</span>/160 caractères</div>
          </div>

          <div class="admin-form-group">
            <label for="description">Description longue</label>
            <textarea id="description" name="description" class="admin-form-control"
              rows="10"
              placeholder="Description détaillée avec HTML autorisé..."><?= h($produit['description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Prix & Stock -->
      <div class="card">
        <div class="card-header"><h3>Prix & Stock</h3></div>
        <div class="card-body">
          <div class="admin-form-row">
            <div class="admin-form-group">
              <label for="prix">Prix de vente <span class="req">*</span> (€)</label>
              <input type="number" id="prix" name="prix" class="admin-form-control"
                value="<?= h($produit['prix'] ?? '') ?>" step="0.01" min="0" required placeholder="0.00">
            </div>
            <div class="admin-form-group">
              <label for="ancien_prix">Ancien prix (barré, pour promo) (€)</label>
              <input type="number" id="ancien_prix" name="ancien_prix" class="admin-form-control"
                value="<?= h($produit['ancien_prix'] ?? '') ?>" step="0.01" min="0" placeholder="0.00">
            </div>
          </div>
          <div class="admin-form-row">
            <div class="admin-form-group">
              <label for="stock">Stock disponible</label>
              <input type="number" id="stock" name="stock" class="admin-form-control"
                value="<?= h($produit['stock'] ?? 0) ?>" min="0">
            </div>
            <div class="admin-form-group">
              <label for="niveau">Niveau</label>
              <select id="niveau" name="niveau" class="admin-form-control">
                <option value="tous"         <?= ($produit['niveau'] ?? '') === 'tous'          ? 'selected' : '' ?>>Tous niveaux</option>
                <option value="debutant"     <?= ($produit['niveau'] ?? '') === 'debutant'      ? 'selected' : '' ?>>Débutant</option>
                <option value="intermediaire"<?= ($produit['niveau'] ?? '') === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
                <option value="expert"       <?= ($produit['niveau'] ?? '') === 'expert'        ? 'selected' : '' ?>>Expert</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Variantes -->
      <div class="card">
        <div class="card-header"><h3>Variantes (tailles, volumes, surfaces…)</h3></div>
        <div class="card-body">
          <div class="admin-form-group">
            <label>Variantes (une par ligne, ou JSON)</label>
            <?php
            $variantesDisplay = '';
            if (!empty($produit['variantes'])) {
                $vArr = json_decode($produit['variantes'], true);
                $variantesDisplay = is_array($vArr) ? implode("\n", $vArr) : $produit['variantes'];
            }
            ?>
            <textarea name="variantes" class="admin-form-control" rows="5"
              placeholder="4m&#10;5m&#10;6m&#10;7m"><?= h($variantesDisplay) ?></textarea>
            <div class="form-hint">Une variante par ligne. Ex: 4m, 5m, 6m — ou S, M, L, XL</div>
          </div>
          <div id="variantesPreview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
        </div>
      </div>

    </div>

    <!-- Colonne droite -->
    <div>

      <!-- Publication -->
      <div class="card">
        <div class="card-header"><h3>Publication</h3></div>
        <div class="card-body">
          <label class="form-check">
            <input type="checkbox" name="est_actif" value="1" <?= !empty($produit['est_actif']) || empty($produitId) ? 'checked' : '' ?>>
            <label class="toggle-label">Produit actif (visible)</label>
          </label>
          <label class="form-check" style="margin-top:10px;">
            <input type="checkbox" name="est_nouveaute" value="1" <?= !empty($produit['est_nouveaute']) ? 'checked' : '' ?>>
            <label class="toggle-label">⭐ Nouveauté</label>
          </label>
          <label class="form-check">
            <input type="checkbox" name="est_promo" value="1" <?= !empty($produit['est_promo']) ? 'checked' : '' ?>>
            <label class="toggle-label">🔥 En promotion</label>
          </label>
          <label class="form-check">
            <input type="checkbox" name="est_occasion" value="1" <?= !empty($produit['est_occasion']) ? 'checked' : '' ?>>
            <label class="toggle-label">♻️ Produit d'occasion</label>
          </label>
        </div>
      </div>

      <!-- Image principale -->
      <div class="card">
        <div class="card-header"><h3>Image principale</h3></div>
        <div class="card-body">
          <?php if (!empty($produit['image_principale'])): ?>
            <img src="../<?= h($produit['image_principale']) ?>" style="max-height:160px;border-radius:8px;margin-bottom:12px;border:1px solid #eee;">
          <?php endif; ?>
          <label class="upload-zone" id="uploadMain">
            <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <p>Cliquer pour choisir une image<br><small>JPG, PNG, WebP — max 2Mo</small></p>
            <input type="file" name="image_principale" accept="image/jpeg,image/png,image/webp"
              id="imageMainInput" onchange="previewImage(this, 'previewMain')">
          </label>
          <img id="previewMain" src="" style="display:none;max-height:120px;margin-top:10px;border-radius:6px;">
        </div>
      </div>

      <!-- Images secondaires -->
      <div class="card">
        <div class="card-header"><h3>Images secondaires (max 5)</h3></div>
        <div class="card-body">
          <?php if (!empty($produit['images_secondaires'])): ?>
            <?php $imgs = json_decode($produit['images_secondaires'], true) ?: []; ?>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
              <?php foreach ($imgs as $img): ?>
                <img src="../<?= h($img) ?>" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #eee;">
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <label class="upload-zone">
            <div class="upload-icon"><i class="fas fa-images"></i></div>
            <p>Ajouter des images (sélection multiple)<br><small>JPG, PNG, WebP — max 2Mo chacune</small></p>
            <input type="file" name="images_secondaires[]" multiple accept="image/jpeg,image/png,image/webp">
          </label>
        </div>
      </div>

      <!-- Bouton enregistrer -->
      <button type="submit" class="btn-admin btn-admin-primary btn-admin-lg" style="width:100%;justify-content:center;">
        <i class="fas fa-save"></i> <?= $produitId ? 'Mettre à jour' : 'Créer le produit' ?>
      </button>
    </div>

  </div><!-- /grid -->
</form>

<?php
echo '</div></div></div>';
echo '<script>
// Compteur description courte
const descEl = document.getElementById("description_courte");
const countEl = document.getElementById("descCount");
function updateCount() { countEl.textContent = descEl.value.length; }
updateCount();
descEl.addEventListener("input", updateCount);

// Preview image
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = "block"; };
        reader.readAsDataURL(input.files[0]);
    }
}

// Preview variantes
const varInput = document.querySelector("textarea[name=variantes]");
const varPreview = document.getElementById("variantesPreview");
function updateVariantesPreview() {
    const lines = varInput.value.split("\n").map(l => l.trim()).filter(l => l);
    varPreview.innerHTML = lines.map(l => `<span style="padding:6px 14px;border:2px solid #dee2e6;border-radius:6px;font-size:0.85rem;">${l}</span>`).join("");
}
if (varInput) { varInput.addEventListener("input", updateVariantesPreview); updateVariantesPreview(); }
</script>';
echo '<script src="../assets/js/admin.js"></script>';
echo '</body></html>';
?>
