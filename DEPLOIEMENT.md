# Déploiement GlisseShop via FileZilla

## Prérequis hébergeur

- PHP 7.4 ou supérieur (PHP 8.0+ recommandé)
- Extension PDO activée
- Extension PDO_SQLite activée
- Support des fichiers `.htaccess` (Apache)
- Accès FTP fourni par l'hébergeur

> Pour vérifier les extensions PHP disponibles : créez un fichier `phpinfo.php` avec `<?php phpinfo(); ?>`, uploadez-le, consultez-le, puis supprimez-le.

---

## Étapes de déploiement

### 1. Ouvrir FileZilla et se connecter

| Champ        | Valeur                                |
|-------------|---------------------------------------|
| Hôte        | `ftp.votredomaine.com`               |
| Identifiant | (fourni par votre hébergeur)          |
| Mot de passe| (fourni par votre hébergeur)          |
| Port        | `21` (standard) ou `22` (SFTP)       |

### 2. Uploader les fichiers

1. Dans FileZilla, naviguez vers `public_html/` ou `www/` dans le panneau de droite
2. Sélectionnez **tout le contenu** du dossier `glisse-shop/` dans le panneau de gauche
3. Glissez-déposez (ou clic droit > Transférer)
4. Attendez la fin du transfert

### 3. Définir les permissions (chmod)

Clic droit sur le dossier → **Permissions du fichier...**

| Dossier/Fichier                | Permission | Octal |
|-------------------------------|------------|-------|
| `data/`                       | rwxr-xr-x  | 755   |
| `assets/images/uploads/`      | rwxr-xr-x  | 755   |
| Tous les fichiers `.php`      | rw-r--r--  | 644   |
| `.htaccess`                   | rw-r--r--  | 644   |

> Sur la plupart des hébergeurs mutualisés, 755 pour les dossiers et 644 pour les fichiers sont les permissions par défaut et suffisent.

### 4. Premier accès

1. Ouvrez votre navigateur : `https://votredomaine.com`
2. La base de données SQLite est **créée automatiquement** à la première visite
3. Le fichier `data/shop.db` est généré

### 5. Connexion à l'administration

- URL : `https://votredomaine.com/admin/`
- Login : `admin`
- Mot de passe : `admin123`

> ⚠️ **CHANGEZ LE MOT DE PASSE immédiatement** dans Admin > Paramètres !

---

## Configuration initiale

### Paramètres du site (Admin > Paramètres)

1. Uploadez votre logo
2. Renseignez le nom du site et le slogan
3. Ajoutez vos coordonnées (email, téléphone, adresse)
4. Définissez vos horaires
5. Ajoutez vos liens réseaux sociaux
6. Définissez le seuil de livraison gratuite

### Ajouter vos produits (Admin > Produits)

1. Cliquez sur "Nouveau produit"
2. Renseignez nom, catégorie, prix, stock
3. Uploadez des photos (JPG/PNG/WebP, max 2Mo)
4. Sauvegardez

---

## Structure des fichiers uploadés

```
public_html/
├── index.php               ← Page d'accueil
├── categorie.php           ← Pages catégories
├── produit.php             ← Fiches produits
├── panier.php              ← Panier
├── commande.php            ← Tunnel commande
├── blog.php                ← Blog
├── article.php             ← Articles
├── contact.php             ← Contact
├── a-propos.php            ← À propos
├── livraison.php           ← Livraison
├── promotions.php          ← Promotions
├── occasion.php            ← Occasion
├── .htaccess               ← Sécurité + redirections
├── admin/                  ← Interface admin
├── assets/                 ← CSS, JS, images
├── includes/               ← Code PHP partagé
└── data/                   ← Base SQLite (auto-créée)
```

---

## Compatibilité hébergeurs testée

| Hébergeur      | Compatible | Notes                        |
|----------------|------------|------------------------------|
| OVH            | ✅         | PHP 7.4+ disponible          |
| Infomaniak     | ✅         | Recommandé                   |
| o2switch       | ✅         | Excellent support PHP        |
| 1&1 / IONOS    | ✅         | Activer PDO_SQLite si besoin |
| Hostinger      | ✅         | Plan Business ou supérieur   |
| Free (perso)   | ⚠️         | SQLite parfois limité        |

---

## Résolution de problèmes

### "La base de données ne se crée pas"
- Vérifiez que le dossier `data/` est en permission 755
- Vérifiez que PDO et PDO_SQLite sont activés (via phpinfo)

### "Les images ne s'uploadent pas"
- Vérifiez que `assets/images/uploads/` est en permission 755
- Vérifiez la taille max upload PHP (`upload_max_filesize` dans php.ini)

### "Page blanche"
- Activez l'affichage des erreurs PHP temporairement : ajoutez en haut de `index.php` : `error_reporting(E_ALL); ini_set('display_errors', 1);`

### "Le .htaccess ne fonctionne pas"
- Vérifiez que mod_rewrite est activé sur votre hébergeur
- Sur certains hébergeurs, le .htaccess doit être dans le dossier racine du domaine

---

## Sécurité en production

1. ✅ Changez le mot de passe admin dans Paramètres
2. ✅ Activez HTTPS (décommentez les lignes dans .htaccess)
3. ✅ Vérifiez que `/data/` n'est pas accessible depuis un navigateur
4. ✅ Supprimez les fichiers de test éventuels (phpinfo.php, etc.)

---

*GlisseShop — Site e-commerce sports de glisse*
*Développé en PHP/SQLite vanilla — Déployable sur tout hébergeur mutualisé*
