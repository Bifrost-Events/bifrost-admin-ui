<?php

declare(strict_types=1);

use App\Support\TenantTypes;

/** @var array<string, mixed>|null $page */
/** @var list<array<string, mixed>> $tenants */
/** @var string $type_filter */
/** @var string|null $api_error */
/** @var array{type: string, message: string, errors: array<string, string>}|null $flash */
/** @var array<string, mixed>|null $user */
/** @var array{tenant_id: int, edit_domain_id: int|null, form: array<string, mixed>, errors: array<string, string>, error: string}|null $domain_form_state */
/** @var int $edit_tenant_id */
/** @var int $edit_domain_id */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$systemCodeLabel = TenantTypes::systemCodeLabel();
$meta = TenantTypes::typeMeta($type_filter);
$listPath = TenantTypes::listPathForType($type_filter);
$docFile = __DIR__ . '/' . TenantTypes::docPartialForType($type_filter) . '.php';

$isSystemAdmin = false;
if (is_array($user)) {
    foreach ($user['system_roles'] ?? [] as $role) {
        if (is_array($role) && ($role['role'] ?? '') === 'SystemAdmin') {
            $isSystemAdmin = true;
            break;
        }
    }
}
$domain_form_state = is_array($domain_form_state ?? null) ? $domain_form_state : null;
$edit_tenant_id = (int) ($edit_tenant_id ?? 0);
$edit_domain_id = (int) ($edit_domain_id ?? 0);
?>
<?php include __DIR__ . '/../_flash.php'; ?>

<h1><?= $h((string) ($page['title'] ?? $meta['list_heading'])) ?></h1>
<p class="lead"><?= $h((string) ($page['description'] ?? $meta['list_lead'])) ?></p>

<?php if (is_file($docFile)) {
    include $docFile;
} ?>

<?php if ($api_error): ?>
    <p class="form-error"><?= $h($api_error) ?></p>
<?php endif; ?>

<div class="toolbar">
    <?php if ($isSystemAdmin): ?>
        <a class="btn btn-primary" href="<?= $h($listPath) ?>/new">Opprett ny</a>
    <?php endif; ?>
</div>

<?php if ($tenants === []): ?>
    <p class="muted"><?= $h((string) $meta['empty']) ?></p>
<?php else: ?>
    <?php
    $rows = $tenants;
    $system_code_label = $systemCodeLabel;
    $list_path = $listPath;
    include __DIR__ . '/_tenant-table.php';
    ?>
<?php endif; ?>
