<?php



declare(strict_types=1);



/** @var string $backend_api_url */

/** @var array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null} $health */

/** @var array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null} $tenants */

/** @var array<string, mixed>|null $user */



$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');



$user = is_array($user ?? null) ? $user : [];

$systemRoles = is_array($user['system_roles'] ?? null) ? $user['system_roles'] : [];

$tenantAdminAccess = is_array($user['tenant_admin_access'] ?? null) ? $user['tenant_admin_access'] : [];

$userName = trim((string) ($user['name'] ?? ''));

$firstRegisteredTenant = is_array($user['first_registered_tenant'] ?? null) ? $user['first_registered_tenant'] : null;



$healthOk = $health['ok'] ?? false;

$healthData = is_array($health['data'] ?? null) ? $health['data'] : [];

$healthStatus = (string) ($healthData['status'] ?? 'unknown');

$dbStatus = (string) ($healthData['database'] ?? 'n/a');



$tenantRows = [];

if ($tenants['ok'] ?? false) {

    $list = $tenants['data']['tenants'] ?? [];

    if (is_array($list)) {

        $tenantRows = $list;

    }

}



?>

<h2 style="margin-top:0;">Oversikt</h2>

<p class="lead">Dashboard for Bifrost-plattformen. Velg cup-kontekst i toppfeltet for cup-spesifikke menypunkter.</p>



<h3>Innlogget bruker (global konto)</h3>

<?php if ($user === []): ?>

    <p class="muted">Ingen brukerdata i session.</p>

<?php else: ?>

    <ul>

        <li>ID: <strong><?= $h((string) ($user['id'] ?? '')) ?></strong></li>

        <li>E-post: <strong><?= $h((string) ($user['email'] ?? '')) ?></strong></li>

        <?php if ($userName !== ''): ?>

            <li>Navn: <strong><?= $h($userName) ?></strong></li>

        <?php endif; ?>

        <li>Aktiv: <strong><?= ($user['active'] ?? false) ? 'ja' : 'nei' ?></strong></li>

        <li>

            Første registrering (tenant):

            <?php if ($firstRegisteredTenant !== null && ($firstRegisteredTenant['slug'] ?? '') !== ''): ?>

                <strong><?= $h((string) ($firstRegisteredTenant['name'] ?? '')) ?></strong>

                (<code><?= $h((string) ($firstRegisteredTenant['slug'] ?? '')) ?></code>)

            <?php else: ?>

                <span class="muted">—</span>

            <?php endif; ?>

        </li>

        <?php if (!empty($user['last_login_at'])): ?>

            <li>Sist innlogget: <?= $h((string) $user['last_login_at']) ?></li>

        <?php endif; ?>

    </ul>



    <h4>Systemroller (Bifrost plattform)</h4>

    <?php if ($systemRoles === []): ?>

        <p class="muted">Ingen systemroller.</p>

    <?php else: ?>

        <table>

            <thead>

                <tr>

                    <th>Rolle</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($systemRoles as $role): ?>

                    <?php if (!is_array($role)) {

                        continue;

                    } ?>

                    <tr>

                        <td><code><?= $h((string) ($role['role'] ?? '')) ?></code></td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>



    <h4>Tenant admin-tilgang (CupAdmin)</h4>

    <?php if ($tenantAdminAccess === []): ?>

        <p class="muted">Ingen cup-admin-tilgang.</p>

    <?php else: ?>

        <table>

            <thead>

                <tr>

                    <th>Tenant</th>

                    <th>Slug</th>

                    <th>Rolle</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($tenantAdminAccess as $access): ?>

                    <?php if (!is_array($access)) {

                        continue;

                    } ?>

                    <tr>

                        <td><?= $h((string) ($access['tenant_name'] ?? '')) ?></td>

                        <td><code><?= $h((string) ($access['tenant_slug'] ?? '')) ?></code></td>

                        <td><code><?= $h((string) ($access['role'] ?? '')) ?></code></td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

    <p class="muted">Deltakerprofiler (<code>event_participant_profiles</code>) vises ikke her — de er ikke adminroller.</p>

<?php endif; ?>



<h3>API health</h3>

<p>

    Backend: <code><?= $h($backend_api_url) ?></code><br>

    HTTP: <?= $h((string) ($health['status'] ?? 0)) ?>

    <?php if ($healthOk): ?>

        <span class="badge badge-ok">OK</span>

    <?php else: ?>

        <span class="badge badge-bad">Feil</span>

    <?php endif; ?>

</p>

<?php if ($healthOk): ?>

    <ul>

        <li>Status: <strong><?= $h($healthStatus) ?></strong></li>

        <li>Database: <strong><?= $h($dbStatus) ?></strong></li>

        <li>Tidspunkt: <?= $h((string) ($healthData['timestamp'] ?? '')) ?></li>

    </ul>

<?php else: ?>

    <p class="badge badge-bad"><?= $h((string) ($health['error'] ?? 'Ukjent feil')) ?></p>

<?php endif; ?>



<h3>Tenants / cuper</h3>

<?php if (!($tenants['ok'] ?? false)): ?>

    <p class="badge badge-bad"><?= $h((string) ($tenants['error'] ?? 'Kunne ikke hente tenants')) ?></p>

<?php elseif ($tenantRows === []): ?>

    <p>Ingen tenants funnet. Kjør seed fra <code>bifrost-shared</code>.</p>

<?php else: ?>

    <table>

        <thead>

            <tr>

                <th>ID</th>

                <th>Navn</th>

                <th>Slug</th>

                <th>Type</th>

                <th>Status</th>

                <th>Domener</th>

            </tr>

        </thead>

        <tbody>

            <?php foreach ($tenantRows as $tenant): ?>

                <?php if (!is_array($tenant)) {

                    continue;

                } ?>

                <tr>

                    <td><?= $h((string) ($tenant['id'] ?? '')) ?></td>

                    <td><?= $h((string) ($tenant['name'] ?? '')) ?></td>

                    <td><code><?= $h((string) ($tenant['slug'] ?? '')) ?></code></td>

                    <td><?= $h((string) ($tenant['tenant_type'] ?? '')) ?></td>

                    <td><?= $h((string) ($tenant['status'] ?? '')) ?></td>

                    <td>

                        <?php

                        $domains = $tenant['domains'] ?? [];

                        if (!is_array($domains) || $domains === []) {

                            echo '<span class="muted">—</span>';

                        } else {

                            echo '<ul class="domains">';

                            foreach ($domains as $domain) {

                                if (!is_array($domain)) {

                                    continue;

                                }

                                $label = ($domain['host'] ?? '') . ' (' . ($domain['purpose'] ?? '') . ')';

                                echo '<li><code>' . $h($label) . '</code></li>';

                            }

                            echo '</ul>';

                        }

                        ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

<?php endif; ?>

