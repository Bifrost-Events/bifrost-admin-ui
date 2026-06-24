<?php

declare(strict_types=1);

use App\Support\UserSearch;

/** @var array<string, mixed>|null $page */
/** @var list<array<string, mixed>> $organizations */
/** @var list<array<string, mixed>> $cup_tenants */
/** @var int $tenant_id */
/** @var string $search */
/** @var string|null $api_error */
/** @var array{type: string, message: string, errors: array<string, string>}|null $flash */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$search = (string) ($search ?? '');
$tenantId = (int) ($tenant_id ?? 0);
?>
<?php include __DIR__ . '/../_flash.php'; ?>

<h1><?= $h((string) ($page['title'] ?? 'Organisasjoner')) ?></h1>
<p class="lead"><?= $h((string) ($page['description'] ?? '')) ?></p>

<?php include __DIR__ . '/docs/organizations.php'; ?>

<?php if ($api_error): ?>
    <p class="form-error"><?= $h($api_error) ?></p>
<?php endif; ?>

<div class="toolbar">
    <a class="btn btn-primary" href="/platform/organizations/new<?= $tenantId > 0 ? '?tenant_id=' . $tenantId : '' ?>">Opprett organisasjon</a>
</div>

<form method="get" action="/platform/organizations" class="toolbar org-filter-form">
    <label for="tenant_id">Cup</label>
    <select id="tenant_id" name="tenant_id" onchange="this.form.submit()">
        <option value="0">— Alle cuper —</option>
        <?php foreach ($cup_tenants as $tenant): ?>
            <?php if (!is_array($tenant)) { continue; } ?>
            <?php $tid = (int) ($tenant['id'] ?? 0); ?>
            <option value="<?= $tid ?>" <?= $tid === $tenantId ? 'selected' : '' ?>>
                <?= $h((string) ($tenant['name'] ?? '')) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<form method="get" action="/platform/organizations" class="toolbar user-search-form" data-min-length="<?= UserSearch::MIN_LENGTH ?>">
    <?php if ($tenantId > 0): ?>
        <input type="hidden" name="tenant_id" value="<?= $tenantId ?>">
    <?php endif; ?>
    <label for="org-search-q">Søk</label>
    <input type="search" id="org-search-q" name="q" value="<?= $h($search) ?>"
           minlength="<?= UserSearch::MIN_LENGTH ?>"
           placeholder="Minst <?= UserSearch::MIN_LENGTH ?> tegn — navn, org.nr. eller sted"
           autocomplete="off">
    <button type="submit" class="btn btn-primary">Søk</button>
    <?php if ($search !== ''): ?>
        <a class="btn" href="/platform/organizations<?= $tenantId > 0 ? '?tenant_id=' . $tenantId : '' ?>">Nullstill</a>
    <?php endif; ?>
</form>

<?php if (!UserSearch::isActive($search)): ?>
    <p class="muted"><?= count($organizations) ?> organisasjoner<?= $tenantId > 0 ? ' for valgt cup' : '' ?>. Søk aktiveres etter <?= UserSearch::MIN_LENGTH ?> tegn.</p>
<?php endif; ?>

<?php if ($search !== '' && !UserSearch::isActive($search)): ?>
    <p class="muted">Skriv minst <?= UserSearch::MIN_LENGTH ?> tegn for å søke.</p>
<?php elseif (UserSearch::isActive($search) && $organizations === []): ?>
    <p class="muted">Ingen organisasjoner matcher «<?= $h($search) ?>».</p>
<?php elseif ($organizations === []): ?>
    <p class="muted">Ingen organisasjoner registrert.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Navn</th>
                <th>Type</th>
                <th>Cup</th>
                <th>Sted</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($organizations as $org): ?>
            <?php if (!is_array($org)) { continue; } ?>
            <?php $oid = (int) ($org['id'] ?? 0); ?>
            <?php $status = (string) ($org['status'] ?? ''); ?>
            <tr>
                <td><?= $h((string) ($org['name'] ?? '')) ?></td>
                <td><?= $h((string) ($org['organization_type'] ?? '')) ?></td>
                <td><?= $h((string) ($org['tenant_name'] ?? '')) ?></td>
                <td>
                    <?php if (($org['postal_code'] ?? '') !== '' || ($org['city'] ?? '') !== ''): ?>
                        <?= $h(trim((string) ($org['postal_code'] ?? '') . ' ' . (string) ($org['city'] ?? ''))) ?>
                    <?php else: ?>
                        <span class="muted">—</span>
                    <?php endif; ?>
                </td>
                <td class="<?= $status === 'active' ? 'status-active' : 'status-inactive' ?>">
                    <?= $status === 'active' ? 'Aktiv' : ($status === 'inactive' ? 'Inaktiv' : $h($status)) ?>
                </td>
                <td>
                    <a href="/platform/organizations/<?= $oid ?>">Vis</a>
                    ·
                    <a href="/platform/organizations/<?= $oid ?>/edit">Rediger</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
