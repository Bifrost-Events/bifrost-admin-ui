<?php

declare(strict_types=1);

/** @var string $title */
/** @var string $error */

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$error = $error ?? '';

?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $h($title) ?> – Bifrost Admin</title>
    <style>
        :root { --bg: #f4f4f2; --ink: #1a1a18; --muted: #5c5c58; --line: #d8d8d4; --accent: #2c5530; --bad: #9b2c2c; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, Segoe UI, Roboto, sans-serif; background: var(--bg); color: var(--ink); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.25rem; }
        .login-card { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 1.5rem; width: 100%; max-width: 420px; }
        h1 { margin: 0 0 0.35rem; font-size: 1.25rem; }
        .muted { color: var(--muted); font-size: 0.9rem; margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.35rem; }
        input { width: 100%; padding: 0.55rem 0.65rem; border: 1px solid var(--line); border-radius: 4px; font-size: 1rem; margin-bottom: 0.9rem; }
        button { width: 100%; padding: 0.65rem 1rem; background: var(--accent); color: #fff; border: none; border-radius: 4px; font-size: 1rem; font-weight: 600; cursor: pointer; }
        button:hover { filter: brightness(1.05); }
        .error { background: #fdeaea; border: 1px solid #f0c4c4; color: var(--bad); padding: 0.65rem 0.75rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Bifrost Admin</h1>
        <p class="muted">Logg inn med admin-bruker (system_admin eller cup_admin).</p>

        <?php if ($error !== ''): ?>
            <div class="error" role="alert"><?= $h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/login">
            <label for="email">E-post</label>
            <input type="email" id="email" name="email" required autocomplete="email" autofocus>

            <label for="password">Passord</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <button type="submit">Logg inn</button>
        </form>
    </div>
</body>
</html>
