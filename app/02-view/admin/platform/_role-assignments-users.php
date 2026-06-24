<?php

declare(strict_types=1);

/** @var array<string, mixed> $assignment_summary */
/** @var string $role */
/** @var int $inline_limit */
/** @var callable(string): string $h */

$roleName = (string) ($role ?? '');
$summary = is_array($assignment_summary ?? null) ? $assignment_summary : [];
$limit = (int) ($inline_limit ?? 5);
$total = array_key_exists('total_count', $summary) && $summary['total_count'] !== null
    ? (int) $summary['total_count']
    : null;
$preview = is_array($summary['preview'] ?? null) ? $summary['preview'] : [];
$tenantScoped = !empty($summary['tenant_scoped']);
?>
<?php if (!empty($summary['restricted'])): ?>
    <span class="muted">Kun SystemAdmin</span>
<?php elseif (($summary['status'] ?? '') === 'planned'): ?>
    <span class="muted">—</span>
<?php elseif ($total === 0): ?>
    <span class="muted">Ingen</span>
<?php else: ?>
    <?php if ($total !== null && $total > $limit): ?>
        <ul class="role-user-list">
            <?php foreach ($preview as $row): ?>
                <?php if (!is_array($row)) { continue; } ?>
                <?php $tenant_scoped = $tenantScoped; ?>
                <?php include __DIR__ . '/_role-assignment-user-item.php'; ?>
            <?php endforeach; ?>
        </ul>
        <p class="role-user-more">
            <a href="/platform/roles/assignments/<?= $h($roleName) ?>">
                Vis alle <?= $total ?> <?= $tenantScoped ? 'tilganger' : 'brukere' ?>
            </a>
        </p>
    <?php else: ?>
        <ul class="role-user-list">
            <?php foreach ($preview as $row): ?>
                <?php if (!is_array($row)) { continue; } ?>
                <?php $tenant_scoped = $tenantScoped; ?>
                <?php include __DIR__ . '/_role-assignment-user-item.php'; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>
