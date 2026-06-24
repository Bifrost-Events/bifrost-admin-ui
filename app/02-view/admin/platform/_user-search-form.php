<?php

declare(strict_types=1);

use App\Support\UserSearch;

/** @var string $form_action */
/** @var string $search */
/** @var callable(string): string $h */
/** @var array<string, int|string> $preserve_query */

$q = (string) ($search ?? '');
$minLength = UserSearch::MIN_LENGTH;
$preserveQuery = is_array($preserve_query ?? null) ? $preserve_query : [];
?>
<form method="get" action="<?= $h($form_action) ?>" class="toolbar user-search-form" data-min-length="<?= $minLength ?>">
    <?php foreach ($preserveQuery as $name => $value): ?>
        <?php if ((string) $name === 'q') { continue; } ?>
        <input type="hidden" name="<?= $h((string) $name) ?>" value="<?= $h((string) $value) ?>">
    <?php endforeach; ?>
    <label for="user-search-q">Søk</label>
    <input type="search" id="user-search-q" name="q" value="<?= $h($q) ?>"
           minlength="<?= $minLength ?>"
           placeholder="Minst <?= $minLength ?> tegn — navn, e-post, telefon eller ID"
           autocomplete="off">
    <button type="submit" class="btn btn-primary">Søk</button>
    <?php if ($q !== ''): ?>
        <?php
        $resetUrl = $form_action;
        if ($preserveQuery !== []) {
            $resetUrl .= '?' . http_build_query($preserveQuery);
        }
        ?>
        <a class="btn" href="<?= $h($resetUrl) ?>">Nullstill</a>
    <?php endif; ?>
    <span class="muted user-search-hint">Søk starter automatisk etter <?= $minLength ?> tegn.</span>
</form>
<script>
(function () {
    document.querySelectorAll('.user-search-form').forEach(function (form) {
        var input = form.querySelector('input[name="q"]');
        if (!input) {
            return;
        }
        var minLength = parseInt(form.getAttribute('data-min-length') || '3', 10);
        var timer = null;

        function urlWithoutQuery() {
            var params = new URLSearchParams(window.location.search);
            params.delete('q');
            var qs = params.toString();
            var base = form.getAttribute('action') || window.location.pathname;
            return base + (qs ? '?' + qs : '');
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            var value = input.value.trim();
            timer = setTimeout(function () {
                if (value.length >= minLength) {
                    form.requestSubmit();
                    return;
                }
                if (value.length === 0 && window.location.search.indexOf('q=') !== -1) {
                    window.location.href = urlWithoutQuery();
                }
            }, 300);
        });
    });
})();
</script>
