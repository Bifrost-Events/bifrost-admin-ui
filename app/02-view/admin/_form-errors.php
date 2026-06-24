<?php

declare(strict_types=1);

/** @var array<string, string> $errors */
/** @var string|null $error */
$errors = $errors ?? [];
$error = $error ?? null;
$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<?php if ($error !== null && $error !== ''): ?>
    <div class="form-error"><?= $h($error) ?></div>
<?php endif; ?>
<?php if ($errors !== []): ?>
    <ul class="form-errors">
        <?php foreach ($errors as $field => $message): ?>
            <li><strong><?= $h((string) $field) ?>:</strong> <?= $h((string) $message) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
