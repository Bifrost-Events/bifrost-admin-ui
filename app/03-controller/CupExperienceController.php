<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\AdminView;

final class CupExperienceController
{
    public function index(): array
    {
        $configDir = dirname(__DIR__, 2) . '/../bifrost-public-ui/config/cups';
        $managedCups = [];

        if (is_dir($configDir)) {
            foreach (scandir($configDir) ?: [] as $file) {
                if (!str_ends_with($file, '.json') || $file === 'default.json') {
                    continue;
                }
                $raw = file_get_contents($configDir . '/' . $file);
                $data = is_string($raw) ? json_decode($raw, true) : null;
                if (!is_array($data)) {
                    continue;
                }
                $managedCups[] = [
                    'file' => $file,
                    'name' => (string) ($data['name'] ?? $file),
                    'domain' => (string) ($data['domain'] ?? ''),
                    'template' => (string) (($data['layout']['template'] ?? '')),
                    'presentation_level' => (string) (($data['sponsors']['presentation_level'] ?? '')),
                ];
            }
        }

        usort($managedCups, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return AdminView::renderContent('cup.experience', 'admin/cup/experience', [
            'managed_cups' => $managedCups,
            'config_path' => 'bifrost-public-ui/config/cups/',
        ]);
    }
}
