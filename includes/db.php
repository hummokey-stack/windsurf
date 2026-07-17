<?php
/**
 * Connexion SQLite + initialisation de la base de données
 * GlisseShop — includes/db.php
 */

define('DB_PATH', __DIR__ . '/../data/shop.db');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA journal_mode=WAL');
            $pdo->exec('PRAGMA foreign_keys=ON');
            initDB($pdo);
        } catch (PDOException $e) {
            die('Erreur base de données : ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

function initDB(PDO $pdo): void {
    // Paramètres du site
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY,
        logo VARCHAR(255) DEFAULT 'assets/images/logo.png',
        site_name VARCHAR(255) DEFAULT 'GlisseShop',
        slogan TEXT DEFAULT 'Votre spécialiste wingsurf & sports de glisse',
        email VARCHAR(255),
        telephone VARCHAR(50),
        adresse TEXT,
        horaires TEXT,
        livraison_gratuite_montant DECIMAL(10,2) DEFAULT 100.00,
        facebook VARCHAR(255),
        instagram VARCHAR(255),
        meta_description TEXT
    )");

    // Catégories
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        parent_id INTEGER DEFAULT NULL,
        description TEXT,
        image VARCHAR(255),
        ordre INTEGER DEFAULT 0,
        actif INTEGER DEFAULT 1
    )");

    // Produits
    $pdo->exec("CREATE TABLE IF NOT EXISTS produits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        categorie_id INTEGER,
        marque VARCHAR(100),
        prix DECIMAL(10,2) NOT NULL,
        ancien_prix DECIMAL(10,2) DEFAULT NULL,
        description TEXT,
        description_courte TEXT,
        stock INTEGER DEFAULT 0,
        image_principale VARCHAR(255),
        images_secondaires TEXT,
        variantes TEXT,
        niveau VARCHAR(50),
        est_occasion INTEGER DEFAULT 0,
        est_nouveaute INTEGER DEFAULT 0,
        est_promo INTEGER DEFAULT 0,
        est_actif INTEGER DEFAULT 1,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Commandes
    $pdo->exec("CREATE TABLE IF NOT EXISTS commandes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        reference VARCHAR(50) UNIQUE,
        client_nom VARCHAR(255),
        client_email VARCHAR(255),
        client_telephone VARCHAR(50),
        adresse_livraison TEXT,
        articles TEXT,
        sous_total DECIMAL(10,2),
        frais_livraison DECIMAL(10,2),
        total DECIMAL(10,2),
        statut VARCHAR(50) DEFAULT 'en_attente',
        date_commande DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Blog
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titre VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE,
        contenu TEXT,
        extrait TEXT,
        image VARCHAR(255),
        categorie VARCHAR(100),
        actif INTEGER DEFAULT 1,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Admin
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
        id INTEGER PRIMARY KEY,
        login VARCHAR(100) DEFAULT 'admin',
        password VARCHAR(255)
    )");

    // ---- Données initiales ----
    insertDefaultData($pdo);
}

function insertDefaultData(PDO $pdo): void {
    // Settings
    $check = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO settings (id, site_name, slogan, email, telephone, adresse, horaires, livraison_gratuite_montant, facebook, instagram, meta_description)
        VALUES (1, 'GlisseShop', 'Votre spécialiste wingsurf & sports de glisse', 'contact@glisseshop.fr', '04 00 00 00 00',
        '123 Promenade des Sports, 06000 Nice', 'Lun-Sam 9h-19h • Dim 10h-17h', 100.00,
        'https://facebook.com/glisseshop', 'https://instagram.com/glisseshop',
        'GlisseShop - Votre spécialiste wingsurf, kitesurf, surf et sports de glisse. Matériel neuf et occasion.')");
    }

    // Admin (mot de passe : admin123)
    $check = $pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn();
    if ($check == 0) {
        $pwd = hash('sha256', 'admin123');
        $pdo->exec("INSERT INTO admin (id, login, password) VALUES (1, 'admin', '$pwd')");
    }

    // Catégories
    $check = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($check == 0) {
        $cats = [
            [1, 'Wing', 'wing', null, 'Tout le matériel de wingsurf : ailes, boards, foils et accessoires', 1],
            [2, 'Surf', 'surf', null, 'Planches de surf, leash, pad et accessoires de surf', 2],
            [3, 'Foil', 'foil', null, 'Foils, mâts, fuselages et ailes de foil toutes disciplines', 3],
            [4, 'Kitesurf', 'kitesurf', null, 'Cerfs-volants, boards kite et accessoires kitesurf', 4],
            [5, 'Windsurf', 'windsurf', null, 'Planches, voiles, mâts et wishbones de windsurf', 5],
            [6, 'SUP', 'sup', null, 'Stands up paddle gonflables et rigides, pagaies', 6],
            [7, 'Wakeboard', 'wakeboard', null, 'Wakeboards, fixations et accessoires nautiques', 7],
            [8, 'eFoil', 'efoil', null, 'Planches électriques hydrofoil pour une glisse sans vent', 8],
            [9, 'Néoprène', 'neoprene', null, 'Combinaisons néoprène, chaussons, gants et capuches', 9],
            [10, 'Lifestyle', 'lifestyle', null, 'Vêtements, bags, accessoires et life style nautique', 10],
            [11, 'Snowboard', 'snowboard', null, 'Snowboards, fixations et boots pour la glisse des pistes', 11],
        ];
        $stmt = $pdo->prepare("INSERT INTO categories (id, nom, slug, parent_id, description, ordre, actif) VALUES (?, ?, ?, NULL, ?, ?, 1)");
        foreach ($cats as $c) {
            $stmt->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5]]);
        }

        // Sous-catégories Wing
        $subcats = [
            ['Ailes Wing', 'wing-ailes', 1, 'Toutes les ailes de wingsurf', 1],
            ['Boards Wing', 'wing-boards', 1, 'Planches dédiées au wingsurf', 2],
            ['Foils Wing', 'wing-foils', 1, 'Foils complets et pièces pour wingsurf', 3],
            ['Packs Wing', 'wing-packs', 1, 'Packs complets pour débuter ou progresser en wing', 4],
            ['Accessoires Wing', 'wing-accessoires', 1, 'Leash, sac, pompe et tous les accessoires wing', 5],
        ];
        $stmt2 = $pdo->prepare("INSERT INTO categories (nom, slug, parent_id, description, ordre, actif) VALUES (?, ?, ?, ?, ?, 1)");
        foreach ($subcats as $sc) {
            $stmt2->execute([$sc[0], $sc[1], $sc[2], $sc[3], $sc[4]]);
        }
    }

    // Produits exemples
    $check = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
    if ($check == 0) {
        $produits = [
            [
                'Aile Wing F-One Swing V3 5m',
                'aile-wing-f-one-swing-v3-5m',
                1, 'F-One', 849.00, 999.00,
                '<p>L\'aile Wing F-One Swing V3 5m est la référence du marché pour les riders intermédiaires à experts. Sa structure rigide garantit une transmission de puissance optimale tout en conservant une excellente maniabilité.</p><h3>Points forts</h3><ul><li>Structure aérodynamique optimisée</li><li>Poignées ergonomiques anti-fatigue</li><li>Fenêtre de visibilité transparente</li><li>Compatible tous foils</li></ul>',
                'Aile de wingsurf haut de gamme, légère et puissante. Idéale intermédiaire/expert.',
                8, null,
                json_encode(['5m', '6m', '7m']),
                'intermediaire', 0, 1, 1
            ],
            [
                'Board Wing Naish Hover 5\'4"',
                'board-wing-naish-hover-5-4',
                2, 'Naish', 1299.00, null,
                '<p>La Naish Hover 5\'4" est une board de wingsurf ultra-performante taillée pour la vitesse et les manœuvres. Son shape compact et son faible volume en font une planche de référence pour les riders confirmés.</p><h3>Caractéristiques</h3><ul><li>Volume : 85L</li><li>Construction carbone</li><li>4 inserts pour foil</li><li>Pad EVA intégré</li></ul>',
                'Board wing carbone ultra légère pour riders confirmés. Volume 85L.',
                5, null,
                json_encode(['5\'4" 85L', '5\'8" 95L', '6\'0" 105L']),
                'expert', 0, 1, 0
            ],
            [
                'Combinaison Rip Curl E-Bomb 4/3mm',
                'combinaison-rip-curl-e-bomb-4-3mm',
                9, 'Rip Curl', 389.00, 459.00,
                '<p>La combinaison Rip Curl E-Bomb 4/3mm est la combinaison néoprène la plus flexible du marché. Conçue pour les sessions longues en eaux fraîches, elle offre un confort et une liberté de mouvement incomparables.</p><h3>Technologie</h3><ul><li>Néoprène E5 ultra-stretch</li><li>Zip dorsal anti-fuite</li><li>Coutures soudées intérieures</li><li>Doublure polaire au torse</li></ul>',
                'Combinaison 4/3mm ultra-flexible pour wingsurf et surf. Modèle hiver.',
                12, null,
                json_encode(['XS', 'S', 'MS', 'M', 'MT', 'L', 'XL']),
                'tous', 0, 0, 1
            ],
            [
                'Pack Wing Débutant Duotone',
                'pack-wing-debutant-duotone',
                4, 'Duotone', 1890.00, 2200.00,
                '<p>Le Pack Wing Débutant Duotone est la solution clé en main pour se lancer dans le wingsurf. Ce pack inclut une aile 6m stable et facile à manier, une board volume 120L parfaite pour l\'apprentissage.</p><h3>Contenu du pack</h3><ul><li>Aile Duotone Unit 6m</li><li>Board 120L + leash</li><li>Foil d\'initiation complet</li><li>Pompe haute pression</li></ul>',
                'Pack complet wing + board + foil pour débutants. Tout pour commencer !',
                3, null,
                json_encode(['Pack S (aile 5m)', 'Pack M (aile 6m)', 'Pack L (aile 7m)']),
                'debutant', 0, 1, 1
            ],
            [
                'SUP Gonflable Red Paddle 10\'6"',
                'sup-gonflable-red-paddle-10-6',
                6, 'Red Paddle', 749.00, null,
                '<p>Le SUP gonflable Red Paddle 10\'6" est la référence mondiale en stand up paddle gonflable. Sa technologie MSL garantit une rigidité optimale et une durabilité exceptionnelle pour des années de glisse.</p><h3>Caractéristiques</h3><ul><li>Longueur : 10\'6" (320cm)</li><li>Largeur : 32" (81cm)</li><li>Volume : 260L</li><li>Poids : 9.5kg</li></ul>',
                'SUP gonflable premium 10\'6" pour débutants et randonnée. Sac + pagaie inclus.',
                15, null,
                json_encode(["10'6\" All Round", "11'0\" Explorer"]),
                'tous', 0, 1, 0
            ],
            [
                'Planche de Surf DHD DX1 6\'2" Occasion',
                'planche-surf-dhd-dx1-6-2-occasion',
                2, 'DHD', 399.00, 699.00,
                '<p>Planche de surf DHD DX1 6\'2" en très bon état. Quelques légères marques d\'usure cosmétiques, aucun impact structurel. Livrée avec 3 ailerons FCS II et leash 6\' offert.</p><h3>État</h3><ul><li>Note : 8/10</li><li>Volume : 32L</li><li>Quelques micro-impacts rails</li><li>Deck propre, pas de délaminage</li></ul>',
                'Surf DHD DX1 6\'2" très bon état. 3 ailerons FCS II + leash offert.',
                1, null,
                json_encode(['6\'2" 32L']),
                'intermediaire', 1, 0, 0
            ],
        ];

        $stmt = $pdo->prepare("INSERT INTO produits
            (nom, slug, categorie_id, marque, prix, ancien_prix, description, description_courte, stock, image_principale, variantes, niveau, est_occasion, est_nouveaute, est_promo, est_actif)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($produits as $p) {
            $stmt->execute($p);
        }

        // Articles de blog
        $articles = [
            [
                'Guide débutant : Comment choisir son premier kit de wingsurf ?',
                'guide-debutant-choisir-kit-wingsurf',
                '<p>Le wingsurf est l\'une des disciplines les plus excitantes des sports nautiques modernes. Dans ce guide complet, nous vous expliquons comment choisir votre premier kit.</p><h2>L\'aile : le cœur du système</h2><p>Pour débuter, privilégiez une aile de taille moyenne entre 5m et 7m selon votre poids et les conditions de votre spot. Une aile trop petite manquera de puissance, une aile trop grande sera difficile à contrôler.</p><h2>La board : le volume avant tout</h2><p>Pour les débutants, une board de 110 à 130 litres est idéale. Plus de volume = plus de stabilité = plus facile à tenir debout et à décoller.</p><h2>Le foil : patient ou direct ?</h2><p>Certains choisissent de débuter sans foil sur une board surf/SUP pour maîtriser d\'abord l\'aile. C\'est une approche valide qui raccourcit la courbe d\'apprentissage globale.</p>',
                'Tout ce qu\'il faut savoir pour choisir son premier kit de wingsurf : aile, board, foil et budget.',
                null, 'Wingsurf', 1
            ],
            [
                'Top 5 des spots de wingsurf en France',
                'top-5-spots-wingsurf-france',
                '<p>La France regorge de spots exceptionnels pour pratiquer le wingsurf. Voici notre sélection des 5 meilleurs endroits où rider.</p><h2>1. La Torche, Bretagne</h2><p>Le spot mythique de la côte Atlantique. Vent régulier, vagues et une communauté de riders dynamique. Idéal de mars à octobre.</p><h2>2. Le Brusc, Méditerranée</h2><p>Dans le Var, ce spot bénéficie du mistral pour des sessions garanties. Eau plate pour les débutants et vagues côtières pour les freeriders.</p><h2>3. Leucate, Aude</h2><p>La Mecque du glisse en France. Spot polyvalent avec un vent de tramontane puissant et fiable toute l\'année.</p><h2>4. Hossegor, Landes</h2><p>Pour les amateurs de gros frissons. Les vagues d\'Hossegor offrent un terrain de jeu unique pour les riders avancés.</p><h2>5. L\'Almanarre, Hyères</h2><p>Spot familial et accessible, parfait pour apprendre dans de bonnes conditions de vent et des eaux peu profondes.</p>',
                'Découvrez les 5 meilleurs spots de wingsurf en France pour votre prochaine session.',
                null, 'Spots', 1
            ],
            [
                'Entretien de votre combinaison néoprène : les bons gestes',
                'entretien-combinaison-neoprene',
                '<p>Une combinaison néoprène bien entretenue durera bien plus longtemps. Voici les gestes essentiels après chaque session.</p><h2>Le rinçage à l\'eau douce</h2><p>C\'est le geste le plus important. Rincez toujours votre combi à l\'eau douce après chaque session en mer. Le sel et le chlore dégradent rapidement le néoprène.</p><h2>Le séchage</h2><p>Retournez la combinaison et séchez-la à l\'ombre, jamais en plein soleil. Les UV détruisent le néoprène et les coutures.</p><h2>Le stockage</h2><p>Rangez votre combi à plat ou sur un cintre épais. Ne la pliez jamais sur elle-même au niveau des genoux ou des coudes sous peine de marques permanentes.</p><h2>L\'entretien des fermetures éclair</h2><p>Lubrifiez régulièrement les zips avec un crayon de cire ou un produit spécifique. Une fermeture qui coince est une fermeture qui va casser.</p>',
                'Conseils pratiques pour entretenir et prolonger la durée de vie de votre combinaison néoprène.',
                null, 'Conseils', 1
            ],
        ];

        $stmtBlog = $pdo->prepare("INSERT INTO blog (titre, slug, contenu, extrait, image, categorie, actif) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($articles as $a) {
            $stmtBlog->execute($a);
        }
    }
}
