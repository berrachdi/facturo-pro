<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Session;
use App\Repository\InvoiceRepository;
use App\Repository\ClientRepository;
use App\Repository\PaymentRepository;

class InvoiceController
{
    private InvoiceRepository $repo;
    private ClientRepository  $clientRepo;

    public function __construct()
    {
        $this->repo       = new InvoiceRepository();
        $this->clientRepo = new ClientRepository();
    }

    public function list(): void
    {
        Auth::requireAuth();

        $filters = array_filter([
            'status'    => $_GET['status']    ?? '',
            'client_id' => $_GET['client_id'] ?? '',
        ]);

        $invoices = $this->repo->findAll($filters);
        $clients  = $this->clientRepo->findAll();
        $success  = Session::getFlash('success');

        require __DIR__ . '/../../views/invoices/list.php';
    }

    public function createForm(): void
    {
        Auth::requireAuth();

        $clients = $this->clientRepo->findAll();
        $errors  = [];
        $old     = [];

        require __DIR__ . '/../../views/invoices/form.php';
    }

    public function create(): void
    {
        Auth::requireAuth();

        $data   = $this->extractFormData();
        $lines  = $this->extractLines();
        $errors = $this->validate($data, $lines);

        if (!empty($errors)) {
            $clients = $this->clientRepo->findAll();
            $old     = $data;
            require __DIR__ . '/../../views/invoices/form.php';
            return;
        }

        $data['user_id'] = Auth::userId();
        $data['number']  = $this->repo->generateNumber();
        $data['status']  = 'draft';

        $id = $this->repo->save($data, $lines);
        Session::flash('success', 'Facture créée avec succès.');
        header("Location: /invoices/{$id}");
        exit;
    }

    public function show(string $id): void
    {
        Auth::requireAuth();

        $invoice = $this->repo->findById((int) $id);

        if ($invoice === null) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        $paymentRepo = new PaymentRepository();
        $payments    = $paymentRepo->findByInvoice((int) $id);
        $totalPaid   = $paymentRepo->sumByInvoice((int) $id);
        $remaining   = (float) $invoice['total_ttc'] - $totalPaid;

        $success = Session::getFlash('success');
        $error   = Session::getFlash('error');
        require __DIR__ . '/../../views/invoices/detail.php';
    }

    public function editForm(string $id): void
    {
        Auth::requireAuth();

        $invoice = $this->repo->findById((int) $id);

        if ($invoice === null) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        $clients = $this->clientRepo->findAll();
        $errors  = [];
        $old     = $invoice;

        require __DIR__ . '/../../views/invoices/form.php';
    }

    public function edit(string $id): void
    {
        Auth::requireAuth();

        $invoice = $this->repo->findById((int) $id);

        if ($invoice === null) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        $data   = $this->extractFormData();
        $lines  = $this->extractLines();
        $errors = $this->validate($data, $lines);

        if (!empty($errors)) {
            $clients     = $this->clientRepo->findAll();
            $old         = array_merge($invoice, $data);
            $old['lines'] = $invoice['lines'];
            require __DIR__ . '/../../views/invoices/form.php';
            return;
        }

        $this->repo->update((int) $id, $data, $lines);
        Session::flash('success', 'Facture modifiée avec succès.');
        header("Location: /invoices/{$id}");
        exit;
    }

    public function changeStatus(string $id): void
    {
        Auth::requireAuth();

        $invoice = $this->repo->findById((int) $id);

        if ($invoice === null) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        $newStatus = $_POST['status'] ?? '';
        $allowed   = ['draft', 'sent', 'paid', 'cancelled'];

        if (in_array($newStatus, $allowed, true)) {
            $this->repo->updateStatus((int) $id, $newStatus);
            Session::flash('success', 'Statut mis à jour.');
        }

        header("Location: /invoices/{$id}");
        exit;
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function extractFormData(): array
    {
        return [
            'client_id'  => (int) ($_POST['client_id']  ?? 0),
            'issue_date' => trim($_POST['issue_date'] ?? ''),
            'due_date'   => trim($_POST['due_date']   ?? ''),
            'tva_rate'   => trim($_POST['tva_rate']   ?? '20'),
            'notes'      => trim($_POST['notes']      ?? ''),
            'total_ht'   => trim($_POST['total_ht']   ?? '0'),
            'total_ttc'  => trim($_POST['total_ttc']  ?? '0'),
        ];
    }

    private function extractLines(): array
    {
        $descriptions = $_POST['line_description'] ?? [];
        $quantities   = $_POST['line_quantity']    ?? [];
        $unitPrices   = $_POST['line_unit_price']  ?? [];
        $totals       = $_POST['line_total']       ?? [];

        $lines = [];
        foreach ($descriptions as $i => $desc) {
            $desc = trim($desc);
            if ($desc === '') {
                continue;
            }
            $lines[] = [
                'description' => $desc,
                'quantity'    => (float) ($quantities[$i]  ?? 0),
                'unit_price'  => (float) ($unitPrices[$i]  ?? 0),
                'total'       => (float) ($totals[$i]      ?? 0),
            ];
        }
        return $lines;
    }

    private function validate(array $data, array $lines): array
    {
        $errors = [];

        if ($data['client_id'] === 0) {
            $errors['client_id'] = 'Veuillez sélectionner un client.';
        }

        if ($data['issue_date'] === '') {
            $errors['issue_date'] = "La date d'émission est obligatoire.";
        }

        if ($data['due_date'] === '') {
            $errors['due_date'] = "La date d'échéance est obligatoire.";
        }

        if (empty($lines)) {
            $errors['lines'] = 'La facture doit contenir au moins une ligne.';
        }

        return $errors;
    }
}
