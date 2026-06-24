<?php

declare(strict_types=1);
?>
<div class="doc-box" role="note" aria-label="Forklaring">
    <h2>Hva er en organisasjon?</h2>
    <p>
        <strong>Organisasjoner</strong> er arrangører, skytterlag og klubber knyttet til en cup.
        I Bifrost lagres de i <code>organizations</code> (medlemmer i <code>organization_members</code>).
    </p>
    <p class="muted">
        Tilsvarer <code>jaktfelt_organizers_v2</code> i jaktfeltnamdalen.
        Ved migrering settes <code>legacy_jaktfelt_organizer_id</code> via
        <code>bifrost_011_backfill_organizations_from_jaktfelt_v2.sql</code>.
        Organizer-rollen (arrangørportal) kommer senere via medlemskap her.
    </p>
</div>
