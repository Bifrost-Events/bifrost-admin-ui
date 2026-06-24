<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\AdminView;

final class HomeController
{
    public function __invoke(): array
    {
        return AdminView::render('overview');
    }
}
