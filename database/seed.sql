-- ============================================================
-- Données de test FacturoPro
-- Connexion : admin@facturo.fr / password
-- ============================================================

INSERT INTO users (name, email, password) VALUES
('Admin FacturoPro', 'admin@facturo.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO clients (name, email, phone, address, siret, status) VALUES
('MTS Arrabelle Solutions', 'contact@mts-arrabelle.fr', '01 23 45 67 89', '12 avenue de la République, 75011 Paris', '82345678901234', 'active'),
('Groupe BTP Méditerranée', 'compta@btp-med.fr',       '04 91 55 44 33', '45 rue du Port, 13002 Marseille',            '50123456700019', 'active'),
('Librairie du Vieux Port',  'librairie@vieuxport.fr',  '04 91 22 11 00', '3 quai du Port, 13002 Marseille',            '31234567800045', 'active'),
('Ancien Client SARL',       'info@ancien-client.fr',   NULL,              NULL,                                         NULL,              'archived');

INSERT INTO invoices (client_id, user_id, number, status, issue_date, due_date, total_ht, tva_rate, total_ttc, notes) VALUES
(1, 1, 'FA-2024-001', 'paid',      '2024-01-15', '2024-02-14', 2500.00, 20.00, 3000.00, 'Mission audit informatique Q1'),
(1, 1, 'FA-2024-002', 'sent',      '2024-02-01', '2024-03-02', 1200.00, 20.00, 1440.00, 'Maintenance mensuelle février'),
(2, 1, 'FA-2024-003', 'paid',      '2024-02-10', '2024-03-11', 4800.00, 20.00, 5760.00, 'Réalisation site vitrine'),
(3, 1, 'FA-2024-004', 'draft',     '2024-03-01', '2024-03-31',  750.00, 20.00,  900.00, NULL),
(2, 1, 'FA-2024-005', 'cancelled', '2024-03-05', '2024-04-04',  600.00, 20.00,  720.00, 'Annulée à la demande du client');

INSERT INTO invoice_lines (invoice_id, description, quantity, unit_price, total) VALUES
(1, 'Audit sécurité infrastructure',  10.00, 150.00, 1500.00),
(1, 'Rapport et recommandations',      5.00, 100.00,  500.00),
(1, 'Formation équipe DSI',            5.00, 100.00,  500.00),
(2, 'Maintenance préventive serveurs', 8.00,  90.00,  720.00),
(2, 'Mise à jour logiciels',           4.00, 120.00,  480.00),
(3, 'Design UI/UX (maquettes)',       16.00, 100.00, 1600.00),
(3, 'Développement front-end',        16.00, 150.00, 2400.00),
(3, 'Intégration CMS',                 5.00, 160.00,  800.00),
(4, 'Conseil stratégie numérique',     5.00, 150.00,  750.00),
(5, 'Prestation annulée',              4.00, 150.00,  600.00);

INSERT INTO payments (invoice_id, amount, paid_at, method, note) VALUES
(1, 3000.00, '2024-02-10 14:32:00', 'virement', 'Paiement intégral reçu'),
(3, 2880.00, '2024-03-05 09:15:00', 'virement', 'Acompte 50%'),
(3, 2880.00, '2024-03-20 11:00:00', 'virement', 'Solde');
