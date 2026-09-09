# PROJECT STATUS — Codexehub Control Plane & Licensing Platform

> Whole-platform checklist & %: `doc/backend/CHECKLIST.md`.
> Workflow: `doc/backend/WORK-INSTRUCTIONS.md`. History: `CLAUDE.md` (repo root).
> Last task detail: `doc/backend/LAST-TASK-SUMMARY.md`.

## Current Release

**v1.2** (the SRS target). Build-out organized into SRS §144 Phases 1–11 + a GA gate.
Whole-platform progress: **11 / 130** (see CHECKLIST). A pre-SRS licensing prototype exists and
partially covers slices of FEAT / PKG / PAY / APISEC / INST / LIC-CP / REL.

## Current Phase

**Phase 1 — Foundation** (7 / 12) — started early on user instruction. Cross-cutting foundation is
**6 / 6 — closed**. Working tree carries AUDIT-002 + CUST-003 + PROD-001 + PROD-002 + FEAT-001 +
FEAT-002 from concurrent sessions — review before commit.

## Current Task

**FEAT-003** — seed the Ecommerce feature catalog (payment_gateway, courier, advanced_inventory,
seo, facebook_pixel, google_analytics, sms_gateway, whatsapp, custom_checkout, custom_invoice,
restaurant_module, multi_vendor, wishlist, coupon, advanced_reporting; SRS §7). Or **SVC-001**.

Prior: **FEAT-002 COMPLETE** — Feature admin CRUD + dependency resolution + config-schema validator.
`Admin\FeatureController` (`HasMiddleware` — `can:features.view` index / `can:features.manage` writes;
Package Manager holds both). Search + type/status filters, sortable columns, create/edit/update,
`PATCH {feature}/status`, `destroy` blocked while another feature depends on it. `admin.features.*`
routes (wildcard `{feature}` bound by `key`, last) + `admin/pages/features/{index,_form,create,edit}`
+ "Feature Catalog" sidebar entry (`@can features.view`). Form: name/key (auto-slug `_`, regex,
unique)/type/status/version/default_price/description, multi-select dependency picker, monospace
config-schema JSON textarea, documentation. New `App\Services\Feature\FeatureDependencyResolver`
(`missing()` / `detectCycle()` DFS / `resolve()` transitive set). Controller `after()` hook rejects
self-deps, unknown keys, circular deps; config-schema must be valid JSON, an object, declaring `type`
or `properties` (SRS §79 — per-package value validation is PKG-003/PKG-009). No new permission keys.
+16 tests (`Unit/FeatureDependencyResolverTest` 6, `Feature/Admin/FeatureAdminTest` 10; suite 130).
No migration.

Prior: **FEAT-001 COMPLETE** — reusable feature catalog. **Distinct-table decision (recorded):** the
marketing `product_features` / `service_features` tables are per-product bullet-list repeaters
(icon + text) — unrelated to an entitlement catalog, so `features` is its own table (no fold-in).
Migration `2026_09_09_000007` `features` (`name`, `key` [unique machine id], `type` [default
`module`, indexed], `description`, `default_price` decimal(12,2) default 0, `version` default
`1.0.0`, `status` [default `active`, indexed], `dependencies` json, `config_schema` json,
`documentation` text). `App\Models\Feature` (`use Auditable, HasFactory`; `STATUSES`
active/inactive/deprecated, `TYPES` core/module/integration/analytics/marketing/custom; `array` casts
for the two json columns, `decimal:2` for price; `key` auto-derived from `name` on create when blank;
`status()` / `active()` / `type()` scopes; `dependencyFeatures()` resolves stored keys to rows; route
key = `key`). `FeatureFactory` (`inactive()` / `deprecated()` / `type()` / `dependsOn()` /
`withConfigSchema()` states). Registered in `AuditLogController::ENTITY_TYPES`. +11 tests
(`Unit/FeatureTest` 8, `Feature/Foundation/FeatureCatalogTest` 3; suite 114). Migrated on the MySQL
dev DB.

Prior: **PROD-002 COMPLETE** — Product admin `key` field + `products.*` gate migration. The marketing
Product admin already had full CRUD + a `version` field; PROD-002 surfaced the PROD-001 `key`
(control-plane machine id) on the form (`ProductController` slugifies + unique-validates it, blank →
kept / slug-derived), and retired the legacy `product.*` permission group — `PermissionCatalog`
51 → 47, `Admin\Product{,Category}Controller` gate on `products.view` / `products.manage` (Package
Manager can manage the catalog per SRS §105), `RolePermissionSeeder` prunes removed keys + re-run on
the dev DB. Sidebar updated. +6 tests (`Feature/Admin/ProductAdminTest`; suite 103). No migration.

Prior: **PROD-001 COMPLETE** — control-plane product. **Decision (user-confirmed):** the marketing
`products` table (`App\Models\Product`, `type` = software|physical) *is* the SRS §52 control-plane
product — no new table. `products.key` added (migration `2026_09_09_000005`, nullable unique,
backfilled ← `slug`; the stable machine id, decoupled from the editable marketing `slug`; model
auto-fills it on create). `licensed_apps` demoted to a link row — `licensed_apps.product_id`
(migration `2026_09_09_000006`, nullable FK, `nullOnDelete`, backfilled by exact slug/key match).
`LicensedApp::product()` / `Product::licensedApps()`. `Product` + `ProductCategory` gain `HasFactory`;
new `ProductFactory` / `ProductCategoryFactory`. `ProductSeeder` seeds the SRS §6 `ecommerce` product
+ "E-Commerce" category; `LicensingSeeder` links `ecommerce-laravel` → it. Vendor API untouched.
+9 tests (suite 97). Migrated + seeded on the MySQL dev DB.

> A parallel session (`codexehub-4a`) briefly started PROD-001 the other way (evolve `licensed_apps`
> with `key`/`version`/`status`), then backed it out after the user's decision. Its stray
> migration / factory / tests were deleted and the dev-DB columns dropped. PROD-001 is now done via
> the fold-in above — do not re-implement.

Prior: **CUST-003 COMPLETE** — Customer detail page shell. `CustomerController@show`
(`can:customers.view`) + `GET admin/customers/{customer}` (`admin.customers.show`, wildcard before
`{customer}/edit`) + `admin/pages/customers/show.blade.php`: Bootstrap `nav-tabs` in SRS §77 order
(15 tabs). Information / License / Audit render real data (customer fields + notes; `licenses()`
hasManyThrough w/ app+package+status+expiry; `AuditLog::forEntity` last 50); the 12 phase-gated tabs
show a shared placeholder naming the phase/task that fills each. Index name + an eye action link to
the page. +5 tests (`Feature/Admin/CustomerDetailPageTest`; suite 88). No migration.

Prior: **AUDIT-002 COMPLETE** — `Auditable` trait rolled out to 14 more models (Customer, Product,
ProductCategory, Service, Portfolio, PortfolioCategory, TeamMember, Consultation, User, LicensedApp,
LicensedAppPackage, License, LicenseCustomer, LicenseTransaction). `License::auditExclude()` adds
`last_verified_at`. New `Admin\AuditLogController` (`can:audit.view`) + `admin/pages/audit/index`
viewer (filter by action / entity type / actor incl. "system" / date range) + `admin.audit.index`
route + sidebar entry. `AuditLog` gains `entity_name` + `change_set` accessors. +10 tests. No migration.

Prior: **CUST-002 COMPLETE** — Customer admin CRUD. `Admin\CustomerController` (`can:customers.view`
index / `can:customers.manage` writes): search, status filter, sortable columns, create / edit /
update, `PATCH {customer}/status` transition, delete (blocked while the customer holds licenses).
`admin.customers.*` + `admin/pages/customers/*` + sidebar entry. Licensing repointed at `customers`:
old `LicenseCustomerController` + `admin.licensing.customers.*` + views **removed**; `LicenseController`
lists `Customer` and bridges to `licenses.license_customer_id` via `resolveLicenseCustomer()`
(`firstOrCreate` a `LicenseCustomer` link row per customer). +14 tests (suite 73). No migration.

Prior: **CUST-001 COMPLETE** — canonical `customers` table (SRS §52 columns + `notes`, unique
`email`, `status` default `prospect`). `App\Models\Customer` (`STATUSES`, `status()` / `active()`
scopes, `licenseCustomers()` hasMany, `licenses()` hasManyThrough) + `CustomerFactory`. Additive
fold-in: `license_customers` gains a nullable backfilled `customer_id` FK (`nullOnDelete`) and a
`customer()` belongsTo — kept as released schema, demoted to a licensing link row. +8 tests
(suite 39). MySQL dev DB migrated.

Prior: **RBAC-003 COMPLETE** (parallel session) — legacy `product.*` / `team.*` / `licensing.*`
gates folded into `PermissionCatalog`, `EnsureUserIsAdmin` role-aware, admin Roles & Permission
screen (`Admin\RoleController`). See `CHANGELOG-BACKEND.md`.

Prior: **RBAC-002 COMPLETE** — SRS §105 role seed. `App\Support\Authorization\RoleCatalog` defines
all 8 roles + their least-privilege permission sets (`super-admin` = `'*'`); `RolePermissionSeeder`
iterates it (idempotent). `UserFactory::asRole()` helper. Seeded on the MySQL dev DB — perms per
role: super-admin 39, sales-manager 14, package-manager 13, finance-manager 12, developer 11,
qa 6, deployment-manager 9, support 9. +9 tests (suite 31).

Prior: **RBAC-001 COMPLETE** — roles & permissions schema (`roles` / `permissions` / `permission_role`
/ `role_user`), `HasRoles` trait, `PermissionCatalog` (39 keys / 19 groups), `Gate::before`
super-admin + catalog `Gate::define` loop.

Prior: **HARD-000 COMPLETE** — PHPUnit 11 harness on SQLite `:memory:`.

## Completed

Pre-SRS (not counted as tasks — `doc/backend/Project-overview.md` + commit `1db4faf`):

- [x] Admin auth (login / forgot / reset / profile), admin dashboard shell + Chart.js, User Management.
- [x] Marketing-site admin modules: Service, Product, Portfolio, Team, Consultation.
- [x] Primitive licensing subsystem: `licensed_apps`, `licensed_app_packages`, `license_customers`,
  `licenses`, `license_activations`, `license_transactions`; `LicenseTokenService` (RS256 JWT);
  Admin\Licensing CRUD + suspend/reactivate/revoke + transaction approve/reject;
  `POST /api/v1/license/{activate,verify}` (unsigned).

SRS build-out:

- [x] HARD-000 — Test harness (PHPUnit 11, SQLite `:memory:`).
- [x] RBAC-001 — Roles & permissions schema + `HasRoles` + `PermissionCatalog` + seeder.
- [x] RBAC-002 — `RoleCatalog` (8 SRS §105 roles) + seeder + `UserFactory::asRole()`.
- [x] RBAC-003 — legacy gates folded into `PermissionCatalog` (51 keys); `User::isStaff()` +
  role-aware `EnsureUserIsAdmin`; `Admin\RoleController` + `admin.roles.*` screen; user role
  assignment + last-super-admin guard; sidebar `@can` gating.
- [x] CUST-001 — canonical `customers` table + `Customer` model + factory; `license_customers`
  folded in additively (nullable backfilled `customer_id` FK).
- [x] AUDIT-001 — `audit_logs` (`2026_09_09_000004`) + `AuditLogger` service + `Auditable` trait
  (on `Role`) + explicit `role.permissions_updated` / `user.roles_updated` logging.
- [x] CUST-002 — Customer admin CRUD (`Admin\CustomerController`, `customers.*` gated) +
  `admin.customers.*` + sidebar entry; licensing customer screens removed and the license form
  repointed at `customers` via a `LicenseCustomer` bridge.
- [x] AUDIT-002 — `Auditable` trait on 14 more models; `Admin\AuditLogController` + `admin.audit.index`
  viewer (action / entity / actor / date filters) behind `audit.view`; sidebar entry. Cross-cutting
  foundation closed (6 / 6).
- [x] CUST-003 — Customer detail page shell — `CustomerController@show` + `admin.customers.show` +
  `admin/pages/customers/show.blade.php` (15 SRS §77 tabs; Information / License / Audit real, the
  rest phase-gated placeholders). +5 tests.
- [x] PROD-001 — control-plane product = marketing `products` fold-in. `products.key` (migration
  `2026_09_09_000005`) + `licensed_apps.product_id` bridge (migration `2026_09_09_000006`);
  `ProductFactory` / `ProductCategoryFactory`; `ecommerce` product seeded + linked. +9 tests.
- [x] PROD-002 — Product admin `key` field on the form + `Admin\Product{,Category}Controller` moved
  from legacy `product.*` gates to `products.view` / `products.manage`; `PermissionCatalog` 51 → 47
  (dropped the `product` group); `RolePermissionSeeder` prunes removed keys. +6 tests.
- [x] FEAT-001 — reusable `features` catalog (migration `2026_09_09_000007`) + `App\Models\Feature`
  (`Auditable`, key auto-derive, json casts, scopes, `dependencyFeatures()`) + `FeatureFactory`;
  registered in `AuditLogController::ENTITY_TYPES`. Distinct table (not a `*_features` repeater
  fold-in). +11 tests.
- [x] FEAT-002 — Feature admin CRUD (`Admin\FeatureController`, `features.*` gated) + `admin.features.*`
  routes + `admin/pages/features/*` + sidebar entry; `App\Services\Feature\FeatureDependencyResolver`
  (missing keys / cycle detection / transitive resolve); config-schema JSON validator; delete guarded
  by reverse-dependency check. +16 tests.

## Pending (next up)

- [ ] FEAT-003 — seed the Ecommerce feature catalog (SRS §7)
- [ ] SVC-001..003 — service catalog table + admin + seed (owes its fold-in decision vs. marketing `services`)
- … then the Phase 1 gate (see CHECKLIST)

## Blocked

- **Real mail delivery** — `.env` `MAIL_PASSWORD` is a placeholder. Mail-dependent tasks (offers,
  invoices, reminders, notifications) are buildable + testable via `MAIL_MAILER=log` + `tinker`;
  live delivery needs the user's Gmail App Password.
- **[VC] tasks** — need the customer product repo (`ecom-single-vendor`) and the Vendor API contract
  (`APISEC-001`) before they can start.

## Known Issues / Decisions to record

- Naming collision: SRS "products" / "features" / "services" vs. the existing marketing `products` /
  `services` tables. **PROD-001 (done):** the marketing `products` table *is* the control-plane
  product — it gained a stable `key` column (distinct from the editable `slug`), and `licensed_apps`
  was demoted to a nullable `product_id` bridge (CUST-001 pattern). The Vendor API still resolves on
  `licensed_apps.slug`; LIC-CP-001 / INST-002 move it onto the product. **FEAT-001 (done):** the
  reusable `features` catalog is a **distinct new table** — the marketing `product_features` /
  `service_features` tables are per-product bullet-list repeaters (icon + text, no key/price/schema),
  unrelated to an entitlement catalog. **FEAT-002 (done):** admin CRUD on it, gated by the
  control-plane `features.view` / `features.manage` (no new keys — already in `PermissionCatalog`,
  already on `package-manager`). SVC-001 still owes its decision vs. marketing `services`.
  — *A second session briefly attempted the opposite for products (evolve `licensed_apps` with
  `key`/`version`/`status`) and reverted; don't re-open.*
- RBAC-003 folded the ad-hoc `isAdmin()` gates into `PermissionCatalog`. **PROD-002** then retired
  the `product.*` group — the Product admin now gates on `products.view` / `products.manage`, so
  `package-manager` can manage the catalog. `team.*` / `licensing.*` remain singular legacy keys
  held only by `super-admin` (add a `content-manager` role if a non-super user needs the Team /
  Licensing modules). `User::isStaff()` gates the admin area (any RBAC role or the legacy `admin`
  string). `RolePermissionSeeder` now prunes any permission not in the catalog.
- `license_customers` fold-in: kept as released schema, demoted to a licensing link row with a
  nullable backfilled `customer_id` → `customers` (CUST-001). CUST-002 removed the standalone
  licensing customer admin; the license form now speaks `customers` and `LicenseController::
  resolveLicenseCustomer()` `firstOrCreate`s one link row per customer. LIC-CP-001 reshapes
  `licenses` to reference `customers` directly and retires the bridge.
- `licenses.status` enum (pending/active/suspended/expired/revoked) is missing the SRS §39 states
  `payment_due` / `grace_period` / `reactivated` / `completed` — LIC-CP-001 extends it.
- Vendor API `/api/v1/license/*` is **unsigned** and has no replay protection / rate limiting —
  APISEC-001..003 address this; `/api/vendor/v1` is the SRS-correct prefix.
- Browser-pane sandbox quirks — see WORK-INSTRUCTIONS §21.
- AUDIT-002: only aggregate models carry `Auditable`. Child/repeater/image rows (`ProductFeature`,
  `ServiceFeature`, `*Image`, `PortfolioHighlight/Result/Technology`, `PortfolioSetting`,
  `LicenseActivation`) are intentionally unaudited — they're delete-and-recreate synced inside the
  parent's edit, so the parent's `updated` row is the meaningful record. `License` excludes
  `last_verified_at` (written on every Vendor-API verify). Auth events (login/logout/reset) and the
  license state-machine transitions are still to be audited by their own later tasks. New audited
  models must be added to `AuditLogController::ENTITY_TYPES` to appear in the viewer's filter.

## Tests

**130 passed / 577 assertions** — `php artisan test` (PHPUnit 11, SQLite `:memory:`, all threads).
PROD-001 adds `Unit/ProductTest` (4) + `Feature/Foundation/ControlPlaneProductTest` (5); PROD-002
adds `Feature/Admin/ProductAdminTest` (6); FEAT-001 adds `Unit/FeatureTest` (8) +
`Feature/Foundation/FeatureCatalogTest` (3); FEAT-002 adds `Unit/FeatureDependencyResolverTest` (6) +
`Feature/Admin/FeatureAdminTest` (10).
`pint` clean on changed files (pre-existing `AdminUserSeeder.php` style nits untouched).
From here: focused tests every task; Vendor-API contract tests on every API task; phase regression
at each gate.

## Last Completed

**FEAT-002** — Feature admin CRUD + dependency resolver + config-schema validator (this session).
**FEAT-001** / **PROD-002** / **PROD-001** / **AUDIT-002** / **CUST-003** — earlier, parallel threads.

## Next Recommended Task

**FEAT-003** — seed the Ecommerce feature catalog (15 features from SRS §7). Then SVC-001..003
(service catalog — owes a fold-in decision vs. the marketing `services` table), and the Phase 1 gate.
