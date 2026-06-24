<?php

declare(strict_types=1);

use App\Support\TenantTypes;

$systemCodeLabel = TenantTypes::systemCodeLabel();
$systemCodeHint = TenantTypes::systemCodeHint();
?>
<div class="doc-box" role="note" aria-label="Forklaring">
    <h2>Hva er plattform?</h2>
    <p>
        <strong>Plattform</strong> er Bifrost sin egen infrastruktur — ikke en konkurranseserie.
        F.eks. <em>Bifrost Admin</em> med domener som <code>admin.bifrost.local</code>.
        Teknisk er det samme tabell som cuper (<code>tenants</code>, type <code>platform</code>).
    </p>
    <p>
        <strong><?= htmlspecialchars($systemCodeLabel, ENT_QUOTES, 'UTF-8') ?></strong>
        — <?= htmlspecialchars($systemCodeHint, ENT_QUOTES, 'UTF-8') ?>
    </p>
    <p class="muted">
        Kun SystemAdmin ser og administrerer plattform-enheter.
        Plattform og domener vises hierarkisk i tabellen under hver enhet.
    </p>
</div>
