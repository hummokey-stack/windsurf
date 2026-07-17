<?php
/**
 * Paramètres du site — GlisseShop Admin
 * admin/parametres.php
 */
$adminTitle = 'Paramètres du site';
require_once '../includes/admin-header.php';

$pdo = getDB();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        $errors[] = 'Requête invalide.';
    } else {
        $fields = [
            'site_name'                  => trim($_POST['site_name'] ?? ''),
            'slogan'                     => trim($_POST['slogan'] ?? ''),
            'email'                      => trim($_POST['email'] ?? ''),
            'telephone'                  => trim($_POST['telephone'] ?? ''),
            'adresse'                    => trim($_POST['adresse'] ?? ''),
            'horaires'                   => trim($_POST['horaires'] ?? ''),
            'livraison_gratuite_montant' => (float)str_replace(',', '.', $_POST['livraison_gratuite_montant'] ?? '100'),
            'facebook'                   => trim($_POST['facebook'] ?? ''),
            'instagram'                  => trim($_POST['instagram'] ?? ''),
            'meta_description'           => trim($_POST['meta_description'] ?? ''),
        ];

        // Validation
        if (empty($fields['site_name'])) $errors[] = 'Le nom du site est obligatoire.';
        if (!empty($fields['email']) && !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email est invalide.';
        }

        // Upload logo
        $logo = $settings['logo'] ?? 'assets/images/logo.png';
        if (!empty($_FILES['logo']['name'])) {
            $uploaded = uploadImage($_FILES['logo'], 'logo');
            if ($uploaded) {
                $logo = $uploaded;
            } else {
                $errors[] = 'Logo invalide (JPG/PNG/WebP, max 2Mo).';
            }
        }

        // Changement mot de passe admin
        $newPwd = trim($_POST['new_password'] ?? '');
        $newPwdConfirm = trim($_POST['new_password_confirm'] ?? '');
        if (!empty($newPwd)) {
            if (strlen($newPwd) < 6) {
                $errors[] = 'Le mot de passe doit faire au moins 6 caractères.';
            } elseif ($newPwd !== $newPwdConfirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            } else {
                $hash = hash('sha256', $newPwd);
                $pdo->prepare("UPDATE admin SET password=? WHERE id=1")->execute([$hash]);
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE settings SET
                site_name=?, slogan=?, email=?, telephone=?, adresse=?, horaires=?,
                livraison_gratuite_montant=?, facebook=?, instagram=?, meta_description=?, logo=?
                WHERE id=1");
            $stmt->execute([
                $fields['site_name'], $fields['slogan'], $fields['email'],
                $fields['telephone'], $fields['adresse'], $fields['horaires'],
                $fields['livraison_gratuite_montant'], $fields['facebook'],
                $fields['instagram'], $fields['meta_description'], $logo
            ]);
            $success = 'Paramètres enregistrés avec succès.';
            $settings = getSettings(); // Recharger
        }
    }
}

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

<form method="post" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

    <div>
      <!-- Identité -->
      <div class="card">
        <div class="card-header"><h3><i class="fas fa-store" style="margin-right:8px;"></i>Identité du site</h3></div>
        <div class="card-body">
          <div class="admin-form-group">
            <label>Nom du site <span class="req">*</span></label>
            <input type="text" name="site_name" class="admin-form-control" value="<?= h($settings['site_name'] ?? '') ?>" required>
          </div>
          <div class="admin-form-group">
            <label>Slogan</label>
            <input type="text" name="slogan" class="admin-form-control" value="<?= h($settings['slogan'] ?? '') ?>">
          </div>
          <div class="admin-form-group">
            <label>Meta description (SEO)</label>
            <textarea name="meta_description" class="admin-form-control" rows="3"><?= h($settings['meta_description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Contact -->
      <div class="card">
        <div class="card-header"><h3><i class="fas fa-address-book" style="margin-right:8px;"></i>Coordonnées</h3></div>
        <div class="card-body">
          <div class="admin-form-row">
            <div class="admin-form-group">
              <label>Email de contact</label>
              <input type="email" name="email" class="admin-form-control" value="<?= h($settings['email'] ?? '') ?>">
            </div>
            <div class="admin-form-group">
              <label>Téléphone</label>
              <input type="text" name="telephone" class="admin-form-control" value="<?= h($settings['telephone'] ?? '') ?>">
            </div>
          </div>
          <div class="admin-form-group">
            <label>Adresse showroom</label>
            <input type="text" name="adresse" class="admin-form-control" value="<?= h($settings['adresse'] ?? '') ?>">
          </div>
          <div class="admin-form-group">
            <label>Horaires d'ouverture</label>
            <input type="text" name="horaires" class="admin-form-control" value="<?= h($settings['horaires'] ?? '') ?>" placeholder="Lun-Sam 9h-19h">
          </div>
        </div>
      </div>

      <!-- Livraison -->
      <div class="card">
        <div class="card-header"><h3><i class="fas fa-truck" style="margin-right:8px;"></i>Livraison</h3></div>
        <div class="card-body">
          <div class="admin-form-group">
            <label>Seuil livraison gratuite (€)</label>
            <input type="number" name="livraison_gratuite_montant" class="admin-form-control"
              value="<?= h($settings['livraison_gratuite_montant'] ?? 100) ?>" step="0.01" min="0">
            <div class="form-hint">Entrez 0 pour toujours offrir la livraison.</div>
          </div>
        </div>
      </div>

      <!-- Réseaux sociaux -->
      <div class="card">
        <div class="card-header"><h3><i class="fas fa-share-alt" style="margin-right:8px;"></i>Réseaux sociaux</h3></div>
        <div class="card-body">
          <div class="admin-form-group">
            <label><i class="fab fa-facebook" style="color:#1877F2;margin-right:6px;"></i>Page Facebook (URL)</label>
            <input type="url" name="facebook" class="admin-form-control" value="<?= h($settings['facebook'] ?? '') ?>" placeholder="https://facebook.com/votrepageglisse">
          </div>
          <div class="admin-form-group">
            <label><i class="fab fa-instagram" style="color:#E1306C;margin-right:6px;"></i>Compte Instagram (URL)</label>
            <input type="url" name="instagram" class="admin-form-control" value="<?= h($settings['instagram'] ?? '') ?>" placeholder="https://instagram.com/votrecompte">
          </div>
        </div>
      </div>

      <!-- Sécurité -->
      <div class="card">
        <div class="card-header"><h3><i class="fas fa-shield-alt" style="margin-right:8px;"></i>Mot de passe administrateur</h3></div>
        <div class="card-body">
          <div class="alert-admin alert-admin-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Laissez vide pour ne pas changer le mot de passe actuel.
          </div>
          <div class="admin-form-row">
            <div class="admin-form-group">
              <label>Nouveau mot de passe</label>
              <input type="password" name="new_password" class="admin-form-control" autocomplete="new-password">
            </div>
            <div class="admin-form-group">
              <label>Confirmer le mot de passe</label>
              <input type="password" name="new_password_confirm" class="admin-form-control" autocomplete="new-password">
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Colonne droite : Logo -->
    <div>
      <div class="card">
        <div class="card-header"><h3><i class="fas fa-image" style="margin-right:8px;"></i>Logo du site</h3></div>
        <div class="card-body">
          <div id="logoPreviewWrap" style="text-align:center;margin-bottom:16px;">
            <?php if (!empty($settings['logo']) && file_exists('../' . $settings['logo'])): ?>
              <img id="logoPreview" src="../<?= h($settings['logo']) ?>"
                style="max-height:100px;max-width:100%;border-radius:8px;border:1px solid #eee;">
            <?php else: ?>
              <div id="logoPreview" style="width:100%;height:80px;background:var(--admin-light);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#aaa;">
                Pas de logo
              </div>
            <?php endif; ?>
          </div>
          <label class="upload-zone" id="logoUploadZone">
            <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <p>Cliquer pour choisir un logo<br><small>JPG, PNG, WebP — max 2Mo</small></p>
            <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" id="logoInput">
          </label>
        </div>
      </div>

      <button type="submit" class="btn-admin btn-admin-primary btn-admin-lg" style="width:100%;justify-content:center;margin-top:0;">
        <i class="fas fa-save"></i> Enregistrer les paramètres
      </button>
    </div>

  </div>
</form>

<?php
echo '</div></div></div>';
echo '<script>
document.getElementById("logoInput").addEventListener("change", function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const prev = document.getElementById("logoPreview");
            if (prev.tagName === "IMG") {
                prev.src = e.target.result;
            } else {
                const img = document.createElement("img");
                img.id = "logoPreview";
                img.src = e.target.result;
                img.style = "max-height:100px;max-width:100%;border-radius:8px;border:1px solid #eee;";
                prev.replaceWith(img);
            }
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>';
echo '<script src="../assets/js/admin.js"></script>';
echo '</body></html>';
?>
