<?php

declare(strict_types=1);

use App\Support\UserSearch;

/** @var array<string, mixed> $organization */
/** @var list<array<string, mixed>> $members */
/** @var list<array<string, mixed>> $users */
/** @var string $search */
/** @var int $pick_user_id */
/** @var array{type: string, message: string, errors: array<string, string>}|null $flash */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$id = (int) ($organization['id'] ?? 0);
$status = (string) ($organization['status'] ?? '');
$districts = is_array($organization['districts'] ?? null) ? $organization['districts'] : [];
$search = (string) ($search ?? '');
$pickUserId = (int) ($pick_user_id ?? 0);
$selectedAuthUserId = $pickUserId;
$memberRoles = ['OWNER' => 'Eier', 'ADMIN' => 'Admin', 'REGISTRAR' => 'Registrar', 'VIEWER' => 'Leser'];
?>
<?php include __DIR__ . '/../_flash.php'; ?>

<h1><?= $h((string) ($organization['name'] ?? 'Organisasjon')) ?></h1>
<p class="lead">
    <?= $h((string) ($organization['tenant_name'] ?? '')) ?>
    ·
    <?= $h((string) ($organization['organization_type'] ?? '')) ?>
</p>

<dl>
    <dt>ID</dt><dd><?= $id ?></dd>
    <dt>Cup</dt><dd><?= $h((string) ($organization['tenant_name'] ?? '')) ?> (<code><?= $h((string) ($organization['tenant_slug'] ?? '')) ?></code>)</dd>
    <?php if (!empty($organization['legacy_jaktfelt_organizer_id'])): ?>
        <dt>Jaktfelt v2-ID</dt><dd><code><?= (int) $organization['legacy_jaktfelt_organizer_id'] ?></code></dd>
    <?php endif; ?>
    <dt>Org.nr.</dt><dd><?= ($organization['organization_number'] ?? '') !== '' ? $h((string) $organization['organization_number']) : '—' ?></dd>
    <dt>Kontakt</dt><dd><?= $h(trim((string) ($organization['contact_person'] ?? '') . ' ' . (string) ($organization['email'] ?? '') . ' ' . (string) ($organization['phone'] ?? ''))) ?: '—' ?></dd>
    <dt>Sted</dt><dd><?= $h(trim((string) ($organization['postal_code'] ?? '') . ' ' . (string) ($organization['city'] ?? ''))) ?: '—' ?></dd>
    <dt>Distrikter</dt>
    <dd>
        <?php if ($districts === []): ?>
            <span class="muted">—</span>
        <?php else: ?>
            <?= $h(implode(', ', array_map('strval', $districts))) ?>
        <?php endif; ?>
    </dd>
    <dt>Status</dt>
    <dd class="<?= $status === 'active' ? 'status-active' : 'status-inactive' ?>">
        <?= $status === 'active' ? 'Aktiv' : ($status === 'inactive' ? 'Inaktiv' : $h($status)) ?>
    </dd>
</dl>

<div class="toolbar">
    <a class="btn" href="/platform/organizations">Tilbake</a>
    <a class="btn btn-primary" href="/platform/organizations/<?= $id ?>/edit">Rediger</a>
    <?php if ($status === 'active'): ?>
        <form class="inline-form" method="post" action="/platform/organizations/<?= $id ?>/deactivate"
              onsubmit="return confirm('Deaktivere denne organisasjonen?');">
            <button type="submit" class="btn btn-danger">Deaktiver</button>
        </form>
    <?php endif; ?>
</div>

<section id="medlemmer" class="type-section">
    <h2 class="type-section-title">Medlemmer</h2>
    <p class="muted type-section-lead">
        Brukere med rolle i organisasjonen (tilsvarer <code>jaktfelt_organizer_members</code>).
    </p>

    <?php if ($members === []): ?>
        <p class="muted">Ingen medlemmer ennå.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Bruker</th><th>E-post</th><th>Rolle</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($members as $member): ?>
                <?php if (!is_array($member)) { continue; } ?>
                <tr>
                    <td>
                        <a href="/platform/users/<?= (int) ($member['auth_user_id'] ?? 0) ?>">
                            <?= $h((string) ($member['user_name'] ?? '')) ?>
                        </a>
                    </td>
                    <td><?= $h((string) ($member['user_email'] ?? '')) ?></td>
                    <td><?= $h((string) ($member['role'] ?? '')) ?></td>
                    <td>
                        <form class="inline-form" method="post"
                              action="/platform/organizations/<?= $id ?>/members/<?= (int) ($member['id'] ?? 0) ?>/delete"
                              onsubmit="return confirm('Fjerne medlem?');">
                            <button type="submit" class="btn btn-danger">Fjern</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h3>Legg til medlem</h3>
    <?php
    $form_action = '/platform/organizations/' . $id;
    $preserve_query = [];
    include __DIR__ . '/_user-search-form.php';
    ?>

    <?php if (UserSearch::isActive($search)): ?>
        <?php
        $mode = 'pick';
        $pick_base_url = '/platform/organizations/' . $id;
        $pick_query_param = 'pick_user_id';
        include __DIR__ . '/_user-search-results.php';
        ?>
    <?php endif; ?>

    <form method="post" action="/platform/organizations/<?= $id ?>/members" class="form-grid member-add-form">
        <div>
            <label for="auth_user_id">Bruker-ID *</label>
            <input id="auth_user_id" name="auth_user_id" type="number" min="1" required
                   value="<?= $selectedAuthUserId > 0 ? $selectedAuthUserId : '' ?>"
                   placeholder="Velg fra søk eller skriv ID">
        </div>
        <div>
            <label for="role">Rolle *</label>
            <select id="role" name="role" required>
                <?php foreach ($memberRoles as $value => $label): ?>
                    <option value="<?= $h($value) ?>"><?= $h($label) ?> (<?= $h($value) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Legg til medlem</button>
        </div>
    </form>
</section>
