<?php
/**
 * Recherche AJAX produits — GlisseShop
 * includes/search-ajax.php
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/functions.php';

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo '[]';
    exit;
}

$results = rechercherProduits($q, 8);
$out = array_map(function($p) {
    return [
        'id'    => (int)$p['id'],
        'nom'   => $p['nom'],
        'slug'  => $p['slug'],
        'prix'  => (float)$p['prix'],
        'image' => imageProduit($p['image_principale']),
    ];
}, $results);

echo json_encode($out, JSON_UNESCAPED_UNICODE);
