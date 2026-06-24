<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\BackendApiClient;
use App\Support\AdminView;
use App\Support\Response;
use App\Support\Session;
use App\Support\TenantTypes;

final class PlatformTenantsController
{
    public function indexCups(): array
    {
        return $this->index('cup', 'platform.cups');
    }

    public function indexPlatform(): array
    {
        if ($redirect = $this->requireSystemAdmin()) {
            return $redirect;
        }

        return $this->index('platform', 'platform.platform');
    }

    public function indexLegacy(): array
    {
        return Response::redirect('/platform/cuper');
    }

    public function domainsLegacy(): array
    {
        Session::setFlash('info', 'Domener administreres på Cup- og Plattform-sidene under hver enhet.');

        return Response::redirect('/platform/cuper');
    }

    public function createFormCups(): array
    {
        return $this->createForm('cup');
    }

    public function createFormPlatform(): array
    {
        return $this->createForm('platform');
    }

    public function createCups(): array
    {
        return $this->create('cup');
    }

    public function createPlatform(): array
    {
        return $this->create('platform');
    }

    public function show(int $id): array
    {
        $client = new BackendApiClient();
        $result = $client->adminTenant($id);
        if (!$result['ok']) {
            Session::setFlash('error', $result['error'] ?? 'Fant ikke enheten');

            return Response::redirect('/platform/cuper');
        }

        $type = (string) ($result['data']['tenant']['tenant_type'] ?? 'cup');
        if (TenantTypes::isSystemAdminOnlyType($type) && ($redirect = $this->requireSystemAdmin())) {
            return $redirect;
        }

        $editDomainId = (int) ($_GET['edit_domain'] ?? 0);
        $url = $this->listUrlForTenant($id, $editDomainId > 0 ? $editDomainId : null);

        return Response::redirect($url);
    }

    public function createDomain(int $tenantId): array
    {
        $body = $this->domainBodyFromPost();
        $client = new BackendApiClient();
        $result = $client->createAdminDomain($tenantId, $body);

        if ($result['ok']) {
            Session::setFlash('success', 'Domene lagt til');

            return Response::redirect($this->listUrlForTenant($tenantId));
        }

        $body['tenant_id'] = $tenantId;

        return $this->renderIndexWithDomainError($tenantId, null, $body, $result['errors'] ?? [], $result['error'] ?? 'Kunne ikke lagre domene');
    }

    public function updateDomain(int $tenantId, int $domainId): array
    {
        $body = $this->domainBodyFromPost();
        $client = new BackendApiClient();
        $result = $client->updateAdminDomain($domainId, $body);

        if ($result['ok']) {
            Session::setFlash('success', 'Domene lagret');

            return Response::redirect($this->listUrlForTenant($tenantId));
        }

        $body['id'] = $domainId;
        $body['tenant_id'] = $tenantId;

        return $this->renderIndexWithDomainError($tenantId, $domainId, $body, $result['errors'] ?? [], $result['error'] ?? 'Kunne ikke lagre');
    }

    public function deleteDomain(int $tenantId, int $domainId): array
    {
        $client = new BackendApiClient();
        $result = $client->deleteAdminDomain($domainId);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Domene fjernet' : ($result['error'] ?? 'Kunne ikke fjerne domene')
        );

        return Response::redirect($this->listUrlForTenant($tenantId));
    }

    public function editForm(int $id): array
    {
        $client = new BackendApiClient();
        $result = $client->adminTenant($id);
        if (!$result['ok']) {
            Session::setFlash('error', $result['error'] ?? 'Fant ikke enheten');

            return Response::redirect('/platform/cuper');
        }

        $tenant = $result['data']['tenant'] ?? [];
        $type = (string) ($tenant['tenant_type'] ?? 'cup');
        if (TenantTypes::isSystemAdminOnlyType($type) && ($redirect = $this->requireSystemAdmin())) {
            return $redirect;
        }

        return AdminView::renderContent(TenantTypes::menuIdForType($type), 'admin/platform/tenants-form', [
            'tenant' => $tenant,
            'form_action' => '/platform/tenants/' . $id . '/edit',
            'form_title' => 'Rediger',
            'preset_type' => null,
            'cancel_path' => TenantTypes::listPathForType($type),
        ]);
    }

    public function update(int $id): array
    {
        $client = new BackendApiClient();
        $existing = $client->adminTenant($id);
        $existingType = 'cup';
        if ($existing['ok'] && is_array($existing['data']['tenant'] ?? null)) {
            $existingType = (string) ($existing['data']['tenant']['tenant_type'] ?? 'cup');
        }

        $body = $this->tenantBodyFromPost();
        $result = $client->updateAdminTenant($id, $body);

        if ($result['ok']) {
            Session::setFlash('success', 'Lagret');

            return Response::redirect($this->listUrlForTenant($id));
        }

        $body['id'] = $id;

        return AdminView::renderContent(TenantTypes::menuIdForType($existingType), 'admin/platform/tenants-form', [
            'tenant' => $body,
            'form_action' => '/platform/tenants/' . $id . '/edit',
            'form_title' => 'Rediger',
            'preset_type' => null,
            'cancel_path' => TenantTypes::listPathForType($existingType),
            'errors' => $result['errors'] ?? [],
            'error' => $result['error'] ?? 'Kunne ikke lagre',
        ]);
    }

    public function deactivate(int $id): array
    {
        $client = new BackendApiClient();
        $existing = $client->adminTenant($id);
        $listPath = '/platform/cuper';
        if ($existing['ok'] && is_array($existing['data']['tenant'] ?? null)) {
            $listPath = TenantTypes::listPathForType((string) ($existing['data']['tenant']['tenant_type'] ?? 'cup'));
        }

        $result = $client->deactivateAdminTenant($id);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Deaktivert' : ($result['error'] ?? 'Kunne ikke deaktivere')
        );

        return Response::redirect($listPath);
    }

    /**
     * @param array{tenant_id: int, edit_domain_id: int|null, form: array<string, mixed>, errors: array<string, string>, error: string}|null $domainFormState
     */
    private function index(string $typeFilter, string $pageId, ?array $domainFormState = null): array
    {
        $client = new BackendApiClient();
        $result = $client->adminTenants();
        $tenants = $result['ok'] ? ($result['data']['tenants'] ?? []) : [];
        $filtered = array_values(array_filter(
            $tenants,
            static fn ($t): bool => is_array($t) && (string) ($t['tenant_type'] ?? '') === $typeFilter
        ));

        foreach ($filtered as $i => $tenant) {
            if (!is_array($tenant)) {
                continue;
            }
            $tenantId = (int) ($tenant['id'] ?? 0);
            $domains = [];
            $domainsApiError = null;
            if ($tenantId > 0) {
                $domainsResult = $client->adminDomains($tenantId);
                if ($domainsResult['ok'] && is_array($domainsResult['data']['domains'] ?? null)) {
                    $domains = $domainsResult['data']['domains'];
                } else {
                    $domainsApiError = $domainsResult['error'] ?? 'Kunne ikke hente domener';
                }
            }
            $filtered[$i]['domains'] = $domains;
            $filtered[$i]['domain_count'] = count($domains);
            $filtered[$i]['domains_api_error'] = $domainsApiError;
        }

        return AdminView::renderContent($pageId, 'admin/platform/tenants-list', [
            'tenants' => $filtered,
            'type_filter' => $typeFilter,
            'api_error' => $result['ok'] ? null : ($result['error'] ?? 'Kunne ikke hente liste'),
            'domain_form_state' => $domainFormState,
            'edit_tenant_id' => (int) ($_GET['edit_tenant'] ?? 0),
            'edit_domain_id' => (int) ($_GET['edit_domain'] ?? 0),
        ]);
    }

    /**
     * @param array<string, mixed> $form
     * @param array<string, string> $errors
     */
    private function renderIndexWithDomainError(
        int $tenantId,
        ?int $editDomainId,
        array $form,
        array $errors,
        string $error,
    ): array {
        $client = new BackendApiClient();
        $tenantResult = $client->adminTenant($tenantId);
        $type = (string) ($tenantResult['data']['tenant']['tenant_type'] ?? 'cup');
        if (TenantTypes::isSystemAdminOnlyType($type) && ($redirect = $this->requireSystemAdmin())) {
            return $redirect;
        }

        return $this->index($type, TenantTypes::menuIdForType($type), [
            'tenant_id' => $tenantId,
            'edit_domain_id' => $editDomainId,
            'form' => $form,
            'errors' => $errors,
            'error' => $error,
        ]);
    }

    private function listUrlForTenant(int $tenantId, ?int $editDomainId = null): string
    {
        $client = new BackendApiClient();
        $result = $client->adminTenant($tenantId);
        $listPath = '/platform/cuper';
        if ($result['ok'] && is_array($result['data']['tenant'] ?? null)) {
            $listPath = TenantTypes::listPathForType((string) ($result['data']['tenant']['tenant_type'] ?? 'cup'));
        }

        $query = '';
        if ($editDomainId !== null && $editDomainId > 0) {
            $query = '?edit_tenant=' . $tenantId . '&edit_domain=' . $editDomainId;
        }

        return $listPath . $query . '#tenant-' . $tenantId;
    }

    private function createForm(string $presetType): array
    {
        if ($redirect = $this->requireSystemAdmin()) {
            return $redirect;
        }

        $pageId = TenantTypes::menuIdForType($presetType);
        $listPath = TenantTypes::listPathForType($presetType);
        $meta = TenantTypes::typeMeta($presetType);

        return AdminView::renderContent($pageId, 'admin/platform/tenants-form', [
            'tenant' => ['tenant_type' => $presetType],
            'form_action' => $listPath,
            'form_title' => 'Opprett ' . strtolower((string) $meta['label']),
            'preset_type' => $presetType,
            'cancel_path' => $listPath,
        ]);
    }

    private function create(string $presetType): array
    {
        if ($redirect = $this->requireSystemAdmin()) {
            return $redirect;
        }

        $body = $this->tenantBodyFromPost();
        $body['tenant_type'] = $presetType;
        $listPath = TenantTypes::listPathForType($presetType);
        $pageId = TenantTypes::menuIdForType($presetType);
        $meta = TenantTypes::typeMeta($presetType);

        $client = new BackendApiClient();
        $result = $client->createAdminTenant($body);

        if ($result['ok']) {
            Session::setFlash('success', 'Opprettet');

            return Response::redirect($listPath);
        }

        return AdminView::renderContent($pageId, 'admin/platform/tenants-form', [
            'tenant' => $body,
            'form_action' => $listPath,
            'form_title' => 'Opprett ' . strtolower((string) $meta['label']),
            'preset_type' => $presetType,
            'cancel_path' => $listPath,
            'errors' => $result['errors'] ?? [],
            'error' => $result['error'] ?? 'Kunne ikke opprette',
        ]);
    }

    /** @return array{status: int, headers: array<string, string>, body: string}|null */
    private function requireSystemAdmin(): ?array
    {
        if (!AdminView::isSystemAdmin(Session::getAuth())) {
            Session::setFlash('error', 'Kun SystemAdmin har tilgang til plattform-enheter.');

            return Response::redirect('/platform/cuper');
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function tenantBodyFromPost(): array
    {
        return [
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'tenant_type' => (string) ($_POST['tenant_type'] ?? 'cup'),
            'status' => (string) ($_POST['status'] ?? 'active'),
        ];
    }

    /** @return array<string, mixed> */
    private function domainBodyFromPost(): array
    {
        return [
            'host' => trim((string) ($_POST['host'] ?? '')),
            'purpose' => (string) ($_POST['purpose'] ?? 'public'),
            'is_primary' => isset($_POST['is_primary']),
        ];
    }
}
