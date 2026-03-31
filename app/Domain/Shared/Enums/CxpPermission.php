<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

/**
 * Nombres de permisos Spatie (guard sanctum). Único catálogo para seed, middleware y validación.
 */
enum CxpPermission: string
{
    case Access = 'access';

    case UsersViewAny = 'users.view-any';
    case UsersInvite = 'users.invite';
    case UsersRemove = 'users.remove';

    case RolesManage = 'roles.manage';
    case SettingsManage = 'settings.manage';

    case SitesViewAny = 'sites.view-any';
    case SitesCreate = 'sites.create';
    case SitesRead = 'sites.read';
    case SitesUpdate = 'sites.update';
    case SitesDelete = 'sites.delete';

    case LocationsViewAny = 'locations.view-any';
    case LocationsCreate = 'locations.create';
    case LocationsRead = 'locations.read';
    case LocationsUpdate = 'locations.update';
    case LocationsDelete = 'locations.delete';

    case LandlordPanel = 'landlord.panel';

    case TenantsViewAny = 'tenants.view-any';
    case TenantsCreate = 'tenants.create';
    case TenantsRead = 'tenants.read';
    case TenantsUpdate = 'tenants.update';
    case TenantsDelete = 'tenants.delete';

    case TenantUsersViewAny = 'tenant-users.view-any';
    case TenantUsersCreate = 'tenant-users.create';

    public function asMiddleware(): string
    {
        return 'permission:'.$this->value;
    }

    /**
     * @param  list<self>  $cases
     */
    public static function middlewareOr(self ...$cases): string
    {
        return 'permission:'.implode('|', array_map(fn (self $c) => $c->value, $cases));
    }

    /**
     * Permisos que un tenant puede asignar a sus roles (catálogo acotado).
     *
     * @return list<self>
     */
    public static function tenantRoleCatalog(): array
    {
        return [
            self::Access,
            self::UsersViewAny,
            self::UsersInvite,
            self::UsersRemove,
            self::RolesManage,
            self::SettingsManage,
            self::SitesViewAny,
            self::SitesCreate,
            self::SitesRead,
            self::SitesUpdate,
            self::SitesDelete,
            self::LocationsViewAny,
            self::LocationsCreate,
            self::LocationsRead,
            self::LocationsUpdate,
            self::LocationsDelete,
        ];
    }

    /**
     * @return list<string>
     */
    public static function tenantRoleCatalogValues(): array
    {
        return array_map(fn (self $c) => $c->value, self::tenantRoleCatalog());
    }

    /**
     * Permisos solo para el equipo landlord (platform team).
     *
     * @return list<self>
     */
    public static function landlordPlatform(): array
    {
        return [
            self::LandlordPanel,
            self::TenantsViewAny,
            self::TenantsCreate,
            self::TenantsRead,
            self::TenantsUpdate,
            self::TenantsDelete,
            self::TenantUsersViewAny,
            self::TenantUsersCreate,
        ];
    }

    /**
     * @return list<string>
     */
    public static function allSeederPermissionValues(): array
    {
        $merged = [...self::landlordPlatform(), ...self::tenantRoleCatalog()];
        $values = array_map(fn (self $c) => $c->value, $merged);

        return array_values(array_unique($values));
    }

    /**
     * Subconjunto para el rol "admin" por defecto (sin gestión de roles ni settings globales).
     *
     * @return list<self>
     */
    public static function defaultAdminRolePermissions(): array
    {
        return [
            self::Access,
            self::UsersViewAny,
            self::UsersInvite,
            self::UsersRemove,
            self::SitesViewAny,
            self::SitesCreate,
            self::SitesRead,
            self::SitesUpdate,
            self::SitesDelete,
            self::LocationsViewAny,
            self::LocationsCreate,
            self::LocationsRead,
            self::LocationsUpdate,
            self::LocationsDelete,
        ];
    }

    /**
     * Subconjunto para el rol "user" por defecto.
     *
     * @return list<self>
     */
    public static function defaultUserRolePermissions(): array
    {
        return [
            self::Access,
            self::SitesViewAny,
            self::SitesRead,
            self::LocationsViewAny,
            self::LocationsRead,
        ];
    }
}
