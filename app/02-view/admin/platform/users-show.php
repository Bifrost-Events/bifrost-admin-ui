<?php

declare(strict_types=1);

/** @var array<string, mixed> $profile */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$id = (int) ($profile['id'] ?? 0);
?>
<h1><?= $h((string) ($profile['name'] ?? 'Bruker')) ?></h1>
<p class="lead"><?= $h((string) ($profile['email'] ?? '')) ?></p>

<dl>
    <dt>ID</dt><dd><?= $id ?></dd>
    <dt>Telefon</dt><dd><?= $h((string) ($profile['phone'] ?? '—')) ?></dd>
    <dt>Status</dt>
    <dd class="<?= !empty($profile['is_active']) ? 'status-active' : 'status-inactive' ?>">
        <?= !empty($profile['is_active']) ? 'Aktiv' : 'Inaktiv' ?>
    </dd>
    <dt>Første registrerte cup</dt>
    <dd>
        <?php if (!empty($profile['first_registered_tenant_name'])): ?>
            <?= $h((string) $profile['first_registered_tenant_name']) ?>
            <span class="muted">(<?= $h((string) ($profile['first_registered_tenant_slug'] ?? '')) ?>)</span>
        <?php else: ?>
            <span class="muted">Ikke satt</span>
        <?php endif; ?>
    </dd>
    <dt>Siste innlogging</dt><dd><?= $h((string) ($profile['last_login_at'] ?? '—')) ?></dd>
</dl>

<div class="toolbar">
    <a class="btn" href="/platform/users">Tilbake</a>
    <a class="btn btn-primary" href="/platform/users/<?= $id ?>/edit">Rediger</a>
    <a class="btn" href="/platform/roles?user_id=<?= $id ?>">Roller og tilganger</a>
    <?php if (!empty($profile['is_active'])): ?>
        <form class="inline-form" method="post" action="/platform/users/<?= $id ?>/deactivate"
              onsubmit="return confirm('Deaktivere denne brukeren?');">
            <button type="submit" class="btn btn-danger">Deaktiver</button>
        </form>
    <?php endif; ?>
</div>
