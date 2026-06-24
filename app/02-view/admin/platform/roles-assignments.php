<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $page */
/** @var string $role */
/** @var list<array<string, mixed>> $assignments */
/** @var bool $tenant_scoped */
/** @var array{type: string, message: string, errors: array<string, string>}|null $flash */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$roleName = (string) ($role ?? '');
$tenantScoped = (bool) ($tenant_scoped ?? false);
?>
<?php include __DIR__ . '/../_flash.php'; ?>

<h1>Tildelinger: <?= $h($roleName) ?></h1>
<p class="lead">
    <?php if ($tenantScoped): ?>
        Alle cup-tilganger med rollen <?= $h($roleName) ?>.
    <?php else: ?>
        Alle brukere med rollen <?= $h($roleName) ?>.
    <?php endif; ?>
</p>

<div class="toolbar">
    <a class="btn" href="/platform/roles">Tilbake til roller</a>
</div>

<?php if ($assignments === []): ?>
    <p class="muted">Ingen tildelinger.</p>
<?php else: ?>
    <p class="muted"><?= count($assignments) ?> <?= $tenantScoped ? 'tilganger' : 'brukere' ?>.</p>
    <table>
        <thead>
            <tr>
                <th>Bruker</th>
                <th>E-post</th>
                <?php if ($tenantScoped): ?>
                    <th>Cup</th>
                <?php endif; ?>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($assignments as $row): ?>
            <?php if (!is_array($row)) { continue; } ?>
            <?php $userId = (int) ($row['user_id'] ?? 0); ?>
            <tr>
                <td><?= $h((string) ($row['user_name'] ?? '')) ?></td>
                <td><?= $h((string) ($row['user_email'] ?? '')) ?></td>
                <?php if ($tenantScoped): ?>
                    <td>
                        <?= $h((string) ($row['tenant_name'] ?? '')) ?>
                        <?php if (($row['tenant_slug'] ?? '') !== ''): ?>
                            <span class="muted">(<code><?= $h((string) $row['tenant_slug']) ?></code>)</span>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
                <td class="<?= !empty($row['user_is_active']) ? 'status-active' : 'status-inactive' ?>">
                    <?= !empty($row['user_is_active']) ? 'Aktiv' : 'Inaktiv' ?>
                </td>
                <td>
                    <a href="/platform/roles?user_id=<?= $userId ?>">Administrer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
