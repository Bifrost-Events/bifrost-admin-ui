<?php

declare(strict_types=1);

/** @var string $title */
/** @var string $content */
/** @var string $active_nav */
/** @var array<string, mixed>|null $user */
/** @var list<array<string, mixed>> $menu_sections */
/** @var array<string, mixed>|null $menu_overview */
/** @var array<string, mixed> $tenant_context */

$activeNav = $active_nav ?? '';
$user = $user ?? null;
$menuSections = $menu_sections ?? [];
$menuOverview = $menu_overview ?? null;
$tenantContext = $tenant_context ?? [
    'selected_tenant_id' => null,
    'selected_tenant' => null,
    'selectable_tenants' => [],
    'is_platform_context' => true,
];

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$userName = '';
$userEmail = '';
if (is_array($user)) {
    $userName = trim((string) ($user['name'] ?? ''));
    $userEmail = (string) ($user['email'] ?? '');
    if ($userName === '') {
        $userName = $userEmail;
    }
}

$selectedTenant = is_array($tenantContext['selected_tenant'] ?? null)
    ? $tenantContext['selected_tenant']
    : null;
$selectableTenants = is_array($tenantContext['selectable_tenants'] ?? null)
    ? $tenantContext['selectable_tenants']
    : [];
$currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';

$activeSectionId = '';
foreach ($menuSections as $section) {
    if (!is_array($section)) {
        continue;
    }
    foreach ($section['items'] ?? [] as $item) {
        if (is_array($item) && ($item['id'] ?? '') === $activeNav) {
            $activeSectionId = (string) ($section['id'] ?? '');
            break 2;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $h($title) ?> – Bifrost Admin</title>
    <style>
        :root {
            --bg: #eef0eb;
            --sidebar: #1e2a22;
            --sidebar-text: #e8ece9;
            --sidebar-muted: #9caaa3;
            --sidebar-active: #3d6b47;
            --topbar: #fff;
            --card: #fff;
            --ink: #1a1a18;
            --muted: #5c5c58;
            --line: #d4d8d2;
            --accent: #2c5530;
            --bad: #9b2c2c;
            --ok: #2c5530;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, Segoe UI, Roboto, sans-serif; background: var(--bg); color: var(--ink); line-height: 1.45; }
        a { color: var(--accent); }
        .admin-shell { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px; flex-shrink: 0; background: var(--sidebar); color: var(--sidebar-text);
            display: flex; flex-direction: column; padding: 1rem 0;
        }
        .sidebar-brand { padding: 0 1rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 0.75rem; }
        .sidebar-brand strong { display: block; font-size: 1rem; }
        .sidebar-brand span { font-size: 0.8rem; color: var(--sidebar-muted); }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 0 0.5rem; }
        .sidebar-nav-tools {
            display: flex; gap: 0.35rem; padding: 0 0.75rem 0.65rem;
            border-bottom: 1px solid rgba(255,255,255,0.06); margin-bottom: 0.5rem;
        }
        .sidebar-nav-tools button {
            flex: 1; background: rgba(255,255,255,0.06); border: none; border-radius: 4px;
            color: var(--sidebar-muted); font-size: 0.72rem; padding: 0.3rem 0.4rem; cursor: pointer;
        }
        .sidebar-nav-tools button:hover { color: var(--sidebar-text); background: rgba(255,255,255,0.1); }
        .nav-overview { margin-bottom: 0.5rem; }
        .nav-overview a, .nav-item a {
            display: block; padding: 0.45rem 0.75rem; border-radius: 4px;
            color: var(--sidebar-text); text-decoration: none; font-size: 0.92rem;
        }
        .nav-overview a:hover, .nav-item a:hover { background: rgba(255,255,255,0.06); }
        .nav-overview a.is-active, .nav-item a.is-active {
            background: var(--sidebar-active); color: #fff; font-weight: 600;
        }
        .nav-section { margin-top: 0.35rem; }
        .nav-section-toggle {
            width: 100%; display: flex; align-items: center; justify-content: space-between;
            gap: 0.5rem; padding: 0.45rem 0.75rem; margin: 0; border: none; border-radius: 4px;
            background: transparent; color: var(--sidebar-muted); font-size: 0.72rem;
            text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; cursor: pointer;
            text-align: left;
        }
        .nav-section-toggle:hover { background: rgba(255,255,255,0.06); color: var(--sidebar-text); }
        .nav-section.is-open .nav-section-toggle { color: var(--sidebar-text); }
        .nav-section-chevron {
            font-size: 0.65rem; transition: transform 0.15s ease; flex-shrink: 0;
        }
        .nav-section.is-open .nav-section-chevron { transform: rotate(90deg); }
        .nav-section-items { display: none; padding-bottom: 0.25rem; }
        .nav-section.is-open .nav-section-items { display: block; }
        .nav-section.has-active .nav-section-toggle { color: #fff; }
        .main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar {
            background: var(--topbar); border-bottom: 1px solid var(--line);
            padding: 0.65rem 1.25rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem 1.25rem;
        }
        .topbar-title { font-weight: 700; font-size: 1.05rem; margin: 0; }
        .tenant-picker { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; }
        .tenant-picker label { color: var(--muted); font-weight: 600; }
        .tenant-picker select {
            min-width: 200px; padding: 0.35rem 0.5rem; border: 1px solid var(--line);
            border-radius: 4px; font-size: 0.9rem; background: #fff;
        }
        .topbar-user { margin-left: auto; display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; color: var(--muted); }
        .topbar-user .name { color: var(--ink); font-weight: 600; }
        .btn-logout {
            background: transparent; border: 1px solid var(--line); border-radius: 4px;
            padding: 0.3rem 0.65rem; font-size: 0.85rem; cursor: pointer; color: var(--ink);
        }
        .btn-logout:hover { background: var(--bg); }
        .content-wrap { padding: 1.25rem; flex: 1; }
        .content-card { background: var(--card); border: 1px solid var(--line); border-radius: 6px; padding: 1.25rem 1.5rem; }
        .lead { color: var(--muted); margin-top: 0; }
        .placeholder-box {
            margin-top: 1.25rem; padding: 1.25rem; border: 1px dashed var(--line);
            border-radius: 6px; background: #fafbf9;
        }
        .doc-box {
            margin: 1rem 0 1.25rem; padding: 1rem 1.15rem; border: 1px solid var(--line);
            border-radius: 6px; background: #f7faf8; border-left: 4px solid var(--accent);
        }
        .doc-box h2 { margin: 0 0 0.5rem; font-size: 1rem; color: var(--accent); }
        .doc-box p { margin: 0 0 0.65rem; font-size: 0.92rem; color: var(--ink); }
        .doc-box p:last-child { margin-bottom: 0; }
        .doc-box ul { margin: 0.35rem 0 0.65rem; padding-left: 1.2rem; font-size: 0.92rem; }
        .doc-box li { margin-bottom: 0.25rem; }
        .doc-box code { font-size: 0.88em; }
        .badge { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600; }
        .badge-ok { background: #e6f2e8; color: var(--ok); }
        .badge-bad { background: #fdeaea; color: var(--bad); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 0.55rem 0.65rem; border-bottom: 1px solid var(--line); vertical-align: top; }
        th { font-size: 0.85rem; color: var(--muted); }
        .muted { color: var(--muted); font-size: 0.9rem; }
        ul.domains { margin: 0; padding-left: 1.1rem; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; margin: 1rem 0; }
        .user-search-form input[type="search"] {
            min-width: min(22rem, 100%);
            padding: 0.45rem 0.6rem;
            border: 1px solid var(--line);
            border-radius: 4px;
        }
        .user-search-hint { font-size: 0.9rem; }
        .selected-user-panel {
            margin: 1.75rem 0 2rem;
            padding: 1.25rem 1.35rem;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fafbf9;
        }
        .selected-user-header {
            display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between;
            gap: 0.75rem 1rem; margin-bottom: 1rem;
        }
        .selected-user-header h2 { margin: 0; font-size: 1.15rem; }
        .selected-user-panel h3 { margin: 1.25rem 0 0.5rem; font-size: 1rem; }
        .role-user-list { margin: 0; padding-left: 1.1rem; }
        .role-user-list li { margin: 0.2rem 0; }
        .role-user-more { margin: 0.35rem 0 0; font-size: 0.92rem; }
        .role-users-cell { min-width: 12rem; max-width: 28rem; vertical-align: top; }
        .btn {
            display: inline-block; padding: 0.4rem 0.85rem; border-radius: 4px; border: 1px solid var(--line);
            background: #fff; color: var(--ink); text-decoration: none; font-size: 0.9rem; cursor: pointer;
        }
        .btn:hover { background: var(--bg); }
        .btn-primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        .btn-primary:hover { filter: brightness(1.05); background: var(--accent); }
        .btn-danger { border-color: #e0b4b4; color: var(--bad); }
        .form-grid { display: grid; gap: 0.85rem; max-width: 520px; margin-top: 1rem; }
        .form-grid label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.25rem; }
        .form-grid input, .form-grid select, .form-grid textarea {
            width: 100%; padding: 0.45rem 0.55rem; border: 1px solid var(--line); border-radius: 4px; font-size: 0.95rem;
        }
        .form-grid .checkbox-row { display: flex; align-items: center; gap: 0.5rem; }
        .form-grid .checkbox-row input { width: auto; }
        .form-error, .form-errors { color: var(--bad); margin: 0.75rem 0; }
        .form-errors { padding-left: 1.2rem; }
        .flash { padding: 0.65rem 0.85rem; border-radius: 4px; margin-bottom: 1rem; font-weight: 600; }
        .flash-success { background: #e6f2e8; color: var(--ok); }
        .flash-error { background: #fdeaea; color: var(--bad); }
        .status-active { color: var(--ok); font-weight: 600; }
        .status-inactive { color: var(--bad); font-weight: 600; }
        .flash-info { background: #e8f0f8; color: #1a4a6e; }
        .type-section { margin-top: 1.75rem; }
        .tenant-tree-table { border-collapse: collapse; }
        .tenant-tree-table .tenant-row td {
            border-top: 2px solid var(--line);
            padding-top: 0.85rem;
            font-weight: 600;
        }
        .tenant-tree-table tbody tr:first-child.tenant-row td { border-top: none; }
        .tenant-tree-table .domain-row td {
            background: #fafbf9;
            font-weight: 400;
            border-top: 1px solid #eef1ec;
        }
        .tenant-tree-table .domain-row-empty td,
        .tenant-tree-table .domain-row-error td { background: #fafbf9; }
        .tenant-tree-table .domain-indent {
            padding-left: 1.5rem;
        }
        .tenant-tree-table .domain-indent::before {
            content: "↳ ";
            color: #7a8578;
        }
        .tenant-tree-table .domain-form-row td {
            background: #f7faf8;
            padding: 0.75rem 1rem 1rem;
            border-top: 1px solid #eef1ec;
        }
        .tenant-tree-table .tenant-row { scroll-margin-top: 1rem; }
        .domain-form-box {
            margin-top: 0; padding: 1rem 1.15rem; border: 1px solid var(--line);
            border-radius: 6px; background: #fafbf9;
        }
        .domain-form-title { margin: 0 0 0.75rem; font-size: 1rem; }
        tr.row-editing td { background: #f7faf8; }
        .type-section-title { margin: 0 0 0.35rem; font-size: 1.1rem; }
        .type-section-lead { margin: 0 0 0.75rem; }
        .inline-form { display: inline; }
        @media (max-width: 900px) {
            .admin-shell { flex-direction: column; }
            .sidebar { width: 100%; max-height: 40vh; }
        }
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar" aria-label="Hovedmeny">
        <div class="sidebar-brand">
            <strong>Bifrost Admin</strong>
            <span>SystemAdmin / CupAdmin</span>
        </div>
        <nav class="sidebar-nav">
            <?php if (is_array($menuOverview)): ?>
                <div class="nav-overview">
                    <a href="<?= $h((string) ($menuOverview['path'] ?? '/')) ?>"
                       class="<?= $activeNav === ($menuOverview['id'] ?? '') ? 'is-active' : '' ?>">
                        <?= $h((string) ($menuOverview['label'] ?? 'Oversikt')) ?>
                    </a>
                </div>
            <?php endif; ?>

            <div class="sidebar-nav-tools">
                <button type="button" data-nav-action="expand-all">Utvid alle</button>
                <button type="button" data-nav-action="collapse-all">Lukk alle</button>
            </div>

            <?php foreach ($menuSections as $section): ?>
                <?php if (!is_array($section)) {
                    continue;
                } ?>
                <?php
                $sectionId = (string) ($section['id'] ?? '');
                $hasActive = $sectionId !== '' && $sectionId === $activeSectionId;
                ?>
                <div class="nav-section<?= $hasActive ? ' has-active is-open' : '' ?>"
                     data-section-id="<?= $h($sectionId) ?>"
                     data-has-active="<?= $hasActive ? '1' : '0' ?>">
                    <button type="button" class="nav-section-toggle" aria-expanded="<?= $hasActive ? 'true' : 'false' ?>">
                        <span><?= $h((string) ($section['label'] ?? '')) ?></span>
                        <span class="nav-section-chevron" aria-hidden="true">▸</span>
                    </button>
                    <div class="nav-section-items">
                        <?php foreach ($section['items'] ?? [] as $item): ?>
                            <?php if (!is_array($item)) {
                                continue;
                            } ?>
                            <div class="nav-item">
                                <a href="<?= $h((string) ($item['path'] ?? '#')) ?>"
                                   class="<?= $activeNav === ($item['id'] ?? '') ? 'is-active' : '' ?>">
                                    <?= $h((string) ($item['label'] ?? '')) ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>
    </aside>

    <div class="main-area">
        <header class="topbar">
            <p class="topbar-title">Bifrost Admin</p>

            <form class="tenant-picker" method="get" action="<?= $h($currentPath) ?>">
                <label for="tenant_id">Cup-kontekst</label>
                <select id="tenant_id" name="tenant_id" onchange="this.form.submit()">
                    <option value="0"<?= $selectedTenant === null ? ' selected' : '' ?>>— Plattform (ingen cup) —</option>
                    <?php foreach ($selectableTenants as $tenant): ?>
                        <?php if (!is_array($tenant)) {
                            continue;
                        } ?>
                        <?php $tid = (int) ($tenant['id'] ?? 0); ?>
                        <option value="<?= $tid ?>"<?= $selectedTenant !== null && (int) ($selectedTenant['id'] ?? 0) === $tid ? ' selected' : '' ?>>
                            <?= $h((string) ($tenant['name'] ?? '')) ?> (<?= $h((string) ($tenant['slug'] ?? '')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($userEmail !== ''): ?>
                <div class="topbar-user">
                    <span class="name"><?= $h($userName) ?></span>
                    <form method="post" action="/logout" style="margin:0;">
                        <button type="submit" class="btn-logout">Logg ut</button>
                    </form>
                </div>
            <?php endif; ?>
        </header>

        <div class="content-wrap">
            <main class="content-card">
                <?php if (is_array($flash ?? null) && (($flash['message'] ?? '') !== '')): ?>
                    <div class="flash flash-<?= $h((string) $flash['type']) ?>">
                        <?= $h((string) $flash['message']) ?>
                    </div>
                <?php endif; ?>
                <?= $content ?>
            </main>
        </div>
    </div>
</div>
<script>
(function () {
    var storageKey = 'bifrost_admin_nav_sections';
    var sections = document.querySelectorAll('.nav-section[data-section-id]');
    if (!sections.length) return;

    function readState() {
        try {
            var raw = localStorage.getItem(storageKey);
            return raw ? JSON.parse(raw) : {};
        } catch (e) {
            return {};
        }
    }

    function writeState(state) {
        try {
            localStorage.setItem(storageKey, JSON.stringify(state));
        } catch (e) { /* ignore */ }
    }

    function setOpen(section, open) {
        section.classList.toggle('is-open', open);
        var btn = section.querySelector('.nav-section-toggle');
        if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    var saved = readState();
    sections.forEach(function (section) {
        var id = section.getAttribute('data-section-id');
        if (!id) return;
        if (Object.prototype.hasOwnProperty.call(saved, id)) {
            setOpen(section, !!saved[id]);
        } else if (section.getAttribute('data-has-active') === '1') {
            setOpen(section, true);
        } else {
            setOpen(section, false);
        }

        var toggle = section.querySelector('.nav-section-toggle');
        if (toggle) {
            toggle.addEventListener('click', function () {
                var open = !section.classList.contains('is-open');
                setOpen(section, open);
                var state = readState();
                state[id] = open;
                writeState(state);
            });
        }
    });

    document.querySelectorAll('[data-nav-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var action = btn.getAttribute('data-nav-action');
            var state = {};
            sections.forEach(function (section) {
                var id = section.getAttribute('data-section-id');
                if (!id) return;
                var open = action === 'expand-all';
                setOpen(section, open);
                state[id] = open;
            });
            writeState(state);
        });
    });
})();
</script>
</body>
</html>
