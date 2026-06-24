<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\BackendApiClient;
use App\Support\AdminView;
use App\Support\Response;
use App\Support\Session;
use App\Support\UserSearch;

final class PlatformRolesController
{
    public function index(): array
    {
        $client = new BackendApiClient();
        $search = trim((string) ($_GET['q'] ?? ''));
        $selectedUserId = (int) ($_GET['user_id'] ?? 0);

        $users = [];
        $usersError = null;
        if (UserSearch::isActive($search)) {
            $usersResult = $client->adminUsers($search);
            $users = $usersResult['ok'] ? ($usersResult['data']['users'] ?? []) : [];
            $usersError = $usersResult['ok'] ? null : ($usersResult['error'] ?? 'Kunne ikke søke');
        }

        $selectedUser = null;
        $access = null;
        if ($selectedUserId > 0) {
            $userResult = $client->adminUser($selectedUserId);
            if ($userResult['ok']) {
                $selectedUser = $userResult['data']['user'] ?? null;
            }
            $accessResult = $client->adminUserAccess($selectedUserId);
            if ($accessResult['ok']) {
                $access = $accessResult['data'];
            }
        }

        $rolesResult = $client->adminRoles();
        $assignmentsResult = $client->adminRoleAssignmentsOverview();
        $tenantsResult = $client->adminTenants();

        $roleAssignments = [];
        $assignmentInlineLimit = 5;
        if ($assignmentsResult['ok'] && is_array($assignmentsResult['data']['assignments'] ?? null)) {
            foreach ($assignmentsResult['data']['assignments'] as $row) {
                if (is_array($row) && ($row['role'] ?? '') !== '') {
                    $roleAssignments[(string) $row['role']] = $row;
                }
            }
            $assignmentInlineLimit = (int) ($assignmentsResult['data']['inline_limit'] ?? 5);
        }

        return AdminView::renderContent('platform.roles', 'admin/platform/roles', [
            'users' => $users,
            'search' => $search,
            'selected_user' => is_array($selectedUser) ? $selectedUser : null,
            'roles' => $rolesResult['ok'] ? ($rolesResult['data']['roles'] ?? []) : [],
            'role_assignments' => $roleAssignments,
            'assignment_inline_limit' => $assignmentInlineLimit,
            'tenants' => $tenantsResult['ok'] ? ($tenantsResult['data']['tenants'] ?? []) : [],
            'selected_user_id' => $selectedUserId,
            'access' => $access,
            'is_system_admin' => AdminView::isSystemAdmin(Session::getAuth()),
            'api_error' => $usersError,
        ]);
    }

    public function assignments(string $role): array
    {
        $client = new BackendApiClient();
        $result = $client->adminRoleAssignments($role);
        if (!$result['ok']) {
            Session::setFlash('error', $result['error'] ?? 'Kunne ikke hente tildelinger');

            return Response::redirect('/platform/roles');
        }

        return AdminView::renderContent('platform.roles', 'admin/platform/roles-assignments', [
            'role' => $role,
            'assignments' => is_array($result['data']['assignments'] ?? null) ? $result['data']['assignments'] : [],
            'tenant_scoped' => $role === 'CupAdmin',
        ]);
    }

    public function grantSystemRole(): array
    {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $client = new BackendApiClient();
        $result = $client->grantSystemRole($userId, ['role' => 'SystemAdmin']);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'SystemAdmin gitt' : ($result['error'] ?? 'Kunne ikke gi rolle'),
            $result['errors'] ?? []
        );

        return Response::redirect('/platform/roles?user_id=' . $userId);
    }

    public function revokeSystemRole(): array
    {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $client = new BackendApiClient();
        $result = $client->revokeSystemRole($userId, 'SystemAdmin');
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'SystemAdmin fjernet' : ($result['error'] ?? 'Kunne ikke fjerne rolle')
        );

        return Response::redirect('/platform/roles?user_id=' . $userId);
    }

    public function grantTenantAccess(): array
    {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $tenantId = (int) ($_POST['tenant_id'] ?? 0);
        $client = new BackendApiClient();
        $result = $client->grantTenantAccess($userId, [
            'tenant_id' => $tenantId,
            'role' => 'CupAdmin',
        ]);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'CupAdmin gitt' : ($result['error'] ?? 'Kunne ikke gi tilgang'),
            $result['errors'] ?? []
        );

        return Response::redirect('/platform/roles?user_id=' . $userId);
    }

    public function revokeTenantAccess(): array
    {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $accessId = (int) ($_POST['access_id'] ?? 0);
        $client = new BackendApiClient();
        $result = $client->revokeTenantAccess($userId, $accessId);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Tilgang fjernet' : ($result['error'] ?? 'Kunne ikke fjerne tilgang')
        );

        return Response::redirect('/platform/roles?user_id=' . $userId);
    }
}
