<?php

declare(strict_types=1);

use App\Controller\CupExperienceController;
use App\Controller\CupSeasonsController;
use App\Controller\AdminPageController;
use App\Controller\HomeController;
use App\Controller\LoginController;
use App\Controller\PlatformOrganizationsController;
use App\Controller\PlatformRolesController;
use App\Controller\PlatformTenantsController;
use App\Controller\PlatformUsersController;
use App\Support\AdminMenu;
use App\Support\Router;

return function (array $app): Router {
    $router = new Router();
    $login = new LoginController();
    $adminPage = new AdminPageController();
    $tenants = new PlatformTenantsController();
    $users = new PlatformUsersController();
    $roles = new PlatformRolesController();
    $organizations = new PlatformOrganizationsController();
    $cupSeasons = new CupSeasonsController();
    $cupExperience = new CupExperienceController();

    $router->get('/login', fn () => $login->showForm());
    $router->post('/login', fn () => $login->submit());
    $router->post('/logout', fn () => $login->logout());
    $router->get('/', fn () => (new HomeController())());

    $router->get('/platform/cuper', fn () => $tenants->indexCups());
    $router->get('/platform/cuper/new', fn () => $tenants->createFormCups());
    $router->post('/platform/cuper', fn () => $tenants->createCups());

    $router->get('/platform/plattform', fn () => $tenants->indexPlatform());
    $router->get('/platform/plattform/new', fn () => $tenants->createFormPlatform());
    $router->post('/platform/plattform', fn () => $tenants->createPlatform());

    $router->get('/platform/tenants', fn () => $tenants->indexLegacy());
    $router->get('/platform/domains', fn () => $tenants->domainsLegacy());
    $router->get('/platform/tenants/{id}', fn (int $id) => $tenants->show($id));
    $router->get('/platform/tenants/{id}/edit', fn (int $id) => $tenants->editForm($id));
    $router->post('/platform/tenants/{id}/edit', fn (int $id) => $tenants->update($id));
    $router->post('/platform/tenants/{id}/deactivate', fn (int $id) => $tenants->deactivate($id));
    $router->post('/platform/tenants/{tenantId}/domains', fn (int $tenantId) => $tenants->createDomain($tenantId));
    $router->post('/platform/tenants/{tenantId}/domains/{domainId}/edit', fn (int $tenantId, int $domainId) => $tenants->updateDomain($tenantId, $domainId));
    $router->post('/platform/tenants/{tenantId}/domains/{domainId}/delete', fn (int $tenantId, int $domainId) => $tenants->deleteDomain($tenantId, $domainId));

    $router->get('/platform/users', fn () => $users->index());
    $router->get('/platform/users/new', fn () => $users->createForm());
    $router->post('/platform/users', fn () => $users->create());
    $router->get('/platform/users/{id}', fn (int $id) => $users->show($id));
    $router->get('/platform/users/{id}/edit', fn (int $id) => $users->editForm($id));
    $router->post('/platform/users/{id}/edit', fn (int $id) => $users->update($id));
    $router->post('/platform/users/{id}/deactivate', fn (int $id) => $users->deactivate($id));

    $router->get('/platform/roles/assignments/{role}', fn (string $role) => $roles->assignments($role));
    $router->get('/platform/roles', fn () => $roles->index());
    $router->post('/platform/roles/grant-system', fn () => $roles->grantSystemRole());
    $router->post('/platform/roles/revoke-system', fn () => $roles->revokeSystemRole());
    $router->post('/platform/roles/grant-tenant', fn () => $roles->grantTenantAccess());
    $router->post('/platform/roles/revoke-tenant', fn () => $roles->revokeTenantAccess());

    $router->get('/platform/organizations', fn () => $organizations->index());
    $router->get('/platform/organizations/new', fn () => $organizations->createForm());
    $router->post('/platform/organizations', fn () => $organizations->create());
    $router->get('/platform/organizations/{id}', fn (int $id) => $organizations->show($id));
    $router->get('/platform/organizations/{id}/edit', fn (int $id) => $organizations->editForm($id));
    $router->post('/platform/organizations/{id}/edit', fn (int $id) => $organizations->update($id));
    $router->post('/platform/organizations/{id}/deactivate', fn (int $id) => $organizations->deactivate($id));
    $router->post('/platform/organizations/{id}/members', fn (int $id) => $organizations->addMember($id));
    $router->post('/platform/organizations/{id}/members/{memberId}/delete', fn (int $id, int $memberId) => $organizations->removeMember($id, $memberId));

    $router->get('/cup/seasons', fn () => $cupSeasons->index());
    $router->get('/cup/experience', fn () => $cupExperience->index());
    $router->post('/cup/seasons', fn () => $cupSeasons->createSeason());
    $router->post('/cup/seasons/rounds', fn () => $cupSeasons->createRoundFromPost());
    $router->post('/cup/seasons/{id}/cup-standings', fn (int $id) => $cupSeasons->updateCupStandings($id));

    $platformPaths = [
        '/platform/cuper',
        '/platform/plattform',
        '/platform/users',
        '/platform/roles',
        '/platform/organizations',
        '/cup/seasons',
        '/cup/experience',
    ];

    foreach (AdminMenu::allPages() as $page) {
        if (!is_array($page)) {
            continue;
        }
        $pageId = (string) ($page['id'] ?? '');
        $path = (string) ($page['path'] ?? '');
        if ($pageId === '' || $path === '' || $path === '/') {
            continue;
        }
        if (in_array($path, $platformPaths, true)) {
            continue;
        }
        $router->get($path, static fn () => $adminPage->show($pageId));
    }

    return $router;
};
