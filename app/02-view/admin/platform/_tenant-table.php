<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var callable(string): string $h */
/** @var string $system_code_label */
/** @var string $list_path */
/** @var array{tenant_id: int, edit_domain_id: int|null, form: array<string, mixed>, errors: array<string, string>, error: string}|null $domain_form_state */
/** @var int $edit_tenant_id */
/** @var int $edit_domain_id */

$purposeLabels = [
    'public' => 'Offentlig',
    'admin' => 'Admin',
    'api' => 'API',
    'arrangor' => 'Arrangør',
    'organizer' => 'Arrangør',
];
$formState = is_array($domain_form_state ?? null) ? $domain_form_state : null;

?>
<table class="tenant-tree-table">
    <thead>
        <tr>
            <th>ID</th>
            <th><?= $h($system_code_label) ?></th>
            <th>Navn / host</th>
            <th>Type</th>
            <th>Primær</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if ($rows === []): ?>
        <tr>
            <td colspan="7" class="muted">—</td>
        </tr>
    <?php else: ?>
        <?php foreach ($rows as $tenant): ?>
            <?php if (!is_array($tenant)) { continue; } ?>
            <?php
            $id = (int) ($tenant['id'] ?? 0);
            $status = (string) ($tenant['status'] ?? '');
            $domains = is_array($tenant['domains'] ?? null) ? $tenant['domains'] : [];
            $domainsApiError = $tenant['domains_api_error'] ?? null;

            $editDomain = null;
            $addDomainForm = null;
            $domainErrors = [];
            $domainError = null;

            if ($formState !== null && (int) ($formState['tenant_id'] ?? 0) === $id) {
                $domainErrors = is_array($formState['errors'] ?? null) ? $formState['errors'] : [];
                $domainError = isset($formState['error']) ? (string) $formState['error'] : null;
                $stateEditId = $formState['edit_domain_id'] ?? null;
                if ($stateEditId !== null && (int) $stateEditId > 0) {
                    $editDomain = is_array($formState['form'] ?? null) ? $formState['form'] : null;
                } else {
                    $addDomainForm = is_array($formState['form'] ?? null) ? $formState['form'] : ['tenant_id' => $id];
                }
            } elseif ($edit_tenant_id === $id && $edit_domain_id > 0) {
                foreach ($domains as $row) {
                    if (is_array($row) && (int) ($row['id'] ?? 0) === $edit_domain_id) {
                        $editDomain = $row;
                        break;
                    }
                }
            }
            $showAddForm = $editDomain === null;
            ?>
            <tr class="tenant-row" id="tenant-<?= $id ?>">
                <td><?= $id ?></td>
                <td><code><?= $h((string) ($tenant['slug'] ?? '')) ?></code></td>
                <td class="tenant-name"><?= $h((string) ($tenant['name'] ?? '')) ?></td>
                <td class="muted">—</td>
                <td class="muted">—</td>
                <td class="<?= $status === 'active' ? 'status-active' : 'status-inactive' ?>">
                    <?= $status === 'active' ? 'Aktiv' : ($status === 'inactive' ? 'Inaktiv' : $h($status)) ?>
                </td>
                <td>
                    <a href="/platform/tenants/<?= $id ?>/edit">Rediger</a>
                </td>
            </tr>

            <?php if ($domainsApiError): ?>
                <tr class="domain-row domain-row-error">
                    <td></td>
                    <td colspan="6" class="form-error"><?= $h((string) $domainsApiError) ?></td>
                </tr>
            <?php elseif ($domains === []): ?>
                <tr class="domain-row domain-row-empty">
                    <td></td>
                    <td colspan="6" class="muted domain-indent">Ingen domener</td>
                </tr>
            <?php else: ?>
                <?php foreach ($domains as $domain): ?>
                    <?php if (!is_array($domain)) { continue; } ?>
                    <?php
                    $did = (int) ($domain['id'] ?? 0);
                    $purpose = (string) ($domain['purpose'] ?? 'public');
                    $isEditingThis = $editDomain !== null && (int) ($editDomain['id'] ?? 0) === $did;
                    ?>
                    <tr class="domain-row<?= $isEditingThis ? ' row-editing' : '' ?>">
                        <td></td>
                        <td></td>
                        <td class="domain-indent"><code><?= $h((string) ($domain['host'] ?? '')) ?></code></td>
                        <td><?= $h($purposeLabels[$purpose] ?? $purpose) ?></td>
                        <td><?= !empty($domain['is_primary']) ? 'Ja' : 'Nei' ?></td>
                        <td></td>
                        <td>
                            <?php if (!$isEditingThis): ?>
                                <a href="<?= $h($list_path) ?>?edit_tenant=<?= $id ?>&amp;edit_domain=<?= $did ?>#tenant-<?= $id ?>">Rediger</a>
                                ·
                                <form class="inline-form" method="post"
                                      action="/platform/tenants/<?= $id ?>/domains/<?= $did ?>/delete"
                                      onsubmit="return confirm('Fjerne dette domenet?');">
                                    <button type="submit" class="btn btn-danger">Fjern</button>
                                </form>
                            <?php else: ?>
                                <span class="muted">Redigeres under</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($editDomain !== null): ?>
                <tr class="domain-form-row">
                    <td colspan="7">
                        <?php
                        $domain = $editDomain;
                        $tenant_id = $id;
                        $form_action = '/platform/tenants/' . $id . '/domains/' . (int) ($editDomain['id'] ?? 0) . '/edit';
                        $form_title = 'Rediger domene';
                        $is_edit = true;
                        $errors = $domainErrors;
                        $error = $domainError;
                        $list_path = $list_path;
                        include __DIR__ . '/_domain-form.php';
                        ?>
                    </td>
                </tr>
            <?php elseif ($showAddForm): ?>
                <tr class="domain-form-row">
                    <td colspan="7">
                        <?php
                        $domain = is_array($addDomainForm) ? $addDomainForm : ['tenant_id' => $id];
                        $tenant_id = $id;
                        $form_action = '/platform/tenants/' . $id . '/domains';
                        $form_title = 'Legg til domene';
                        $is_edit = false;
                        $errors = $domainErrors;
                        $error = $domainError;
                        $list_path = $list_path;
                        include __DIR__ . '/_domain-form.php';
                        ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
