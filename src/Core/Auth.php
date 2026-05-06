<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function userId(): ?int
    {
        return Session::get('user_id');
    }

    public static function userName(): ?string
    {
        return Session::get('user_name');
    }

    public static function login(int $userId, string $userName): void
    {
        session_regenerate_id(true);
        Session::set('user_id', $userId);
        Session::set('user_name', $userName);
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
