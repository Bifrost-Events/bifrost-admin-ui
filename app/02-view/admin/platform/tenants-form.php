<?php

declare(strict_types=1);

use App\Support\TenantTypes;

/** @var array<string, mixed>|null $tenant */
/** @var string $form_action */
/** @var string $form_title */
/** @var array<string, string> $errors */
/** @var string|null $error */
/** @var array<string, mixed>|null $user */
/** @var string|null $preset_type */
/** @var string $cancel_path */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$systemCodeLabel = TenantTypes::systemCodeLabel();
$systemCodeHint = TenantTypes::systemCodeHint();
$presetType = isset($preset_type) && is_string($preset_type) && $preset_type !== '' ? $preset_type : null;
$cancelPath = (string) ($cancel_path ?? '/platform/cuper');
$isSystemAdmin = false;
if (is_array($user)) {
    foreach ($user['system_roles'] ?? [] as $role) {
        if (is_array($role) && ($role['role'] ?? '') === 'SystemAdmin') {
            $isSystemAdmin = true;
            break;
        }
    }
}
$isCreate = $tenant === null || !isset($tenant['id']);
$data = is_array($tenant) ? $tenant : [];
?>
<h1><?= $h($form_title) ?></h1>

<?php include __DIR__ . '/../_form-errors.php'; ?>

<form method="post" action="<?= $h($form_action) ?>" class="form-grid">
    <div>
        <label for="slug"><?= $h($systemCodeLabel) ?> *</label>
        <input id="slug" name="slug" required value="<?= $h((string) ($data['slug'] ?? '')) ?>"
               pattern="[a-z0-9][a-z0-9-]{1,62}"
               <?= $isCreate && !$isSystemAdmin ? 'readonly' : '' ?>>
        <?php if ($systemCodeHint !== ''): ?>
            <p class="muted" style="margin:0.35rem 0 0;font-size:0.88rem;"><?= $h($systemCodeHint) ?></p>
        <?php endif; ?>
    </div>
    <div>
        <label for="name">Navn *</label>
        <input id="name" name="name" required value="<?= $h((string) ($data['name'] ?? '')) ?>">
    </div>
    <div>
        <label for="tenant_type">Type *</label>
        <?php if ($presetType !== null && $isCreate): ?>
            <input type="hidden" name="tenant_type" value="<?= $h($presetType) ?>">
            <p class="muted" style="margin:0;"><?= $h(TenantTypes::typeLabel($presetType)) ?></p>
        <?php else: ?>
        <select id="tenant_type" name="tenant_type" <?= !$isSystemAdmin ? 'disabled' : '' ?>>
            <?php foreach (TenantTypes::selectOptions() as $option): ?>
                <option value="<?= $h($option['value']) ?>"
                    <?= ($data['tenant_type'] ?? 'cup') === $option['value'] ? 'selected' : '' ?>>
                    <?= $h($option['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!$isSystemAdmin): ?>
            <input type="hidden" name="tenant_type" value="<?= $h((string) ($data['tenant_type'] ?? 'cup')) ?>">
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="active" <?= ($data['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktiv</option>
            <option value="inactive" <?= ($data['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inaktiv</option>
        </select>
    </div>
    <div class="toolbar">
        <button type="submit" class="btn btn-primary">Lagre</button>
        <a class="btn" href="<?= $h($cancelPath) ?>">Avbryt</a>
    </div>
</form>
