<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\AdminView;

final class AdminPageController
{
    public function show(string $pageId): array
    {
        return AdminView::render($pageId);
    }
}
