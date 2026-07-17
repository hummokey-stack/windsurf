<?php
/**
 * Vérification authentification admin — GlisseShop
 * includes/auth.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminLogged(): bool {
    return !empty($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
}

function requireAdmin(): void {
    if (!isAdminLogged()) {
        header('Location: ' . getAdminBase() . 'login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function getAdminBase(): string {
    // Détermine le chemin relatif vers admin/
    $script = $_SERVER['SCRIPT_FILENAME'] ?? '';
    if (strpos($script, '/admin/') !== false) {
        return '';
    }
    return 'admin/';
}

function loginAdmin(string $login, string $password): bool {
    require_once __DIR__ . '/db.php';
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE login=? LIMIT 1");
    $stmt->execute([$login]);
    $admin = $stmt->fetch();
    if (!$admin) return false;

    $hash = hash('sha256', $password);
    if (hash_equals($admin['password'], $hash)) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_regenerate_id(true);
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_login']  = $admin['login'];
        $_SESSION['admin_id']     = $admin['id'];
        return true;
    }
    return false;
}

function logoutAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION = [];
    session_destroy();
}
