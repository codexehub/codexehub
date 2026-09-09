# Backend Changelog — Codexehub Control Plane

One line per completed task (newest first within a phase). See `doc/backend/LAST-TASK-SUMMARY.md`
for the current task's detail and `CLAUDE.md` for the running narrative.

## Setup

- 2026-09-09 — Created the SRS build-out workflow: `WORK-INSTRUCTIONS.md` (v1.0.0), `CHECKLIST.md`
  (130 tasks, Phases 1–11 + GA), `PROJECT-STATUS.md`, `LAST-TASK-SUMMARY.md`, this file.

## Cross-cutting foundation

- 2026-09-09 — **AUDIT-002** — audit rollout + admin viewer (closes the foundation 6 / 6).
  `Auditable` trait added to 14 models (Customer, Product, ProductCategory, Service, Portfolio,
  PortfolioCategory, TeamMember, Consultation, User, LicensedApp, LicensedAppPackage, License,
  LicenseCustomer, LicenseTransaction). `License::auditExclude()` adds `last_verified_at`
  (Vendor-API verify noise). New `Admin\AuditLogController` (`can:audit.view`) +
  `admin/pages/audit/index` viewer (filter by action / entity type / actor incl. "system" / date
  range) + `admin.audit.index` route + sidebar entry. `AuditLog` gains `entity_name` + `change_set`
  accessors. Child / repeater / image models left unaudited by design. +10 tests
  (`Feature/Admin/AuditLogViewerTest` 7, `Feature/Foundation/AuditableRolloutTest` 3; suite 88).
  No migration.
- 2026-09-09 — **AUDIT-001** — audit log infrastructure. Migration `2026_09_09_000004` `audit_logs`
  (append-only). `AuditLog` model; `App\Services\Audit\AuditLogger` (`log()`, `withoutAuditing()`,
  singleton); `App\Models\Concerns\Auditable` trait (model events → logger, `auditExclude`).
  Applied to `Role`; seeder wrapped in `withoutAuditing`. `RoleController@update` /
  `UserController@update` log `role.permissions_updated` / `user.roles_updated`. +9 tests (suite 72).
- 2026-09-09 — **RBAC-003** — legacy gates folded in + admin Roles & Permission screen.
  `PermissionCatalog` +`product`/`team`/`licensing` groups (39 → 51); `AppServiceProvider` legacy
  `isAdmin()` gate loop removed. `User::isStaff()` + `EnsureUserIsAdmin` role-aware.
  `Admin\RoleController` (index/edit/update, `roles.*` gated, super-admin locked) + views +
  `admin.roles.*` routes (replaces `PlaceholderController@roles`). `UserController` role assignment
  + last-super-admin guard. Sidebar `@can` gating. +13 tests.
- 2026-09-09 — **RBAC-002** — SRS §105 role seed. `App\Support\Authorization\RoleCatalog` (8 roles,
  least-privilege sets, `super-admin` = `'*'`); `RolePermissionSeeder` iterates it; `UserFactory::asRole()`.
  +9 tests (suite 31). MySQL dev DB seeded (perm counts 39/14/13/12/11/6/9/9).
- 2026-09-09 — **RBAC-001** — roles & permissions schema. Migration `2026_09_09_000001`
  (`roles` / `permissions` / `permission_role` / `role_user`). `Role` / `Permission` models,
  `HasRoles` trait on `User`, `PermissionCatalog` (39 keys / 19 groups). `AppServiceProvider`
  `Gate::before` super-admin + catalog `Gate::define` loop. `RolePermissionSeeder` (idempotent) →
  `DatabaseSeeder`. +10 tests (suite 22). MySQL dev DB migrated + seeded.
- 2026-09-09 — **HARD-000** — PHPUnit 11 test harness (SQLite `:memory:`, no Pest). Removed scaffold
  `ExampleTest`s; `UserFactory` `role` default + `nonAdmin()` state; `UserTest`, `AdminAccessTest`,
  `SmokeTest` (7 public pages on empty DB). `php artisan test` → 12 passed.

## Phase 1 — Foundation

- 2026-09-09 — **FEAT-002** — Feature admin CRUD + dependency resolution + config-schema validator.
  `Admin\FeatureController` (`HasMiddleware` — `can:features.view` index / `can:features.manage`
  writes; Package Manager holds both, SRS §105): search + type/status filters, sortable columns,
  create/edit/update, `PATCH {feature}/status`, `destroy` blocked while another feature depends on
  it. `admin.features.*` routes (wildcard `{feature}` bound by `key`, last) +
  `admin/pages/features/{index,_form,create,edit}` + "Feature Catalog" sidebar entry
  (`bi-puzzle-fill`, `@can features.view`). Form: name / key (auto-slug `_`, `regex:/^[a-z0-9_]+$/`,
  unique) / type / status / version / default_price / description, multi-select dependency picker,
  monospace config-schema JSON textarea, documentation. New
  `App\Services\Feature\FeatureDependencyResolver` (`missing()` unknown keys / `detectCycle()` DFS /
  `resolve()` transitive set). Controller `after()` hook rejects self-deps, unknown keys and circular
  deps; config-schema must be valid JSON, a JSON object, and declare `type` or `properties` (SRS §79
  — per-package value validation is PKG-003/PKG-009). No new permission keys. No migration. +16 tests
  (`Unit/FeatureDependencyResolverTest` 6, `Feature/Admin/FeatureAdminTest` 10; suite 130).
- 2026-09-09 — **FEAT-001** — reusable feature catalog. **Distinct-table decision:** the marketing
  `product_features` / `service_features` tables are per-product bullet-list repeaters (icon + text)
  — unrelated to an entitlement catalog, so `features` is its own table. Migration
  `2026_09_09_000007` `features` (`name`, `key` [unique machine id — SRS §52 `key`, checklist's
  `machine_key`], `type` [default `module`, indexed], `description`, `default_price` decimal(12,2)
  default 0, `version` default `1.0.0`, `status` [default `active`, indexed], `dependencies` json,
  `config_schema` json [SRS §79], `documentation`). `App\Models\Feature` (`Auditable` + `HasFactory`;
  `STATUSES` active/inactive/deprecated, `TYPES` core/module/integration/analytics/marketing/custom;
  `array` casts for the json columns, `decimal:2` price; `key` auto-derived from `name` on create;
  `status()` / `active()` / `type()` scopes; `dependencyFeatures()`; route key `key`). `FeatureFactory`
  (`inactive()` / `deprecated()` / `type()` / `dependsOn()` / `withConfigSchema()`). Registered in
  `AuditLogController::ENTITY_TYPES`. Admin CRUD → FEAT-002, catalog seed → FEAT-003. +11 tests
  (`Unit/FeatureTest` 8, `Feature/Foundation/FeatureCatalogTest` 3; suite 114). Migrated on the MySQL
  dev DB.
- 2026-09-09 — **PROD-002** — Product admin `key` field + `products.*` gate migration. The marketing
  Product admin already had full CRUD + a `version` field. Added the PROD-001 `key` (control-plane
  machine id) to `_form.blade.php`; `ProductController` slugifies it, `Rule::unique('products','key')`,
  blank → current key (edit) / model slug-derivation (create). Retired the legacy `product.*`
  permission group (`PermissionCatalog` 51 → 47); `Admin\Product{,Category}Controller` now gate on
  `products.view` / `products.manage` (Package Manager can manage the catalog, SRS §105).
  `RolePermissionSeeder` prunes removed keys (`whereNotIn(catalog)->delete()`); re-run on the dev DB.
  Sidebar `can` key updated. +6 tests (`Feature/Admin/ProductAdminTest`; suite 103). No migration.
- 2026-09-09 — **PROD-001** — control-plane product. **Decision (user-confirmed):** the marketing
  `products` table (`App\Models\Product`, `type` = software|physical) *is* the SRS §52 control-plane
  product — no new table. Migration `2026_09_09_000005` adds `products.key` (nullable unique,
  backfilled ← `slug`); model auto-fills it ← `slug` on create. `licensed_apps` demoted to a link
  row — migration `2026_09_09_000006` adds nullable `licensed_apps.product_id` (FK, `nullOnDelete`,
  backfilled by exact slug/key match). `LicensedApp::product()` / `Product::licensedApps()`.
  `Product` + `ProductCategory` gain `HasFactory`; new `ProductFactory` / `ProductCategoryFactory`.
  `ProductSeeder` adds the SRS §6 `ecommerce` product + "E-Commerce" category; `LicensingSeeder`
  links `ecommerce-laravel` → it. Vendor API untouched. +9 tests (`Unit/ProductTest` 4,
  `Feature/Foundation/ControlPlaneProductTest` 5; suite 97). Migrated + seeded on the MySQL dev DB.
- 2026-09-09 — **CUST-003** — Customer detail page shell (SRS §77). `CustomerController@show`
  (`can:customers.view`) + `GET admin/customers/{customer}` → `admin.customers.show` (wildcard
  declared before `{customer}/edit`) + `admin/pages/customers/show.blade.php` — Bootstrap `nav-tabs`
  in SRS §77 order (Information / Products / Packages / Package Features / Services / Requirements /
  Custom Development / Invoices / Payments / License / Installation / Software Version / Deployments /
  Ownership / Audit History). Information / License / Audit tabs render real data (customer fields +
  notes; `Customer::licenses()` hasManyThrough with app / package / status / expiry; `AuditLog::forEntity`
  last 50); the 12 phase-gated tabs render a shared placeholder naming the phase + task that fills each.
  Index links the customer name + adds an eye action to the page. +5 tests
  (`Feature/Admin/CustomerDetailPageTest`; suite 88). No migration.
- 2026-09-09 — **CUST-002** — Customer admin CRUD. `Admin\CustomerController` (`HasMiddleware`:
  `can:customers.view` on index, `can:customers.manage` on write) — search (name/company/email),
  status filter, sortable columns, create/edit/update, `PATCH .../status` transition, delete
  (blocked while the customer has licenses). New `admin.customers.*` routes + `admin/pages/customers/*`
  views + sidebar entry (`@can customers.view`). Licensing repointed at `customers`: the old
  `Admin\Licensing\LicenseCustomerController` + `admin.licensing.customers.*` + its views **removed**;
  `LicenseController` now lists `Customer` and bridges to the released `licenses.license_customer_id`
  via `LicenseController::resolveLicenseCustomer()` (one `LicenseCustomer` link row per customer,
  `firstOrCreate`). +14 tests (`Feature/Admin/CustomerCrudTest` 11, `Feature/Admin/LicenseCustomerBridgeTest` 2,
  `Unit/CustomerTest` +1). No migration.
- 2026-09-09 — **CUST-001** — canonical `customers` table (SRS §52 columns + `notes`, `status`
  default `prospect`, unique `email`). `App\Models\Customer` (`STATUSES`, `status()` / `active()`
  scopes, `licenseCustomers()` hasMany, `licenses()` hasManyThrough) + `CustomerFactory`.
  Additive fold-in: migration `2026_09_09_000003` adds nullable `license_customers.customer_id`
  (FK, `nullOnDelete`) and backfills — each legacy row promoted to a `customers` row, deduped by
  email. `LicenseCustomer` gains `HasFactory` + `customer()` belongsTo. +8 tests (suite 39).
  MySQL dev DB migrated.
