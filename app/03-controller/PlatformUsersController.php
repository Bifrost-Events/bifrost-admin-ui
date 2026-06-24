<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\BackendApiClient;
use App\Support\AdminView;
use App\Support\Response;
use App\Support\Session;
use App\Support\UserSearch;

final class PlatformUsersController
{
    public function index(): array
    {
        $search = trim((string) ($_GET['q'] ?? ''));
        $client = new BackendApiClient();
        $activeSearch = UserSearch::isActive($search) ? $search : '';
        $result = $client->adminUsers($activeSearch !== '' ? $activeSearch : null);

        return AdminView::renderContent('platform.users', 'admin/platform/users-list', [
            'users' => $result['ok'] ? ($result['data']['users'] ?? []) : [],
            'search' => $search,
            'api_error' => $result['ok'] ? null : ($result['error'] ?? 'Kunne ikke hente brukere'),
        ]);
    }

    public function show(int $id): array
    {
        $client = new BackendApiClient();
        $result = $client->adminUser($id);
        if (!$result['ok']) {
            Session::setFlash('error', $result['error'] ?? 'Bruker ikke funnet');

            return Response::redirect('/platform/users');
        }

        return AdminView::renderContent('platform.users', 'admin/platform/users-show', [
            'profile' => $result['data']['user'] ?? [],
        ]);
    }

    public function createForm(): array
    {
        $client = new BackendApiClient();
        $tenants = $client->adminTenants();

        return AdminView::renderContent('platform.users', 'admin/platform/users-form', [
            'profile' => null,
            'tenants' => $tenants['ok'] ? ($tenants['data']['tenants'] ?? []) : [],
            'form_action' => '/platform/users',
            'form_title' => 'Opprett bruker',
        ]);
    }

    public function create(): array
    {
        $body = $this->userBodyFromPost(true);
        $client = new BackendApiClient();
        $result = $client->createAdminUser($body);

        if ($result['ok']) {
            Session::setFlash('success', 'Bruker opprettet');
            $id = (int) ($result['data']['user']['id'] ?? 0);

            return Response::redirect($id > 0 ? '/platform/users/' . $id : '/platform/users');
        }

        $tenants = $client->adminTenants();

        return AdminView::renderContent('platform.users', 'admin/platform/users-form', [
            'profile' => $body,
            'tenants' => $tenants['ok'] ? ($tenants['data']['tenants'] ?? []) : [],
            'form_action' => '/platform/users',
            'form_title' => 'Opprett bruker',
            'errors' => $result['errors'] ?? [],
            'error' => $result['error'] ?? 'Kunne ikke opprette bruker',
        ]);
    }

    public function editForm(int $id): array
    {
        $client = new BackendApiClient();
        $result = $client->adminUser($id);
        if (!$result['ok']) {
            Session::setFlash('error', $result['error'] ?? 'Bruker ikke funnet');

            return Response::redirect('/platform/users');
        }

        $tenants = $client->adminTenants();

        return AdminView::renderContent('platform.users', 'admin/platform/users-form', [
            'profile' => $result['data']['user'] ?? [],
            'tenants' => $tenants['ok'] ? ($tenants['data']['tenants'] ?? []) : [],
            'form_action' => '/platform/users/' . $id . '/edit',
            'form_title' => 'Rediger bruker',
        ]);
    }

    public function update(int $id): array
    {
        $body = $this->userBodyFromPost(false);
        $client = new BackendApiClient();
        $result = $client->updateAdminUser($id, $body);

        if ($result['ok']) {
            Session::setFlash('success', 'Lagret');

            return Response::redirect('/platform/users/' . $id);
        }

        $body['id'] = $id;
        $tenants = $client->adminTenants();

        return AdminView::renderContent('platform.users', 'admin/platform/users-form', [
            'profile' => $body,
            'tenants' => $tenants['ok'] ? ($tenants['data']['tenants'] ?? []) : [],
            'form_action' => '/platform/users/' . $id . '/edit',
            'form_title' => 'Rediger bruker',
            'errors' => $result['errors'] ?? [],
            'error' => $result['error'] ?? 'Kunne ikke lagre',
        ]);
    }

    public function deactivate(int $id): array
    {
        $client = new BackendApiClient();
        $result = $client->deactivateAdminUser($id);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Bruker deaktivert' : ($result['error'] ?? 'Kunne ikke deaktivere')
        );

        return Response::redirect('/platform/users');
    }

    /** @return array<string, mixed> */
    private function userBodyFromPost(bool $isCreate): array
    {
        $body = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')) ?: null,
            'is_active' => isset($_POST['is_active']),
            'first_registered_tenant_id' => ($_POST['first_registered_tenant_id'] ?? '') !== ''
                ? (int) $_POST['first_registered_tenant_id']
                : null,
        ];

        $password = (string) ($_POST['password'] ?? '');
        if ($isCreate || $password !== '') {
            $body['password'] = $password;
        }

        return $body;
    }
}
