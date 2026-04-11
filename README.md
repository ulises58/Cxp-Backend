# CXP Backend

API REST **multitenancy** pensada como **base reutilizable** para productos SaaS: un solo código, varios tenants, permisos por equipo (tenant) y un panel “landlord” para operación de plataforma.

> Convenciones probadas con tests de integración (`tests/Feature/Api/V1`). Úsalo como plantilla y extiende dominio (`app/Domain`), DTOs (`Spatie Laravel Data`) y permisos (`CxpPermission`) de forma coherente.

---

## Stack principal

| Pieza | Uso en este proyecto |
|--------|----------------------|
| **Laravel 13** | Framework, rutas API, Eloquent |
| **Laravel Sanctum** | Tokens API (`auth:sanctum`) |
| **stancl/tenancy** | Modelo `Tenant`, inicialización de contexto tenant (`Tenancy::initialize`) |
| **spatie/laravel-permission** | Roles y permisos con **teams** = tenant (y equipo platform para landlord) |
| **spatie/laravel-data** | Respuestas API v1 tipadas (`*Data::toArray()`) |

---

## Modelo mental: dos mundos en la misma API

1. **Landlord (plataforma)**  
   Usuarios sin `tenant_id` (o con equipo platform). Rutas bajo prefijo `landlord`, middleware `landlord`. Ej.: CRUD de tenants, alta de usuarios en un tenant.

2. **Tenant (organización cliente)**  
   Usuarios con `tenant_id`. Rutas bajo middleware `tenant.context`, que exige usuario con tenant activo y ejecuta `Tenancy::initialize($tenant)`. Los permisos Spatie se resuelven con `setPermissionsTeamId` vía middleware `permission.team`.

El **catálogo de permisos** que un tenant puede asignar a sus roles está acotado en `App\Domain\Shared\Enums\CxpPermission::tenantRoleCatalog()`.

---

## Datos por tenant (recursos de negocio)

Orden conceptual:

```
Tenant
  └── Group (opcional, agrupación lógica)
  └── Site
        └── Location (dirección, geo opcional, timezone, metadata JSON)
```

- **`groups`**: pertenecen al tenant (`tenant_id`); un **site** puede tener `group_id` nullable.
- **`sites`** y **`locations`**: siempre acotados por `tenant_id` y, en locations, por `site_id`.

---

## API v1

- Prefijo: **`/api/v1`** (definido en `routes/api.php` → `routes/api/v1.php`).
- Autenticación: **Bearer Sanctum** (header `Authorization: Bearer {token}`).
- Respuestas de recurso único: habitualmente `{ "data": { ... } }`; listados paginados: formato paginator + `meta` / `links` según `ApiV1PaginatedResponse`.

### Rutas tenant (ejemplos)

| Área | Rutas |
|------|--------|
| Perfil | `GET /profile` |
| Permisos asignables | `GET /permissions` (requiere `roles.manage`) |
| Roles | `GET/POST /roles`, `GET/PATCH/DELETE /roles/{id}` |
| Usuarios | `GET /users`, `GET /users/{id}`, `PATCH /users/{id}/roles` |
| Grupos | `GET/POST /groups`, `GET/PATCH/DELETE /groups/{id}` |
| Sites | `GET/POST /sites`, `GET/PATCH/DELETE /sites/{id}` |
| Locations | Anidadas: `/sites/{site}/locations` (api resource) |

Los nombres de parámetro en ruta (`tenantSite`, `tenantGroup`, etc.) se resuelven con **bindings personalizados** para evitar acceso cruzado entre tenants (IDOR).

---

## Seguridad multitenancy: `Route::bind`

En `App\Providers\AppServiceProvider` se registran resolutores para parámetros como `tenantSite`, `tenantGroup`, `tenantLocation`, etc.: siempre filtran por `auth()->user()->tenant_id` (y anidación correcta site → location). Si el registro no pertenece al tenant actual → **404**, no 403, para no filtrar existencia de IDs ajenos.

---

## Permisos (`CxpPermission`)

- Enum único: `app/Domain/Shared/Enums/CxpPermission.php`.
- Valores string = nombres en tabla `permissions` (guard `sanctum`).
- **Seed global**: `Database\Seeders\RolePermissionSeeder` crea todos los permisos listados en `CxpPermission::allSeederPermissionValues()`.
- **Nuevo tenant**: `BootstrapTenantDefaultRolesAction` crea roles `owner`, `admin`, `user` y sincroniza permisos según `tenantRoleCatalog()`, `defaultAdminRolePermissions()` y `defaultUserRolePermissions()`.

Para añadir un permiso nuevo al catálogo tenant:

1. Añade el `case` al enum.
2. Inclúyelo en `tenantRoleCatalog()` (y en los subconjuntos de roles que correspondan).
3. Ejecuta migraciones/seed en entornos que lo necesiten (o `Permission::firstOrCreate` en un seeder incremental).

---

## Cómo arrancar en local

Requisitos: PHP 8.3+, Composer, base de datos (MySQL/PostgreSQL/SQLite).

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed   # o al menos RolePermissionSeeder según tu DatabaseSeeder
php artisan serve
```

Con **Laravel Sail** (recomendado si usas Docker):

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
```

---

## Tests

Los tests de API usan **SQLite en memoria** (`phpunit.xml`) y `RefreshDatabase` + `RolePermissionSeeder` en `ApiV1TestCase`.

```bash
php artisan test
# o
./vendor/bin/phpunit
# con Sail:
./vendor/bin/sail artisan test
```

Hay **tests de integración (Feature)** para los flujos principales y **tests unitarios** puntuales del dominio (p. ej. resolución del catálogo de permisos). La validación de **locations** (longitud, radio, timezone) y de **grupos** (nombre único por tenant, FK al borrar) está cubierta; las rutas tenant/landlord responden **401** sin token (`ApiV1GuestTenantRoutesTest`).

### Cobertura (`tests/`)

| Archivo | Qué cubre |
|---------|-----------|
| `Feature/Api/V1/AuthLoginTest` | Login, Sanctum, tenant inactivo |
| `Feature/Api/V1/LandlordHealthTest`, `LandlordTenantApiTest`, `LandlordTenantUserApiTest` | Panel plataforma |
| `Feature/Api/V1/TenantProfileApiTest` | Perfil tenant + middleware |
| `Feature/Api/V1/TenantPermissionsCatalogTest` | Catálogo de permisos |
| `Feature/Api/V1/TenantRolesApiTest` | CRUD roles tenant |
| `Feature/Api/V1/TenantUsersApiTest` | Listado usuarios, sync roles |
| `Feature/Api/V1/TenantGroupsApiTest` | CRUD grupos, aislamiento entre tenants, nombre duplicado (422), borrado → `sites.group_id` null |
| `Feature/Api/V1/TenantSitesAndLocationsApiTest` | Sites/locations, `group_id`, geo básica, location bajo site incorrecto → 404 |
| `Feature/Api/V1/TenantLocationValidationApiTest` | Reglas de validación geo/timezone + PATCH que limpia campos |
| `Feature/Api/V1/ApiV1GuestTenantRoutesTest` | Invitado sin token en rutas tenant y landlord |
| `Unit/Domain/TenantCatalogPermissionResolverTest` | `TenantCatalogPermissionResolver`: catálogo vs DB |

Añade tests nuevos en el mismo estilo cuando incorpores endpoints v1 o lógica de dominio crítica.

---

## Estructura de código (orientación)

- **`app/Domain/`**: acciones, servicios, repositorios de dominio (tenant, landlord compartido donde aplique).
- **`app/Http/Controllers/Api/V1/`**: capa HTTP fina; validación en `FormRequest`; permisos en constructor del controlador con `CxpPermission`.
- **`app/Domain/Shared/Data/Api/V1/`**: DTOs de respuesta (`*Data`).

---

## Frontends y BFF

Este repositorio es solo el backend. Un cliente (por ejemplo Next.js) puede actuar como **BFF**: cookie httpOnly + proxy a Laravel. Los tests aquí no sustituyen contratos E2E en el cliente.

---

## Licencia

El esqueleto Laravel y las dependencias conservan sus licencias (p. ej. MIT). El código de dominio específico del proyecto queda bajo la licencia que definas en tu organización.
