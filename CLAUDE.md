# FacturoPro — Contexte projet pour Claude Code

## Projet
Système de gestion et suivi de factures (inspiré MTS Arrabelle Solutions).
PHP 8.2 natif SANS framework — architecture MVC maison.

## Stack technique
- **Backend** : PHP 8.2 natif (pas de Laravel/Symfony)
- **BDD** : MySQL 8 via Docker — PDO + prepared statements
- **Frontend** : HTML généré par PHP + jQuery + Chart.js
- **Auth** : Sessions PHP natives
- **Env** : Docker Compose (PHP+Apache+MySQL)
- **CI/CD** : Jenkins (Sprint 5)

## Architecture MVC maison
public/index.php         ← front controller unique (comme DispatcherServlet Java)
public/.htaccess         ← redirige toutes les URLs vers index.php
src/Core/Database.php    ← singleton PDO connexion MySQL
src/Core/Router.php      ← lit l'URL → appelle le bon Controller
src/Core/Session.php     ← wrapper session PHP
src/Core/Auth.php        ← vérifie si connecté, redirige sinon
src/Controller/          ← logique métier (comme @Controller Java)
src/Model/               ← classes métier avec promoted properties PHP 8
src/Repository/          ← accès BDD via PDO (comme @Repository Java)
views/                   ← templates PHP+HTML
database/schema.sql      ← structure des tables
database/seed.sql        ← données de test
## Base de données — 5 tables
users          → id, name, email, password, created_at
clients        → id, name, email, phone, address, siret, status, created_at
invoices       → id, client_id(FK), user_id(FK), number, status, issue_date,
due_date, total_ht, tva_rate, total_ttc, notes, created_at
invoice_lines  → id, invoice_id(FK), description, quantity, unit_price, total
payments       → id, invoice_id(FK), amount, paid_at, method, note
## Relations
- users 1──< invoices     (1 user crée N factures)
- clients 1──< invoices   (1 client a N factures)
- invoices 1──< invoice_lines
- invoices 1──< payments

## Conventions de code
- Classes PHP 8 avec promoted properties + readonly sur les IDs
- Toujours === jamais == pour les comparaisons
- Toujours htmlspecialchars() sur les données affichées
- Toujours prepared statements PDO — jamais de concaténation SQL
- array_values() après array_filter() si accès par index
- fn() pour callbacks 1 ligne, function() use() pour multi-lignes

## Modules / Sprints
- **S1 (actuel)** : Docker + BDD + Auth + Router
- **S2** : CRUD Clients
- **S3** : Factures + lignes dynamiques jQuery
- **S4** : Paiements + Dashboard + Chart.js
- **S5** : Jenkins CI/CD

## Environnement Docker
- PHP+Apache : http://localhost:8080
- MySQL : localhost:3306 — db: facturo, user: facturo, pass: facturo
- phpMyAdmin : http://localhost:8081

## Ce qu'on NE fait PAS
- Pas de framework (pas Laravel, pas Symfony)
- Pas de /var/www/html sur la machine hôte — tout dans Docker
- Pas de jQuery CDN aléatoire — version fixée dans views/layout/header.php
- Pas de SQL brut concaténé — toujours PDO prepare/execute

## Référence Java → PHP (pour comprendre les concepts)
- @Controller     → src/Controller/
- @Repository     → src/Repository/
- @Entity         → src/Model/
- JPA findById()  → Repository::findById() avec PDO
- stream().filter → array_filter() + fn()
- Optional        → ?Type + ?? 'défaut'
- @OneToMany      → JOIN SQL dans Repository
- bcrypt          → password_hash() / password_verify()
