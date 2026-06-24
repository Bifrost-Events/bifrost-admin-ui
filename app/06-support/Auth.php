<?php

declare(strict_types=1);

namespace App\Support;

use App\Service\AuthService;

final class Auth
{
    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        return Session::getAuth();
    }

    public static function check(): bool
    {
        $user = self::user();

        return $user !== null && AuthService::canAccessAdmin($user);
    }

    public static function requireAdmin(): ?array
    {
        if (!self::check()) {
            return Response::redirect('/login');
        }

        if (Session::getBackendCookie() === '') {
            Session::clear();
            Session::setFlash('error', 'Backend-sesjon mangler — logg inn på nytt.');

            return Response::redirect('/login');
        }

        return null;
    }
}
