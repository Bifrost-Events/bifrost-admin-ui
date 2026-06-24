<?php

declare(strict_types=1);

namespace App\Support;

final class TenantTypes
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /** @return array<string, mixed> */
    public static function config(): array
    {
        if (self::$config === null) {
            $path = dirname(__DIR__, 2) . '/config/tenant-types.php';
            $loaded = file_exists($path) ? require $path : [];
            self::$config = is_array($loaded) ? $loaded : [];
        }

        return self::$config;
    }

    public static function systemCodeLabel(): string
    {
        return (string) (self::config()['system_code_label'] ?? 'Systemkode');
    }

    public static function systemCodeHint(): string
    {
        return (string) (self::config()['system_code_hint'] ?? '');
    }

    /** @return list<string> */
    public static function orderedTypeKeys(): array
    {
        $order = self::config()['order'] ?? [];
        if (!is_array($order)) {
            return [];
        }

        return array_values(array_filter($order, static fn ($k): bool => is_string($k) && $k !== ''));
    }

    public static function typeLabel(string $type): string
    {
        $types = self::config()['types'] ?? [];
        if (is_array($types) && is_array($types[$type] ?? null)) {
            return (string) ($types[$type]['label'] ?? $type);
        }

        return (string) (self::config()['unknown_type']['label'] ?? $type);
    }

    /**
     * @return array{label: string, list_heading: string, list_lead: string, empty: string}
     */
    public static function typeMeta(string $type): array
    {
        $types = self::config()['types'] ?? [];
        $base = is_array($types[$type] ?? null) ? $types[$type] : (self::config()['unknown_type'] ?? []);

        return [
            'label' => (string) ($base['label'] ?? $type),
            'list_heading' => (string) ($base['list_heading'] ?? self::typeLabel($type)),
            'list_lead' => (string) ($base['list_lead'] ?? ''),
            'empty' => (string) ($base['empty'] ?? 'Ingen enheter.'),
        ];
    }

    /**
     * @param list<array<string, mixed>> $tenants
     * @return array<string, list<array<string, mixed>>>
     */
    public static function groupByType(array $tenants): array
    {
        $grouped = [];
        foreach ($tenants as $tenant) {
            if (!is_array($tenant)) {
                continue;
            }
            $type = (string) ($tenant['tenant_type'] ?? '');
            if ($type === '') {
                $type = '_unknown';
            }
            $grouped[$type][] = $tenant;
        }

        foreach ($grouped as &$rows) {
            usort($rows, static fn (array $a, array $b): int => strcasecmp(
                (string) ($a['name'] ?? ''),
                (string) ($b['name'] ?? '')
            ));
        }
        unset($rows);

        return $grouped;
    }

    /**
     * @param array<string, list<array<string, mixed>>> $grouped
     * @return list<array{key: string, meta: array<string, string>, rows: list<array<string, mixed>>}>
     */
    public static function sectionsForList(array $grouped): array
    {
        $sections = [];
        $seen = [];

        foreach (self::orderedTypeKeys() as $typeKey) {
            $rows = $grouped[$typeKey] ?? [];
            $seen[$typeKey] = true;
            $sections[] = [
                'key' => $typeKey,
                'meta' => self::typeMeta($typeKey),
                'rows' => $rows,
            ];
        }

        foreach ($grouped as $typeKey => $rows) {
            if (isset($seen[$typeKey])) {
                continue;
            }
            $sections[] = [
                'key' => $typeKey,
                'meta' => self::typeMeta($typeKey),
                'rows' => $rows,
            ];
        }

        return $sections;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function selectOptions(): array
    {
        $options = [];
        foreach (self::orderedTypeKeys() as $typeKey) {
            $options[] = ['value' => $typeKey, 'label' => self::typeLabel($typeKey)];
        }

        return $options;
    }

    public static function menuIdForType(string $type): string
    {
        $types = self::config()['types'] ?? [];
        if (is_array($types[$type] ?? null)) {
            $id = (string) ($types[$type]['menu_id'] ?? '');
            if ($id !== '') {
                return $id;
            }
        }

        return (string) (self::config()['unknown_type']['menu_id'] ?? 'platform.cups');
    }

    public static function listPathForType(string $type): string
    {
        $types = self::config()['types'] ?? [];
        if (is_array($types[$type] ?? null)) {
            $path = (string) ($types[$type]['menu_path'] ?? '');
            if ($path !== '') {
                return $path;
            }
        }

        return '/platform/cuper';
    }

    public static function docPartialForType(string $type): string
    {
        $types = self::config()['types'] ?? [];
        if (is_array($types[$type] ?? null)) {
            $partial = (string) ($types[$type]['doc_partial'] ?? '');
            if ($partial !== '') {
                return $partial;
            }
        }

        return 'docs/tenants';
    }

    public static function isSystemAdminOnlyType(string $type): bool
    {
        $types = self::config()['types'] ?? [];

        return is_array($types[$type] ?? null) && !empty($types[$type]['system_admin_only']);
    }
}
