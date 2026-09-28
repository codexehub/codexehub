# PROJECT STATUS — Codexehub Control Plane & Licensing Platform

> Whole-platform checklist & %: `doc/backend/CHECKLIST.md`.
> Workflow: `doc/backend/WORK-INSTRUCTIONS.md`. History: `CLAUDE.md` (repo root).
> Last task detail: `doc/backend/LAST-TASK-SUMMARY.md`.

## Current Release

**v1.2** (the SRS target). Build-out organized into SRS §144 Phases 1–11 + a GA gate.
Whole-platform progress: **20 / 100** (see CHECKLIST — Phase 5, Phase 7, Phase 8, and
`GIT-001`/`GIT-002` were removed from the checklist entirely on 2026-09-28, deployment is manual, see
Known Issues / Decisions below). A pre-SRS licensing prototype exists and partially covers slices of
FEAT / PKG / PAY / INST / LIC-CP.

## Current Phase

**Phase 1 — Foundation** (7 / 12) — started early on user instruction. Cross-cutting foundation is
**6 / 6 — closed**. **Phase 6 — Vendor Client & Sync** (7 / 20) also has work landed (`APISEC-001`,
`APISEC-002`, `APISEC-003`, `LIC-CP-001`, `LIC-CP-002`, `INST-001`, `INST-002`, pulled forward on
explicit user instruction to build out the licensing side of Phase 6/9 next — see Known Issues).
Working tree carries AUDIT-002 + CUST-003 + PROD-001 + PROD-002 + FEAT-001 + FEAT-002 +
APISEC-001/002/003 + LIC-CP-001/002 + INST-001 from concurrent sessions, plus this session's
`INST-002` — review before commit.

## Current Task

**SYNC-001** — `GET /packages/{id}` + `/packages/{id}/features` + `POST /packages/sync` +
`/packages/acknowledge`, signed/versioned/idempotent package-sync payload (SRS §19, §67, §117) — the
next unblocked task in checklist order for Phase 6, now that both `installations` (`INST-001`) and its
register/verify/heartbeat endpoints (`INST-002`) exist. (Phase 1's `FEAT-003`/`SVC-001` remain
available too — see Known Issues.)

Prior: **INST-002 COMPLETE** — `POST /installations/register` + `/verify` + `GET /installations/{id}` +
`POST /installations/heartbeat` (SRS §38, §65). New `App\Services\Installation\InstallationBinder`
domain service reuses `VendorLicenseResolver` for license lookup/operational-state checks rather than
duplicating it (WORK-INSTRUCTIONS §8). The envelope's own `installation_id` *is* the SRS §38
installation identity. `register()`: resolves+validates the license, rejects a conflicting binding
either direction (license already bound elsewhere / installation already bound to a different
customer) with a new `INSTALLATION_ALREADY_BOUND` (409) error, then creates-or-updates `installations`
and (re)binds `licenses.installation_id` — closing the gap `INST-001` deliberately left open. A
first-time registration is immediately ACTIVE; **domain change requires revalidation (recorded
decision):** re-registering with a *different* domain persists the new domain but flips status to
PENDING (`INSTALLATION_PENDING_REVALIDATION`, `data.revalidation_required: true`) instead of silently
trusting it — `/verify` is what confirms it and flips PENDING→ACTIVE. `/heartbeat` deliberately never
touches status (even when PENDING) — a liveness ping must not be able to complete a revalidation only
`/verify` should. New business error `INSTALLATION_SUSPENDED` (403). `GET /installations/{id}` is
self-lookup only (`{id}` must equal the caller's own envelope `installation_id`, else `NOT_FOUND` —
SRS §80/§81 customer isolation), implemented with a plain string route param rather than implicit
route-model-binding (a missing model would 404 outside the standard error envelope). **Recorded
decision:** real per-installation signing credentials (which several earlier tasks' comments assumed
"INST-002 would add") were considered and explicitly deferred — a materially bigger security change
than this task's actual scope; the shared `VENDOR_API_SIGNING_SECRET` is unchanged. `doc/backend/
API-CONTRACT.md` gained a full "Installations" section; Domain binding / Installation binding flip to
✅ in the security checklist, Installation credentials stays ⬜ with the deferral recorded. +30 tests
(`Unit/Installation/InstallationBinderTest` 12, `Feature/Api/Vendor/Installation/*` 18; suite **291**).
No migration. Verified end-to-end against a live `artisan serve` + the real MySQL dev DB with genuinely
signed HMAC requests and real RSA-signed responses (register → domain-change → PENDING → verify →
ACTIVE → heartbeat → GET); all test data reverted afterwards.

Prior: **INST-001 COMPLETE** — SRS §57 `installations` table (schema + model only — `INST-002` builds
the endpoints). Migration `2026_09_09_000009` creates `installations` (`customer_id`/`product_id`
real FKs onto `customers`/`products`; `installation_id` unique business key; `domain`, `environment`
[default `production`], `vendor_client_version`, `software_version`, `status` [default `pending`],
`last_heartbeat_at`/`last_verified_at`/`last_deployed_at`). SRS's `package_id` (→ not-yet-existing
`customer_packages`, Phase 2) reuses the pre-SRS `licensed_app_packages` table as
`licensed_app_package_id`, the same stand-in `licenses.licensed_app_package_id` already uses.
**Recorded decision:** "supersede `license_activations` additively" means `license_activations` stays
untouched (still used by the legacy `/api/v1/license/activate` endpoint); the migration backfills its
existing rows into `installations` via the license's customer/product bridges — verified via `tinker`
on the MySQL dev DB (2 real activations → 2 correctly-populated installations). `App\Models\Installation`
(`Auditable`, `HasFactory`) gains `STATUS_*`/`ENVIRONMENT_*` consts, an auto-generated collision-checked
`installation_id`, `customer()`/`product()`/`package()` relations, scopes, and
`getRouteKeyName() → installation_id` ahead of `INST-002`'s `GET /installations/{id}`. Registered in
`AuditLogController::ENTITY_TYPES`. `licenses.installation_id` deliberately left unbound — that's
`INST-002`'s register/verify logic. +12 tests (`Unit/InstallationTest` 9,
`Feature/Foundation/InstallationCatalogTest` 3; suite 261). No new dependency. Migrated on the MySQL
dev DB.

Prior: **LIC-CP-002 COMPLETE** — `POST /licenses/verify` + `/renew` + `/status`, signed license
response (SRS §61, §66) on the now fully-secured `/api/vendor/v1` prefix. New
`App\Services\License\VendorLicenseResolver` (shared domain service) — `findByKey()` (+ the same
ACTIVE→EXPIRED staleness safety net the legacy endpoint has), `businessErrorFor()`/
`businessMessageFor()` (maps a non-operational status to its SRS §72 code — `pending` → `PAYMENT_DUE`
since no dedicated code exists for "not yet activated", matching the legacy controller's identical
framing, a recorded decision), `toSignedPayload()` (the SRS §61 shape, deliberately excluding
`software_version` [needs `INST-002`] and package `features`/entitlements [`SYNC-001`'s job]),
`renewIfPaymentRecorded()` (renew-only: transitions PAYMENT_DUE/GRACE_PERIOD → ACTIVE immediately
when `next_payment_due` already looks future — e.g. an admin advanced it manually — rather than
waiting on LIC-001's still-unbuilt scheduled job; never itself records a payment). Three thin
controllers (`VerifyLicenseController`/`LicenseStatusController`/`RenewLicenseController`) share one
`LicenseKeyRequest`. New `vendor-api.licenses.{verify,renew,status}` routes + rate-limit config
(verify/status 30/min, renew 10/min). `GET /licenses/{id}` deliberately not built — outside this
task's own scoped checklist line. `doc/backend/API-CONTRACT.md` gained a full "Licenses" section.
+24 tests (`Feature/Api/Vendor/License/*`; suite 249). No migration.

Prior: **APISEC-003 COMPLETE** — Response signing + key management/rotation (SRS §61, §63). New
`App\Support\VendorApi\VendorApiResponseSigner` — RSA-SHA256 (`openssl_sign`/`openssl_verify`) over
the same canonical form `VendorApiSigner` already builds for requests (payload minus `signature`,
recursive `ksort` + `json_encode`), one shared canonicalization routine for both directions.
`signature = "{key_id}:" + base64(raw signature)` — the prefix is what makes rotation possible:
`config('vendor_api.response_signing')` has `active_key_id` (signs new responses) + a `keys` map of
every key this server has ever signed with (old entries stay for verification). `VendorApiResponse
::success()` now signs every response for free; `error()` untouched (still no `signature` key, per
the APISEC-001 asymmetry decision). New `php artisan vendor-api:generate-signing-key` generates an
RSA-2048 keypair into `storage/keys/vendor-api/{key-id}-{private,public}.pem` (gitignored — private
keys never committed); run once locally for `v1`. **Recorded gap:** public-key *distribution* to the
Vendor Client is out-of-band/file-based today — no SRS endpoint exists to fetch it (matches the
pre-existing `LicenseTokenService` precedent); revisit with `INST-002`/`VC-001` rather than inventing
one now. Tests use a **committed test-only fixture keypair** (`tests/fixtures/vendor-api/`,
`Tests\Concerns\UsesEphemeralVendorApiSigningKey`), not runtime key generation —
`openssl_pkey_new()` was found to fail on this machine's stock Windows/XAMPP PHP without an explicit
`OPENSSL_CONF` (a one-time local-keygen snag only; `openssl_sign`/`openssl_verify` against an
already-loaded PEM need no such config — confirmed against a live `artisan serve`). +10 tests
(`Unit/Support/VendorApi/VendorApiResponseSignerTest` 6, `Feature/Api/Vendor/VendorApiResponseSigningTest`
4; existing Vendor-API tests updated to assert a real verifiable signature instead of the old `null`
placeholder; suite 225). No migration.

Prior: **LIC-CP-001 COMPLETE** — Extended `licenses` to the SRS §59 shape + the full §39 lifecycle
(SRS §39, §40, §45, §46, §59). Migration `2026_09_09_000008` adds `installation_id` (plain string,
nullable, indexed — `installations` doesn't exist yet, INST-001 can turn it into a real FK later),
`issued_at`/`starts_at`, `grace_period_days`, `next_payment_due` (indexed), `ownership_status`, and
widens `status` from the released 5-value MySQL enum to a plain indexed `string(20)` (Laravel 11+
`->change()`, no doctrine/dbal needed) covering all 9 SRS §39 states. `license_customer_id` /
`licensed_app_id` / `licensed_app_package_id` are untouched — SRS §59's `customer_id`/`package_id`
FKs need `customer_packages` (Phase 2), not added here. `App\Models\License` gains
`STATUS_*`/`STATUSES`, `OPERATIONAL_STATUSES` (ACTIVE/PAYMENT_DUE/GRACE_PERIOD/REACTIVATED/COMPLETED
— SRS §45/§46: only SUSPENDED/REVOKED/EXPIRED lock the site), new-column casts, a `creating` hook
defaulting `issued_at`/`starts_at`/`grace_period_days`, `isOperational()`, a redefined `isUsable()`.
New `App\Services\License\LicenseLifecycle` — a transition-graph guard permissive of the existing
manual admin overrides (ACTIVE→SUSPENDED direct, SUSPENDED→ACTIVE direct) but rejecting nonsense
moves (reactivating REVOKED, leaving a terminal state other than to REVOKED, unknown statuses).
`Admin\Licensing\LicenseController`'s suspend/reactivate/revoke now route through it; new
`PATCH admin/licensing/licenses/{license}/status` reaches PAYMENT_DUE/GRACE_PERIOD/COMPLETED (no
dedicated button yet — LIC-001's automatic job drives these later) via a "Move to Status" control
on the show page. Status badges updated (licenses index/show, the CUST-003 customer License tab).
The legacy unsigned `LicenseApiController::resolve()` (still live — LIC-CP-002 migrates its
functionality) now gates on `isOperational()` instead of a hardcoded `active` check and echoes the
real status instead of a hardcoded `'active'`. +54 tests (`Unit/LicenseTest` 11,
`Unit/License/LicenseLifecycleTest` 19, `Feature/Admin/LicenseLifecycleAdminTest` 8,
`Feature/Api/LicenseApiLifecycleTest` 16; suite 215 at landing time — see Known Issues for an
unrelated concurrent-thread flake). Migrated on the MySQL dev DB.

Prior: **APISEC-002 COMPLETE** — Request signing + verification, timestamp/replay-window validation,
request-id dedupe, per-route rate limits (SRS §63, §70, §120). Two middleware on every
`routes/vendor-api.php` route, ahead of the controller/FormRequest: `ThrottleVendorApiRequest` (rate
limit, first) then `VerifyVendorApiSignature` (signature → timestamp → dedupe order — signature
first so a forged request can't pollute the dedupe cache or leak timing info). New
`App\Support\VendorApi\VendorApiSigner` — HMAC-SHA256 over the request body minus `signature`,
canonicalized by recursive `ksort` + `json_encode` (unescaped slashes/unicode); the exact algorithm
is spelled out in `API-CONTRACT.md` for the `[VC]` side to mirror. Signing secret is one shared
`VENDOR_API_SIGNING_SECRET` value today — no per-installation credential exists yet (`INST-002`
adds one; the lookup swaps without changing the algorithm). New `config/vendor_api.php`
(`signing_secret`, `replay_window_seconds` 300, `request_id_dedupe_ttl_seconds` 600, `rate_limits`
map keyed by route name, `default` 60/60s). 3 new `VendorApiErrorCode` cases (`REQUEST_EXPIRED`,
`DUPLICATE_REQUEST`, `RATE_LIMIT_EXCEEDED` — 401/401/429). The security middleware deliberately does
not duplicate `HasVendorEnvelope`'s shape validation — a request missing a field it needs passes
through untouched so the normal 422 `VALIDATION_ERROR` still fires downstream. +15 tests
(`Unit/Support/VendorApi/VendorApiSignerTest` 6, `Feature/Api/Vendor/VendorApiSecurityTest` 9; suite
161). No migration. `.env` seeded with a generated dev-only secret; `.env.example` + `phpunit.xml`
updated.

Prior: **APISEC-001 COMPLETE** — Vendor API skeleton `/api/vendor/v1` (SRS §64, §70, §71, §72).
`routes/vendor-api.php` (required from `routes/api.php` under `vendor/v1`; the legacy
`/api/v1/license/*` pair is untouched). `App\Support\VendorApi\VendorApiErrorCode` — backed enum of
the SRS §72 business codes (`LICENSE_SUSPENDED`/`LICENSE_EXPIRED`/`PAYMENT_DUE`/`LICENSE_REVOKED`)
vs technical (`API_TIMEOUT`/`NETWORK_ERROR`/`INVALID_RESPONSE`/`SERVER_ERROR`/`SIGNATURE_ERROR` +
skeleton-only `VALIDATION_ERROR`/`NOT_FOUND`), `classification()`/`isBusiness()`/`isTechnical()`.
`App\Support\VendorApi\VendorApiResponse` — the SRS §71 success envelope
(`success,request_id,code,message,data,signature` — `signature` always `null` until `APISEC-003`)
and error envelope (`success,request_id,code,message,data` — no `signature` key, matching the SRS
example). `App\Http\Requests\VendorApi\VendorApiRequest` (abstract Form Request) +
`Concerns\HasVendorEnvelope` validate the SRS §70 envelope fields (`installation_id`/`request_id`/
`timestamp`/`client_version`/`signature` — presence/shape only) and override `failedValidation()` to
render the standard error envelope instead of Laravel's default. `App\Http\Controllers\Api\Vendor\
VendorApiController` (abstract base — `success()`/`error()` helpers) + a diagnostic `POST /ping`
(`PingController`) proving the skeleton end-to-end — not an SRS endpoint. `doc/backend/API-CONTRACT.md`
created — prefix, envelope, response shapes, error-code table, endpoint status table (every later
task's endpoint listed as ⬜), SRS §63 security checklist with per-item status. +16 tests
(`Unit/Support/VendorApi/VendorApiErrorCodeTest` 11, `Feature/Api/Vendor/VendorApiEnvelopeTest` 4,
`Feature/Api/Vendor/PingEndpointTest` 1; suite 146). No migration. Pulled forward ahead of Phase 1's
remaining `FEAT-003`/`SVC-001` on explicit user instruction to build out the license/entitlement part
of Phase 6 + Phase 9 next (see [[manual-deployment-no-git-automation]] in project memory — deployment
automation stayed descoped, licensing stayed in scope).

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
- [x] APISEC-001 — Vendor API skeleton `/api/vendor/v1` — envelope validation, standard
  success/error response envelope, `VendorApiErrorCode` business/technical classification,
  `doc/backend/API-CONTRACT.md`. +16 tests.
- [x] APISEC-002 — request signing + verification (`VendorApiSigner` HMAC-SHA256, canonical
  ksort+json_encode), timestamp/replay-window validation, request-id dedupe, per-route rate
  limiting — `ThrottleVendorApiRequest` + `VerifyVendorApiSignature` middleware on every
  `routes/vendor-api.php` route. +15 tests.
- [x] LIC-CP-001 — extended `licenses` to the SRS §59 shape + the full §39 9-state lifecycle;
  `App\Services\License\LicenseLifecycle` transition guard; admin suspend/reactivate/revoke +
  status endpoint; legacy `LicenseApiController` now operational-state-aware. +54 tests.
- [x] APISEC-003 — response signing (RSA, `VendorApiResponseSigner`) + key management/rotation
  (`config/vendor_api.php` key map + `vendor-api:generate-signing-key`) — every success response is
  now genuinely signed. +10 tests.
- [x] LIC-CP-002 — signed `POST /licenses/{verify,renew,status}` — `VendorLicenseResolver` domain
  service (lookup + staleness safety net + business-error mapping + SRS §61 payload builder +
  renew-time PAYMENT_DUE/GRACE_PERIOD→ACTIVE auto-close-the-loop). +24 tests.
- [x] INST-001 — SRS §57 `installations` table + `App\Models\Installation` + factory (schema only —
  `INST-002` builds the endpoints). Backfills existing `license_activations` rows; `license_activations`
  itself stays untouched. +12 tests.
- [x] INST-002 — `POST /installations/register` + `/verify` + `GET /installations/{id}` +
  `POST /installations/heartbeat` — `App\Services\Installation\InstallationBinder` (reuses
  `VendorLicenseResolver`); domain-change → PENDING, `/verify` revalidates → ACTIVE; heartbeat never
  touches status; license/installation conflict checks (`INSTALLATION_ALREADY_BOUND`, 409); per-
  installation signing credentials deliberately deferred. +30 tests.

## Pending (next up)

- [ ] SYNC-001 — `GET /packages/{id}` + `/packages/{id}/features` + `POST /packages/sync` +
  `/packages/acknowledge` — signed, versioned, idempotent package-sync payload
- [ ] FEAT-003 — seed the Ecommerce feature catalog (SRS §7) — Phase 1, if resumed instead
- [ ] SVC-001..003 — service catalog table + admin + seed — Phase 1, if resumed instead
- … then the Phase 1 gate / Phase 6 gate (see CHECKLIST)

## Blocked

- **Real mail delivery** — `.env` `MAIL_PASSWORD` is a placeholder. Mail-dependent tasks (offers,
  invoices, reminders, notifications) are buildable + testable via `MAIL_MAILER=log` + `tinker`;
  live delivery needs the user's Gmail App Password.
- **[VC] tasks** (`VC-001..006`, `ENT-001..003`, `LIC-005/006`) — need the customer product repo
  (`ecom-single-vendor`) — the Vendor API contract they build against (`doc/backend/API-CONTRACT.md`)
  now covers a full signing scheme (`APISEC-001/002/003`), the signed license endpoints (`LIC-CP-002`),
  and the installation register/verify/heartbeat/lookup endpoints (`INST-001`/`INST-002`), but
  `SYNC-001` (package/feature sync) should land first so the VC side isn't built against a moving
  target — and per-installation signing credentials are still deferred (see Known Issues), so a real
  Vendor Client would still share the one environment-wide secret today.

## Known Issues / Decisions to record

- **Deployment is manual, not git-automated (2026-09-28).** The developer edits code and uploads it
  to a client's site by hand (FTP / cPanel File Manager / manual `git pull` on the server) — Codexehub
  does not connect to a client's git repo or push code automatically. **Phase 5 (Release Management),
  Phase 7 (Deployment), Phase 8 (cPanel drivers), and `GIT-001`/`GIT-002` (Phase 4) are out of
  scope** — first descoped-but-kept-as-a-record, then **removed from `CHECKLIST.md` entirely
  (2026-09-28, developer's explicit instruction)**; this note is the record of why they existed and
  why they're gone, so a future self-service/automated tier reintroduces them from the SRS rather than
  un-deleting a stub. **License/package sync stays in scope** (Phase 6 Vendor Client & Sync, Phase 9
  License Enforcement) — installation registration, heartbeat, and package/feature entitlement sync
  are about what a customer is licensed for and whether their license is valid, independent of how
  code reaches their server. Whole-platform total: **100** (130 raw SRS − 30 removed).
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
  resolveLicenseCustomer()` `firstOrCreate`s one link row per customer. LIC-CP-001 deliberately left
  `licenses.license_customer_id` as-is (SRS §59's direct `customer_id` FK needs a bigger reshape than
  this task's scope) — a future task can point it straight at `customers` and retire the bridge.
- `licenses.status` (LIC-CP-001, done) is now a plain indexed `string(20)` carrying all 9 SRS §39
  states; `App\Services\License\LicenseLifecycle` guards which transitions are legal. `installation_id`
  is a plain string, not a FK (matching `licenses.licensed_app_package_id`'s own non-FK style), but is
  now genuinely bound: `INST-002`'s `/installations/register` sets it to the real
  `installations.installation_id` it creates/updates, and refuses to rebind a license already pointing
  at a *different* installation (`INSTALLATION_ALREADY_BOUND`). SRS §59's `customer_id`/`package_id`
  FKs still await `customer_packages` (Phase 2) — `licenses` keeps reaching a canonical `Customer` via
  the existing `license_customer_id` → `LicenseCustomer.customer_id` bridge.
- `installations` (INST-001, done) reuses the pre-SRS `licensed_app_packages` table as
  `licensed_app_package_id` for what SRS §57 calls `package_id`, since `customer_packages` (Phase 2)
  doesn't exist yet — the same stand-in `licenses.licensed_app_package_id` already established.
  "Supersede `license_activations` additively" was interpreted as: leave `license_activations`
  untouched (still what the legacy `/api/v1/license/activate` endpoint uses), and one-time backfill
  its existing rows into `installations` in the creating migration so pre-existing real activations
  aren't invisible to the new table — verified via `tinker` against the MySQL dev DB (2 rows → 2 rows).
- **INST-002 (done)** — the register → verify → heartbeat lifecycle: `register()` creates/updates an
  `installations` row and binds `licenses.installation_id`; a **domain change on an existing
  installation is accepted but flips status to `PENDING`** rather than silently trusting the new
  domain (SRS §38 "requires authorization/revalidation") — `/verify` is the confirming step that flips
  `PENDING → ACTIVE`; `/heartbeat` is a pure liveness signal that deliberately never changes `status`,
  even when `PENDING`, so a liveness ping alone can't complete a revalidation. Two new conflict guards:
  a license already bound to a different installation, and an installation identity already bound to a
  different customer's license, both → `INSTALLATION_ALREADY_BOUND` (409) — the schema (`licenses`
  carrying a single `installation_id` value, not a pivot) only really supports one license ↔ one
  installation, unlike the pre-SRS multi-activation `license_activations` model. **Recorded decision:**
  issuing real per-installation signing credentials — which earlier tasks' comments (APISEC-002,
  APISEC-003, the old API-CONTRACT.md security checklist) had assumed "INST-002 would add" — was
  considered and explicitly **deferred**: it's a materially bigger security change (secure generation,
  at-rest encryption per SRS §63, and a chicken-and-egg problem at the very first `/register` call
  before any per-installation secret exists) than this task's actual scope. `VENDOR_API_SIGNING_SECRET`
  stays the one shared secret for every installation; a future task can swap the lookup without
  touching the signing algorithm.
- Vendor API `/api/v1/license/*` is **unsigned** and has no replay protection / rate limiting — it is
  left untouched functionally (still status-aware via `License::isOperational()`, LIC-CP-001) and
  stays live until the Vendor Client (`ecom-single-vendor`) moves onto its signed, secured
  replacement — `/api/vendor/v1/licenses/{verify,renew,status}` — which now exists (`LIC-CP-002`).
  `/api/vendor/v1` (`APISEC-001/002/003`, done) is the SRS-correct prefix — requests must carry a
  valid HMAC signature (one shared `VENDOR_API_SIGNING_SECRET` today — see the INST-002 deferral note
  above), a fresh timestamp, and an unused `request_id`, and are rate-limited per route; every success
  response carries a real RSA signature (`VendorApiResponseSigner`), and the caller's claimed
  `installation_id` is now cross-checked against a real `installations` row once it's registered
  (`INST-002`). Not yet done: public-key *distribution* to the Vendor Client for verifying response
  signatures is out-of-band/file-based today (no SRS endpoint exists for it — recorded gap, see
  `API-CONTRACT.md`'s Response signing section; revisit with `VC-001`).
- `LIC-CP-002`'s signed license response deliberately omits `software_version` (available now on
  `installations.software_version` since `INST-002`, but `VendorLicenseResolver::toSignedPayload()`
  hasn't been wired to read it — a small follow-up, not done here) and package `features`/entitlements
  (that's `SYNC-001`'s job — `GET /packages/{id}/features`, `POST /packages/sync`); it also maps a
  `pending` license to the `PAYMENT_DUE` business code since the SRS §72 catalog has no dedicated "not
  yet activated" code (matches the legacy `LicenseApiController`'s identical framing — a recorded
  decision, not an oversight). `GET /licenses/{id}` (SRS §66) was intentionally not built — outside
  this task's own scoped checklist line; add it if a real caller ever needs a by-id lookup.
- Browser-pane sandbox quirks — see WORK-INSTRUCTIONS §21.
- AUDIT-002: only aggregate models carry `Auditable`. Child/repeater/image rows (`ProductFeature`,
  `ServiceFeature`, `*Image`, `PortfolioHighlight/Result/Technology`, `PortfolioSetting`,
  `LicenseActivation`) are intentionally unaudited — they're delete-and-recreate synced inside the
  parent's edit, so the parent's `updated` row is the meaningful record. `License` excludes
  `last_verified_at` (written on every Vendor-API verify). Auth events (login/logout/reset) and the
  license state-machine transitions are still to be audited by their own later tasks. New audited
  models must be added to `AuditLogController::ENTITY_TYPES` to appear in the viewer's filter.

## Tests

**291 passed / 1026 assertions** — `php artisan test` (PHPUnit 11, SQLite `:memory:`, all threads).
INST-002 adds `Unit/Installation/InstallationBinderTest` (12) +
`Feature/Api/Vendor/Installation/{Register,Verify,Heartbeat,Show}InstallationTest` (18). INST-001 adds
`Unit/InstallationTest` (9) + `Feature/Foundation/InstallationCatalogTest` (3).
LIC-CP-002 adds `Feature/Api/Vendor/License/{VerifyLicenseTest,RenewLicenseTest,LicenseStatusTest}`
(24, incl. data sets). APISEC-003 adds `Unit/Support/VendorApi/VendorApiResponseSignerTest` (6) +
`Feature/Api/Vendor/VendorApiResponseSigningTest` (4); LIC-CP-001 adds `Unit/LicenseTest` (11),
`Unit/License/LicenseLifecycleTest` (19), `Feature/Admin/LicenseLifecycleAdminTest` (8),
`Feature/Api/LicenseApiLifecycleTest` (16). APISEC-002 adds `Unit/Support/VendorApi/VendorApiSignerTest`
(6) + `Feature/Api/Vendor/VendorApiSecurityTest` (9); APISEC-001 adds
`Unit/Support/VendorApi/VendorApiErrorCodeTest` (11) + `Feature/Api/Vendor/VendorApiEnvelopeTest` (4)
+ `Feature/Api/Vendor/PingEndpointTest` (1). PROD-001 adds `Unit/ProductTest` (4) +
`Feature/Foundation/ControlPlaneProductTest` (5); PROD-002 adds `Feature/Admin/ProductAdminTest` (6);
FEAT-001 adds `Unit/FeatureTest` (8) + `Feature/Foundation/FeatureCatalogTest` (3); FEAT-002 adds
`Unit/FeatureDependencyResolverTest` (6) + `Feature/Admin/FeatureAdminTest` (10).
`pint` clean on all changed files.
From here: focused tests every task; Vendor-API contract tests on every API task; phase regression
at each gate.

## Last Completed

**INST-002** — `POST /installations/{register,verify,heartbeat}` + `GET /installations/{id}` on
`/api/vendor/v1`, `InstallationBinder` domain service, domain-change-requires-revalidation flow (this
session).
**INST-001** — SRS §57 `installations` table + `Installation` model/factory, backfilled from
`license_activations` — earlier this session.
**LIC-CP-002** — signed `POST /licenses/{verify,renew,status}` on `/api/vendor/v1` — earlier still.
**APISEC-003** — Vendor API response signing (RSA) + key management/rotation — earlier still.
**LIC-CP-001** — extended `licenses` to the SRS §59 shape + the full §39 9-state lifecycle, plus a
`LicenseLifecycle` transition guard — earlier, parallel thread.
**APISEC-002** / **APISEC-001** / **FEAT-002** / **FEAT-001** / **PROD-002** / **PROD-001** /
**AUDIT-002** / **CUST-003** — earlier still, parallel threads.

## Next Recommended Task

**SYNC-001** — `GET /packages/{id}` + `/packages/{id}/features` + `POST /packages/sync` +
`/packages/acknowledge`, signed/versioned/idempotent package-sync payload (SRS §19, §67, §117) — both
`installations` (`INST-001`) and its register/verify/heartbeat endpoints (`INST-002`) now exist for a
package-sync call to bind against. (`FEAT-003`/`SVC-001..003` + the Phase 1 gate remain available if
Phase 1 is resumed instead of continuing Phase 6.)
