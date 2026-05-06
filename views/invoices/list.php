<?php
$pageTitle    = 'Factures — FacturoPro';
$statusLabels = [
    'draft'     => 'Brouillon',
    'sent'      => 'Envoyée',
    'paid'      => 'Payée',
    'cancelled' => 'Annulée',
];
require __DIR__ . '/../layout/header.php';
?>
<main>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h1 style="margin:0;">Factures</h1>
        <a href="/invoices/create" class="btn btn-primary">+ Nouvelle facture</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="padding:16px 24px;margin-bottom:16px;">
        <form method="GET" action="/invoices" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div class="form-group" style="margin:0;min-width:160px;">
                <label>Statut</label>
                <select name="status">
                    <option value="">Tous</option>
                    <?php foreach ($statusLabels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($_GET['status'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:200px;">
                <label>Client</label>
                <select name="client_id">
                    <option value="">Tous les clients</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"
                            <?= (int) ($_GET['client_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
            <a href="/invoices" class="btn btn-secondary">Réinitialiser</a>
        </form>
    </div>

    <div class="card" style="padding:0;">
        <table>
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Client</th>
                    <th>Date émission</th>
                    <th>Échéance</th>
                    <th>Total TTC</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;color:#999;padding:32px;">
                            Aucune facture trouvée.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($inv['number'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($inv['client_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($inv['issue_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($inv['due_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= number_format((float) $inv['total_ttc'], 2, ',', ' ') ?> €</strong></td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($inv['status'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= $statusLabels[$inv['status']] ?? htmlspecialchars($inv['status'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <a href="/invoices/<?= (int) $inv['id'] ?>"
                                   class="btn btn-secondary"
                                   style="padding:5px 12px;font-size:12px;">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php require __DIR__ . '/../layout/footer.php'; ?>
