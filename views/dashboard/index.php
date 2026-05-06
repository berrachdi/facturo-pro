<?php
$pageTitle    = 'Tableau de bord — FacturoPro';
$statusLabels = [
    'draft'     => 'Brouillon',
    'sent'      => 'Envoyée',
    'paid'      => 'Payée',
    'cancelled' => 'Annulée',
];
require __DIR__ . '/../layout/header.php';
?>
<main>
    <div style="margin-bottom:24px;">
        <h1 style="margin-bottom:4px;">Tableau de bord</h1>
        <p style="color:#888;font-size:14px;margin:0;">
            Bonjour, <strong><?= htmlspecialchars(\App\Core\Auth::userName() ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
    </div>

    <!-- KPIs -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;">
        <div class="card" style="text-align:center;border-top:3px solid #1e3a5f;">
            <div style="font-size:34px;font-weight:700;color:#1e3a5f;line-height:1.1;">
                <?= $activeClients ?>
            </div>
            <div style="color:#666;font-size:13px;margin-top:6px;">Clients actifs</div>
        </div>
        <div class="card" style="text-align:center;border-top:3px solid #e67e22;">
            <div style="font-size:34px;font-weight:700;color:#e67e22;line-height:1.1;">
                <?= $pendingInvoices ?>
            </div>
            <div style="color:#666;font-size:13px;margin-top:6px;">Factures en cours</div>
        </div>
        <div class="card" style="text-align:center;border-top:3px solid #27ae60;">
            <div style="font-size:34px;font-weight:700;color:#27ae60;line-height:1.1;">
                <?= number_format($monthRevenue, 0, ',', ' ') ?> €
            </div>
            <div style="color:#666;font-size:13px;margin-top:6px;">CA ce mois (TTC)</div>
        </div>
        <div class="card" style="text-align:center;border-top:3px solid #e74c3c;">
            <div style="font-size:34px;font-weight:700;color:#e74c3c;line-height:1.1;">
                <?= number_format($unpaidAmount, 0, ',', ' ') ?> €
            </div>
            <div style="color:#666;font-size:13px;margin-top:6px;">Impayés (TTC)</div>
        </div>
    </div>

    <!-- Graphique + Dernières factures -->
    <div style="display:grid;grid-template-columns:3fr 2fr;gap:16px;">

        <div class="card">
            <h2>Chiffre d'affaires — 6 derniers mois</h2>
            <canvas id="revenueChart" height="110"></canvas>
        </div>

        <div class="card" style="padding:0;overflow:hidden;">
            <div style="padding:20px 24px 12px;">
                <h2 style="margin:0;">Dernières factures</h2>
            </div>
            <?php if (empty($latestInvoices)): ?>
                <p style="padding:16px 24px;color:#999;font-size:14px;">Aucune facture pour le moment.</p>
            <?php else: ?>
                <table>
                    <tbody>
                        <?php foreach ($latestInvoices as $inv): ?>
                            <tr>
                                <td>
                                    <a href="/invoices/<?= (int) $inv['id'] ?>"
                                       style="font-weight:600;color:#1e3a5f;text-decoration:none;">
                                        <?= htmlspecialchars($inv['number'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                    <div style="font-size:12px;color:#999;margin-top:2px;">
                                        <?= htmlspecialchars($inv['client_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <span class="badge badge-<?= htmlspecialchars($inv['status'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= $statusLabels[$inv['status']] ?? htmlspecialchars($inv['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <div style="font-size:13px;font-weight:600;margin-top:4px;color:#333;">
                                        <?= number_format((float) $inv['total_ttc'], 2, ',', ' ') ?> €
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="padding:12px 24px;border-top:1px solid #eef0f3;">
                    <a href="/invoices" style="font-size:13px;color:#1e3a5f;text-decoration:none;">
                        Voir toutes les factures →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    var months   = <?= json_encode($months,   JSON_THROW_ON_ERROR) ?>;
    var revenues = <?= json_encode($revenues, JSON_THROW_ON_ERROR) ?>;

    var monthNames = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
    var labels = months.map(function (m) {
        var parts = m.split('-');
        return monthNames[parseInt(parts[1], 10) - 1] + ' ' + parts[0].slice(2);
    });

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'CA TTC',
                data: revenues,
                backgroundColor: 'rgba(30,58,95,0.8)',
                borderColor: '#1e3a5f',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            return ' ' + ctx.parsed.y.toFixed(2).replace('.', ',') + ' €';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (v) { return v + ' €'; }
                    }
                }
            }
        }
    });
})();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
