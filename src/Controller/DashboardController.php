<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Database;

class DashboardController
{
    public function index(): void
    {
        Auth::requireAuth();

        $pdo = Database::getInstance();

        $activeClients = (int) $pdo->query(
            "SELECT COUNT(*) FROM clients WHERE status = 'active'"
        )->fetchColumn();

        $pendingInvoices = (int) $pdo->query(
            "SELECT COUNT(*) FROM invoices WHERE status IN ('draft','sent')"
        )->fetchColumn();

        $monthRevenue = (float) $pdo->query(
            "SELECT COALESCE(SUM(total_ttc), 0) FROM invoices
             WHERE status = 'paid'
               AND YEAR(issue_date)  = YEAR(NOW())
               AND MONTH(issue_date) = MONTH(NOW())"
        )->fetchColumn();

        $unpaidAmount = (float) $pdo->query(
            "SELECT COALESCE(SUM(total_ttc), 0) FROM invoices WHERE status = 'sent'"
        )->fetchColumn();

        // CA mensuel sur les 6 derniers mois (factures payées)
        $chartRows = $pdo->query(
            "SELECT DATE_FORMAT(issue_date, '%Y-%m') AS month,
                    SUM(total_ttc) AS total
             FROM invoices
             WHERE status = 'paid'
               AND issue_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month
             ORDER BY month ASC"
        )->fetchAll();

        $months   = [];
        $revenues = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[]   = date('Y-m', strtotime("-{$i} months"));
            $revenues[] = 0.0;
        }
        foreach ($chartRows as $row) {
            $pos = array_search($row['month'], $months, true);
            if ($pos !== false) {
                $revenues[$pos] = (float) $row['total'];
            }
        }

        $latestInvoices = $pdo->query(
            "SELECT i.id, i.number, i.status, i.total_ttc, i.issue_date,
                    c.name AS client_name
             FROM invoices i
             JOIN clients c ON c.id = i.client_id
             ORDER BY i.created_at DESC
             LIMIT 5"
        )->fetchAll();

        require __DIR__ . '/../../views/dashboard/index.php';
    }
}
