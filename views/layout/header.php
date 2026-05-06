<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'FacturoPro', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
            crossorigin="anonymous"></script>
</head>
<body>
<?php if (\App\Core\Auth::check()): ?>
<nav>
    <span class="nav-brand">FacturoPro</span>
    <a href="/">Tableau de bord</a>
    <a href="/clients">Clients</a>
    <a href="/invoices">Factures</a>
    <span class="nav-user"><?= htmlspecialchars(\App\Core\Auth::userName() ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    <a href="/logout">Déconnexion</a>
</nav>
<?php endif; ?>
