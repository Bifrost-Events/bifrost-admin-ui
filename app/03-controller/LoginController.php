<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\BackendApiClient;
use App\Support\Auth;
use App\Support\Response;
use App\Support\Session;

final class LoginController
{
    public function showForm(): array
    {
        if (Auth::check()) {
            return Response::redirect('/');
        }

        $flash = Session::pullFlash();

        return Response::view('admin/login', [
            'title' => 'Logg inn',
            'error' => is_array($flash) ? (string) ($flash['message'] ?? '') : '',
        ]);
    }

    public function submit(): array
    {
        if (Auth::check()) {
            return Response::redirect('/');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            return Response::view('admin/login', [
                'title' => 'Logg inn',
                'error' => 'E-post og passord er påkrevd.',
            ], 422);
        }

        $client = new BackendApiClient();
        $result = $client->login($email, $password);

        if (!($result['ok'] ?? false)) {
            $message = (string) ($result['error'] ?? 'Innlogging feilet.');
            if (str_contains(strtolower($message), 'invalid email or password')) {
                $message = 'Ugyldig e-post eller passord.';
            }
            if (str_contains(strtolower($message), 'access denied')) {
                $message = 'Ingen tilgang — krever SystemAdmin eller CupAdmin.';
            }

            return Response::view('admin/login', [
                'title' => 'Logg inn',
                'error' => $message,
            ], (int) ($result['status'] ?? 401));
        }

        $user = $result['data']['user'] ?? null;
        if (!is_array($user)) {
            return Response::view('admin/login', [
                'title' => 'Logg inn',
                'error' => 'Ugyldig svar fra backend.',
            ], 502);
        }

        if (!AuthService::canAccessAdmin($user)) {
            return Response::view('admin/login', [
                'title' => 'Logg inn',
                'error' => 'Ingen tilgang — krever SystemAdmin eller CupAdmin.',
            ], 403);
        }

        Session::setAuth($user);

        return Response::redirect('/');
    }

    public function logout(): array
    {
        $client = new BackendApiClient();
        $client->logout();
        Session::clear();

        return Response::redirect('/login');
    }
}
