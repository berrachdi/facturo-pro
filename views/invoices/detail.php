<?php
$pageTitle    = 'Facture ' . htmlspecialchars($invoice['number'], ENT_QUOTES, 'UTF-8') . ' — FacturoPro';
$statusLabels = [
    'draft'     => 'Brouillon',
    'sent'      => 'Envoyée',
    'paid'      => 'Payée',
    'cancelled' => 'Annulée',
];
$methodLabels = [
    'virement' => 'Virement',
    'cheque'   => 'Chèque',
    'especes'  => 'Espèces',
    'carte'    => 'Carte',
];
$nextStatuses = [
    'draft'     => ['sent' => 'Marquer envoyée', 'cancelled' => 'Annuler'],
    'sent'      => ['paid' => 'Marquer payée',   'cancelled' => 'Annuler'],
    'paid'      => [],
    'cancelled' => [],
];
require __DIR__ . '/../layout/header.php';
?>
<main>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <h1 style="margin:0;">
                <?= htmlspecialchars($invoice['number'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <span class="badge badge-<?= htmlspecialchars($invoice['status'], ENT_QUOTES, 'UTF-8') ?>">
                <?= $statusLabels[$invoice['status']] ?? htmlspecialchars($invoice['status'], ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <?php foreach (($nextStatuses[$invoice['status']] ?? []) as $newStatus => $label): ?>
                <form method="POST"
                      action="/invoices/<?= (int) $invoice['id'] ?>/status"
                      style="display:inline;">
                    <input type="hidden" name="status"
                           value="<?= htmlspecialchars($newStatus, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit"
                            class="btn <?= $newStatus === 'cancelled' ? 'btn-danger' : 'btn-success' ?>"
                            style="font-size:13px;padding:7px 14px;">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </form>
            <?php endforeach; ?>

            <?php if ($invoice['status'] === 'draft'): ?>
                <a href="/invoices/<?= (int) $invoice['id'] ?>/edit" class="btn btn-secondary">
                    Modifier
                </a>
            <?php endif; ?>

            <a href="/invoices" class="btn btn-secondary">← Retour</a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Informations générales -->
    <div class="card">
        <h2>Informations</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
            <div>
                <div style="font-size:11px;color:#999;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">
                    Client
                </div>
                <strong><?= htmlspecialchars($invoice['client_name'], ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div>
                <div style="font-size:11px;color:#999;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">
                    Date d'émission
                </div>
                <strong><?= htmlspecialchars($invoice['issue_date'], ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div>
                <div style="font-size:11px;color:#999;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">
                    Date d'échéance
                </div>
                <strong><?= htmlspecialchars($invoice['due_date'], ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </div>

        <?php if (!empty($invoice['notes'])): ?>
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid #eef0f3;">
                <div style="font-size:11px;color:#999;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">
                    Notes
                </div>
                <p style="margin:0;color:#555;line-height:1.6;">
                    <?= nl2br(htmlspecialchars($invoice['notes'], ENT_QUOTES, 'UTF-8')) ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Lignes + totaux -->
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:20px 24px 0;">
            <h2>Lignes de facture</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:50%;">Description</th>
                    <th>Quantité</th>
                    <th>Prix unitaire HT</th>
                    <th>Total HT</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoice['lines'])): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;color:#999;padding:24px;">
                            Aucune ligne.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoice['lines'] as $line): ?>
                        <tr>
                            <td><?= htmlspecialchars($line['description'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($line['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= number_format((float) $line['unit_price'], 2, ',', ' ') ?> €</td>
                            <td><strong><?= number_format((float) $line['total'], 2, ',', ' ') ?> €</strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="display:flex;justify-content:flex-end;padding:16px 24px 20px;border-top:1px solid #eef0f3;">
            <div style="min-width:300px;">
                <div style="display:flex;justify-content:space-between;padding:7px 0;">
                    <span style="color:#555;">Total HT</span>
                    <strong><?= number_format((float) $invoice['total_ht'], 2, ',', ' ') ?> €</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:7px 0;">
                    <span style="color:#555;">TVA (<?= (float) $invoice['tva_rate'] ?>%)</span>
                    <strong>
                        <?= number_format(
                            (float) $invoice['total_ttc'] - (float) $invoice['total_ht'],
                            2, ',', ' '
                        ) ?> €
                    </strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;
                            border-top:2px solid #1e3a5f;margin-top:4px;">
                    <span style="color:#1e3a5f;font-weight:700;font-size:16px;">Total TTC</span>
                    <strong style="color:#1e3a5f;font-size:16px;">
                        <?= number_format((float) $invoice['total_ttc'], 2, ',', ' ') ?> €
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Paiements -->
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:20px 24px 12px;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="margin:0;">Paiements</h2>
            <div style="font-size:14px;">
                <?php if ($remaining > 0.01): ?>
                    <span style="color:#e74c3c;font-weight:600;">
                        Reste à payer : <?= number_format($remaining, 2, ',', ' ') ?> €
                    </span>
                <?php else: ?>
                    <span style="color:#27ae60;font-weight:600;">Soldée ✓</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($payments)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Méthode</th>
                        <th>Montant</th>
                        <th>Note</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars(substr($p['paid_at'], 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $methodLabels[$p['method']] ?? htmlspecialchars($p['method'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= number_format((float) $p['amount'], 2, ',', ' ') ?> €</strong></td>
                            <td style="color:#888;"><?= htmlspecialchars($p['note'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="text-align:center;">
                                <form method="POST"
                                      action="/invoices/<?= (int) $invoice['id'] ?>/payments/<?= (int) $p['id'] ?>/delete"
                                      onsubmit="return confirm('Supprimer ce paiement ?');">
                                    <button type="submit" class="btn btn-danger"
                                            style="padding:4px 10px;font-size:12px;">✕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display:flex;justify-content:flex-end;padding:12px 24px;
                        border-top:1px solid #eef0f3;background:#f9fafb;">
                <span style="font-size:14px;color:#555;">
                    Total encaissé :
                    <strong style="color:#27ae60;">
                        <?= number_format($totalPaid, 2, ',', ' ') ?> €
                    </strong>
                </span>
            </div>
        <?php else: ?>
            <p style="padding:16px 24px;color:#999;font-size:14px;margin:0;">Aucun paiement enregistré.</p>
        <?php endif; ?>

        <!-- Formulaire ajout paiement -->
        <?php if ($invoice['status'] !== 'cancelled' && $remaining > 0.01): ?>
            <div style="padding:20px 24px;border-top:2px dashed #eef0f3;background:#fafbfc;">
                <h2 style="margin-bottom:16px;font-size:15px;">Enregistrer un paiement</h2>
                <form method="POST"
                      action="/invoices/<?= (int) $invoice['id'] ?>/payments"
                      style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:12px;align-items:flex-end;">
                    <div class="form-group" style="margin:0;">
                        <label>Montant (€) *</label>
                        <input type="number" name="amount" step="0.01" min="0.01"
                               max="<?= number_format($remaining, 2, '.', '') ?>"
                               value="<?= number_format($remaining, 2, '.', '') ?>"
                               required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Date *</label>
                        <input type="date" name="paid_at"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Méthode *</label>
                        <select name="method" required>
                            <?php foreach ($methodLabels as $val => $label): ?>
                                <option value="<?= $val ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Note</label>
                        <input type="text" name="note" placeholder="Optionnel">
                    </div>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../layout/footer.php'; ?>
