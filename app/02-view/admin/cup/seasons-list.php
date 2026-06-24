<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $page */
/** @var list<array<string, mixed>> $seasons */
/** @var int $tenant_id */
/** @var string|null $api_error */
/** @var bool $no_tenant */
/** @var int $open_season_id */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$seasons = is_array($seasons ?? null) ? $seasons : [];
$openSeasonId = (int) ($open_season_id ?? 0);
$defaultPlacementPoints = [
    1 => 25.0, 2 => 18.0, 3 => 15.0, 4 => 12.0, 5 => 10.0,
    6 => 8.0, 7 => 6.0, 8 => 4.0, 9 => 2.0, 10 => 1.0,
];
$maxPlacementPlace = 25;
$defaultCountBest = 6;
?>
<?php include __DIR__ . '/../_flash.php'; ?>

<h1><?= $h((string) ($page['title'] ?? 'Sesonger')) ?></h1>
<p class="lead"><?= $h((string) ($page['description'] ?? '')) ?></p>

<?php include __DIR__ . '/docs/seasons.php'; ?>

<?php if (!empty($no_tenant)): ?>
    <p class="form-error">Velg cup i menyen øverst for å administrere sesonger.</p>
<?php elseif ($api_error): ?>
    <p class="form-error"><?= $h($api_error) ?></p>
<?php else: ?>

<p class="cup-intro muted">Sesonger og runder brukes ved cup-oppsett. Stevner opprettes under <strong>Stevner</strong> (kommer senere).</p>

<?php if ($seasons !== []): ?>
<section class="seasons-accordion" aria-label="Sesonger">
    <?php foreach ($seasons as $idx => $season): ?>
        <?php if (!is_array($season)) { continue; } ?>
        <?php
        $sid = (int) ($season['id'] ?? 0);
        $open = $openSeasonId === $sid || ($openSeasonId === 0 && ($idx === 0 || !empty($season['is_active'])));
        include __DIR__ . '/_season-item.php';
        ?>
    <?php endforeach; ?>
</section>
<?php else: ?>
    <p class="muted">Ingen sesonger registrert ennå. Opprett en sesong nedenfor.</p>
<?php endif; ?>

<section class="create-forms" aria-label="Opprett sesong eller runde">
    <h2 class="section-heading">Opprett sesong eller runde</h2>
    <p class="muted">Brukes ved sesongoppsett. For stevner, gå til <strong>Stevner</strong> når det er klart.</p>

    <details class="create-form-block">
        <summary>Opprett runde</summary>
        <form method="post" action="/cup/seasons/rounds" class="form-grid">
            <div class="form-group">
                <label for="round_season_id">Sesong *</label>
                <select id="round_season_id" name="season_id" required>
                    <option value="">Velg sesong</option>
                    <?php foreach ($seasons as $s): ?>
                        <?php if (!is_array($s)) { continue; } ?>
                        <option value="<?= (int) ($s['id'] ?? 0) ?>">
                            <?= $h((string) ($s['name'] ?? '') . ' ' . (string) ($s['year'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="round_number">Runde-nr</label>
                <input type="number" id="round_number" name="round_number" min="1" max="10" value="1" required>
            </div>
            <div class="form-group">
                <label for="round_name">Navn</label>
                <input type="text" id="round_name" name="name" placeholder="Runde 1" required>
            </div>
            <div class="form-group">
                <label for="round_start">Start</label>
                <input type="date" id="round_start" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="round_end">Slutt</label>
                <input type="date" id="round_end" name="end_date" required>
            </div>
            <div class="form-group">
                <label for="round_deadline">Frist resultater</label>
                <input type="date" id="round_deadline" name="result_deadline" required>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" checked> Aktiv</label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Opprett runde</button>
            </div>
        </form>
    </details>

    <details class="create-form-block">
        <summary>Opprett sesong</summary>
        <form method="post" action="/cup/seasons" class="form-grid">
            <div class="form-group">
                <label for="season_name">Navn *</label>
                <input type="text" id="season_name" name="name" required placeholder="f.eks. Jaktfeltkarusell Namdalen 2026">
            </div>
            <div class="form-group">
                <label for="season_year">År *</label>
                <input type="number" id="season_year" name="year" min="2024" max="2050" value="<?= (int) date('Y') ?>" required>
            </div>
            <div class="form-group">
                <label for="season_start">Startdato</label>
                <input type="date" id="season_start" name="start_date">
            </div>
            <div class="form-group">
                <label for="season_end">Sluttdato</label>
                <input type="date" id="season_end" name="end_date">
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1"> Aktiv sesong</label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Opprett sesong</button>
            </div>
        </form>
    </details>
</section>

<?php endif; ?>

<style>
.cup-intro { margin-bottom: 1.25rem; }
.section-heading { font-size: 1.1rem; margin: 0 0 0.5rem; }
.seasons-accordion { margin: 1em 0 1.5em; }
.season-accordion-item { border: 1px solid var(--line); border-radius: 8px; margin-bottom: 8px; overflow: hidden; background: var(--card); }
.season-accordion-item[open] { border-color: var(--accent); }
.season-accordion-summary { padding: 12px 16px; cursor: pointer; list-style: none; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; background: #f8f9fa; font-weight: 500; }
.season-accordion-summary::-webkit-details-marker { display: none; }
.season-accordion-summary::before { content: '▶'; margin-right: 4px; font-size: 0.75rem; transition: transform 0.2s; }
.season-accordion-item[open] .season-accordion-summary::before { transform: rotate(90deg); }
.season-meta { color: var(--muted); font-size: 0.9rem; font-weight: 400; }
.season-dates-inline { color: var(--muted); font-size: 0.85rem; margin-left: auto; }
.badge { background: #e9ecef; padding: 2px 8px; border-radius: 4px; font-size: 0.85em; }
.badge-active { background: var(--ok); color: #fff; }
.season-accordion-body { padding: 16px; border-top: 1px solid var(--line); }
.rounds-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
.rounds-table th, .rounds-table td { padding: 8px 12px; text-align: left; border-bottom: 1px solid var(--line); }
.rounds-table th { font-weight: 600; background: #f8f9fa; }
.competitions-row td { padding: 0 12px 12px; background: #fafafa; }
.competitions-list { padding-left: 12px; }
.competition-item { padding: 4px 0; font-size: 0.95rem; }
.competition-item .organizer { color: var(--muted); margin-left: 8px; }
.create-forms { margin-top: 2em; padding-top: 1.5em; border-top: 1px solid var(--line); }
.create-form-block { margin-bottom: 12px; border: 1px solid var(--line); border-radius: 6px; overflow: hidden; }
.create-form-block summary { padding: 10px 14px; cursor: pointer; background: #f0f0f0; font-weight: 500; }
.create-form-block form { padding: 16px; }
.form-grid { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; }
.form-group { display: flex; flex-direction: column; gap: 4px; min-width: 140px; }
.form-group label { font-weight: 500; font-size: 0.9rem; }
.form-group input, .form-group select { padding: 8px 12px; border: 1px solid var(--line); border-radius: 4px; }
.form-check { justify-content: flex-end; padding-bottom: 8px; }
.form-actions { width: 100%; }
.cup-standings-settings { margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
.cup-standings-heading { margin: 0 0 8px; font-size: 1rem; }
.cup-standings-help { margin: 0 0 12px; font-size: 0.9rem; color: var(--muted); }
.cup-points-trigger { display: none; margin-bottom: 14px; }
.cup-points-trigger.is-visible { display: block; }
.btn-sm { padding: 8px 16px; font-size: 0.9rem; border: 1px solid var(--accent); background: #fff; color: var(--accent); border-radius: 4px; cursor: pointer; }
.cup-points-modal { display: none; position: fixed; inset: 0; z-index: 10050; align-items: center; justify-content: center; padding: 16px; }
.cup-points-modal.is-open { display: flex; }
.cup-points-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.45); cursor: pointer; }
.cup-points-modal__dialog { position: relative; background: #fff; border-radius: 10px; max-width: 640px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 18px 20px; box-shadow: 0 12px 40px rgba(0,0,0,0.2); z-index: 1; }
.cup-points-modal__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 8px; }
.cup-points-modal__title { margin: 0; font-size: 1.05rem; }
.cup-points-modal__x { border: none; background: transparent; font-size: 1.5rem; cursor: pointer; color: var(--muted); }
.placement-points-grid { display: flex; flex-wrap: wrap; gap: 8px 12px; margin-bottom: 12px; }
.pp-cell { display: flex; flex-direction: column; gap: 2px; font-size: 0.85rem; }
.pp-cell input { width: 4.5rem; padding: 6px 8px; border: 1px solid var(--line); border-radius: 4px; }
.cup-competition-checklist { list-style: none; margin: 0; padding: 8px 12px; max-height: 280px; overflow-y: auto; border: 1px solid var(--line); border-radius: 6px; background: #fafafa; }
.cup-competition-checklist li { margin: 6px 0; }
.cup-competition-label { display: flex; flex-wrap: wrap; gap: 8px 12px; cursor: pointer; font-weight: normal; }
.cup-competition-meta { font-size: 0.85rem; color: var(--muted); }
</style>

<script>
(function() {
    var accordion = document.querySelector('.seasons-accordion');
    if (!accordion) return;
    accordion.querySelectorAll('details.season-accordion-item').forEach(function(details) {
        details.addEventListener('toggle', function() {
            if (!details.open) return;
            accordion.querySelectorAll('details.season-accordion-item').forEach(function(other) {
                if (other !== details) other.open = false;
            });
        });
    });
})();

(function() {
    function bodyLock() {
        var n = document.querySelectorAll('.cup-points-modal.is-open').length;
        document.body.style.overflow = n > 0 ? 'hidden' : '';
    }
    document.addEventListener('keydown', function(ev) {
        if (ev.key !== 'Escape') return;
        var open = document.querySelector('.cup-points-modal.is-open');
        if (!open) return;
        open.classList.remove('is-open');
        open.setAttribute('aria-hidden', 'true');
        bodyLock();
    });
    document.querySelectorAll('.cup-standings-form').forEach(function(form) {
        var select = form.querySelector('select[name="cup_standings_mode"]');
        var trigger = form.querySelector('.cup-points-trigger');
        var modal = form.querySelector('.cup-points-modal');
        if (!select || !trigger || !modal) return;
        var openBtn = form.querySelector('.cup-open-points-modal');
        var closeBtn = modal.querySelector('.cup-points-modal__done');
        var xBtn = modal.querySelector('.cup-points-modal__x');
        var backdrop = modal.querySelector('.cup-points-modal__backdrop');
        function syncTrigger() {
            trigger.classList.toggle('is-visible', select.value === 'placement_points');
            if (select.value !== 'placement_points') closeModal();
        }
        function openModal() {
            if (select.value !== 'placement_points') return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            bodyLock();
        }
        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            bodyLock();
        }
        select.addEventListener('change', syncTrigger);
        syncTrigger();
        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (xBtn) xBtn.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);
    });
})();
</script>
