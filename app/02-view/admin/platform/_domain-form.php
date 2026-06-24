<?php

declare(strict_types=1);

/** @var array<string, mixed> $domain */
/** @var int $tenant_id */
/** @var string $form_action */
/** @var string $form_title */
/** @var array<string, string> $errors */
/** @var string|null $error */
/** @var bool $is_edit */
/** @var string|null $list_path */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$tenantId = (int) ($tenant_id ?? ($domain['tenant_id'] ?? 0));
$listPath = (string) ($list_path ?? '/platform/cuper');
$purpose = (string) ($domain['purpose'] ?? 'public');
if ($purpose === 'arrangor') {
    $purpose = 'organizer';
}
$isEdit = (bool) ($is_edit ?? isset($domain['id']));
$cancelUrl = $listPath . '#tenant-' . $tenantId;
?>
<div class="domain-form-box">
    <h3 class="domain-form-title"><?= $h($form_title) ?></h3>

    <?php
    $errors = $errors ?? [];
    $error = $error ?? null;
    include __DIR__ . '/../_form-errors.php';
    ?>

    <form method="post" action="<?= $h($form_action) ?>" class="form-grid">
        <div>
            <label for="host-<?= $tenantId ?>-<?= $isEdit ? 'edit' : 'add' ?>">Host *</label>
            <input id="host-<?= $tenantId ?>-<?= $isEdit ? 'edit' : 'add' ?>" name="host" required
                   placeholder="namdal.jaktfeltkarusell.local"
                   value="<?= $h((string) ($domain['host'] ?? '')) ?>">
        </div>
        <div>
            <label for="purpose-<?= $tenantId ?>-<?= $isEdit ? 'edit' : 'add' ?>">Type *</label>
            <select id="purpose-<?= $tenantId ?>-<?= $isEdit ? 'edit' : 'add' ?>" name="purpose">
                <option value="public" <?= $purpose === 'public' ? 'selected' : '' ?>>Offentlig (public)</option>
                <option value="admin" <?= $purpose === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="api" <?= $purpose === 'api' ? 'selected' : '' ?>>API</option>
                <option value="organizer" <?= in_array($purpose, ['organizer', 'arrangor'], true) ? 'selected' : '' ?>>Arrangør (organizer)</option>
            </select>
        </div>
        <div class="checkbox-row">
            <input type="checkbox" id="is_primary-<?= $tenantId ?>-<?= $isEdit ? 'edit' : 'add' ?>" name="is_primary" value="1"
                <?= !empty($domain['is_primary']) ? 'checked' : '' ?>>
            <label for="is_primary-<?= $tenantId ?>-<?= $isEdit ? 'edit' : 'add' ?>">Primært domene</label>
        </div>
        <div class="toolbar">
            <button type="submit" class="btn btn-primary">Lagre domene</button>
            <a class="btn" href="<?= $h($cancelUrl) ?>">Avbryt</a>
        </div>
    </form>
</div>
