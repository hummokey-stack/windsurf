<?php
require_once 'includes/functions.php';
$pageTitle = 'À propos';
require_once 'includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb"><a href="index.php">Accueil</a><span>›</span><span>À propos</span></nav>
    <h1>Notre histoire</h1>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="expertise-layout">
      <div class="expertise-img">
        <img src="assets/images/placeholder.jpg" alt="Équipe GlisseShop" style="height:450px;object-fit:cover;filter:hue-rotate(200deg);">
      </div>
      <div class="expertise-text">
        <h2>GlisseShop, née d'une passion</h2>
        <p>Fondée en 2014 par une équipe de riders passionnés, GlisseShop est devenue en 10 ans la référence des sports de glisse nautique en région PACA.</p>
        <p>Notre showroom de 400m² à Nice accueille les amateurs et professionnels de wing, kite, surf, foil et windsurf. Nous stockons plus de 500 références en permanence, des meilleures marques mondiales.</p>
        <p>Notre équipe de 8 personnes est composée exclusivement de pratiquants actifs. Chaque conseil que nous donnons est fondé sur une expérience terrain réelle.</p>
        <div class="expertise-points">
          <div class="expertise-point"><div class="dot"></div><span>+1000 clients satisfaits depuis 2014</span></div>
          <div class="expertise-point"><div class="dot"></div><span>Showroom 400m² à Nice</span></div>
          <div class="expertise-point"><div class="dot"></div><span>8 riders experts au service de votre pratique</span></div>
          <div class="expertise-point"><div class="dot"></div><span>Partenaire officiel F-One, Naish, Duotone, Cabrinha</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-header">
      <h2>Nos engagements</h2>
      <div class="underline"></div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:30px;">
      <?php
      $engagements = [
          ['🎯', 'Expertise', 'Conseils personnalisés par des riders actifs pour choisir le matériel adapté à votre niveau et vos conditions.'],
          ['♻️', 'Occasion certifiée', 'Chaque produit d\'occasion est inspecté, réparé si nécessaire et vendu avec une garantie de satisfaction.'],
          ['🚀', 'Service premium', 'Livraison rapide, retours 30 jours, SAV réactif et assistance téléphonique 6j/7.'],
      ];
      foreach ($engagements as $eng): ?>
        <div style="background:white;border-radius:12px;padding:30px;box-shadow:0 2px 12px rgba(0,0,0,0.07);text-align:center;">
          <div style="font-size:2.5rem;margin-bottom:16px;"><?= $eng[0] ?></div>
          <h3 style="font-size:1.3rem;margin-bottom:12px;"><?= $eng[1] ?></h3>
          <p style="color:#666;font-size:0.92rem;line-height:1.7;"><?= $eng[2] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
