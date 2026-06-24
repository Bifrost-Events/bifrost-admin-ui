<?php

declare(strict_types=1);

use App\Support\TenantTypes;

/** @var array<string, mixed> $tenant */
/** @var array<string, mixed>|null $page */
/** @var array<string, mixed>|null $user */
/** @var string $list_path */
/** @var list<array<string, mixed>> $domains */
/** @var array<string, mixed>|null $edit_domain */
/** @var array<string, mixed>|null $add_domain_form */
/** @var array<string, string> $domain_errors */
/** @var string|null $domain_error */
/** @var string|null $domains_api_error */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$systemCodeLabel = TenantTypes::systemCodeLabel();
$id = (int) ($tenant['id'] ?? 0);
$status = (string) ($tenant['status'] ?? '');
$listPath = (string) ($list_path ?? TenantTypes::listPathForType((string) ($tenant['tenant_type'] ?? 'cup')));
$domains = $domains ?? [];
?>
<h1><?= $h((string) ($tenant['name'] ?? 'Enhet')) ?></h1>
<p class="lead">
    <?= $h(TenantTypes::typeLabel((string) ($tenant['tenant_type'] ?? ''))) ?>
    ·
    <code><?= $h((string) ($tenant['slug'] ?? '')) ?></code>
</p>

<dl>
    <dt>ID</dt><dd><?= $id ?></dd>
    <dt><?= $h($systemCodeLabel) ?></dt><dd><code><?= $h((string) ($tenant['slug'] ?? '')) ?></code></dd>
    <dt>Type</dt><dd><?= $h(TenantTypes::typeLabel((string) ($tenant['tenant_type'] ?? ''))) ?></dd>
    <dt>Status</dt>
    <dd class="<?= $status === 'active' ? 'status-active' : 'status-inactive' ?>">
        <?= $status === 'active' ? 'Aktiv' : ($status === 'inactive' ? 'Inaktiv' : $h($status)) ?>
    </dd>
    <dt>Opprettet</dt><dd><?= $h((string) ($tenant['created_at'] ?? '')) ?></dd>
</dl>

<div class="toolbar">
    <a class="btn" href="<?= $h($listPath) ?>">Tilbake</a>
    <a class="btn btn-primary" href="/platform/tenants/<?= $id ?>/edit">Rediger</a>
    <?php if ($status === 'active'): ?>
        <form class="inline-form" method="post" action="/platform/tenants/<?= $id ?>/deactivate"
              onsubmit="return confirm('Deaktivere «<?= $h((string) ($tenant['name'] ?? '')) ?>»?');">
            <button type="submit" class="btn btn-danger">Deaktiver</button>
        </form>
    <?php endif; ?>
</div>

<?php
$tenant_id = $id;
include __DIR__ . '/_domains-section.php';
?>
