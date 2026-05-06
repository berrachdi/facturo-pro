<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Session;
use App\Repository\ClientRepository;

class ClientController
{
    private ClientRepository $repo;

    public function __construct()
    {
        $this->repo = new ClientRepository();
    }

    public function list(): void
    {
        Auth::requireAuth();

        $clients = $this->repo->findAll();
        $success = Session::getFlash('success');

        require __DIR__ . '/../../views/clients/list.php';
    }

    public function createForm(): void
    {
        Auth::requireAuth();

        $errors = [];
        $old    = [];

        require __DIR__ . '/../../views/clients/form.php';
    }

    public function create(): void
    {
        Auth::requireAuth();

        $data = $this->extractFormData();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $old = $data;
            require __DIR__ . '/../../views/clients/form.php';
            return;
        }

        $this->repo->create($data);
        Session::flash('success', 'Client créé avec succès.');
        header('Location: /clients');
        exit;
    }

    public function editForm(string $id): void
    {
        Auth::requireAuth();

        $client = $this->repo->findById((int) $id);

        if ($client === null) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        $errors = [];
        $old    = $client;

        require __DIR__ . '/../../views/clients/form.php';
    }

    public function edit(string $id): void
    {
        Auth::requireAuth();

        $client = $this->repo->findById((int) $id);

        if ($client === null) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        $data   = $this->extractFormData();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $old = $data;
            require __DIR__ . '/../../views/clients/form.php';
            return;
        }

        $this->repo->update((int) $id, $data);
        Session::flash('success', 'Client modifié avec succès.');
        header('Location: /clients');
        exit;
    }

    public function archive(string $id): void
    {
        Auth::requireAuth();

        $this->repo->archive((int) $id);
        Session::flash('success', 'Client archivé.');
        header('Location: /clients');
        exit;
    }

    // ── Méthodes privées ──────────────────────────────────────────────

    private function extractFormData(): array
    {
        return [
            'name'    => trim($_POST['name']    ?? ''),
            'email'   => trim($_POST['email']   ?? ''),
            'phone'   => trim($_POST['phone']   ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'siret'   => trim($_POST['siret']   ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = 'Le nom est obligatoire.';
        }

        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        }

        if ($data['siret'] !== '' && !preg_match('/^\d{14}$/', $data['siret'])) {
            $errors['siret'] = 'Le SIRET doit contenir exactement 14 chiffres.';
        }

        return $errors;
    }
}
