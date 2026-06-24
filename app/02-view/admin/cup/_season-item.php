<?php

declare(strict_types=1);

/** @var array<string, mixed> $season */
/** @var bool $open */
/** @var array<int, float> $defaultPlacementPoints */
/** @var int $maxPlacementPlace */
/** @var int $defaultCountBest */
/** @var callable $h */

$rounds = is_array($season['rounds'] ?? null) ? $season['rounds'] : [];
$roundCount = count($rounds);
$compCount = 0;
foreach ($rounds as $r) {
    if (is_array($r)) {
        $compCount += count(is_array($r['competitions'] ?? null) ? $r['competitions'] : []);
    }
}

$cupMode = (string) ($season['cup_standings_mode'] ?? 'total_score');
if ($cupMode !== 'placement_points' && $cupMode !== 'total_score') {
    $cupMode = 'total_score';
}
$storedPts = is_array($season['cup_placement_points'] ?? null) ? $season['cup_placement_points'] : [];
$cupCountBest = (int) ($season['cup_standings_count_best'] ?? $defaultCountBest);
if ($cupCountBest < 0) {
    $cupCountBest = 0;
}
if ($cupCountBest > 99) {
    $cupCountBest = 99;
}
$cupCompChoices = is_array($season['cup_competition_choices'] ?? null) ? $season['cup_competition_choices'] : [];
$cupStoredIds = $season['cup_standings_competition_ids'] ?? null;
?>
<details class="season-accordion-item" <?= $open ? 'open' : '' ?> data-season-id="<?= $sid ?>">
    <summary class="season-accordion-summary">
        <span class="season-title"><?= $h((string) ($season['name'] ?? '')) ?> <span class="year"><?= (int) ($season['year'] ?? 0) ?></span></span>
        <?php if (!empty($season['is_active'])): ?><span class="badge badge-active">Aktiv</span><?php endif; ?>
        <span class="season-meta"><?= $roundCount ?> runde<?= $roundCount !== 1 ? 'r' : '' ?>, <?= $compCount ?> stevne<?= $compCount !== 1 ? 'r' : '' ?></span>
        <?php if (!empty($season['start_date']) || !empty($season['end_date'])): ?>
            <span class="season-dates-inline">
                <?= !empty($season['start_date']) ? date('d.m.Y', strtotime((string) $season['start_date'])) : '–' ?>
                –
                <?= !empty($season['end_date']) ? date('d.m.Y', strtotime((string) $season['end_date'])) : '–' ?>
            </span>
        <?php endif; ?>
    </summary>
    <div class="season-accordion-body">
        <section class="cup-standings-settings" aria-label="Sammenlagt for sesong">
            <h3 class="cup-standings-heading">Sammenlagt (cup-system)</h3>
            <p class="cup-standings-help">Styrer hvordan den offentlige sammenlagtlista beregnes for denne sesongen.</p>
            <form method="post" action="/cup/seasons/<?= $sid ?>/cup-standings" class="cup-standings-form">
                <div class="form-group">
                    <label for="cup_mode_<?= $sid ?>">Modus</label>
                    <select id="cup_mode_<?= $sid ?>" name="cup_standings_mode">
                        <option value="placement_points"<?= $cupMode === 'placement_points' ? ' selected' : '' ?>>Poeng etter plassering per stevne</option>
                        <option value="total_score"<?= $cupMode === 'total_score' ? ' selected' : '' ?>>Sum skytepoeng (alle stevner)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cup_count_best_<?= $sid ?>">Antall stevner som teller i total</label>
                    <input type="number" id="cup_count_best_<?= $sid ?>" name="cup_standings_count_best" min="0" max="99" value="<?= $cupCountBest ?>">
                    <p class="muted" style="font-size:0.88rem;margin:4px 0 0"><em>0 = alle cup-stevner teller. F.eks. 6 = beste seks stevner per skytter.</em></p>
                </div>
                <div class="cup-points-trigger<?= $cupMode === 'placement_points' ? ' is-visible' : '' ?>">
                    <p class="muted" style="font-size:0.88rem">Definer cup-poeng for plass 1–<?= $maxPlacementPlace ?>.</p>
                    <button type="button" class="btn-sm cup-open-points-modal">Åpne tabell for poeng per plassering</button>
                </div>
                <div class="cup-points-modal" aria-hidden="true">
                    <div class="cup-points-modal__backdrop" tabindex="-1"></div>
                    <div class="cup-points-modal__dialog" role="dialog" aria-modal="true">
                        <div class="cup-points-modal__head">
                            <h4 class="cup-points-modal__title">Poeng per plassering</h4>
                            <button type="button" class="cup-points-modal__x" aria-label="Lukk">&times;</button>
                        </div>
                        <div class="placement-points-grid">
                            <?php for ($pl = 1; $pl <= $maxPlacementPlace; $pl++): ?>
                                <?php
                                $keyHas = array_key_exists($pl, $storedPts) || array_key_exists((string) $pl, $storedPts);
                                $val = $keyHas ? ($storedPts[$pl] ?? $storedPts[(string) $pl]) : null;
                                if (!$keyHas && $cupMode === 'placement_points' && $storedPts === []) {
                                    $val = $defaultPlacementPoints[$pl] ?? null;
                                }
                                $dispAttr = $val !== null && $val !== '' ? $h((string) $val) : '';
                                ?>
                                <label class="pp-cell">
                                    <span class="pp-pl"><?= $pl ?>.</span>
                                    <input type="number" name="placement_points[<?= $pl ?>]" value="<?= $dispAttr ?>" step="0.001" min="0">
                                </label>
                            <?php endfor; ?>
                        </div>
                        <div class="cup-points-modal__foot">
                            <button type="button" class="btn btn-primary cup-points-modal__done">Ferdig</button>
                        </div>
                    </div>
                </div>
                <?php if ($cupCompChoices !== []): ?>
                <div class="cup-competitions-block" style="margin:16px 0">
                    <p class="muted" style="font-size:0.88rem"><strong>Stevner som teller i cupen</strong>. Alle avkrysset = alle stevner i sesongen.</p>
                    <ul class="cup-competition-checklist">
                        <?php foreach ($cupCompChoices as $cc): ?>
                            <?php if (!is_array($cc)) { continue; } ?>
                            <?php
                            $ccid = (int) ($cc['id'] ?? 0);
                            if ($ccid < 1) {
                                continue;
                            }
                            $isInCup = $cupStoredIds === null || (is_array($cupStoredIds) && in_array($ccid, $cupStoredIds, true));
                            ?>
                            <li>
                                <label class="cup-competition-label">
                                    <input type="checkbox" name="cup_competition_ids[]" value="<?= $ccid ?>"<?= $isInCup ? ' checked' : '' ?>>
                                    <span><?= $h((string) ($cc['name'] ?? 'Stevne')) ?></span>
                                    <span class="cup-competition-meta"><?= date('d.m.Y', strtotime((string) ($cc['competition_date'] ?? 'today'))) ?><?= !empty($cc['is_published']) ? '' : ' (upublisert)' ?></span>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php else: ?>
                <p class="muted" style="font-size:0.9rem">Ingen stevner i sesongen ennå — sammenlagt teller da alle publiserte stevner når de opprettes.</p>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">Lagre sammenlagt-innstillinger</button>
            </form>
        </section>

        <?php if ($rounds !== []): ?>
        <table class="rounds-table">
            <thead>
                <tr>
                    <th>Runde</th>
                    <th>Navn</th>
                    <th>Periode</th>
                    <th>Frist</th>
                    <th>Stevner</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rounds as $round): ?>
                <?php if (!is_array($round)) { continue; } ?>
                <?php $comps = is_array($round['competitions'] ?? null) ? $round['competitions'] : []; ?>
                <tr>
                    <td><strong>Runde <?= (int) ($round['round_number'] ?? 0) ?></strong></td>
                    <td><?= $h((string) ($round['name'] ?? '')) ?></td>
                    <td>
                        <?= date('d.m.Y', strtotime((string) ($round['start_date'] ?? 'today'))) ?>
                        –
                        <?= date('d.m.Y', strtotime((string) ($round['end_date'] ?? 'today'))) ?>
                    </td>
                    <td><?= date('d.m.Y', strtotime((string) ($round['result_deadline'] ?? 'today'))) ?></td>
                    <td><span class="badge"><?= count($comps) ?> stevne<?= count($comps) !== 1 ? 'r' : '' ?></span></td>
                </tr>
                <?php if ($comps !== []): ?>
                <tr class="competitions-row">
                    <td colspan="5">
                        <div class="competitions-list">
                            <?php foreach ($comps as $c): ?>
                                <?php if (!is_array($c)) { continue; } ?>
                                <div class="competition-item">
                                    <strong><?= $h((string) ($c['name'] ?? '')) ?></strong>
                                    <?php if (($c['location'] ?? '') !== ''): ?>
                                        – <?= $h((string) $c['location']) ?>
                                    <?php endif; ?>
                                    (<?= date('d.m.Y', strtotime((string) ($c['competition_date'] ?? 'today'))) ?>)
                                    <?php if (($c['organizer_name'] ?? '') !== ''): ?>
                                        <span class="organizer"><?= $h((string) $c['organizer_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="muted"><em>Ingen runder for denne sesongen.</em></p>
        <?php endif; ?>
    </div>
</details>
