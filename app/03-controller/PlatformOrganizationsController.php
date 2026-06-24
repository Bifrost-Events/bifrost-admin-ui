<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\BackendApiClient;
use App\Support\AdminView;
use App\Support\Response;
use App\Support\Session;
use App\Support\UserSearch;

final class PlatformOrganizationsController
{
    public function index(): array
    {
        $client = new BackendApiClient();
        $search = trim((string) ($_GET['q'] ?? ''));
        $tenantId = (int) ($_GET['tenant_id'] ?? 0);
        $activeSearch = UserSearch::isActive($search) ? $search : null;

        $result = $client->adminOrganizations(
            $tenantId > 0 ? $tenantId : null,
            $activeSearch,
        );
        $tenantsResult = $client->adminTenants();
        $cupTenants = array_values(array_filter(
            $tenantsResult['ok'] ? ($tenantsResult['data']['tenants'] ?? []) : [],
            static fn ($t): bool => is_array($t) && ($t['tenant_type'] ?? '') === 'cup'
        ));

        return AdminView::renderContent('platform.organizations', 'admin/platform/organizations-list', [
            'organizations' => $result['ok'] ? ($result['data']['organizations'] ?? []) : [],
            'cup_tenants' => $cupTenants,
            'tenant_id' => $tenantId,
            'search' => $search,
            'api_error' => $result['ok'] ? null : ($result['error'] ?? 'Kunne ikke hente organisasjoner'),
        ]);
    }

    public function show(int $id): array
    {
        $client = new BackendApiClient();
        $result = $client->adminOrganization($id);
        if (!$result['ok']) {
            Session::setFlash('error', $result['error'] ?? 'Fant ikke organisasjonen');

            return Response::redirect('/platform/organizations');
        }

        $search = trim((string) ($_GET['q'] ?? ''));
        $pickUserId = (int) ($_GET['pick_user_id'] ?? 0);
        $users = [];
        if (UserSearch::isActive($search)) {
            $usersResult = $client->adminUsers($search);
            $users = $usersResult['ok'] ? ($usersResult['data']['users'] ?? []) : [];
        }

        return AdminView::renderContent('platform.organizations', 'admin/platform/organizations-show', [
            'organization' => $result['data']['organization'] ?? [],
            'members' => is_array($result['data']['members'] ?? null) ? $result['data']['members'] : [],
            'search' => $search,
            'pick_user_id' => $pickUserId,
            'users' => $users,
        ]);
    }

    public function createForm(): array
    {
        $client = new BackendApiClient();
        $tenantsResult = $client->adminTenants();

        return AdminView::renderContent('platform.organizations', 'admin/platform/organizations-form', [
            'organization' => null,
            'cup_tenants' => $this->cupTenants($tenantsResult),
            'form_action' => '/platform/organizations',
            'form_title' => 'Opprett organisasjon',
            'preset_tenant_id' => (int) ($_GET['tenant_id'] ?? 0),
        ]);
    }

    public function create(): array
    {
        $body = $this->organizationBodyFromPost(true);
        $client = new BackendApiClient();
        $result = $client->createAdminOrganization($body);

        if ($result['ok']) {
            Session::setFlash('success', 'Organisasjon opprettet');
            $id = (int) ($result['data']['organization']['id'] ?? 0);

            return Response::redirect($id > 0 ? '/platform/organizations/' . $id : '/platform/organizations');
        }

        $tenantsResult = $client->adminTenants();

        return AdminView::renderContent('platform.organizations', 'admin/platform/organizations-form', [
            'organization' => $body,
            'cup_tenants' => $this->cupTenants($tenantsResult),
            'form_action' => '/platform/organizations',
            'form_title' => 'Opprett organisasjon',
            'preset_tenant_id' => (int) ($body['tenant_id'] ?? 0),
            'errors' => $result['errors'] ?? [],
            'error' => $result['error'] ?? 'Kunne ikke opprette',
        ]);
    }

    public function editForm(int $id): array
    {
        $client = new BackendApiClient();
        $result = $client->adminOrganization($id);
        if (!$result['ok']) {
            Session::setFlash('error', $result['error'] ?? 'Fant ikke organisasjonen');

            return Response::redirect('/platform/organizations');
        }

        $tenantsResult = $client->adminTenants();

        return AdminView::renderContent('platform.organizations', 'admin/platform/organizations-form', [
            'organization' => $result['data']['organization'] ?? [],
            'cup_tenants' => $this->cupTenants($tenantsResult),
            'form_action' => '/platform/organizations/' . $id . '/edit',
            'form_title' => 'Rediger organisasjon',
            'preset_tenant_id' => 0,
        ]);
    }

    public function update(int $id): array
    {
        $body = $this->organizationBodyFromPost(false);
        $client = new BackendApiClient();
        $result = $client->updateAdminOrganization($id, $body);

        if ($result['ok']) {
            Session::setFlash('success', 'Lagret');

            return Response::redirect('/platform/organizations/' . $id);
        }

        $body['id'] = $id;
        $tenantsResult = $client->adminTenants();

        return AdminView::renderContent('platform.organizations', 'admin/platform/organizations-form', [
            'organization' => $body,
            'cup_tenants' => $this->cupTenants($tenantsResult),
            'form_action' => '/platform/organizations/' . $id . '/edit',
            'form_title' => 'Rediger organisasjon',
            'preset_tenant_id' => 0,
            'errors' => $result['errors'] ?? [],
            'error' => $result['error'] ?? 'Kunne ikke lagre',
        ]);
    }

    public function deactivate(int $id): array
    {
        $client = new BackendApiClient();
        $result = $client->deactivateAdminOrganization($id);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Organisasjon deaktivert' : ($result['error'] ?? 'Kunne ikke deaktivere')
        );

        return Response::redirect('/platform/organizations');
    }

    public function addMember(int $id): array
    {
        $authUserId = (int) ($_POST['auth_user_id'] ?? 0);
        $role = (string) ($_POST['role'] ?? 'VIEWER');
        $client = new BackendApiClient();
        $result = $client->addAdminOrganizationMember($id, [
            'auth_user_id' => $authUserId,
            'role' => $role,
        ]);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Medlem lagt til' : ($result['error'] ?? 'Kunne ikke legge til medlem'),
            $result['errors'] ?? []
        );

        return Response::redirect('/platform/organizations/' . $id . '#medlemmer');
    }

    public function removeMember(int $id, int $memberId): array
    {
        $client = new BackendApiClient();
        $result = $client->removeAdminOrganizationMember($id, $memberId);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Medlem fjernet' : ($result['error'] ?? 'Kunne ikke fjerne medlem')
        );

        return Response::redirect('/platform/organizations/' . $id . '#medlemmer');
    }

    /** @param array{ok: bool, data?: array<string, mixed>|null} $tenantsResult @return list<array<string, mixed>> */
    private function cupTenants(array $tenantsResult): array
    {
        return array_values(array_filter(
            $tenantsResult['ok'] ? ($tenantsResult['data']['tenants'] ?? []) : [],
            static fn ($t): bool => is_array($t) && ($t['tenant_type'] ?? '') === 'cup'
        ));
    }

    /** @return array<string, mixed> */
    private function organizationBodyFromPost(bool $isCreate): array
    {
        $districtsRaw = trim((string) ($_POST['districts'] ?? ''));
        $districts = [];
        if ($districtsRaw !== '') {
            $districts = array_values(array_unique(array_filter(
                array_map('trim', preg_split('/[\r\n,]+/', $districtsRaw) ?: []),
                static fn (string $v): bool => $v !== ''
            )));
        }

        $body = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'organization_number' => trim((string) ($_POST['organization_number'] ?? '')) ?: null,
            'organization_type' => (string) ($_POST['organization_type'] ?? 'skytterlag'),
            'contact_person' => trim((string) ($_POST['contact_person'] ?? '')) ?: null,
            'email' => trim((string) ($_POST['email'] ?? '')) ?: null,
            'phone' => trim((string) ($_POST['phone'] ?? '')) ?: null,
            'postal_code' => trim((string) ($_POST['postal_code'] ?? '')) ?: null,
            'city' => trim((string) ($_POST['city'] ?? '')) ?: null,
            'districts' => $districts,
            'status' => (string) ($_POST['status'] ?? 'active'),
        ];

        if ($isCreate) {
            $body['tenant_id'] = (int) ($_POST['tenant_id'] ?? 0);
        }

        return $body;
    }
}
