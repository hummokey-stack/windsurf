<?php
/**
 * Génère une image placeholder SVG
 * Accessible via assets/images/placeholder.jpg (si redirigé)
 * ou directement via placeholder.php
 */
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');
?>
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">
  <rect width="400" height="300" fill="#f0f4f8"/>
  <rect x="150" y="90" width="100" height="80" rx="8" fill="#cbd5e0"/>
  <circle cx="200" cy="75" r="20" fill="#a0aec0"/>
  <path d="M155 170 L245 170 L230 130 L215 155 L205 140 L190 165 L175 150 Z" fill="#a0aec0"/>
  <text x="200" y="215" font-family="Arial,sans-serif" font-size="14" fill="#718096" text-anchor="middle">Image produit</text>
  <text x="200" y="235" font-family="Arial,sans-serif" font-size="11" fill="#a0aec0" text-anchor="middle">400 × 300</text>
</svg>
