<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\BackendApiClient;
use App\Support\AdminView;
use App\Support\Response;
use App\Support\Session;

final class CupSeasonsController
{
    public function index(): array
    {
        $tenantId = Session::getSelectedTenantId();
        if ($tenantId === null || $tenantId <= 0) {
            return AdminView::renderContent('cup.seasons', 'admin/cup/seasons-list', [
                'seasons' => [],
                'tenant_id' => 0,
                'api_error' => null,
                'no_tenant' => true,
            ]);
        }

        $client = new BackendApiClient();
        $result = $client->adminSeasons($tenantId);

        return AdminView::renderContent('cup.seasons', 'admin/cup/seasons-list', [
            'seasons' => $result['ok'] ? ($result['data']['seasons'] ?? []) : [],
            'tenant_id' => $tenantId,
            'api_error' => $result['ok'] ? null : ($result['error'] ?? 'Kunne ikke hente sesonger'),
            'no_tenant' => false,
            'open_season_id' => (int) ($_GET['season'] ?? 0),
        ]);
    }

    public function createSeason(): array
    {
        $tenantId = Session::getSelectedTenantId();
        if ($tenantId === null || $tenantId <= 0) {
            Session::setFlash('error', 'Velg cup i menyen øverst først');

            return Response::redirect('/cup/seasons');
        }

        $body = [
            'tenant_id' => $tenantId,
            'name' => trim((string) ($_POST['name'] ?? '')),
            'year' => (int) ($_POST['year'] ?? 0),
            'start_date' => trim((string) ($_POST['start_date'] ?? '')) ?: null,
            'end_date' => trim((string) ($_POST['end_date'] ?? '')) ?: null,
            'is_active' => isset($_POST['is_active']),
        ];

        $client = new BackendApiClient();
        $result = $client->createAdminSeason($body);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok']
                ? 'Sesong «' . $body['name'] . '» er opprettet'
                : ($result['error'] ?? 'Kunne ikke opprette sesong'),
            $result['errors'] ?? []
        );

        return Response::redirect('/cup/seasons');
    }

    public function createRoundFromPost(): array
    {
        $seasonId = (int) ($_POST['season_id'] ?? 0);
        if ($seasonId <= 0) {
            Session::setFlash('error', 'Velg sesong for runden');

            return Response::redirect('/cup/seasons');
        }

        return $this->createRound($seasonId);
    }

    public function createRound(int $seasonId): array
    {
        $body = [
            'round_number' => (int) ($_POST['round_number'] ?? 1),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'start_date' => trim((string) ($_POST['start_date'] ?? '')),
            'end_date' => trim((string) ($_POST['end_date'] ?? '')),
            'result_deadline' => trim((string) ($_POST['result_deadline'] ?? '')),
            'is_active' => isset($_POST['is_active']),
        ];

        $client = new BackendApiClient();
        $result = $client->createAdminSeasonRound($seasonId, $body);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok']
                ? 'Runde «' . $body['name'] . '» er opprettet'
                : ($result['error'] ?? 'Kunne ikke opprette runde'),
            $result['errors'] ?? []
        );

        return Response::redirect('/cup/seasons?season=' . $seasonId);
    }

    public function updateCupStandings(int $seasonId): array
    {
        $placementPoints = $_POST['placement_points'] ?? [];
        if (!is_array($placementPoints)) {
            $placementPoints = [];
        }

        $compIds = $_POST['cup_competition_ids'] ?? [];
        if (!is_array($compIds)) {
            $compIds = [];
        }

        $body = [
            'cup_standings_mode' => (string) ($_POST['cup_standings_mode'] ?? 'total_score'),
            'cup_standings_count_best' => (int) ($_POST['cup_standings_count_best'] ?? 6),
            'placement_points' => $placementPoints,
            'cup_competition_ids' => array_map('intval', $compIds),
        ];

        $client = new BackendApiClient();
        $result = $client->updateAdminSeasonCupStandings($seasonId, $body);
        Session::setFlash(
            $result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Sammenlagt-innstillinger lagret' : ($result['error'] ?? 'Kunne ikke lagre'),
            $result['errors'] ?? []
        );

        return Response::redirect('/cup/seasons?season=' . $seasonId);
    }
}
