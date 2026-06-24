<?php

declare(strict_types=1);

namespace App\Service;

use App\Support\Config;
use App\Support\Session;

final class BackendApiClient
{
    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function health(): array
    {
        return $this->get('/api/health');
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function tenants(): array
    {
        return $this->get('/api/tenants');
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminTenants(): array
    {
        return $this->get('/api/admin/tenants');
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminTenant(int $id): array
    {
        return $this->get('/api/admin/tenants/' . $id);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function createAdminTenant(array $body): array
    {
        return $this->post('/api/admin/tenants', $body);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function updateAdminTenant(int $id, array $body): array
    {
        return $this->put('/api/admin/tenants/' . $id, $body);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function deactivateAdminTenant(int $id): array
    {
        return $this->delete('/api/admin/tenants/' . $id);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminDomains(int $tenantId): array
    {
        return $this->get('/api/admin/tenants/' . $tenantId . '/domains');
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function createAdminDomain(int $tenantId, array $body): array
    {
        return $this->post('/api/admin/tenants/' . $tenantId . '/domains', $body);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function updateAdminDomain(int $id, array $body): array
    {
        return $this->put('/api/admin/domains/' . $id, $body);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function deleteAdminDomain(int $id): array
    {
        return $this->delete('/api/admin/domains/' . $id);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminUsers(?string $search = null, int $limit = 50): array
    {
        $path = '/api/admin/users';
        if ($search !== null && trim($search) !== '') {
            $path .= '?q=' . rawurlencode(trim($search)) . '&limit=' . max(1, min(100, $limit));
        }

        return $this->get($path);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminUser(int $id): array
    {
        return $this->get('/api/admin/users/' . $id);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function createAdminUser(array $body): array
    {
        return $this->post('/api/admin/users', $body);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function updateAdminUser(int $id, array $body): array
    {
        return $this->put('/api/admin/users/' . $id, $body);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function deactivateAdminUser(int $id): array
    {
        return $this->delete('/api/admin/users/' . $id);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminRoles(): array
    {
        return $this->get('/api/admin/roles');
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminRoleAssignmentsOverview(): array
    {
        return $this->get('/api/admin/role-assignments');
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminRoleAssignments(string $role): array
    {
        return $this->get('/api/admin/role-assignments/' . rawurlencode($role));
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminUserAccess(int $id): array
    {
        return $this->get('/api/admin/users/' . $id . '/access');
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function grantSystemRole(int $userId, array $body): array
    {
        return $this->post('/api/admin/users/' . $userId . '/system-roles', $body);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function revokeSystemRole(int $userId, string $role): array
    {
        return $this->delete('/api/admin/users/' . $userId . '/system-roles/' . rawurlencode($role));
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function grantTenantAccess(int $userId, array $body): array
    {
        return $this->post('/api/admin/users/' . $userId . '/tenant-access', $body);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function revokeTenantAccess(int $userId, int $accessId): array
    {
        return $this->delete('/api/admin/users/' . $userId . '/tenant-access/' . $accessId);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminOrganizations(?int $tenantId = null, ?string $search = null): array
    {
        $query = [];
        if ($tenantId !== null && $tenantId > 0) {
            $query[] = 'tenant_id=' . $tenantId;
        }
        if ($search !== null && trim($search) !== '') {
            $query[] = 'q=' . rawurlencode(trim($search));
        }
        $path = '/api/admin/organizations';
        if ($query !== []) {
            $path .= '?' . implode('&', $query);
        }

        return $this->get($path);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminOrganization(int $id): array
    {
        return $this->get('/api/admin/organizations/' . $id);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function createAdminOrganization(array $body): array
    {
        return $this->post('/api/admin/organizations', $body);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function updateAdminOrganization(int $id, array $body): array
    {
        return $this->put('/api/admin/organizations/' . $id, $body);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function deactivateAdminOrganization(int $id): array
    {
        return $this->delete('/api/admin/organizations/' . $id);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function addAdminOrganizationMember(int $organizationId, array $body): array
    {
        return $this->post('/api/admin/organizations/' . $organizationId . '/members', $body);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function removeAdminOrganizationMember(int $organizationId, int $memberId): array
    {
        return $this->delete('/api/admin/organizations/' . $organizationId . '/members/' . $memberId);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminSeasons(?int $tenantId = null): array
    {
        $path = '/api/admin/seasons';
        if ($tenantId !== null && $tenantId > 0) {
            $path .= '?tenant_id=' . $tenantId;
        }

        return $this->get($path);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function adminSeason(int $id): array
    {
        return $this->get('/api/admin/seasons/' . $id);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function createAdminSeason(array $body): array
    {
        return $this->post('/api/admin/seasons', $body);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function updateAdminSeason(int $id, array $body): array
    {
        return $this->put('/api/admin/seasons/' . $id, $body);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function updateAdminSeasonCupStandings(int $id, array $body): array
    {
        return $this->put('/api/admin/seasons/' . $id . '/cup-standings', $body);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function createAdminSeasonRound(int $seasonId, array $body): array
    {
        return $this->post('/api/admin/seasons/' . $seasonId . '/rounds', $body);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function login(string $email, string $password): array
    {
        $result = $this->request('POST', '/api/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);
        if ($result['ok'] ?? false) {
            $this->storeBackendSessionFromLoginResponse($result['data'] ?? []);
            $this->captureSessionCookieFromLastResponse();
        }

        return $result;
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function logout(): array
    {
        $result = $this->post('/api/auth/logout', []);
        Session::clearBackendCookie();

        return $result;
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    public function me(): array
    {
        return $this->get('/api/auth/me');
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    private function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    private function post(string $path, array $body): array
    {
        return $this->request('POST', $path, $body);
    }

    /**
     * @param array<string, mixed> $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    private function put(string $path, array $body): array
    {
        return $this->request('PUT', $path, $body);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    private function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    /** @var list<string>|null */
    private static ?array $lastResponseHeaders = null;

    /**
     * @param array<string, mixed>|null $body
     * @return array{ok: bool, status: int, data: array<string, mixed>|null, error: string|null, errors?: array<string, string>}
     */
    private function request(string $method, string $path, ?array $body = null): array
    {
        $baseUrl = (string) Config::get('backend.api_base_url', '');
        if ($baseUrl === '') {
            return [
                'ok' => false,
                'status' => 0,
                'data' => null,
                'error' => 'BACKEND_API_URL is not configured',
            ];
        }

        $url = $baseUrl . $path;
        $headers = "Accept: application/json\r\n";
        $cookie = Session::getBackendCookie();
        if ($cookie !== '') {
            $headers .= 'Cookie: ' . $cookie . "\r\n";
        }

        $options = [
            'method' => $method,
            'timeout' => 12,
            'ignore_errors' => true,
            'header' => $headers,
        ];

        if ($body !== null) {
            $payload = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $options['header'] .= "Content-Type: application/json\r\n";
            $options['content'] = $payload;
        }

        $context = stream_context_create(['http' => $options]);
        self::$lastResponseHeaders = null;
        $responseBody = @file_get_contents($url, false, $context);
        self::$lastResponseHeaders = $http_response_header ?? null;

        $status = 0;
        if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
            $status = (int) $m[1];
        }

        if ($responseBody === false) {
            return [
                'ok' => false,
                'status' => $status,
                'data' => null,
                'error' => 'Could not reach backend at ' . $url,
            ];
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [
                'ok' => false,
                'status' => $status,
                'data' => null,
                'error' => 'Invalid JSON from backend',
            ];
        }

        $ok = $status >= 200 && $status < 300;
        $result = [
            'ok' => $ok,
            'status' => $status,
            'data' => $decoded,
            'error' => $ok ? null : (string) ($decoded['error'] ?? 'HTTP ' . $status),
        ];

        if (!$ok && is_array($decoded['errors'] ?? null)) {
            /** @var array<string, string> $fieldErrors */
            $fieldErrors = $decoded['errors'];
            $result['errors'] = $fieldErrors;
            $first = reset($fieldErrors);
            if (is_string($first) && $first !== '') {
                $result['error'] = $first;
            }
        }

        return $result;
    }

    /** @param array<string, mixed> $data */
    private function storeBackendSessionFromLoginResponse(array $data): void
    {
        $session = $data['session'] ?? null;
        if (!is_array($session)) {
            return;
        }

        $name = trim((string) ($session['name'] ?? ''));
        $id = trim((string) ($session['id'] ?? ''));
        if ($name !== '' && $id !== '') {
            Session::setBackendCookie($name . '=' . $id);
        }
    }

    private function captureSessionCookieFromLastResponse(): void
    {
        $headers = self::$lastResponseHeaders ?? [];
        foreach ($headers as $header) {
            if (!str_starts_with(strtolower($header), 'set-cookie:')) {
                continue;
            }
            if (!preg_match('/^Set-Cookie:\s*([^=]+)=([^;]+)/i', $header, $m)) {
                continue;
            }
            $name = trim($m[1]);
            if ($name === 'BIFROSTSESSID') {
                Session::setBackendCookie($name . '=' . trim($m[2]));
                break;
            }
        }
    }
}
