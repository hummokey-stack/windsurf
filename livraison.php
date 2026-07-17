<?php
require_once 'includes/functions.php';
$pageTitle = 'Livraison & Retours';
$settings  = getSettings();
$seuil = $settings['livraison_gratuite_montant'] ?? 100;
require_once 'includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb"><a href="index.php">Accueil</a><span>›</span><span>Livraison & Retours</span></nav>
    <h1>🚚 Livraison & Retours</h1>
  </div>
</div>

<div class="container" style="padding:50px 0 80px;">
  <div style="max-width:900px;margin:0 auto;">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-bottom:50px;">
      <div style="background:var(--light);border-radius:12px;padding:28px;border-left:4px solid var(--primary);">
        <h3 style="font-size:1.3rem;margin-bottom:16px;">🚚 Livraison standard</h3>
        <ul style="list-style:none;color:#555;">
          <li style="padding:6px 0;">✅ Délai : 3 à 5 jours ouvrés</li>
          <li style="padding:6px 0;">💶 Tarif : 9,90 €</li>
          <li style="padding:6px 0;">🎁 Gratuite dès <?= formatPrix($seuil) ?> d'achat</li>
          <li style="padding:6px 0;">📦 Transporteur : Colissimo, DPD</li>
        </ul>
      </div>
      <div style="background:var(--light);border-radius:12px;padding:28px;border-left:4px solid var(--accent);">
        <h3 style="font-size:1.3rem;margin-bottom:16px;">⚡ Livraison express</h3>
        <ul style="list-style:none;color:#555;">
          <li style="padding:6px 0;">✅ Délai : 24-48h ouvrées</li>
          <li style="padding:6px 0;">💶 Tarif : 19,90 €</li>
          <li style="padding:6px 0;">📋 Commande avant 12h</li>
          <li style="padding:6px 0;">📞 Sur devis pour gros colis</li>
        </ul>
      </div>
    </div>

    <div style="background:rgba(0,119,204,0.06);border-radius:12px;padding:28px;margin-bottom:40px;">
      <h3 style="font-size:1.3rem;margin-bottom:12px;">🏪 Retrait en magasin</h3>
      <p style="color:#555;">Retirez votre commande gratuitement dans notre showroom de Nice. Disponible sous 2h après confirmation.</p>
      <p style="color:#555;margin-top:8px;">
        <?php if ($settings['adresse']): ?><strong>Adresse :</strong> <?= h($settings['adresse']) ?><?php endif; ?>
        <?php if ($settings['horaires']): ?> — <?= h($settings['horaires']) ?><?php endif; ?>
      </p>
    </div>

    <h2 style="font-size:1.8rem;margin-bottom:20px;">🔄 Politique de retours</h2>
    <div style="color:#555;line-height:1.9;">
      <p><strong>Délai :</strong> 30 jours à compter de la réception pour retourner votre commande.</p>
      <p><strong>Conditions :</strong> Article en état d'origine, non utilisé, dans son emballage d'origine.</p>
      <p><strong>Procédure :</strong></p>
      <ol style="padding-left:22px;margin:12px 0;">
        <li>Contactez-nous par email ou téléphone avec votre numéro de commande</li>
        <li>Nous vous envoyons un bon de retour prépayé</li>
        <li>Déposez le colis à La Poste</li>
        <li>Remboursement sous 5 jours ouvrés après réception</li>
      </ol>
      <p><strong>Produits exclus :</strong> Articles personnalisés, produits d'hygiène (combinaisons portées), produits téléchargeables.</p>
      <p><strong>Défaut ou erreur :</strong> En cas d'article défectueux ou d'erreur de notre part, le retour est entièrement à notre charge.</p>
    </div>

    <div style="text-align:center;margin-top:40px;">
      <a href="contact.php" class="btn btn-primary">Une question ? Contactez-nous</a>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
