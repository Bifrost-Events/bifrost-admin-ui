<?php



declare(strict_types=1);



namespace App\Service;



final class AuthService

{

    private const ADMIN_SYSTEM_ROLES = ['SystemAdmin'];

    private const ADMIN_TENANT_ROLES = ['CupAdmin'];



    /**

     * @param array<string, mixed> $user

     */

    public static function canAccessAdmin(array $user): bool

    {

        if (($user['can_access_admin'] ?? false) === true) {

            return true;

        }



        foreach ($user['system_roles'] ?? [] as $role) {

            if (is_array($role) && in_array($role['role'] ?? '', self::ADMIN_SYSTEM_ROLES, true)) {

                return true;

            }

        }



        foreach ($user['tenant_admin_access'] ?? [] as $access) {

            if (is_array($access) && in_array($access['role'] ?? '', self::ADMIN_TENANT_ROLES, true)) {

                return true;

            }

        }



        return false;

    }

}

