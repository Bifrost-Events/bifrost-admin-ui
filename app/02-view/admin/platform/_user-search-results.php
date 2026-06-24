<?php

declare(strict_types=1);

use App\Support\UserSearch;

/** @var list<array<string, mixed>> $users */
/** @var string $search */
/** @var callable(string): string $h */
/** @var string $mode list|pick */
/** @var string|null $pick_base_url */
/** @var string $pick_query_param */

$mode = (string) ($mode ?? 'list');
$pickBase = (string) ($pick_base_url ?? '/platform/roles');
$pickParam = (string) ($pick_query_param ?? 'user_id');
$q = (string) ($search ?? '');
$searchActive = UserSearch::isActive($q);
?>
<?php if ($q !== '' && !$searchActive): ?>
    <p class="muted">Skriv minst <?= UserSearch::MIN_LENGTH ?> tegn for å søke.</p>
<?php elseif ($searchActive && $users === []): ?>
    <p class="muted">Ingen brukere matcher «<?= $h($q) ?>».</p>
<?php elseif ($users === [] && $mode === 'list'): ?>
    <p class="muted">Ingen brukere registrert.</p>
<?php elseif ($users !== []): ?>
    <?php if ($searchActive): ?>
        <p class="muted"><?= count($users) ?> treff<?= count($users) >= 50 ? ' (maks 50 vises)' : '' ?> for «<?= $h($q) ?>».</p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Navn</th>
                <th>E-post</th>
                <?php if ($mode === 'list'): ?>
                    <th>Første cup</th>
                <?php endif; ?>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $row): ?>
            <?php if (!is_array($row)) { continue; } ?>
            <?php $uid = (int) ($row['id'] ?? 0); ?>
            <tr>
                <td><?= $uid ?></td>
                <td><?= $h((string) ($row['name'] ?? '')) ?></td>
                <td><?= $h((string) ($row['email'] ?? '')) ?></td>
                <?php if ($mode === 'list'): ?>
                    <td>
                        <?php if (!empty($row['first_registered_tenant_name'])): ?>
                            <?= $h((string) $row['first_registered_tenant_name']) ?>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
                <td class="<?= !empty($row['is_active']) ? 'status-active' : 'status-inactive' ?>">
                    <?= !empty($row['is_active']) ? 'Aktiv' : 'Inaktiv' ?>
                </td>
                <td>
                    <?php if ($mode === 'pick'): ?>
                        <a href="<?= $h($pickBase) ?>?<?= $h($pickParam) ?>=<?= $uid ?><?= $searchActive ? '&amp;q=' . rawurlencode($q) : '' ?>">Velg</a>
                    <?php else: ?>
                        <a href="/platform/users/<?= $uid ?>">Vis</a>
                        ·
                        <a href="/platform/users/<?= $uid ?>/edit">Rediger</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
