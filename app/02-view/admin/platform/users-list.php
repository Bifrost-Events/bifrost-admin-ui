<?php

declare(strict_types=1);

use App\Support\UserSearch;

/** @var array<string, mixed>|null $page */
/** @var list<array<string, mixed>> $users */
/** @var string $search */
/** @var string|null $api_error */
/** @var array{type: string, message: string, errors: array<string, string>}|null $flash */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$search = (string) ($search ?? '');
?>
<?php include __DIR__ . '/../_flash.php'; ?>

<h1><?= $h((string) ($page['title'] ?? 'Brukere')) ?></h1>
<p class="lead"><?= $h((string) ($page['description'] ?? '')) ?></p>

<?php if ($api_error): ?>
    <p class="form-error"><?= $h($api_error) ?></p>
<?php endif; ?>

<div class="toolbar">
    <a class="btn btn-primary" href="/platform/users/new">Opprett bruker</a>
    <a class="btn" href="/platform/roles">Roller og tilganger</a>
</div>

<?php
$form_action = '/platform/users';
$preserve_query = [];
include __DIR__ . '/_user-search-form.php';
?>

<?php if (!UserSearch::isActive($search)): ?>
    <p class="muted"><?= count($users) ?> brukere totalt. Søk aktiveres etter <?= UserSearch::MIN_LENGTH ?> tegn.</p>
<?php endif; ?>

<?php
$mode = 'list';
include __DIR__ . '/_user-search-results.php';
?>
