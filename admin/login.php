<?php
/**
 * Page de connexion admin — GlisseShop
 * admin/login.php
 */
session_start();
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Déjà connecté → dashboard
if (isAdminLogged()) {
    redirect('index.php');
}

$error = '';
$settings = getSettings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || !verifyCsrf($_POST['csrf_token'])) {
        $error = 'Requête invalide. Veuillez réessayer.';
    } else {
        $login    = trim($_POST['login']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($login) || empty($password)) {
            $error = 'Veuillez renseigner votre identifiant et mot de passe.';
        } elseif (loginAdmin($login, $password)) {
            $redirect = $_GET['redirect'] ?? 'index.php';
            // Sécurité : redirect doit rester local
            if (!preg_match('/^[a-zA-Z0-9\/_\-\.]+$/', $redirect)) {
                $redirect = 'index.php';
            }
            redirect($redirect);
        } else {
            $error = 'Identifiant ou mot de passe incorrect.';
        }
    }
}

$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion Admin — <?= h($settings['site_name'] ?? 'GlisseShop') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="admin-body">

<div class="login-page">
  <div class="login-card">
    <div class="login-logo">
      <div class="name"><?= h($settings['site_name'] ?? 'GlisseShop') ?></div>
      <div class="sub">Interface d'administration</div>
    </div>

    <h2><i class="fas fa-lock" style="margin-right:8px;color:var(--admin-primary);"></i>Connexion</h2>

    <?php if ($error): ?>
      <div class="alert-admin alert-admin-danger">
        <i class="fas fa-exclamation-circle"></i> <?= h($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="login.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">

      <div class="admin-form-group">
        <label for="login">Identifiant</label>
        <div style="position:relative;">
          <i class="fas fa-user" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;"></i>
          <input type="text" id="login" name="login"
            class="admin-form-control"
            style="padding-left:38px;"
            value="<?= h($_POST['login'] ?? '') ?>"
            placeholder="admin"
            autocomplete="username"
            required>
        </div>
      </div>

      <div class="admin-form-group">
        <label for="password">Mot de passe</label>
        <div style="position:relative;">
          <i class="fas fa-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;"></i>
          <input type="password" id="password" name="password"
            class="admin-form-control"
            style="padding-left:38px;"
            placeholder="••••••••"
            autocomplete="current-password"
            required>
          <button type="button" id="togglePwd"
            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aaa;">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-admin btn-admin-primary btn-admin-lg" style="width:100%;justify-content:center;margin-top:8px;">
        <i class="fas fa-sign-in-alt"></i> Se connecter
      </button>
    </form>

    <div class="login-footer-text">
      <a href="../index.php"><i class="fas fa-arrow-left"></i> Retour au site</a>
    </div>
  </div>
</div>

<script>
document.getElementById('togglePwd').addEventListener('click', function() {
    const pwd = document.getElementById('password');
    const icon = this.querySelector('i');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        pwd.type = 'password';
        icon.className = 'fas fa-eye';
    }
});
</script>
</body>
</html>
