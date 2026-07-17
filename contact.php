<?php
/**
 * Page contact — GlisseShop
 * contact.php
 */
require_once 'includes/functions.php';
$pageTitle = 'Nous contacter';
$settings  = getSettings();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Requête invalide.';
    } else {
        $nom     = trim($_POST['nom']     ?? '');
        $email   = trim($_POST['email']   ?? '');
        $sujet   = trim($_POST['sujet']   ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($nom))     $errors[] = 'Le nom est obligatoire.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
        if (empty($sujet))   $errors[] = 'Le sujet est obligatoire.';
        if (empty($message) || strlen($message) < 10) $errors[] = 'Le message est trop court.';

        if (empty($errors)) {
            if (!empty($settings['email']) && function_exists('mail')) {
                $body = "Nom : $nom\nEmail : $email\nSujet : $sujet\n\nMessage :\n$message";
                @mail($settings['email'], "[Contact GlisseShop] $sujet", $body,
                    "From: $email\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8");
            }
            $success = true;
        }
    }
}

$csrf = csrfToken();
require_once 'includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb"><a href="index.php">Accueil</a><span>›</span><span>Contact</span></nav>
    <h1>📞 Nous contacter</h1>
    <p style="color:rgba(255,255,255,0.75);margin-top:8px;">Notre équipe répond dans les 24h</p>
  </div>
</div>

<div class="container">
  <div class="contact-layout">

    <!-- Infos -->
    <div class="contact-info">
      <h2>Notre showroom</h2>
      <p style="color:#666;margin-bottom:28px;">Venez nous rendre visite ou contactez-nous par téléphone, email ou via ce formulaire.</p>

      <?php if ($settings['adresse']): ?>
      <div class="contact-item">
        <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
        <div class="text">
          <strong>Adresse</strong>
          <span><?= h($settings['adresse']) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($settings['telephone']): ?>
      <div class="contact-item">
        <div class="icon"><i class="fas fa-phone"></i></div>
        <div class="text">
          <strong>Téléphone</strong>
          <span><a href="tel:<?= h(preg_replace('/\s/', '', $settings['telephone'])) ?>"><?= h($settings['telephone']) ?></a></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($settings['email']): ?>
      <div class="contact-item">
        <div class="icon"><i class="fas fa-envelope"></i></div>
        <div class="text">
          <strong>Email</strong>
          <span><a href="mailto:<?= h($settings['email']) ?>"><?= h($settings['email']) ?></a></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($settings['horaires']): ?>
      <div class="contact-item">
        <div class="icon"><i class="fas fa-clock"></i></div>
        <div class="text">
          <strong>Horaires</strong>
          <span><?= h($settings['horaires']) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <div class="map-container">
        <iframe
          src="https://www.openstreetmap.org/export/embed.html?bbox=7.1,43.6,7.35,43.75&layer=mapnik"
          title="Localisation GlisseShop">
        </iframe>
      </div>
    </div>

    <!-- Formulaire -->
    <div>
      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <strong>Message envoyé !</strong> Nous vous répondrons dans les plus brefs délais.
        </div>
      <?php else: ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul style="margin:0;padding-left:18px;"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <div class="form-step" style="box-shadow:none;border:1px solid var(--gray-light);">
        <h2 style="font-size:1.4rem;margin-bottom:24px;">Envoyez-nous un message</h2>
        <form method="post" novalidate>
          <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
          <div class="form-row">
            <div class="form-group">
              <label>Votre nom <span class="required">*</span></label>
              <input type="text" name="nom" class="form-control" value="<?= h($_POST['nom'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Votre email <span class="required">*</span></label>
              <input type="email" name="email" class="form-control" value="<?= h($_POST['email'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label>Sujet <span class="required">*</span></label>
            <select name="sujet" class="form-control" required>
              <option value="">Sélectionner un sujet</option>
              <option value="Conseil produit" <?= ($_POST['sujet'] ?? '') === 'Conseil produit' ? 'selected' : '' ?>>Conseil produit</option>
              <option value="Commande en cours" <?= ($_POST['sujet'] ?? '') === 'Commande en cours' ? 'selected' : '' ?>>Commande en cours</option>
              <option value="SAV / Retour" <?= ($_POST['sujet'] ?? '') === 'SAV / Retour' ? 'selected' : '' ?>>SAV / Retour</option>
              <option value="Disponibilité" <?= ($_POST['sujet'] ?? '') === 'Disponibilité' ? 'selected' : '' ?>>Disponibilité produit</option>
              <option value="Autre" <?= ($_POST['sujet'] ?? '') === 'Autre' ? 'selected' : '' ?>>Autre</option>
            </select>
          </div>
          <div class="form-group">
            <label>Message <span class="required">*</span></label>
            <textarea name="message" class="form-control" rows="6" required><?= h($_POST['message'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;">
            <i class="fas fa-paper-plane"></i> Envoyer le message
          </button>
        </form>
      </div>

      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
