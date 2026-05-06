SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_lines;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100)     NOT NULL,
    email      VARCHAR(150)     NOT NULL,
    password   VARCHAR(255)     NOT NULL,
    created_at DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clients (
    id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name       VARCHAR(150)     NOT NULL,
    email      VARCHAR(150)         NULL,
    phone      VARCHAR(20)          NULL,
    address    TEXT                 NULL,
    siret      CHAR(14)             NULL,
    status     ENUM('active','archived') NOT NULL DEFAULT 'active',
    created_at DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE invoices (
    id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    client_id  INT UNSIGNED     NOT NULL,
    user_id    INT UNSIGNED     NOT NULL,
    number     VARCHAR(20)      NOT NULL,
    status     ENUM('draft','sent','paid','cancelled') NOT NULL DEFAULT 'draft',
    issue_date DATE             NOT NULL,
    due_date   DATE             NOT NULL,
    total_ht   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    tva_rate   DECIMAL(5,2)    NOT NULL DEFAULT 20.00,
    total_ttc  DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    notes      TEXT                 NULL,
    created_at DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_invoices_number (number),
    CONSTRAINT fk_invoices_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_invoices_user   FOREIGN KEY (user_id)   REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE invoice_lines (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    invoice_id  INT UNSIGNED    NOT NULL,
    description VARCHAR(255)    NOT NULL,
    quantity    DECIMAL(10,2)   NOT NULL,
    unit_price  DECIMAL(10,2)   NOT NULL,
    total       DECIMAL(10,2)   NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_lines_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    invoice_id  INT UNSIGNED    NOT NULL,
    amount      DECIMAL(10,2)   NOT NULL,
    paid_at     DATETIME        NOT NULL,
    method      ENUM('virement','cheque','especes','carte') NOT NULL,
    note        TEXT                NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
