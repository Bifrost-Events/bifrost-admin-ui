<?php

declare(strict_types=1);

/**
 * Admin navigation structure for Bifrost Admin (SystemAdmin / CupAdmin).
 * Arrangørportal har egen meny senere.
 */
return [
    'overview' => [
        'id' => 'overview',
        'label' => 'Oversikt',
        'path' => '/',
        'title' => 'Oversikt',
        'description' => 'Dashboard med status for plattform og valgt cup.',
    ],
    'sections' => [
        [
            'id' => 'system-setup',
            'label' => 'Systemoppsett',
            'items' => [
                [
                    'id' => 'platform.cups',
                    'label' => 'Cuper',
                    'path' => '/platform/cuper',
                    'title' => 'Cuper',
                    'description' => 'Administrer cup, domener, systemkode og status.',
                    'tenant_type' => 'cup',
                ],
                [
                    'id' => 'platform.platform',
                    'label' => 'Plattform',
                    'path' => '/platform/plattform',
                    'title' => 'Plattform',
                    'description' => 'Bifrost-plattform, domener og infrastruktur (f.eks. Bifrost Admin).',
                    'tenant_type' => 'platform',
                    'system_admin_only' => true,
                ],
            ],
        ],
        [
            'id' => 'access',
            'label' => 'Brukere og tilgang',
            'items' => [
                [
                    'id' => 'platform.users',
                    'label' => 'Brukere',
                    'path' => '/platform/users',
                    'title' => 'Brukere',
                    'description' => 'Globale Bifrost-kontoer (auth_users) på tvers av cuper.',
                ],
                [
                    'id' => 'platform.roles',
                    'label' => 'Roller og tilganger',
                    'path' => '/platform/roles',
                    'title' => 'Roller og tilganger',
                    'description' => 'SystemAdmin, CupAdmin og andre administrative tilganger.',
                ],
                [
                    'id' => 'platform.organizations',
                    'label' => 'Organisasjoner',
                    'path' => '/platform/organizations',
                    'title' => 'Organisasjoner',
                    'description' => 'Arrangører og skytterlag per cup (organizations — tilsvarer jaktfelt_organizers_v2).',
                ],
            ],
        ],
        [
            'id' => 'cup',
            'label' => 'Cup-oppsett',
            'items' => [
                [
                    'id' => 'cup.seasons',
                    'label' => 'Sesonger',
                    'path' => '/cup/seasons',
                    'title' => 'Sesonger',
                    'description' => 'Sesonger og cup-struktur for valgt tenant.',
                ],
                [
                    'id' => 'cup.classes',
                    'label' => 'Klasser',
                    'path' => '/cup/classes',
                    'title' => 'Klasser',
                    'description' => 'Deltakerklasser og kategorier (event_classes).',
                ],
                [
                    'id' => 'cup.rulesets',
                    'label' => 'Regelsett',
                    'path' => '/cup/rulesets',
                    'title' => 'Regelsett',
                    'description' => 'Regelsett og konkurransetyper for cupen.',
                ],
                [
                    'id' => 'cup.registration',
                    'label' => 'Påmelding',
                    'path' => '/cup/registration',
                    'title' => 'Påmelding',
                    'description' => 'Oppsett av påmeldingsflyt og felter.',
                ],
                [
                    'id' => 'cup.results-setup',
                    'label' => 'Resultatoppsett',
                    'path' => '/cup/results-setup',
                    'title' => 'Resultatoppsett',
                    'description' => 'Scoring, resultatformater og cup-stilling.',
                ],
                [
                    'id' => 'cup.publishing',
                    'label' => 'Publisering',
                    'path' => '/cup/publishing',
                    'title' => 'Publisering',
                    'description' => 'Offentlig visning, domener og publiseringsregler.',
                ],
            ],
        ],
        [
            'id' => 'events',
            'label' => 'Stevner',
            'items' => [
                [
                    'id' => 'events.overview',
                    'label' => 'Stevneoversikt',
                    'path' => '/events/overview',
                    'title' => 'Stevneoversikt',
                    'description' => 'Liste og status for stevner/konkurranser i valgt cup.',
                ],
                [
                    'id' => 'events.organizers',
                    'label' => 'Arrangører',
                    'path' => '/events/organizers',
                    'title' => 'Arrangører',
                    'description' => 'Knytte arrangører til stevner (admin — ikke arrangørportal).',
                ],
                [
                    'id' => 'events.setup',
                    'label' => 'Stevneoppsett',
                    'path' => '/events/setup',
                    'title' => 'Stevneoppsett',
                    'description' => 'Konfigurasjon av enkeltstevner (event_competitions).',
                ],
            ],
        ],
        [
            'id' => 'sponsors',
            'label' => 'Sponsorer',
            'items' => [
                [
                    'id' => 'sponsors.packages',
                    'label' => 'Sponsorpakker',
                    'path' => '/sponsors/packages',
                    'title' => 'Sponsorpakker',
                    'description' => 'Pakker og nivåer for sponsing.',
                ],
                [
                    'id' => 'sponsors.agreements',
                    'label' => 'Sponsoravtaler',
                    'path' => '/sponsors/agreements',
                    'title' => 'Sponsoravtaler',
                    'description' => 'Avtaler knyttet til cup eller stevner.',
                ],
                [
                    'id' => 'sponsors.display',
                    'label' => 'Sponsorvisning',
                    'path' => '/sponsors/display',
                    'title' => 'Sponsorvisning',
                    'description' => 'Logo, plassering og visning på nettsider.',
                ],
            ],
        ],
        [
            'id' => 'system',
            'label' => 'System',
            'items' => [
                [
                    'id' => 'system.logs',
                    'label' => 'Logger',
                    'path' => '/system/logs',
                    'title' => 'Logger',
                    'description' => 'System- og audit-logger.',
                ],
                [
                    'id' => 'system.notifications',
                    'label' => 'Varsler',
                    'path' => '/system/notifications',
                    'title' => 'Varsler',
                    'description' => 'E-post og andre varsler (notification_*).',
                ],
                [
                    'id' => 'system.import-export',
                    'label' => 'Import / eksport',
                    'path' => '/system/import-export',
                    'title' => 'Import / eksport',
                    'description' => 'Dataimport og -eksport.',
                ],
                [
                    'id' => 'system.settings',
                    'label' => 'Innstillinger',
                    'path' => '/system/settings',
                    'title' => 'Innstillinger',
                    'description' => 'Plattform- og cup-innstillinger.',
                ],
            ],
        ],
    ],
];
