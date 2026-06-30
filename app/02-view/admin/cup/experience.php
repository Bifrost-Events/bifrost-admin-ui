<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $page */
/** @var list<array<string, mixed>> $managed_cups */
/** @var string $config_path */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$managedCups = is_array($managed_cups ?? null) ? $managed_cups : [];
$configPath = (string) ($config_path ?? 'bifrost-public-ui/config/cups/');
?>
<?php include __DIR__ . '/../_flash.php'; ?>

<h1><?= $h((string) ($page['title'] ?? 'Cup Experience')) ?></h1>
<p class="lead"><?= $h((string) ($page['description'] ?? '')) ?></p>

<?php include __DIR__ . '/docs/experience.php'; ?>

<section class="managed-cups" aria-label="Managed cup-konfigurasjoner">
    <h2 class="section-heading">Managed cuper (JSON-config)</h2>
    <?php if ($managedCups === []): ?>
        <p class="muted">Fant ingen cup-config i <code><?= $h($configPath) ?></code>. Sjekk at bifrost-public-ui ligger ved siden av admin-ui i workspace.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Config-fil</th>
                    <th>Cup</th>
                    <th>Domene</th>
                    <th>Mal</th>
                    <th>Sponsornivå</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($managedCups as $cup): ?>
                    <?php if (!is_array($cup)) { continue; } ?>
                    <tr>
                        <td><code><?= $h((string) ($cup['file'] ?? '')) ?></code></td>
                        <td><?= $h((string) ($cup['name'] ?? '')) ?></td>
                        <td><code><?= $h((string) ($cup['domain'] ?? '')) ?></code></td>
                        <td><?= $h((string) ($cup['template'] ?? '')) ?></td>
                        <td><?= $h((string) ($cup['presentation_level'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<style>
.section-heading { font-size: 1.1rem; margin: 1.5rem 0 0.75rem; }
.managed-cups .data-table { margin-top: 0.5rem; }
</style>
