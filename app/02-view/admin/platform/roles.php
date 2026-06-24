<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $page */
/** @var list<array<string, mixed>> $users */
/** @var string $search */
/** @var array<string, mixed>|null $selected_user */
/** @var list<array<string, mixed>> $roles */
/** @var array<string, array<string, mixed>> $role_assignments */
/** @var int $assignment_inline_limit */
/** @var list<array<string, mixed>> $tenants */
/** @var int $selected_user_id */
/** @var array<string, mixed>|null $access */
/** @var bool $is_system_admin */
/** @var string|null $api_error */
/** @var array{type: string, message: string, errors: array<string, string>}|null $flash */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$search = (string) ($search ?? '');
$systemRoles = is_array($access['system_roles'] ?? null) ? $access['system_roles'] : [];
$tenantAccess = is_array($access['tenant_admin_access'] ?? null) ? $access['tenant_admin_access'] : [];
$hasSystemAdmin = false;
foreach ($systemRoles as $row) {
    if (is_array($row) && ($row['role'] ?? '') === 'SystemAdmin') {
        $hasSystemAdmin = true;
        break;
    }
}
$selectedUser = is_array($selected_user ?? null) ? $selected_user : null;
$roleAssignments = is_array($role_assignments ?? null) ? $role_assignments : [];
$assignmentInlineLimit = (int) ($assignment_inline_limit ?? 5);
?>
<?php include __DIR__ . '/../_flash.php'; ?>

<h1><?= $h((string) ($page['title'] ?? 'Roller og tilganger')) ?></h1>
<p class="lead"><?= $h((string) ($page['description'] ?? '')) ?></p>

<?php if ($api_error): ?>
    <p class="form-error"><?= $h($api_error) ?></p>
<?php endif; ?>

<h2>Velg bruker</h2>
<p class="muted">Søk etter bruker for å tildele eller fjerne roller og cup-tilgang.</p>

<?php
$form_action = '/platform/roles';
$preserve_query = $selected_user_id > 0 ? ['user_id' => $selected_user_id] : [];
include __DIR__ . '/_user-search-form.php';
?>

<?php
$mode = 'pick';
$pick_base_url = '/platform/roles';
include __DIR__ . '/_user-search-results.php';
?>

<?php if ($selected_user_id > 0): ?>
    <section class="selected-user-panel">
        <?php if ($selectedUser === null): ?>
            <p class="form-error">Fant ikke bruker #<?= $selected_user_id ?>.</p>
        <?php else: ?>
            <div class="selected-user-header">
                <h2>
                    <?= $h((string) ($selectedUser['name'] ?? '')) ?>
                    <span class="muted">&lt;<?= $h((string) ($selectedUser['email'] ?? '')) ?>&gt;</span>
                </h2>
                <a class="btn" href="/platform/roles">Bytt bruker</a>
            </div>

            <h3>Systemroller</h3>
            <?php if ($systemRoles === []): ?>
                <p class="muted">Ingen systemroller.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($systemRoles as $row): ?>
                        <?php if (!is_array($row)) { continue; } ?>
                        <li>
                            <strong><?= $h((string) ($row['role'] ?? '')) ?></strong>
                            <?php if ($is_system_admin && ($row['role'] ?? '') === 'SystemAdmin'): ?>
                                <form class="inline-form" method="post" action="/platform/roles/revoke-system">
                                    <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">
                                    <button type="submit" class="btn btn-danger">Fjern</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($is_system_admin && !$hasSystemAdmin): ?>
                <form method="post" action="/platform/roles/grant-system" class="toolbar">
                    <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">
                    <button type="submit" class="btn btn-primary">Gi SystemAdmin</button>
                </form>
            <?php endif; ?>

            <h3>Cup-tilganger (CupAdmin)</h3>
            <?php if ($tenantAccess === []): ?>
                <p class="muted">Ingen cup-tilganger.</p>
            <?php else: ?>
                <table>
                    <thead><tr><th>Cup</th><th>Rolle</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($tenantAccess as $row): ?>
                        <?php if (!is_array($row)) { continue; } ?>
                        <tr>
                            <td><?= $h((string) ($row['tenant_name'] ?? '')) ?> (<?= $h((string) ($row['tenant_slug'] ?? '')) ?>)</td>
                            <td><?= $h((string) ($row['role'] ?? '')) ?></td>
                            <td>
                                <form class="inline-form" method="post" action="/platform/roles/revoke-tenant">
                                    <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">
                                    <input type="hidden" name="access_id" value="<?= (int) ($row['id'] ?? 0) ?>">
                                    <button type="submit" class="btn btn-danger">Fjern</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <form method="post" action="/platform/roles/grant-tenant" class="form-grid">
                <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">
                <div>
                    <label for="tenant_id">Gi CupAdmin for cup</label>
                    <select id="tenant_id" name="tenant_id" required>
                        <option value="">— Velg cup —</option>
                        <?php foreach ($tenants as $tenant): ?>
                            <?php if (!is_array($tenant) || ($tenant['tenant_type'] ?? '') !== 'cup') { continue; } ?>
                            <option value="<?= (int) ($tenant['id'] ?? 0) ?>">
                                <?= $h((string) ($tenant['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Gi CupAdmin</button>
                </div>
            </form>
        <?php endif; ?>
    </section>
<?php endif; ?>

<h2>Rolleoversikt</h2>
<table>
    <thead><tr><th>Rolle</th><th>Område</th><th>Brukere</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($roles as $role): ?>
        <?php if (!is_array($role)) { continue; } ?>
        <?php
        $roleName = (string) ($role['role'] ?? '');
        $assignmentSummary = $roleAssignments[$roleName] ?? [];
        $tenantScoped = $roleName === 'CupAdmin';
        ?>
        <tr>
            <td><?= $h($roleName) ?></td>
            <td><?= $h((string) ($role['scope'] ?? '')) ?></td>
            <td class="role-users-cell">
                <?php
                $assignment_summary = $assignmentSummary;
                $role = $roleName;
                $inline_limit = $assignmentInlineLimit;
                $tenant_scoped = $tenantScoped;
                include __DIR__ . '/_role-assignments-users.php';
                ?>
            </td>
            <td>
                <?php if (($role['status'] ?? '') === 'planned'): ?>
                    <span class="muted">Planlagt (organization_*)</span>
                <?php elseif (!empty($role['grantable'])): ?>
                    Kan tildeles
                <?php else: ?>
                    <span class="muted">Kun SystemAdmin kan tildele</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p class="muted">Participant er ikke adminrolle (event_participant_profiles).</p>
