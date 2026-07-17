<?php
/**
 * En-tête interface admin — GlisseShop
 * includes/admin-header.php
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
requireAdmin();

$settings = getSettings();
$adminLogin = h($_SESSION['admin_login'] ?? 'Admin');

// Compter les commandes en attente
$pdo = getDB();
$nbCommandesAttente = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut='en_attente'")->fetchColumn();

$currentPage = basename($_SERVER['PHP_SELF']);
function isAdminPage(string $page): string {
    global $currentPage;
    return $currentPage === $page ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($adminTitle) ? h($adminTitle) . ' — Admin' : 'Administration' ?> — <?= h($settings['site_name'] ?? 'GlisseShop') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="admin-body">

<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
      <div class="brand-name"><?= h($settings['site_name'] ?? 'Glisse') ?><span>Admin</span></div>
      <div class="brand-sub">Panneau d'administration</div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section">Principal</div>
      <ul>
        <li><a href="index.php" class="<?= isAdminPage('index.php') ?>"><span class="icon"><i class="fas fa-tachometer-alt"></i></span> Dashboard</a></li>
        <li><a href="../index.php" target="_blank"><span class="icon"><i class="fas fa-external-link-alt"></i></span> Voir le site</a></li>
      </ul>

      <div class="nav-section">Catalogue</div>
      <ul>
        <li><a href="produits.php" class="<?= isAdminPage('produits.php') ?>"><span class="icon"><i class="fas fa-box"></i></span> Produits</a></li>
        <li><a href="categories.php" class="<?= isAdminPage('categories.php') ?>"><span class="icon"><i class="fas fa-tags"></i></span> Catégories</a></li>
      </ul>

      <div class="nav-section">Ventes</div>
      <ul>
        <li>
          <a href="commandes.php" class="<?= isAdminPage('commandes.php') ?>">
            <span class="icon"><i class="fas fa-shopping-bag"></i></span> Commandes
            <?php if ($nbCommandesAttente > 0): ?>
              <span class="badge-nav"><?= (int)$nbCommandesAttente ?></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>

      <div class="nav-section">Contenu</div>
      <ul>
        <li><a href="blog-admin.php" class="<?= isAdminPage('blog-admin.php') ?>"><span class="icon"><i class="fas fa-newspaper"></i></span> Blog</a></li>
      </ul>

      <div class="nav-section">Paramètres</div>
      <ul>
        <li><a href="parametres.php" class="<?= isAdminPage('parametres.php') ?>"><span class="icon"><i class="fas fa-cog"></i></span> Paramètres</a></li>
      </ul>
    </nav>

    <div class="sidebar-footer">
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
  </aside>

  <!-- Main -->
  <div class="admin-main">
    <!-- Top bar -->
    <div class="admin-topbar">
      <div style="display:flex;align-items:center;gap:14px;">
        <button id="sidebarToggle" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:#555;">
          <i class="fas fa-bars"></i>
        </button>
        <div class="topbar-title"><?= isset($adminTitle) ? h($adminTitle) : 'Dashboard' ?></div>
      </div>
      <div class="topbar-actions">
        <a href="produit-form.php" class="btn-admin btn-admin-primary btn-admin-sm">
          <i class="fas fa-plus"></i> Nouveau produit
        </a>
        <div class="topbar-user">
          <div class="avatar"><?= strtoupper(substr($adminLogin, 0, 1)) ?></div>
          <span><?= $adminLogin ?></span>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="admin-content">
