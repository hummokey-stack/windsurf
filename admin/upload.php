<?php
/**
 * Endpoint upload image AJAX — GlisseShop Admin
 * admin/upload.php
 */
header('Content-Type: application/json');
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isAdminLogged()) {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

if (empty($_FILES['image'])) {
    echo json_encode(['error' => 'Aucun fichier reçu']);
    exit;
}

$result = uploadImage($_FILES['image'], 'upload');
if ($result) {
    echo json_encode(['success' => true, 'path' => $result, 'url' => '../' . $result]);
} else {
    echo json_encode(['error' => 'Fichier invalide. Formats autorisés : JPG, PNG, WebP. Taille max : 2Mo.']);
}
