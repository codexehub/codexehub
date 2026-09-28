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

## Phase 6 — Vendor Client & Sync

- 2026-09-28 — **INST-002** — `POST /installations/{register,verify,heartbeat}` +
  `GET /installations/{id}` on `/api/vendor/v1`. New `App\Services\Installation\InstallationBinder`
  (reuses `VendorLicenseResolver` for license lookup — WORK-INSTRUCTIONS §8). `register()`
  creates/updates the `installations` row and (re)binds `licenses.installation_id`; a first-time
  registration is ACTIVE, a domain change on an existing installation is accepted but flips status to
  `PENDING` (SRS §38 "requires authorization/revalidation") until `/verify` confirms it back to
  `ACTIVE`. `/heartbeat` deliberately never changes status, even from `PENDING`, so a liveness ping
  alone can't complete a revalidation. Two new conflict guards (a license already bound elsewhere; an
  installation identity already bound to a different customer) → new business error
  `INSTALLATION_ALREADY_BOUND` (409); a new `INSTALLATION_SUSPENDED` (403) blocks verify/heartbeat for
  a suspended installation. `GET /installations/{id}` is self-lookup only (`{id}` must equal the
  caller's own envelope `installation_id`) with a plain string route param, not implicit route-model
  -binding, so a 404 stays in the standard error envelope. **Recorded decision:** real
  per-installation signing credentials (assumed by earlier tasks' comments) are deliberately deferred —
  a materially bigger, separable security change than this task's actual scope; the shared
  `VENDOR_API_SIGNING_SECRET` is unchanged. `doc/backend/API-CONTRACT.md` gained a full "Installations"
  section. +30 tests (`Unit/Installation/InstallationBinderTest` 12,
  `Feature/Api/Vendor/Installation/*` 18; suite 291). No migration. Verified end-to-end against a live
  `artisan serve` + the real MySQL dev DB with genuinely signed requests and real RSA-signed responses.
- 2026-09-28 — **INST-001** — SRS §57 `installations` table (schema + model only; `INST-002` builds
  the register/verify/heartbeat endpoints). Migration `2026_09_09_000009` creates `installations`
  (`customer_id`/`product_id` real FKs onto `customers`/`products`; `installation_id` unique business
  key; `domain`, `environment`, `vendor_client_version`, `software_version`, `status`,
  `last_heartbeat_at`/`last_verified_at`/`last_deployed_at`). SRS's `package_id` reuses the pre-SRS
  `licensed_app_packages` table as `licensed_app_package_id` (same stand-in `licenses` already uses;
  `customer_packages` doesn't exist yet — Phase 2). "Supersede `license_activations` additively"
  decision: `license_activations` stays untouched; the migration backfills its rows into `installations`
  (verified via `tinker` on the MySQL dev DB, 2 real rows → 2 correctly-populated rows). New
  `App\Models\Installation` (`Auditable`, `HasFactory`) — `STATUS_*`/`ENVIRONMENT_*` consts,
  auto-generated collision-checked `installation_id`, relations, scopes, route key = `installation_id`.
  Registered in `AuditLogController::ENTITY_TYPES`. `licenses.installation_id` deliberately left
  unbound (that's `INST-002`'s job). +12 tests (`Unit/InstallationTest` 9,
  `Feature/Foundation/InstallationCatalogTest` 3; suite 261). No new dependency.
- 2026-09-28 — **LIC-CP-002** — Signed `POST /licenses/{verify,renew,status}` on `/api/vendor/v1`.
  New `App\Services\License\VendorLicenseResolver` (shared domain service) — `findByKey()` (+ the
  legacy ACTIVE→EXPIRED staleness safety net, same narrow scope), `businessErrorFor()`/
  `businessMessageFor()` (`suspended`→`LICENSE_SUSPENDED`, `revoked`→`LICENSE_REVOKED`,
  `expired`→`LICENSE_EXPIRED`, `pending`→`PAYMENT_DUE` — no dedicated SRS code for "not yet
  activated", matches the legacy controller's framing), `toSignedPayload()` (SRS §61 shape,
  deliberately excluding `software_version` [`INST-002`] and package `features` [`SYNC-001`]),
  `renewIfPaymentRecorded()` (renew-only: PAYMENT_DUE/GRACE_PERIOD → ACTIVE when `next_payment_due`
  already looks future, ahead of LIC-001's unbuilt scheduled job; never itself records a payment).
  Three thin controllers (`Verify`/`Status`/`Renew`LicenseController) share one `LicenseKeyRequest`.
  New `vendor-api.licenses.{verify,renew,status}` routes + `config/vendor_api.php` rate limits
  (verify/status 30/min, renew 10/min). `GET /licenses/{id}` deliberately not built — outside this
  task's checklist scope. `doc/backend/API-CONTRACT.md` gained a full "Licenses" section. +24 tests
  (`Feature/Api/Vendor/License/*`; suite 249). No migration.
- 2026-09-28 — **APISEC-003** — Response signing + key management/rotation. New
  `App\Support\VendorApi\VendorApiResponseSigner` — RSA (`openssl_sign`/`openssl_verify`,
  SHA-256) over the same canonical form `VendorApiSigner` already builds for requests, one shared
  canonicalization routine both directions. `signature = "{key_id}:" + base64(raw signature)` — the
  prefix enables rotation via `config('vendor_api.response_signing')`'s `active_key_id` + `keys` map
  (additive: add a new key, flip `active_key_id`, old entries stay for verification). New
  `php artisan vendor-api:generate-signing-key` generates an RSA-2048 keypair into
  `storage/keys/vendor-api/{key-id}-{private,public}.pem` (gitignored). `VendorApiResponse::success()`
  now signs every response for free; `error()` untouched. Recorded gap: public-key distribution to
  the Vendor Client is out-of-band/file-based (no SRS fetch endpoint exists yet — revisit with
  `INST-002`/`VC-001`). Tests use a committed test-only fixture keypair
  (`tests/fixtures/vendor-api/`), not runtime `openssl_pkey_new()` — that call was found to depend on
  the host's OpenSSL config discovery in a way a test suite shouldn't. +10 tests
  (`Unit/Support/VendorApi/VendorApiResponseSignerTest` 6,
  `Feature/Api/Vendor/VendorApiResponseSigningTest` 4; suite 225). No migration.
- 2026-09-28 — **LIC-CP-001** — Extended `licenses` to the SRS §59 shape + the full SRS §39 9-state
  lifecycle. Migration `2026_09_09_000008` adds `installation_id` (plain string, no `installations`
  table yet), `issued_at`/`starts_at`, `grace_period_days`, `next_payment_due`, `ownership_status`,
  and widens `status` from a 5-value MySQL enum to a plain indexed string (Laravel 11+ `->change()`,
  no doctrine/dbal). New `App\Services\License\LicenseLifecycle` transition-graph guard (permissive
  of the existing manual admin overrides, rejects nonsense moves). Admin suspend/reactivate/revoke
  route through it; new `PATCH .../licenses/{license}/status` + a "Move to Status" control reach
  PAYMENT_DUE/GRACE_PERIOD/COMPLETED. Legacy `LicenseApiController` now gates on
  `License::isOperational()` (SRS §45/§46 — PAYMENT_DUE/GRACE_PERIOD stay usable) instead of a
  hardcoded `active` check, and echoes the real status. +54 tests (suite 215). No seeder.
- 2026-09-28 — **APISEC-002** — Request signing + verification, timestamp/replay-window validation,
  request-id dedupe, per-route rate limiting. New `App\Support\VendorApi\VendorApiSigner`
  (HMAC-SHA256 over the request body minus `signature`, canonicalized via recursive `ksort` +
  `json_encode`). Two middleware on every `routes/vendor-api.php` route, ahead of the
  controller/FormRequest: `ThrottleVendorApiRequest` (rate limit, first) then
  `VerifyVendorApiSignature` (signature → timestamp freshness → request-id dedupe, in that order).
  New `config/vendor_api.php` (`signing_secret` — one shared `VENDOR_API_SIGNING_SECRET` today, no
  per-installation credential until `INST-002`; `replay_window_seconds` 300;
  `request_id_dedupe_ttl_seconds` 600; `rate_limits` map, `default` 60/60s). 3 new
  `VendorApiErrorCode` cases (`REQUEST_EXPIRED`/`DUPLICATE_REQUEST`/`RATE_LIMIT_EXCEEDED` — 401/401/429).
  The security middleware deliberately doesn't duplicate `HasVendorEnvelope`'s shape validation — a
  request missing a field it needs passes through untouched so the existing 422 `VALIDATION_ERROR`
  still fires. `doc/backend/API-CONTRACT.md` updated with the exact signing algorithm for the `[VC]`
  side to mirror. +15 tests (`Unit/Support/VendorApi/VendorApiSignerTest` 6,
  `Feature/Api/Vendor/VendorApiSecurityTest` 9; suite 161). No migration.
- 2026-09-28 — **APISEC-001** — Vendor API skeleton `/api/vendor/v1`. New `routes/vendor-api.php`
  (required from `routes/api.php` under `vendor/v1`; legacy `/api/v1/license/*` untouched).
  `App\Support\VendorApi\VendorApiErrorCode` (backed enum — SRS §72 business codes
  `LICENSE_SUSPENDED`/`LICENSE_EXPIRED`/`PAYMENT_DUE`/`LICENSE_REVOKED` vs technical
  `API_TIMEOUT`/`NETWORK_ERROR`/`INVALID_RESPONSE`/`SERVER_ERROR`/`SIGNATURE_ERROR` +
  `VALIDATION_ERROR`/`NOT_FOUND`, `classification()`). `App\Support\VendorApi\VendorApiResponse`
  (SRS §71 success/error envelopes; `signature` always `null` until `APISEC-003`, omitted on error).
  `App\Http\Requests\VendorApi\VendorApiRequest` + `HasVendorEnvelope` trait validate the SRS §70
  envelope (shape only, no crypto yet) and render the standard error envelope on failure instead of
  Laravel's default. `App\Http\Controllers\Api\Vendor\VendorApiController` base + diagnostic
  `POST /ping`. New `doc/backend/API-CONTRACT.md` (envelope, response shapes, error-code table,
  endpoint status table, SRS §63 security checklist). Pulled ahead of Phase 1's remaining tasks on
  user instruction — see `PROJECT-STATUS.md` Known Issues (deployment descoped, licensing in scope).
  +16 tests (`Unit/Support/VendorApi/VendorApiErrorCodeTest` 11,
  `Feature/Api/Vendor/VendorApiEnvelopeTest` 4, `Feature/Api/Vendor/PingEndpointTest` 1; suite 146).
  No migration.

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
