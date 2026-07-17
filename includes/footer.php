<?php
/**
 * Pied de page site public — GlisseShop
 * includes/footer.php
 */
if (!isset($settings)) $settings = getSettings();
$fb  = h($settings['facebook']  ?? '#');
$ig  = h($settings['instagram'] ?? '#');
?>
</main>

<footer id="footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Col 1 : Brand -->
      <div class="footer-col">
        <div class="footer-logo"><?= h($settings['site_name'] ?? 'Glisse') ?><span>Shop</span></div>
        <p style="margin-top:12px;"><?= h($settings['slogan'] ?? '') ?></p>
        <?php if (!empty($settings['email'])): ?>
          <p style="margin-top:10px;"><i class="fas fa-envelope" style="color:var(--secondary);margin-right:6px;"></i><?= h($settings['email']) ?></p>
        <?php endif; ?>
        <?php if (!empty($settings['telephone'])): ?>
          <p style="margin-top:6px;"><i class="fas fa-phone" style="color:var(--secondary);margin-right:6px;"></i><?= h($settings['telephone']) ?></p>
        <?php endif; ?>
        <div class="footer-socials">
          <?php if (!empty($settings['facebook'])): ?>
            <a href="<?= $fb ?>" class="footer-social-btn" target="_blank" rel="noopener" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          <?php endif; ?>
          <?php if (!empty($settings['instagram'])): ?>
            <a href="<?= $ig ?>" class="footer-social-btn" target="_blank" rel="noopener" title="Instagram"><i class="fab fa-instagram"></i></a>
          <?php endif; ?>
          <a href="#" class="footer-social-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>

      <!-- Col 2 : Navigation -->
      <div class="footer-col">
        <h4>Navigation</h4>
        <ul>
          <?php
          $cats = getCategoriesParentes();
          foreach (array_slice($cats, 0, 6) as $cat):
          ?>
            <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>categorie.php?slug=<?= h($cat['slug']) ?>"><i class="fas fa-chevron-right" style="font-size:0.7rem;margin-right:6px;"></i><?= h($cat['nom']) ?></a></li>
          <?php endforeach; ?>
          <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>promotions.php"><i class="fas fa-chevron-right" style="font-size:0.7rem;margin-right:6px;"></i>Promotions</a></li>
          <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>occasion.php"><i class="fas fa-chevron-right" style="font-size:0.7rem;margin-right:6px;"></i>Occasion</a></li>
        </ul>
      </div>

      <!-- Col 3 : Infos -->
      <div class="footer-col">
        <h4>Informations</h4>
        <ul>
          <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>a-propos.php"><i class="fas fa-chevron-right" style="font-size:0.7rem;margin-right:6px;"></i>À propos</a></li>
          <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>livraison.php"><i class="fas fa-chevron-right" style="font-size:0.7rem;margin-right:6px;"></i>Livraison & retours</a></li>
          <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>contact.php"><i class="fas fa-chevron-right" style="font-size:0.7rem;margin-right:6px;"></i>Contact</a></li>
          <li><a href="<?= isset($baseUrl) ? $baseUrl : '' ?>blog.php"><i class="fas fa-chevron-right" style="font-size:0.7rem;margin-right:6px;"></i>Blog</a></li>
          <li><a href="#"><i class="fas fa-chevron-right" style="font-size:0.7rem;margin-right:6px;"></i>CGV</a></li>
          <li><a href="#"><i class="fas fa-chevron-right" style="font-size:0.7rem;margin-right:6px;"></i>Mentions légales</a></li>
        </ul>
      </div>

      <!-- Col 4 : Newsletter + contact -->
      <div class="footer-col">
        <h4>Notre showroom</h4>
        <?php if (!empty($settings['adresse'])): ?>
          <p><i class="fas fa-map-marker-alt" style="color:var(--accent);margin-right:6px;"></i><?= h($settings['adresse']) ?></p>
        <?php endif; ?>
        <?php if (!empty($settings['horaires'])): ?>
          <p style="margin-top:8px;"><i class="fas fa-clock" style="color:var(--accent);margin-right:6px;"></i><?= h($settings['horaires']) ?></p>
        <?php endif; ?>
        <h4 style="margin-top:20px;">Newsletter</h4>
        <p style="font-size:0.82rem;margin-bottom:8px;">Recevez nos bons plans & conseils glisse !</p>
        <div class="newsletter-form">
          <input type="email" placeholder="votre@email.fr">
          <button type="button"><i class="fas fa-paper-plane"></i></button>
        </div>
      </div>

    </div><!-- /footer-grid -->

    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= h($settings['site_name'] ?? 'GlisseShop') ?> — Tous droits réservés</span>
      <span>
        <a href="#">CGV</a> &bull;
        <a href="#">Mentions légales</a> &bull;
        <a href="#">Politique de confidentialité</a>
      </span>
      <span>Paiement sécurisé 🔒 CB, Virement</span>
    </div>
  </div>
</footer>

<?php $jsBase = isset($baseUrl) ? $baseUrl : ''; ?>
<script src="<?= $jsBase ?>assets/js/main.js"></script>
</body>
</html>
