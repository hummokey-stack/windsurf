<?php
/**
 * Fonctions utilitaires — GlisseShop
 * includes/functions.php
 */

require_once __DIR__ . '/db.php';

/**
 * Génère un slug propre depuis un texte
 */
function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $chars = [
        'à'=>'a','â'=>'a','ä'=>'a','á'=>'a','ã'=>'a',
        'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
        'ì'=>'i','î'=>'i','ï'=>'i','í'=>'i',
        'ò'=>'o','ô'=>'o','ö'=>'o','ó'=>'o','õ'=>'o',
        'ù'=>'u','û'=>'u','ü'=>'u','ú'=>'u',
        'ç'=>'c','ñ'=>'n','ý'=>'y','ÿ'=>'y',
        '&'=>'et',
    ];
    $text = strtr($text, $chars);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return trim($text, '-');
}

/**
 * Récupère les paramètres du site
 */
function getSettings(): array {
    $pdo = getDB();
    $s = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch();
    return $s ?: [];
}

/**
 * Formate un prix en euros
 */
function formatPrix(float $prix): string {
    return number_format($prix, 2, ',', ' ') . ' €';
}

/**
 * Calcule le pourcentage de réduction
 */
function calculReduction(float $ancien, float $nouveau): int {
    if ($ancien <= 0) return 0;
    return (int) round((($ancien - $nouveau) / $ancien) * 100);
}

/**
 * Récupère toutes les catégories actives (parent uniquement)
 */
function getCategoriesParentes(): array {
    $pdo = getDB();
    return $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL AND actif=1 ORDER BY ordre ASC")->fetchAll();
}

/**
 * Récupère les sous-catégories d'une catégorie parente
 */
function getSousCategories(int $parentId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id=? AND actif=1 ORDER BY ordre ASC");
    $stmt->execute([$parentId]);
    return $stmt->fetchAll();
}

/**
 * Récupère une catégorie par son slug
 */
function getCategorieBySlug(string $slug): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug=? AND actif=1");
    $stmt->execute([$slug]);
    $r = $stmt->fetch();
    return $r ?: null;
}

/**
 * Récupère un produit par son ID
 */
function getProduitById(int $id): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug
        FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id
        WHERE p.id=? AND p.est_actif=1");
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    return $r ?: null;
}

/**
 * Récupère un produit par son slug
 */
function getProduitBySlug(string $slug): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug
        FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id
        WHERE p.slug=? AND p.est_actif=1");
    $stmt->execute([$slug]);
    $r = $stmt->fetch();
    return $r ?: null;
}

/**
 * Récupère les produits d'une catégorie
 */
function getProduitsByCategorie(int $categorieId, array $options = []): array {
    $pdo = getDB();
    $where = ["p.est_actif=1", "(p.categorie_id=:cat_id OR c.parent_id=:cat_id2)"];
    $params = [':cat_id' => $categorieId, ':cat_id2' => $categorieId];

    if (!empty($options['marque'])) {
        $where[] = "p.marque=:marque";
        $params[':marque'] = $options['marque'];
    }
    if (!empty($options['prix_min'])) {
        $where[] = "p.prix>=:prix_min";
        $params[':prix_min'] = $options['prix_min'];
    }
    if (!empty($options['prix_max'])) {
        $where[] = "p.prix<=:prix_max";
        $params[':prix_max'] = $options['prix_max'];
    }
    if (!empty($options['niveau'])) {
        $where[] = "p.niveau=:niveau";
        $params[':niveau'] = $options['niveau'];
    }

    $orderBy = 'p.date_creation DESC';
    if (!empty($options['tri'])) {
        switch ($options['tri']) {
            case 'prix_asc':  $orderBy = 'p.prix ASC'; break;
            case 'prix_desc': $orderBy = 'p.prix DESC'; break;
            case 'promo':     $orderBy = 'p.est_promo DESC, p.prix ASC'; break;
        }
    }

    $sql = "SELECT p.*, c.nom as categorie_nom
        FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $orderBy";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Récupère les derniers produits
 */
function getDerniersProduitsNouveautes(int $limite = 4): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE est_actif=1 AND est_nouveaute=1 ORDER BY date_creation DESC LIMIT ?");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

/**
 * Récupère les produits en promo
 */
function getProduitsPromo(int $limite = 8): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE est_actif=1 AND est_promo=1 ORDER BY date_creation DESC LIMIT ?");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

/**
 * Récupère les produits d'occasion
 */
function getProduitsOccasion(int $limite = 12): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE est_actif=1 AND est_occasion=1 ORDER BY date_creation DESC LIMIT ?");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

/**
 * Récupère les produits similaires (même catégorie)
 */
function getProduitsSimilaires(int $produitId, int $categorieId, int $limite = 4): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE est_actif=1 AND categorie_id=? AND id!=? ORDER BY RANDOM() LIMIT ?");
    $stmt->execute([$categorieId, $produitId, $limite]);
    return $stmt->fetchAll();
}

/**
 * Récupère les marques disponibles dans une catégorie
 */
function getMarquesParCategorie(int $categorieId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT DISTINCT marque FROM produits WHERE categorie_id=? AND est_actif=1 AND marque IS NOT NULL AND marque!='' ORDER BY marque");
    $stmt->execute([$categorieId]);
    return array_column($stmt->fetchAll(), 'marque');
}

/**
 * Recherche de produits
 */
function rechercherProduits(string $q, int $limite = 10): array {
    $pdo = getDB();
    $q = '%' . $q . '%';
    $stmt = $pdo->prepare("SELECT id, nom, slug, prix, image_principale FROM produits
        WHERE est_actif=1 AND (nom LIKE ? OR marque LIKE ? OR description_courte LIKE ?)
        LIMIT ?");
    $stmt->execute([$q, $q, $q, $limite]);
    return $stmt->fetchAll();
}

/**
 * Récupère les articles de blog
 */
function getDerniersBlogArticles(int $limite = 3): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM blog WHERE actif=1 ORDER BY date_creation DESC LIMIT ?");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

/**
 * Récupère un article de blog par son slug
 */
function getBlogArticleBySlug(string $slug): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM blog WHERE slug=? AND actif=1");
    $stmt->execute([$slug]);
    $r = $stmt->fetch();
    return $r ?: null;
}

/**
 * Génère une référence de commande unique
 */
function genererReference(): string {
    return 'GS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
}

/**
 * Retourne le chemin de l'image produit ou le placeholder
 */
function imageProduit(?string $image, string $base = ''): string {
    if (!empty($image) && file_exists(__DIR__ . '/../' . $image)) {
        return $base . $image;
    }
    // Essayer JPG d'abord, sinon SVG
    if (file_exists(__DIR__ . '/../assets/images/placeholder.jpg')) {
        return $base . 'assets/images/placeholder.jpg';
    }
    return $base . 'assets/images/placeholder.svg';
}

/**
 * Génère un token CSRF et le stocke en session
 */
function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 */
function verifyCsrf(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Échappe une valeur pour l'affichage HTML
 */
function h(?string $str): string {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redirige vers une URL
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Vérifie si une extension de fichier est une image autorisée
 */
function isImageAutorisee(string $filename): bool {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
}

/**
 * Upload d'une image avec validation
 */
function uploadImage(array $file, string $prefix = 'img'): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > 2 * 1024 * 1024) return false; // max 2Mo
    if (!isImageAutorisee($file['name'])) return false;

    // Vérification MIME réelle
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) return false;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = __DIR__ . '/../assets/images/uploads/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'assets/images/uploads/' . $filename;
    }
    return false;
}

/**
 * Génère le breadcrumb d'une catégorie
 */
function breadcrumbCategorie(array $categorie): string {
    $html = '<nav class="breadcrumb"><a href="index.php">Accueil</a>';
    if ($categorie['parent_id']) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
        $stmt->execute([$categorie['parent_id']]);
        $parent = $stmt->fetch();
        if ($parent) {
            $html .= ' <span>›</span> <a href="categorie.php?slug=' . h($parent['slug']) . '">' . h($parent['nom']) . '</a>';
        }
    }
    $html .= ' <span>›</span> <span>' . h($categorie['nom']) . '</span></nav>';
    return $html;
}

/**
 * Retourne le label du niveau
 */
function labelNiveau(?string $niveau): string {
    $niveaux = [
        'debutant'     => 'Débutant',
        'intermediaire'=> 'Intermédiaire',
        'expert'       => 'Expert',
        'tous'         => 'Tous niveaux',
    ];
    return $niveaux[$niveau] ?? 'Tous niveaux';
}

/**
 * Retourne le label du statut de commande
 */
function labelStatut(string $statut): array {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'badge-warning'],
        'confirmee'  => ['label' => 'Confirmée',  'class' => 'badge-info'],
        'expediee'   => ['label' => 'Expédiée',   'class' => 'badge-primary'],
        'livree'     => ['label' => 'Livrée',      'class' => 'badge-success'],
        'annulee'    => ['label' => 'Annulée',     'class' => 'badge-danger'],
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'badge-secondary'];
}

/**
 * Formate une date en français
 */
function formatDate(string $date): string {
    $mois = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $ts = strtotime($date);
    return date('d', $ts) . ' ' . $mois[(int)date('m', $ts)] . ' ' . date('Y', $ts);
}
