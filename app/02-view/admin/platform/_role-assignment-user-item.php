<?php

declare(strict_types=1);

/** @var array<string, mixed> $row */
/** @var callable(string): string $h */
/** @var bool $tenant_scoped */

$userId = (int) ($row['user_id'] ?? 0);
$userName = (string) ($row['user_name'] ?? '');
$userEmail = (string) ($row['user_email'] ?? '');
$showTenant = (bool) ($tenant_scoped ?? false);
?>
<li>
    <a href="/platform/roles?user_id=<?= $userId ?>">
        <?= $h($userName !== '' ? $userName : $userEmail) ?>
    </a>
    <?php if ($showTenant && ($row['tenant_name'] ?? '') !== ''): ?>
        <span class="muted"> — <?= $h((string) $row['tenant_name']) ?></span>
    <?php elseif ($userEmail !== '' && $userName !== ''): ?>
        <span class="muted"> &lt;<?= $h($userEmail) ?>&gt;</span>
    <?php endif; ?>
</li>
