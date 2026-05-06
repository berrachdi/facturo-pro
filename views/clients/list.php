<?php
$pageTitle = 'Clients — FacturoPro';
require __DIR__ . '/../layout/header.php';
?>
<main>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h1 style="margin:0;">Clients</h1>
        <a href="/clients/create" class="btn btn-primary">+ Nouveau client</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="padding:0;">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>SIRET</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:#999;padding:32px;">
                            Aucun client pour le moment.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($client['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($client['phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($client['siret'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge badge-<?= $client['status'] === 'active' ? 'active' : 'archived' ?>">
                                    <?= $client['status'] === 'active' ? 'Actif' : 'Archivé' ?>
                                </span>
                            </td>
                            <td>
                                <a href="/clients/<?= (int) $client['id'] ?>/edit" class="btn btn-secondary" style="padding:5px 12px;font-size:12px;">
                                    Modifier
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
