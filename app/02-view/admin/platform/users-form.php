<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $profile */
/** @var list<array<string, mixed>> $tenants */
/** @var string $form_action */
/** @var string $form_title */
/** @var array<string, string> $errors */
/** @var string|null $error */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$isCreate = $profile === null || !isset($profile['id']);
$data = is_array($profile) ? $profile : [];
$selectedTenant = (int) ($data['first_registered_tenant_id'] ?? 0);
?>
<h1><?= $h($form_title) ?></h1>

<?php include __DIR__ . '/../_form-errors.php'; ?>

<form method="post" action="<?= $h($form_action) ?>" class="form-grid">
    <div>
        <label for="name">Navn *</label>
        <input id="name" name="name" required value="<?= $h((string) ($data['name'] ?? '')) ?>">
    </div>
    <div>
        <label for="email">E-post *</label>
        <input id="email" name="email" type="email" required value="<?= $h((string) ($data['email'] ?? '')) ?>">
    </div>
    <div>
        <label for="phone">Telefon</label>
        <input id="phone" name="phone" value="<?= $h((string) ($data['phone'] ?? '')) ?>">
    </div>
    <div>
        <label for="password">Passord<?= $isCreate ? ' *' : '' ?></label>
        <input id="password" name="password" type="password" <?= $isCreate ? 'required' : '' ?>
               autocomplete="new-password" placeholder="<?= $isCreate ? '' : 'La stå tom for å beholde' ?>">
    </div>
    <div>
        <label for="first_registered_tenant_id">Første registrerte cup</label>
        <select id="first_registered_tenant_id" name="first_registered_tenant_id">
            <option value="">— Ingen —</option>
            <?php foreach ($tenants as $tenant): ?>
                <?php if (!is_array($tenant)) { continue; } ?>
                <?php $tid = (int) ($tenant['id'] ?? 0); ?>
                <option value="<?= $tid ?>" <?= $tid === $selectedTenant ? 'selected' : '' ?>>
                    <?= $h((string) ($tenant['name'] ?? '')) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="checkbox-row">
        <input type="checkbox" id="is_active" name="is_active" value="1"
            <?= ($isCreate || !empty($data['is_active'])) ? 'checked' : '' ?>>
        <label for="is_active">Aktiv bruker</label>
    </div>
    <div class="toolbar">
        <button type="submit" class="btn btn-primary">Lagre</button>
        <a class="btn" href="/platform/users">Avbryt</a>
    </div>
</form>
