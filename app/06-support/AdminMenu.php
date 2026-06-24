<?php

declare(strict_types=1);

namespace App\Support;

final class AdminMenu
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /** @return array<string, mixed> */
    public static function config(): array
    {
        if (self::$config === null) {
            /** @var array<string, mixed> $loaded */
            $loaded = require dirname(__DIR__, 2) . '/config/admin-menu.php';
            self::$config = $loaded;
        }

        return self::$config;
    }

    /** @return list<array<string, mixed>> */
    public static function sections(): array
    {
        $sections = self::config()['sections'] ?? [];

        return is_array($sections) ? $sections : [];
    }

    /**
     * @param array<string, mixed>|null $user
     * @return list<array<string, mixed>>
     */
    public static function sectionsForUser(?array $user): array
    {
        $isSystemAdmin = self::userIsSystemAdmin($user);
        $sections = [];

        foreach (self::sections() as $section) {
            if (!is_array($section)) {
                continue;
            }
            $items = [];
            foreach ($section['items'] ?? [] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (!empty($item['system_admin_only']) && !$isSystemAdmin) {
                    continue;
                }
                $items[] = $item;
            }
            if ($items === []) {
                continue;
            }
            $section['items'] = $items;
            $sections[] = $section;
        }

        return $sections;
    }

    /** @param array<string, mixed>|null $user */
    private static function userIsSystemAdmin(?array $user): bool
    {
        if ($user === null) {
            return false;
        }
        foreach ($user['system_roles'] ?? [] as $role) {
            if (is_array($role) && ($role['role'] ?? '') === 'SystemAdmin') {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, mixed>|null */
    public static function overview(): ?array
    {
        $overview = self::config()['overview'] ?? null;

        return is_array($overview) ? $overview : null;
    }

    /** @return array<string, mixed>|null */
    public static function findById(string $pageId): ?array
    {
        $overview = self::overview();
        if ($overview !== null && ($overview['id'] ?? '') === $pageId) {
            return $overview;
        }

        foreach (self::sections() as $section) {
            if (!is_array($section)) {
                continue;
            }
            foreach ($section['items'] ?? [] as $item) {
                if (is_array($item) && ($item['id'] ?? '') === $pageId) {
                    return $item;
                }
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public static function allPages(): array
    {
        $pages = [];
        $overview = self::overview();
        if ($overview !== null) {
            $pages[] = $overview;
        }

        foreach (self::sections() as $section) {
            if (!is_array($section)) {
                continue;
            }
            foreach ($section['items'] ?? [] as $item) {
                if (is_array($item)) {
                    $pages[] = $item;
                }
            }
        }

        return $pages;
    }
}
