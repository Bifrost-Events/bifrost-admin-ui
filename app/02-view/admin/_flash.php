<?php

declare(strict_types=1);

/** @var array{type: string, message: string, errors: array<string, string>}|null $flash */
$flash = $flash ?? null;
$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<?php if (is_array($flash) && ($flash['message'] ?? '') !== ''): ?>
    <div class="flash flash-<?= $h((string) $flash['type']) ?>">
        <?= $h((string) $flash['message']) ?>
    </div>
<?php endif; ?>
