<?php
/**
 * Tunnel de commande — GlisseShop
 * commande.php
 */
require_once 'includes/functions.php';
$pageTitle = 'Commander';
$settings  = getSettings();
$livraisonSeuil = $settings['livraison_gratuite_montant'] ?? 100;
$livraisonFrais = 9.90;

$errors  = [];
$success = false;
$reference = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_commande'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Requête invalide.';
    } else {
        $prenom   = trim($_POST['prenom']   ?? '');
        $nom      = trim($_POST['nom']      ?? '');
        $email    = trim($_POST['email']    ?? '');
        $telephone= trim($_POST['telephone']?? '');
        $adresse  = trim($_POST['adresse']  ?? '');
        $cp       = trim($_POST['cp']       ?? '');
        $ville    = trim($_POST['ville']    ?? '');
        $articles_json = $_POST['articles_json'] ?? '[]';
        $sous_total = (float)($_POST['sous_total'] ?? 0);

        // Validation
        if (empty($prenom))  $errors[] = 'Le prénom est obligatoire.';
        if (empty($nom))     $errors[] = 'Le nom est obligatoire.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'Email invalide.';
        if (empty($adresse)) $errors[] = 'L\'adresse est obligatoire.';
        if (empty($cp) || !preg_match('/^\d{5}$/', $cp))
            $errors[] = 'Code postal invalide (5 chiffres).';
        if (empty($ville))   $errors[] = 'La ville est obligatoire.';

        // Valider articles JSON
        $articles = json_decode($articles_json, true);
        if (!is_array($articles) || empty($articles)) {
            $errors[] = 'Votre panier est vide.';
        }

        if (empty($errors)) {
            $frais_livraison = $sous_total >= $livraisonSeuil ? 0 : $livraisonFrais;
            $total = $sous_total + $frais_livraison;

            $adresse_complete = "$prenom $nom, $adresse, $cp $ville";
            $reference = genererReference();

            $pdo = getDB();
            $stmt = $pdo->prepare("INSERT INTO commandes
                (reference, client_nom, client_email, client_telephone, adresse_livraison,
                 articles, sous_total, frais_livraison, total, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')");
            $stmt->execute([
                $reference, "$prenom $nom", $email, $telephone, $adresse_complete,
                $articles_json, $sous_total, $frais_livraison, $total
            ]);

            // Email de confirmation (si disponible)
            if (function_exists('mail') && !empty($settings['email'])) {
                $subject = "Confirmation de commande - $reference";
                $body = "Bonjour $prenom,\n\nVotre commande $reference a bien été enregistrée.\nTotal : " . number_format($total, 2, ',', ' ') . " €\n\nMerci de votre confiance !\n\n" . ($settings['site_name'] ?? 'GlisseShop');
                @mail($email, $subject, $body, "From: " . ($settings['email'] ?? '') . "\r\nContent-Type: text/plain; charset=UTF-8");
            }

            $success = true;
        }
    }
}

$csrf = csrfToken();
require_once 'includes/header.php';
?>

<div class="page-hero" style="padding:40px 0;">
  <div class="container">
    <h1>💳 Commander</h1>
    <nav class="breadcrumb">
      <a href="index.php">Accueil</a><span>›</span>
      <a href="panier.php">Panier</a><span>›</span>
      <span>Commande</span>
    </nav>
  </div>
</div>

<div class="container">

<?php if ($success): ?>
  <!-- Confirmation -->
  <div style="text-align:center;padding:80px 20px;">
    <div style="font-size:5rem;margin-bottom:20px;">✅</div>
    <h2 style="font-size:2rem;color:var(--success);margin-bottom:10px;">Commande confirmée !</h2>
    <p style="font-size:1.1rem;color:#666;margin-bottom:8px;">
      Votre numéro de commande : <strong><?= h($reference) ?></strong>
    </p>
    <p style="color:#888;max-width:500px;margin:0 auto;">
      Un email de confirmation vous a été envoyé. Notre équipe vous contactera sous 24h pour finaliser votre commande.
    </p>
    <div style="margin-top:32px;display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
      <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
      <a href="contact.php" class="btn btn-outline">Nous contacter</a>
    </div>
    <script>localStorage.setItem('glisse_cart', '[]');</script>
  </div>

<?php else: ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="margin-top:20px;">
      <i class="fas fa-exclamation-circle"></i>
      <ul style="margin:0;padding-left:18px;"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <div class="order-layout">

    <!-- Formulaire -->
    <div class="form-step">
      <div class="step-indicator">
        <div class="step done">✓ Panier</div>
        <div class="step active">📋 Coordonnées</div>
        <div class="step">✅ Confirmation</div>
      </div>

      <form id="orderForm" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
        <input type="hidden" name="articles_json" id="articlesJson" value="">
        <input type="hidden" name="sous_total"   id="sousTotal"   value="0">
        <input type="hidden" name="valider_commande" value="1">

        <h3 style="font-size:1.2rem;margin-bottom:20px;">Vos coordonnées</h3>

        <div class="form-row">
          <div class="form-group">
            <label>Prénom <span class="required">*</span></label>
            <input type="text" name="prenom" class="form-control" value="<?= h($_POST['prenom'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label>Nom <span class="required">*</span></label>
            <input type="text" name="nom" class="form-control" value="<?= h($_POST['nom'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <input type="email" name="email" class="form-control" value="<?= h($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label>Téléphone</label>
            <input type="tel" name="telephone" class="form-control" value="<?= h($_POST['telephone'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label>Adresse <span class="required">*</span></label>
          <input type="text" name="adresse" class="form-control" value="<?= h($_POST['adresse'] ?? '') ?>" required placeholder="Numéro et nom de rue">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Code postal <span class="required">*</span></label>
            <input type="text" name="cp" class="form-control" value="<?= h($_POST['cp'] ?? '') ?>" maxlength="5" pattern="\d{5}" required>
          </div>
          <div class="form-group">
            <label>Ville <span class="required">*</span></label>
            <input type="text" name="ville" class="form-control" value="<?= h($_POST['ville'] ?? '') ?>" required>
          </div>
        </div>

        <div style="background:var(--light);border-radius:8px;padding:16px;margin:20px 0;font-size:0.88rem;">
          <strong><i class="fas fa-shield-alt" style="color:var(--primary);margin-right:6px;"></i>Paiement sécurisé</strong><br>
          <span style="color:#666;margin-top:4px;display:block;">Le paiement s'effectue par virement bancaire ou carte bancaire après confirmation par notre équipe.</span>
        </div>

        <button type="submit" class="btn btn-accent btn-lg" style="width:100%;justify-content:center;" id="btnSubmitOrder">
          <i class="fas fa-lock"></i> Valider ma commande
        </button>
      </form>
    </div>

    <!-- Récapitulatif -->
    <div class="order-recap">
      <h3 style="font-family:Rajdhani,sans-serif;font-size:1.15rem;margin-bottom:18px;">
        <i class="fas fa-shopping-bag" style="margin-right:8px;color:var(--primary);"></i>
        Votre commande
      </h3>
      <div id="orderItems"><!-- Rempli par JS --></div>
      <div style="padding-top:14px;margin-top:8px;border-top:1px solid var(--gray-light);">
        <div class="summary-row">
          <span>Sous-total</span>
          <span id="orderSubtotal">—</span>
        </div>
        <div class="summary-row">
          <span>Livraison</span>
          <span id="orderLivraison">—</span>
        </div>
        <div class="summary-row total">
          <span>Total</span>
          <span id="orderTotal">—</span>
        </div>
      </div>
      <div id="emptyCartWarning" style="display:none;text-align:center;padding:20px;color:#999;">
        <i class="fas fa-shopping-cart" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
        Votre panier est vide.<br>
        <a href="index.php" style="color:var(--primary);">Continuer mes achats</a>
      </div>
    </div>

  </div>

<?php endif; ?>
</div>

<script>
const SEUIL = <?= (float)$livraisonSeuil ?>;
const FRAIS = <?= (float)$livraisonFrais ?>;

function formatE(n) { return n.toFixed(2).replace('.', ',') + ' €'; }

function loadOrderRecap() {
    const cart = JSON.parse(localStorage.getItem('glisse_cart') || '[]');
    const itemsEl  = document.getElementById('orderItems');
    const emptyEl  = document.getElementById('emptyCartWarning');
    const btnEl    = document.getElementById('btnSubmitOrder');

    if (!cart.length) {
        if (itemsEl)  itemsEl.innerHTML = '';
        if (emptyEl)  emptyEl.style.display = 'block';
        if (btnEl)    btnEl.disabled = true;
        return;
    }
    if (emptyEl) emptyEl.style.display = 'none';

    let html = '';
    let subtotal = 0;
    cart.forEach(item => {
        const lt = item.prix * item.quantite;
        subtotal += lt;
        html += `<div class="recap-item">
            <img src="${item.image}" alt="${item.nom}" onerror="this.src='assets/images/placeholder.jpg'">
            <div class="recap-item-name">${item.nom}${item.variante ? ' (' + item.variante + ')' : ''}<br>
            <small style="color:#999;">x${item.quantite}</small></div>
            <span class="recap-item-price">${formatE(lt)}</span>
        </div>`;
    });

    if (itemsEl) itemsEl.innerHTML = html;

    const livraison = subtotal >= SEUIL ? 0 : FRAIS;
    const total = subtotal + livraison;

    const sub = document.getElementById('orderSubtotal');
    const liv = document.getElementById('orderLivraison');
    const tot = document.getElementById('orderTotal');
    const ajInput = document.getElementById('articlesJson');
    const stInput = document.getElementById('sousTotal');

    if (sub) sub.textContent = formatE(subtotal);
    if (liv) liv.textContent = livraison === 0 ? 'Gratuite' : formatE(livraison);
    if (tot) tot.textContent = formatE(total);
    if (ajInput) ajInput.value = JSON.stringify(cart);
    if (stInput) stInput.value = subtotal.toFixed(2);
}

document.addEventListener('DOMContentLoaded', loadOrderRecap);
</script>

<?php require_once 'includes/footer.php'; ?>
