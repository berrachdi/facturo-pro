<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;

class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            header('Location: /');
            exit;
        }

        $error = Session::getFlash('error');
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            Session::flash('error', 'Email et mot de passe requis.');
            header('Location: /login');
            exit;
        }

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user === false || !password_verify($password, $user['password'])) {
            Session::flash('error', 'Identifiants incorrects.');
            header('Location: /login');
            exit;
        }

        Auth::login((int) $user['id'], $user['name']);
        header('Location: /');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}
