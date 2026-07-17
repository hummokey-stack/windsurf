<?php
/**
 * Gestion des commandes — GlisseShop Admin
 * admin/commandes.php
 */
$adminTitle = 'Commandes';
require_once '../includes/admin-header.php';

$pdo = getDB();
$success = '';

// Changement de statut
if (isset($_POST['change_statut']) && is_numeric($_POST['commande_id'])) {
    if (!empty($_POST['csrf_token']) && verifyCsrf($_POST['csrf_token'])) {
        $statuts_valides = ['en_attente', 'confirmee', 'expediee', 'livree', 'annulee'];
        $newStatut = $_POST['nouveau_statut'] ?? '';
        if (in_array($newStatut, $statuts_valides)) {
            $pdo->prepare("UPDATE commandes SET statut=? WHERE id=?")
                ->execute([$newStatut, (int)$_POST['commande_id']]);
            $success = 'Statut mis à jour.';
        }
    }
}

// Filtres
$statutFil = $_GET['statut'] ?? '';
$where = [];
$params = [];
if (!empty($statutFil)) {
    $where[] = "statut=:statut";
    $params[':statut'] = $statutFil;
}
$sql = "SELECT * FROM commandes"
     . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY date_commande DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll();

$csrf = csrfToken();
$statuts = ['en_attente' => 'En attente', 'confirmee' => 'Confirmée', 'expediee' => 'Expédiée', 'livree' => 'Livrée', 'annulee' => 'Annulée'];
?>

<?php if ($success): ?>
  <div class="alert-admin alert-admin-success"><i class="fas fa-check-circle"></i> <?= h($success) ?></div>
<?php endif; ?>

<!-- Filtres statut -->
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
  <a href="commandes.php" class="btn-admin <?= !$statutFil ? 'btn-admin-primary' : 'btn-admin-outline' ?> btn-admin-sm">
    Toutes (<?= count($commandes) ?>)
  </a>
  <?php foreach ($statuts as $k => $v):
    $nb = $pdo->prepare("SELECT COUNT(*) FROM commandes WHERE statut=?");
    $nb->execute([$k]);
  ?>
    <a href="commandes.php?statut=<?= $k ?>" class="btn-admin <?= $statutFil === $k ? 'btn-admin-primary' : 'btn-admin-outline' ?> btn-admin-sm">
      <?= h($v) ?> (<?= $nb->fetchColumn() ?>)
    </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <div style="overflow-x:auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Référence</th>
          <th>Client</th>
          <th>Email</th>
          <th>Total</th>
          <th>Statut</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($commandes)): ?>
          <tr><td colspan="7" style="text-align:center;padding:40px;color:#aaa;">Aucune commande</td></tr>
        <?php else: ?>
          <?php foreach ($commandes as $cmd): ?>
            <?php $s = labelStatut($cmd['statut']); ?>
            <tr>
              <td><strong><?= h($cmd['reference']) ?></strong></td>
              <td><?= h($cmd['client_nom']) ?></td>
              <td><a href="mailto:<?= h($cmd['client_email']) ?>"><?= h($cmd['client_email']) ?></a></td>
              <td><strong><?= formatPrix((float)$cmd['total']) ?></strong></td>
              <td><span class="badge-admin <?= h($s['class']) ?>"><?= h($s['label']) ?></span></td>
              <td style="font-size:0.82rem;color:#999;"><?= formatDate($cmd['date_commande']) ?></td>
              <td>
                <button class="btn-admin btn-admin-outline btn-admin-sm"
                  onclick="openCommande(<?= htmlspecialchars(json_encode($cmd), ENT_QUOTES) ?>)">
                  <i class="fas fa-eye"></i> Voir
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal détail commande -->
<div class="modal-overlay" id="cmdModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Détail commande <span id="modalRef"></span></h3>
      <button class="modal-close" onclick="document.getElementById('cmdModal').classList.remove('active')">&times;</button>
    </div>
    <div class="modal-body" id="modalBody">
    </div>
  </div>
</div>

<?php
echo '</div></div></div>';
echo '<script>
const csrf = ' . json_encode($csrf) . ';
const statuts = ' . json_encode($statuts) . ';

function openCommande(cmd) {
    document.getElementById("modalRef").textContent = cmd.reference;
    const articles = JSON.parse(cmd.articles || "[]");
    let html = "";

    // Info client
    html += `<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
        <div><strong>Client</strong><br>${cmd.client_nom || "—"}</div>
        <div><strong>Email</strong><br><a href="mailto:${cmd.client_email}">${cmd.client_email}</a></div>
        <div><strong>Téléphone</strong><br>${cmd.client_telephone || "—"}</div>
        <div><strong>Date</strong><br>${cmd.date_commande}</div>
    </div>`;
    html += `<div style="margin-bottom:16px;"><strong>Adresse de livraison</strong><br>${cmd.adresse_livraison || "—"}</div>`;

    // Articles
    html += `<h4 style="margin-bottom:10px;font-family:Rajdhani,sans-serif;">Articles commandés</h4>`;
    html += `<table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
        <thead><tr style="background:#f4f6f9;">
            <th style="padding:8px;text-align:left;">Produit</th>
            <th style="padding:8px;text-align:right;">Qté</th>
            <th style="padding:8px;text-align:right;">Prix unit.</th>
            <th style="padding:8px;text-align:right;">Total</th>
        </tr></thead><tbody>`;
    articles.forEach(a => {
        html += `<tr style="border-bottom:1px solid #eee;">
            <td style="padding:8px;">${a.nom}${a.variante ? " (" + a.variante + ")" : ""}</td>
            <td style="padding:8px;text-align:right;">${a.quantite}</td>
            <td style="padding:8px;text-align:right;">${parseFloat(a.prix).toFixed(2)} €</td>
            <td style="padding:8px;text-align:right;font-weight:700;">${(a.quantite * a.prix).toFixed(2)} €</td>
        </tr>`;
    });
    html += `</tbody></table>`;
    html += `<div style="text-align:right;margin-top:12px;padding-top:12px;border-top:1px solid #eee;">
        <div>Sous-total : ${parseFloat(cmd.sous_total).toFixed(2)} €</div>
        <div>Livraison : ${parseFloat(cmd.frais_livraison).toFixed(2)} €</div>
        <div style="font-size:1.1rem;font-weight:700;color:var(--admin-primary);margin-top:6px;">TOTAL : ${parseFloat(cmd.total).toFixed(2)} €</div>
    </div>`;

    // Changement statut
    html += `<div style="margin-top:20px;padding-top:16px;border-top:1px solid #eee;">
        <form method="post" style="display:flex;gap:10px;align-items:center;">
            <input type="hidden" name="csrf_token" value="${csrf}">
            <input type="hidden" name="commande_id" value="${cmd.id}">
            <label style="font-weight:600;font-size:0.88rem;">Statut :</label>
            <select name="nouveau_statut" class="admin-form-control" style="flex:1;">
                ${Object.entries(statuts).map(([k,v]) => `<option value="${k}" ${cmd.statut===k?"selected":""}>${v}</option>`).join("")}
            </select>
            <button type="submit" name="change_statut" value="1" class="btn-admin btn-admin-primary btn-admin-sm">
                Mettre à jour
            </button>
        </form>
    </div>`;

    document.getElementById("modalBody").innerHTML = html;
    document.getElementById("cmdModal").classList.add("active");
}

document.getElementById("cmdModal").addEventListener("click", function(e) {
    if (e.target === this) this.classList.remove("active");
});
</script>';
echo '<script src="../assets/js/admin.js"></script>';
echo '</body></html>';
?>
