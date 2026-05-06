<?php
$isEdit    = isset($invoice);
$pageTitle = $isEdit ? 'Modifier la facture — FacturoPro' : 'Nouvelle facture — FacturoPro';
require __DIR__ . '/../layout/header.php';

$val = fn(string $key, mixed $default = '') => $old[$key] ?? $default;
$existingLines = $old['lines'] ?? [];
?>
<main>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h1 style="margin:0;"><?= $isEdit ? 'Modifier la facture' : 'Nouvelle facture' ?></h1>
        <a href="<?= $isEdit ? '/invoices/' . (int) $invoice['id'] : '/invoices' ?>" class="btn btn-secondary">
            Annuler
        </a>
    </div>

    <?php if (!empty($errors['lines'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($errors['lines'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST"
          action="<?= $isEdit ? '/invoices/' . (int) $invoice['id'] . '/edit' : '/invoices/create' ?>">

        <!-- Informations générales -->
        <div class="card">
            <h2>Informations générales</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <div class="form-group">
                    <label>Client *</label>
                    <select name="client_id">
                        <option value="">— Sélectionnez un client —</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= (int) $c['id'] ?>"
                                <?= (int) $val('client_id') === (int) $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['client_id'])): ?>
                        <span class="field-error">
                            <?= htmlspecialchars($errors['client_id'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Taux TVA (%)</label>
                    <select name="tva_rate" id="tva_rate">
                        <?php foreach (['0', '5.5', '10', '20'] as $rate): ?>
                            <option value="<?= $rate ?>"
                                <?= (string) $val('tva_rate', '20') === $rate ? 'selected' : '' ?>>
                                <?= $rate ?> %
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date d'émission *</label>
                    <input type="date" name="issue_date"
                           value="<?= htmlspecialchars($val('issue_date', date('Y-m-d')), ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['issue_date'])): ?>
                        <span class="field-error">
                            <?= htmlspecialchars($errors['issue_date'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Date d'échéance *</label>
                    <input type="date" name="due_date"
                           value="<?= htmlspecialchars(
                               $val('due_date', date('Y-m-d', strtotime('+30 days'))),
                               ENT_QUOTES, 'UTF-8'
                           ) ?>">
                    <?php if (!empty($errors['due_date'])): ?>
                        <span class="field-error">
                            <?= htmlspecialchars($errors['due_date'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label>Notes</label>
                <textarea name="notes" rows="3"><?= htmlspecialchars($val('notes'), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>

        <!-- Lignes de facture -->
        <div class="card">
            <h2>Lignes de facture</h2>

            <table id="lines-table" style="margin-bottom:16px;">
                <thead>
                    <tr>
                        <th style="width:45%;">Description</th>
                        <th style="width:14%;">Quantité</th>
                        <th style="width:20%;">Prix unitaire HT</th>
                        <th style="width:16%;">Total HT</th>
                        <th style="width:5%;"></th>
                    </tr>
                </thead>
                <tbody id="lines-body">
                    <?php foreach ($existingLines as $line): ?>
                        <tr class="line-row">
                            <td>
                                <input type="text" name="line_description[]" class="line-desc" required
                                       value="<?= htmlspecialchars($line['description'], ENT_QUOTES, 'UTF-8') ?>">
                            </td>
                            <td>
                                <input type="number" name="line_quantity[]" class="line-qty"
                                       step="0.01" min="0" required
                                       value="<?= htmlspecialchars($line['quantity'], ENT_QUOTES, 'UTF-8') ?>">
                            </td>
                            <td>
                                <input type="number" name="line_unit_price[]" class="line-price"
                                       step="0.01" min="0" required
                                       value="<?= htmlspecialchars($line['unit_price'], ENT_QUOTES, 'UTF-8') ?>">
                            </td>
                            <td>
                                <input type="hidden" name="line_total[]" class="line-total-hidden"
                                       value="<?= htmlspecialchars($line['total'], ENT_QUOTES, 'UTF-8') ?>">
                                <span class="line-total-display">
                                    <?= number_format((float) $line['total'], 2, ',', ' ') ?> €
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="btn btn-danger btn-remove-line"
                                        style="
                                        padding:4px 10px;font-size:12px;">✕</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="button" id="btn-add-line" class="btn btn-secondary">+ Ajouter une ligne</button>

            <!-- Totaux -->
            <div style="display:flex;justify-content:flex-end;margin-top:24px;">
                <div style="min-width:300px;">
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid #eee;">
                        <span style="color:#555;">Total HT</span>
                        <strong id="display-ht">0,00 €</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;">
                        <span style="color:#555;">TVA (<span id="display-tva-rate">20</span>%)</span>
                        <strong id="display-tva">0,00 €</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:10px 0;
                                border-top:2px solid #1e3a5f;font-size:16px;">
                        <span style="color:#1e3a5f;font-weight:700;">Total TTC</span>
                        <strong id="display-ttc" style="color:#1e3a5f;">0,00 €</strong>
                    </div>
                </div>
            </div>

            <input type="hidden" name="total_ht"
                   id="total_ht"  value="<?= htmlspecialchars($val('total_ht',  '0'), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="total_ttc"
                   id="total_ttc" value="<?= htmlspecialchars($val('total_ttc', '0'), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div style="text-align:right;margin-bottom:32px;">
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? 'Enregistrer les modifications' : 'Créer la facture' ?>
            </button>
        </div>

    </form>
</main>

<!-- Template ligne (caché, non rendu) -->
<template id="line-template">
    <tr class="line-row">
        <td>
            <input type="text" name="line_description[]" class="line-desc"
                   placeholder="Description du service / produit" required>
        </td>
        <td>
            <input type="number" name="line_quantity[]" class="line-qty"
                   step="0.01" min="0" value="1" required>
        </td>
        <td>
            <input type="number" name="line_unit_price[]" class="line-price"
                   step="0.01" min="0" value="0.00" required>
        </td>
        <td>
            <input type="hidden" name="line_total[]" class="line-total-hidden" value="0">
            <span class="line-total-display">0,00 €</span>
        </td>
        <td style="text-align:center;">
            <button type="button" class="btn btn-danger btn-remove-line"
                    style="padding:4px 10px;font-size:12px;">✕</button>
        </td>
    </tr>
</template>

<script>
$(function () {
    function fmt(n) {
        return n.toFixed(2).replace('.', ',') + ' €';
    }

    function recalcLine($row) {
        var qty   = parseFloat($row.find('.line-qty').val())   || 0;
        var price = parseFloat($row.find('.line-price').val()) || 0;
        var total = qty * price;
        $row.find('.line-total-hidden').val(total.toFixed(2));
        $row.find('.line-total-display').text(fmt(total));
    }

    function recalcTotals() {
        var totalHt = 0;
        $('#lines-body .line-row').each(function () {
            totalHt += parseFloat($(this).find('.line-total-hidden').val()) || 0;
        });

        var tvaRate = parseFloat($('#tva_rate').val()) || 0;
        var tva     = totalHt * tvaRate / 100;
        var ttc     = totalHt + tva;

        $('#display-ht').text(fmt(totalHt));
        $('#display-tva').text(fmt(tva));
        $('#display-ttc').text(fmt(ttc));
        $('#display-tva-rate').text(tvaRate);
        $('#total_ht').val(totalHt.toFixed(2));
        $('#total_ttc').val(ttc.toFixed(2));
    }

    // Recalcul initial (mode édition — les lignes sont déjà en DOM)
    $('#lines-body .line-row').each(function () { recalcLine($(this)); });
    recalcTotals();

    // Ajouter une ligne vide si aucune ligne présente
    if ($('#lines-body .line-row').length === 0) {
        addLine();
    }

    function addLine() {
        var tmpl  = document.getElementById('line-template');
        var clone = $(tmpl.content.cloneNode(true));
        $('#lines-body').append(clone);
        recalcTotals();
    }

    $('#btn-add-line').on('click', addLine);

    // Suppression (délégation d'événement)
    $('#lines-body').on('click', '.btn-remove-line', function () {
        if ($('#lines-body .line-row').length <= 1) {
            alert('La facture doit contenir au moins une ligne.');
            return;
        }
        $(this).closest('.line-row').remove();
        recalcTotals();
    });

    // Recalcul en temps réel
    $('#lines-body').on('input', '.line-qty, .line-price', function () {
        recalcLine($(this).closest('.line-row'));
        recalcTotals();
    });

    $('#tva_rate').on('change', recalcTotals);
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
