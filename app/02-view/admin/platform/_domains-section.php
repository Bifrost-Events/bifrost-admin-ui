<?php

declare(strict_types=1);

/** @var int $tenant_id */
/** @var list<array<string, mixed>> $domains */
/** @var array<string, mixed>|null $edit_domain */
/** @var array<string, mixed>|null $add_domain_form */
/** @var array<string, string> $domain_errors */
/** @var string|null $domain_error */
/** @var string|null $domains_api_error */
/** @var string|null $list_path */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$tenantId = (int) $tenant_id;
$listPath = (string) ($list_path ?? '/platform/cuper');
$purposeLabels = [
    'public' => 'Offentlig (public)',
    'admin' => 'Admin',
    'api' => 'API',
    'arrangor' => 'Arrangør (organizer)',
];
$editDomain = is_array($edit_domain) ? $edit_domain : null;
$showAddForm = $editDomain === null;
?>
<section class="type-section domains-section">
    <h2 class="type-section-title">Domener</h2>
    <p class="muted type-section-lead">
        Hostnavn knyttet til denne enheten. Sletting er permanent (ingen aktiv/inaktiv-status i databasen ennå).
    </p>

    <?php if ($domains_api_error): ?>
        <p class="form-error"><?= $h($domains_api_error) ?></p>
    <?php endif; ?>

    <?php if ($domains === []): ?>
        <p class="muted">Ingen domener registrert.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Host</th>
                    <th>Type</th>
                    <th>Primær</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($domains as $domain): ?>
                <?php if (!is_array($domain)) { continue; } ?>
                <?php
                $did = (int) ($domain['id'] ?? 0);
                $purpose = (string) ($domain['purpose'] ?? 'public');
                $isEditingThis = $editDomain !== null && (int) ($editDomain['id'] ?? 0) === $did;
                ?>
                <tr<?= $isEditingThis ? ' class="row-editing"' : '' ?>>
                    <td><code><?= $h((string) ($domain['host'] ?? '')) ?></code></td>
                    <td><?= $h($purposeLabels[$purpose] ?? $purpose) ?></td>
                    <td><?= !empty($domain['is_primary']) ? 'Ja' : 'Nei' ?></td>
                    <td>
                        <?php if (!$isEditingThis): ?>
                            <a href="<?= $h($listPath) ?>?edit_tenant=<?= $tenantId ?>&amp;edit_domain=<?= $did ?>#tenant-<?= $tenantId ?>">Rediger</a>
                            ·
                            <form class="inline-form" method="post"
                                  action="/platform/tenants/<?= $tenantId ?>/domains/<?= $did ?>/delete"
                                  onsubmit="return confirm('Fjerne dette domenet?');">
                                <button type="submit" class="btn btn-danger">Fjern</button>
                            </form>
                        <?php else: ?>
                            <span class="muted">Redigeres under</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($editDomain !== null): ?>
        <?php
        $domain = $editDomain;
        $form_action = '/platform/tenants/' . $tenantId . '/domains/' . (int) ($editDomain['id'] ?? 0) . '/edit';
        $form_title = 'Rediger domene';
        $is_edit = true;
        $errors = $domain_errors ?? [];
        $error = $domain_error ?? null;
        include __DIR__ . '/_domain-form.php';
        ?>
    <?php elseif ($showAddForm): ?>
        <?php
        $domain = is_array($add_domain_form) ? $add_domain_form : ['tenant_id' => $tenantId];
        $form_action = '/platform/tenants/' . $tenantId . '/domains';
        $form_title = 'Legg til domene';
        $is_edit = false;
        $errors = $domain_errors ?? [];
        $error = $domain_error ?? null;
        include __DIR__ . '/_domain-form.php';
        ?>
    <?php endif; ?>
</section>
