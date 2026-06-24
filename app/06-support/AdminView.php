<?php

declare(strict_types=1);

namespace App\Support;

use App\Service\BackendApiClient;

final class AdminView
{
  /**
   * @return array{status: int, headers: array<string, string>, body: string}
   */
    public static function render(string $pageId): array
    {
        if ($redirect = Auth::requireAdmin()) {
            return $redirect;
        }

        $page = AdminMenu::findById($pageId);
        if ($page === null) {
            return Response::json(['error' => 'Not Found'], 404);
        }

        $user = Auth::user();
        $client = new BackendApiClient();
        $tenantsResponse = $client->tenants();
        $tenantContext = self::resolveTenantContext($tenantsResponse, $user);

        if ($pageId === 'overview') {
            $content = Response::partial('admin/home-content', [
                'backend_api_url' => Config::get('backend.api_base_url', ''),
                'health' => $client->health(),
                'tenants' => $tenantsResponse,
                'user' => $user,
            ]);
        } else {
            $content = Response::partial('admin/placeholder', [
                'title' => (string) ($page['title'] ?? ''),
                'description' => (string) ($page['description'] ?? ''),
            ]);
        }

        return Response::view('admin/layout', [
            'title' => (string) ($page['title'] ?? 'Admin'),
            'content' => $content,
            'active_nav' => $pageId,
            'user' => $user,
            'menu_sections' => AdminMenu::sectionsForUser($user),
            'menu_overview' => AdminMenu::overview(),
            'tenant_context' => $tenantContext,
            'flash' => Session::pullFlash(),
        ]);
    }

    /**
     * @param array<string, mixed> $contentData
     * @return array{status: int, headers: array<string, string>, body: string}
     */
    public static function renderContent(string $pageId, string $partial, array $contentData = []): array
    {
        if ($redirect = Auth::requireAdmin()) {
            return $redirect;
        }

        $page = AdminMenu::findById($pageId);
        if ($page === null) {
            return Response::json(['error' => 'Not Found'], 404);
        }

        $user = Auth::user();
        $client = new BackendApiClient();
        $tenantsResponse = $client->tenants();
        $tenantContext = self::resolveTenantContext($tenantsResponse, $user);

        $contentData['flash'] = Session::pullFlash();
        $contentData['page'] = $page;
        $contentData['user'] = $user;
        $contentData['tenant_context'] = $tenantContext;
        $content = Response::partial($partial, $contentData);

        return Response::view('admin/layout', [
            'title' => (string) ($page['title'] ?? 'Admin'),
            'content' => $content,
            'active_nav' => $pageId,
            'user' => $user,
            'menu_sections' => AdminMenu::sectionsForUser($user),
            'menu_overview' => AdminMenu::overview(),
            'tenant_context' => $tenantContext,
            'flash' => null,
        ]);
    }

    /** @param array<string, mixed> $user */
    public static function isSystemAdmin(?array $user): bool
    {
        if ($user === null) {
            return false;
        }
        foreach ($user['system_roles'] ?? [] as $role) {
            if (is_array($role) && ($role['role'] ?? '') === 'SystemAdmin') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null} $tenantsResponse
     * @param array<string, mixed>|null $user
     * @return array{
     *   selected_tenant_id: int|null,
     *   selected_tenant: array<string, mixed>|null,
     *   selectable_tenants: list<array<string, mixed>>,
     *   is_platform_context: bool
     * }
     */
    private static function resolveTenantContext(array $tenantsResponse, ?array $user): array
    {
        if (isset($_GET['tenant_id'])) {
            $raw = (string) $_GET['tenant_id'];
            if ($raw === '' || $raw === '0') {
                Session::setSelectedTenantId(null);
            } else {
                Session::setSelectedTenantId((int) $raw);
            }
        }

        $allTenants = [];
        if ($tenantsResponse['ok'] && is_array($tenantsResponse['data']['tenants'] ?? null)) {
            foreach ($tenantsResponse['data']['tenants'] as $tenant) {
                if (is_array($tenant)) {
                    $allTenants[] = $tenant;
                }
            }
        }

        $cupTenants = array_values(array_filter(
            $allTenants,
            static fn (array $t): bool => ($t['tenant_type'] ?? '') === 'cup'
        ));

        $allowedIds = self::allowedTenantIds($user, $cupTenants);
        $selectable = array_values(array_filter(
            $cupTenants,
            static fn (array $t): bool => in_array((int) ($t['id'] ?? 0), $allowedIds, true)
        ));

        $selectedId = Session::getSelectedTenantId();
        if ($selectedId !== null && !in_array($selectedId, $allowedIds, true)) {
            $selectedId = null;
            Session::setSelectedTenantId(null);
        }

        if ($selectedId === null && $selectable !== []) {
            $selectedId = (int) $selectable[0]['id'];
            Session::setSelectedTenantId($selectedId);
        }

        $selectedTenant = null;
        if ($selectedId !== null) {
            foreach ($selectable as $tenant) {
                if ((int) ($tenant['id'] ?? 0) === $selectedId) {
                    $selectedTenant = $tenant;
                    break;
                }
            }
        }

        return [
            'selected_tenant_id' => $selectedId,
            'selected_tenant' => $selectedTenant,
            'selectable_tenants' => $selectable,
            'is_platform_context' => $selectedId === null,
        ];
    }

    /**
     * @param array<string, mixed>|null $user
     * @param list<array<string, mixed>> $cupTenants
     * @return list<int>
     */
    private static function allowedTenantIds(?array $user, array $cupTenants): array
    {
        if ($user === null) {
            return [];
        }

        $isSystemAdmin = false;
        foreach ($user['system_roles'] ?? [] as $role) {
            if (is_array($role) && ($role['role'] ?? '') === 'SystemAdmin') {
                $isSystemAdmin = true;
                break;
            }
        }

        if ($isSystemAdmin) {
            return array_map(static fn (array $t): int => (int) $t['id'], $cupTenants);
        }

        $ids = [];
        foreach ($user['tenant_admin_access'] ?? [] as $access) {
            if (!is_array($access)) {
                continue;
            }
            if (($access['role'] ?? '') === 'CupAdmin') {
                $ids[] = (int) ($access['tenant_id'] ?? 0);
            }
        }

        return array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));
    }
}
