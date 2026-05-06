<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Session;
use App\Repository\PaymentRepository;
use App\Repository\InvoiceRepository;

class PaymentController
{
    private PaymentRepository $repo;
    private InvoiceRepository $invoiceRepo;

    public function __construct()
    {
        $this->repo        = new PaymentRepository();
        $this->invoiceRepo = new InvoiceRepository();
    }

    public function store(string $id): void
    {
        Auth::requireAuth();

        $invoice = $this->invoiceRepo->findById((int) $id);

        if ($invoice === null) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        $amount = (float) ($_POST['amount'] ?? 0);
        $method = trim($_POST['method'] ?? '');
        $paidAt = trim($_POST['paid_at'] ?? '');
        $note   = trim($_POST['note']   ?? '');

        if ($amount <= 0 || $method === '' || $paidAt === '') {
            Session::flash('error', 'Données de paiement invalides.');
            header("Location: /invoices/{$id}");
            exit;
        }

        $alreadyPaid = $this->repo->sumByInvoice((int) $id);
        $remaining   = (float) $invoice['total_ttc'] - $alreadyPaid;

        if ($amount > $remaining + 0.01) {
            Session::flash('error', 'Le montant dépasse le solde restant dû (' .
                number_format($remaining, 2, ',', ' ') . ' €).');
            header("Location: /invoices/{$id}");
            exit;
        }

        $this->repo->save([
            'invoice_id' => (int) $id,
            'amount'     => $amount,
            'paid_at'    => $paidAt,
            'method'     => $method,
            'note'       => $note,
        ]);

        // Auto-marquer payée si facture soldée
        if ($alreadyPaid + $amount >= (float) $invoice['total_ttc'] - 0.01) {
            $this->invoiceRepo->updateStatus((int) $id, 'paid');
        }

        Session::flash('success', 'Paiement enregistré.');
        header("Location: /invoices/{$id}");
        exit;
    }

    public function destroy(string $id, string $pid): void
    {
        Auth::requireAuth();

        $this->repo->delete((int) $pid);
        Session::flash('success', 'Paiement supprimé.');
        header("Location: /invoices/{$id}");
        exit;
    }
}
