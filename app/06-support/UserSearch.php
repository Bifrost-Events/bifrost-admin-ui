<?php

declare(strict_types=1);

namespace App\Support;

final class UserSearch
{
    public const MIN_LENGTH = 3;

    public static function isActive(string $query): bool
    {
        return mb_strlen(trim($query)) >= self::MIN_LENGTH;
    }
}
