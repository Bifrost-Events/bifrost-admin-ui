<?php

declare(strict_types=1);

use App\Support\TenantTypes;

$systemCodeLabel = TenantTypes::systemCodeLabel();
$systemCodeHint = TenantTypes::systemCodeHint();
?>
<div class="doc-box" role="note" aria-label="Forklaring">
    <h2>Hva er en cup?</h2>
    <p>
        En <strong>cup</strong> er en konkurranseserie i Bifrost — f.eks. Namdal Jaktfeltkarusell eller Jaktfeltcup.
        Hver cup har egen <strong>systemkode</strong>, domener, CupAdmin-tilgang og cup-struktur i <code>cups</code>.
    </p>
    <p>
        <strong><?= htmlspecialchars($systemCodeLabel, ENT_QUOTES, 'UTF-8') ?></strong>
        — <?= htmlspecialchars($systemCodeHint, ENT_QUOTES, 'UTF-8') ?>
        <strong>Navn</strong> er det visningsnavnet som vises i admin og på nettsider.
    </p>
    <p class="muted">
        CupAdmin administrerer bare cuper de har tilgang til.
        Cup og tilhørende domener vises hierarkisk i tabellen. Legg til og rediger domener under hver cup-rad.
    </p>
</div>
