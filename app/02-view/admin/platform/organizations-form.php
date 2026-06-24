<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $organization */
/** @var list<array<string, mixed>> $cup_tenants */
/** @var string $form_action */
/** @var string $form_title */
/** @var int $preset_tenant_id */
/** @var array<string, string> $errors */
/** @var string|null $error */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$isCreate = $organization === null || !isset($organization['id']);
$data = is_array($organization) ? $organization : [];
$selectedTenant = (int) ($data['tenant_id'] ?? ($preset_tenant_id ?? 0));
$districts = is_array($data['districts'] ?? null) ? $data['districts'] : [];
$districtsText = implode("\n", array_map(static fn ($d): string => (string) $d, $districts));
$orgTypes = ['skytterlag' => 'Skytterlag', 'klubb' => 'Klubb', 'forbund' => 'Forbund', 'annet' => 'Annet'];
$selectedType = (string) ($data['organization_type'] ?? 'skytterlag');
$status = (string) ($data['status'] ?? 'active');
?>
<h1><?= $h($form_title) ?></h1>

<?php include __DIR__ . '/../_form-errors.php'; ?>

<form method="post" action="<?= $h($form_action) ?>" class="form-grid">
    <?php if ($isCreate): ?>
        <div>
            <label for="tenant_id">Cup *</label>
            <select id="tenant_id" name="tenant_id" required>
                <option value="">— Velg cup —</option>
                <?php foreach ($cup_tenants as $tenant): ?>
                    <?php if (!is_array($tenant)) { continue; } ?>
                    <?php $tid = (int) ($tenant['id'] ?? 0); ?>
                    <option value="<?= $tid ?>" <?= $tid === $selectedTenant ? 'selected' : '' ?>>
                        <?= $h((string) ($tenant['name'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <div>
        <label for="name">Navn *</label>
        <input id="name" name="name" required value="<?= $h((string) ($data['name'] ?? '')) ?>">
    </div>
    <div>
        <label for="organization_type">Type *</label>
        <select id="organization_type" name="organization_type">
            <?php foreach ($orgTypes as $value => $label): ?>
                <option value="<?= $h($value) ?>" <?= $selectedType === $value ? 'selected' : '' ?>><?= $h($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="organization_number">Org.nr.</label>
        <input id="organization_number" name="organization_number" value="<?= $h((string) ($data['organization_number'] ?? '')) ?>">
    </div>
    <div>
        <label for="contact_person">Kontaktperson</label>
        <input id="contact_person" name="contact_person" value="<?= $h((string) ($data['contact_person'] ?? '')) ?>">
    </div>
    <div>
        <label for="email">E-post</label>
        <input id="email" name="email" type="email" value="<?= $h((string) ($data['email'] ?? '')) ?>">
    </div>
    <div>
        <label for="phone">Telefon</label>
        <input id="phone" name="phone" value="<?= $h((string) ($data['phone'] ?? '')) ?>">
    </div>
    <div>
        <label for="postal_code">Postnr.</label>
        <input id="postal_code" name="postal_code" value="<?= $h((string) ($data['postal_code'] ?? '')) ?>">
    </div>
    <div>
        <label for="city">Sted</label>
        <input id="city" name="city" value="<?= $h((string) ($data['city'] ?? '')) ?>">
    </div>
    <div>
        <label for="districts">Distrikter</label>
        <textarea id="districts" name="districts" rows="3"
                  placeholder="Ett distrikt per linje, f.eks. namdalen"><?= $h($districtsText) ?></textarea>
    </div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktiv</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inaktiv</option>
        </select>
    </div>
    <div class="toolbar">
        <button type="submit" class="btn btn-primary">Lagre</button>
        <a class="btn" href="/platform/organizations">Avbryt</a>
    </div>
</form>
