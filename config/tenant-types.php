<?php

declare(strict_types=1);

/**
 * Brukervendte etiketter for tenant_type (tenants.tenant_type).
 * Legg til nye typer her når schema utvides — UI plukker dem opp automatisk.
 */
return [
    'system_code_label' => 'Systemkode',
    'system_code_hint' => 'Stabil identifikator i små bokstaver og bindestrek (f.eks. namdal). Endres sjelden. Teknisk felt: slug.',

    /** Visningsrekkefølge for tabeller på oversiktssiden */
    'order' => ['platform', 'cup'],

    'types' => [
        'platform' => [
            'label' => 'Plattform',
            'list_heading' => 'Plattform',
            'list_lead' => 'Bifrost-plattform og tilhørende infrastruktur (f.eks. Bifrost Admin).',
            'empty' => 'Ingen plattform-enheter er registrert.',
            'menu_id' => 'platform.platform',
            'menu_path' => '/platform/plattform',
            'doc_partial' => 'docs/plattform',
            'system_admin_only' => true,
        ],
        'cup' => [
            'label' => 'Cup',
            'list_heading' => 'Cuper',
            'list_lead' => 'Konkurranseserier med egen cup-struktur, domener og admin-tilgang.',
            'empty' => 'Ingen cuper er registrert.',
            'menu_id' => 'platform.cups',
            'menu_path' => '/platform/cuper',
            'doc_partial' => 'docs/cuper',
            'system_admin_only' => false,
        ],
    ],

    'unknown_type' => [
        'label' => 'Annet',
        'list_heading' => 'Andre typer',
        'list_lead' => 'Enheter med tenant_type som ikke har egen konfigurasjon ennå.',
        'empty' => 'Ingen enheter av denne typen.',
    ],
];
