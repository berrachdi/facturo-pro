<?php
$isEdit    = isset($client);
$pageTitle = $isEdit ? 'Modifier ' . htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') : 'Nouveau client';
$action    = $isEdit ? '/clients/' . (int) $client['id'] . '/edit' : '/clients/create';

require __DIR__ . '/../layout/header.php';
?>
<main>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <a href="/clients" class="btn btn-secondary" style="padding:6px 12px;font-size:13px;">← Retour</a>
        <h1 style="margin:0;"><?= $pageTitle ?></h1>
    </div>

    <div class="card" style="max-width:640px;">
        <form method="POST" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="name">Nom <span style="color:#e74c3c;">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                    autofocus
                >
                <?php if (isset($errors['name'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="form-group">
                <label for="siret">SIRET <span style="color:#999;font-size:12px;">(14 chiffres)</span></label>
                <input
                    type="text"
                    id="siret"
                    name="siret"
                    maxlength="14"
                    value="<?= htmlspecialchars($old['siret'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
                <?php if (isset($errors['siret'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['siret'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" rows="3"><?= htmlspecialchars($old['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le client' ?>
                </button>
                <a href="/clients" class="btn btn-secondary">Annuler</a>
            </div>

        </form>
    </div>

    <?php if ($isEdit): ?>
    <div class="card" style="max-width:640px;margin-top:16px;border-top:3px solid #e74c3c;">
        <h2 style="color:#e74c3c;margin-bottom:12px;">Zone de danger</h2>
        <p style="color:#666;font-size:14px;margin-bottom:16px;">
            Archiver ce client le masquera des listes actives. Cette action est réversible depuis la base de données.
        </p>
        <form method="POST" action="/clients/<?= (int) $client['id'] ?>/archive"
              onsubmit="return confirm('Archiver ce client ?')">
            <button type="submit" class="btn btn-danger">Archiver ce client</button>
        </form>
    </div>
    <?php endif; ?>

</main>
<?php require __DIR__ . '/../layout/footer.php'; ?>
