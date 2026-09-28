# Last Task Summary

> Concurrent threads. Sections below, newest first: **INST-002**, **INST-001**, **LIC-CP-002**,
> **APISEC-003**, **LIC-CP-001**, **APISEC-002**, **APISEC-001**, **FEAT-002**, **FEAT-001**,
> **PROD-002**, **PROD-001**, **CUST-003**, **AUDIT-002**, **AUDIT-001**, **CUST-002**. Working tree
> carries all of them — review before commit.
>
> `codexehub-4a` briefly started PROD-001 the other way (evolve `licensed_apps`), backed it out
> cleanly after the user confirmed the `products` fold-in, then did PROD-002 on top of the
> parallel session's PROD-001 — nothing of the abandoned attempt remains.

---

# INST-002 — Installation register/verify/heartbeat/lookup endpoints

## Task

**INST-002** — [CP] `POST /installations/register` + `/verify` + `GET /installations/{id}` +
`POST /installations/heartbeat`; installation binding to customer / product / domain / identity;
domain change requires revalidation (Phase 6 — Vendor Client & Sync; SRS §38, §65).

## Date

2026-09-28

## Why this task, now

Directly named by the user, and the next unblocked task in checklist order for Phase 6 — the
`installations` table (`INST-001`, prior session) existed with nothing writing to it yet.

## What landed

- **`App\Services\Installation\InstallationBinder`** (new domain service, SRS §130 layout) — all the
  register/verify/heartbeat/lookup business logic, reusing
  `App\Services\License\VendorLicenseResolver` for license lookup + operational-state checks rather
  than duplicating that logic (WORK-INSTRUCTIONS §8):
  - `findByInstallationId()` — plain lookup by the business key.
  - `isLicenseBoundElsewhere(License, installationId)` / `isInstallationBoundToAnotherCustomer
    (Installation, License)` — the two conflict guards `register()`'s controller checks before
    writing anything (see Decisions).
  - `register(License, installationId, domain, environment, vendorClientVersion, softwareVersion)` —
    creates or updates the `installations` row for that identity, binding `customer_id`/`product_id`/
    `licensed_app_package_id` from the license's own bridges (`LicenseCustomer.customer_id`,
    `LicensedApp.product_id`, `licensed_app_package_id` directly), and (re)binds
    `licenses.installation_id` to it. First-time registration → `STATUS_ACTIVE` immediately. An
    existing installation re-registering with a **different** domain → the new domain is persisted but
    status flips to `STATUS_PENDING` instead of staying `ACTIVE` (the SRS §38 "domain change requires
    revalidation" rule); same-domain re-registration is a plain idempotent metadata refresh with no
    status change.
  - `businessErrorFor()`/`businessMessageFor()` — `STATUS_SUSPENDED` → the new `INSTALLATION_SUSPENDED`
    business error; everything else operational.
  - `verify(Installation)` — the actual revalidation step: flips `PENDING → ACTIVE` and always
    refreshes `last_verified_at`; returns whether a revalidation happened (drives the response
    message/code).
  - `heartbeat(Installation, ?softwareVersion)` — refreshes `last_heartbeat_at` (+ `software_version`
    if given) and **deliberately never touches `status`**, even from `PENDING` — see Decisions.
  - `toPayload(Installation)` — the shared response shape (`installation_id`, `status`, `domain`,
    `environment`, `customer` `{id,name}`, `product` `{id,key,title}`, `package` `{id,name}`,
    `vendor_client_version`, `software_version`, `last_heartbeat_at`, `last_verified_at`).
- **Requests** — `App\Http\Requests\VendorApi\Installation\{Register,Verify,Heartbeat,Show}
  InstallationRequest`, each extending `VendorApiRequest` (envelope validation inherited).
  `RegisterInstallationRequest` adds `license_key` (required), `domain` (required),
  `environment` (nullable, `Rule::in(Installation::ENVIRONMENTS)`), `software_version` (nullable) —
  `vendor_client_version` is *not* a separate field, it's read straight from the envelope's own
  `client_version`. `HeartbeatInstallationRequest` adds only `software_version` (nullable).
  `Verify`/`ShowInstallationRequest` add nothing beyond the envelope.
- **Controllers** — `App\Http\Controllers\Api\Vendor\Installation\{Register,Verify,Heartbeat,Show}
  InstallationController`, mirroring the LIC-CP-002 License controllers' explicit multi-step style
  (resolve → check business error → check conflict → act → respond) rather than a single opaque
  service call, so every branch is visible at the controller layer:
  - `RegisterInstallationController` — license lookup (`NOT_FOUND` if unknown) → license operational
    check → `isLicenseBoundElsewhere` → `isInstallationBoundToAnotherCustomer` → `register()` →
    success, with `code` and `message` depending on whether a domain change was detected
    (`INSTALLATION_REGISTERED` vs `INSTALLATION_PENDING_REVALIDATION`) and `data.revalidation_required`
    (bool) always present.
  - `VerifyInstallationController` — lookup (`NOT_FOUND` if never registered) → business-error check →
    `verify()` → success `INSTALLATION_VERIFIED` (message varies on whether it just revalidated).
  - `HeartbeatInstallationController` — same lookup/business-error shape → `heartbeat()` → success
    `INSTALLATION_HEARTBEAT_RECEIVED`.
  - `ShowInstallationController` — **self-lookup only**: the `{installationId}` route segment must
    equal the caller's own envelope `installation_id`, otherwise `NOT_FOUND` *before* even querying the
    database (SRS §80/§81 customer isolation — never confirms or denies that a different installation
    exists). Takes a plain `string $installationId` route parameter, not implicit Eloquent
    route-model-binding — see Decisions for why.
- **Routes** — `routes/vendor-api.php` gains a `Route::prefix('installations')->name
  ('vendor-api.installations.')` group (`register`/`verify`/`heartbeat` POST, `{installationId}` GET),
  inside the same `['vendor-api.throttle', 'vendor-api.verify']` middleware group every other route
  uses.
- **`config/vendor_api.php`** — four new `rate_limits` entries: `register` 20/min, `verify` 30/min,
  `heartbeat` 120/min (the highest of any Vendor API route — a liveness ping is expected to be the most
  frequent call in the whole API), `show` 30/min.
- **`App\Support\VendorApi\VendorApiErrorCode`** — two new cases: `InstallationSuspended`
  (`INSTALLATION_SUSPENDED`) and `InstallationAlreadyBound` (`INSTALLATION_ALREADY_BOUND`), both
  classified `business` (not in the SRS §72 catalog by name, added the same way `VALIDATION_ERROR`/
  `NOT_FOUND` were at APISEC-001). `VendorApiResponse::defaultStatusFor()` maps the former to 403
  (alongside the license business codes) and the latter to 409 (a genuine conflict, not an access
  denial).
- **`doc/backend/API-CONTRACT.md`** — Status/Endpoints tables flip to ✅ for installations; a full new
  "Installations" section (mirroring the existing "Licenses" section's structure) documenting all four
  endpoints, the shared response shape, rate limits, and the two deferred items below; error-codes
  table gains the two new codes; security checklist's "Domain binding"/"Installation binding" rows
  flip to ✅, "Installation credentials" stays ⬜ with the deferral explicitly recorded (previously it
  said "⬜ INST-002" as if this task would close it).

## Decisions (recorded)

- **Per-installation signing credentials are deliberately deferred, not built here.** Several earlier
  tasks' comments (`APISEC-002`'s docblocks, the original `API-CONTRACT.md` security checklist) assumed
  "INST-002 adds a per-installation signing secret." On reflection this is a materially bigger security
  change than this task's actual checklist wording ("installation binding to customer/product/domain/
  identity") — it needs secure secret generation, at-rest encryption (SRS §63 "Secret encryption"), and
  has a genuine chicken-and-egg problem: a Vendor Client has no per-installation secret until *after*
  its first successful `/register` call, so that very first call still has to be verified with
  something else (the shared secret). Building real installation binding (customer/product/domain
  identity, conflict detection) using the existing shared `VENDOR_API_SIGNING_SECRET` is a complete,
  useful, independently-shippable slice; per-installation credentials are cleanly separable future work
  that swaps the secret *lookup* in `VerifyVendorApiSignature` without touching the signing algorithm.
  Recorded in three places (`API-CONTRACT.md`, `PROJECT-STATUS.md`, here) so it's picked up
  deliberately by a future task rather than assumed done.
- **Domain change → PENDING, not a hard rejection, and not a silent accept.** SRS §38 says domain
  changes "must require authorization/revalidation" but doesn't specify a mechanism, and no admin
  approval-queue UI exists yet (out of scope for this task). The chosen design: `/register` *does*
  accept and persist the new domain (so the Vendor Client isn't stuck retrying forever), but flips the
  installation to `PENDING` rather than leaving it `ACTIVE` — meaning it's `isOperational()`-adjacent
  but not confirmed-trustworthy. `/verify` is the explicit second step that confirms the change and
  flips it back to `ACTIVE`. This gives a real two-call "authorize then confirm" mechanic without
  inventing an admin UI, and is symmetric with how a first-time registration needs no such step (there
  is nothing to revalidate against yet).
- **Heartbeat never revalidates, even from PENDING — only verify does.** If heartbeat could silently
  flip `PENDING → ACTIVE`, the entire point of the domain-revalidation step would be defeated (a
  misbehaving or compromised client could "confirm" its own domain change just by pinging liveness).
  Keeping `verify()` and `heartbeat()` as two distinctly-scoped methods on `InstallationBinder` makes
  this separation structural, not just a comment.
- **Two conflict checks, both mapped to `INSTALLATION_ALREADY_BOUND`.** The SRS §59 `licenses` table
  has a single `installation_id` value (not a pivot), so — unlike the pre-SRS multi-activation
  `license_activations` model — this schema really only supports **one license ↔ one installation**.
  `isLicenseBoundElsewhere()` catches "this license is already registered to a different install";
  `isInstallationBoundToAnotherCustomer()` catches the reverse (this exact `installation_id` already
  resolved to a different customer, e.g. a random 8-char id collision or a misbehaving client reusing
  an id) — both close a real SRS §80/§81 customer-isolation gap, verified directly by a dedicated test
  for each direction.
- **`GET /installations/{id}` is a self-lookup only, not a general admin/lookup endpoint.** `{id}` must
  equal the caller's own claimed `installation_id`; any mismatch is `NOT_FOUND`, checked *before* any
  database query, so the endpoint never confirms or denies that a different installation exists.
- **No implicit Eloquent route-model-binding for the GET route**, despite `Installation::
  getRouteKeyName()` having been set up in `INST-001` specifically anticipating this route. Verified
  directly against Laravel's `ImplicitRouteBinding::resolveForRoute()` source: a missing model always
  throws `ModelNotFoundException`, which renders via Laravel's default exception handling (a bare 404),
  not the standard Vendor API error envelope — regardless of whether the controller parameter is
  nullable. A plain `string $installationId` route parameter plus an explicit `findByInstallationId()`
  lookup (the same pattern `VendorLicenseResolver::findByKey()` already established) keeps every
  response, including a 404, in the one standard envelope shape.
- **Controllers stay explicit/multi-step rather than one opaque service call**, mirroring the
  LIC-CP-002 License controllers' style — `resolve → check business error → check conflict → act →
  respond`, each step visible in the controller, `InstallationBinder` staying a composable set of small
  methods rather than a single `handleRegister()` that hides the branching.

## Tests

`php artisan test` → **291 passed (1026 assertions)** (all threads). +30 for INST-002:

- `Unit/Installation/InstallationBinderTest` (12) — first-time registration creates an ACTIVE row +
  binds the license; same-domain re-registration doesn't change status (and keeps unset fields);
  different-domain re-registration flips to PENDING; `isLicenseBoundElsewhere()` / `isInstallationBoundToAnotherCustomer()`
  detect both conflict directions; `businessErrorFor()` only fires for SUSPENDED; `verify()` activates
  a PENDING row (and is a no-op status-wise on an already-ACTIVE one, always refreshing
  `last_verified_at`); `heartbeat()` updates `last_heartbeat_at`/optional `software_version` without
  ever touching status (including while PENDING); `toPayload()` shape.
- `Feature/Api/Vendor/Installation/RegisterInstallationTest` (8) — first-time registration 200 +
  persists + binds the license; a changed domain returns `INSTALLATION_PENDING_REVALIDATION` +
  `PENDING`; unknown license key → 404 `NOT_FOUND`; a non-operational license → its business error, no
  row created; a license already bound elsewhere → 409 `INSTALLATION_ALREADY_BOUND`; the same
  installation id claimed by a different customer's license → 409; `domain` required → 422; an
  unsigned/forged request → 401 `SIGNATURE_ERROR` (proves the shared `vendor-api.verify` middleware
  applies here exactly as it does to every other route).
- `Feature/Api/Vendor/Installation/VerifyInstallationTest` (4) — PENDING → ACTIVE + `last_verified_at`
  set; ACTIVE stays ACTIVE and still refreshes; SUSPENDED → 403 `INSTALLATION_SUSPENDED`; unregistered
  → 404 `NOT_FOUND`.
- `Feature/Api/Vendor/Installation/HeartbeatInstallationTest` (4) — updates `last_heartbeat_at` +
  reported `software_version`; a PENDING installation stays PENDING after a heartbeat (the key
  behavioral guarantee this task adds); SUSPENDED → 403; unregistered → 404.
- `Feature/Api/Vendor/Installation/ShowInstallationTest` (3) — self-lookup returns the record;
  requesting a different id than the envelope's own claim → 404 `NOT_FOUND` (checked before any DB
  query — the target id in this test doesn't even exist, proving the mismatch short-circuit); an
  unregistered id → 404.

`pint --dirty` → clean, no changes needed.

## Migration / upgrade

No migration — every column already existed from `INST-001`. No new Composer dependency.

**Verified end-to-end against a live `artisan serve` process + the real MySQL dev DB** (not just the
SQLite test suite), using genuinely HMAC-signed requests built via `tinker` + `VendorApiSigner::sign()`
and curl:
1. Created a real `License`/`LicensedApp`/`LicensedAppPackage`/`LicenseCustomer` in the dev DB.
2. `POST /installations/register` (first time) → `200 INSTALLATION_REGISTERED`, correct
   `customer_id`(4)/`licensed_app_package_id`(4) resolved from the license's bridges, `license.
   installation_id` bound — confirmed directly against the DB via `tinker`.
3. A second `curl` call accidentally reused the same `request_id` (a shell pipe fallback quirk) and was
   correctly rejected `401 DUPLICATE_REQUEST` — an unplanned but useful live confirmation the
   replay-protection middleware (APISEC-002) applies to these new routes exactly as it should.
4. `POST /installations/register` again with a **different** domain → `200
   INSTALLATION_PENDING_REVALIDATION`, `status: pending`, `revalidation_required: true`, a real RSA
   `signature` on the response.
5. `POST /installations/verify` → `200 INSTALLATION_VERIFIED`, `status: active`,
   "Installation revalidated and is now active."
6. `POST /installations/heartbeat` (with `software_version: 3.4.0`) → `200
   INSTALLATION_HEARTBEAT_RECEIVED`, `last_heartbeat_at` set, `software_version` updated, `status`
   still `active` (unchanged by design).
7. `GET /installations/INS-TINKTEST1?...` (envelope as query params) → `200 INSTALLATION_STATUS`,
   matching the state left by step 6.
8. All test data (the license, licensed app/package, license customer, canonical customer, and the two
   `installations` rows created during this run) removed afterwards — confirmed the dev DB's
   pre-existing `installations` count (2, from INST-001's `license_activations` backfill) and `licenses`
   count (1, the pre-existing seeded license) were unchanged by the cleanup.

## Out of scope

- Real per-installation signing credentials (see Decisions) — a future task.
- An admin approval/authorization UI for a pending domain-change revalidation — none exists; `/verify`
  is the only mechanism that confirms one today. A future task could add an admin-side gate in front of
  it if manual approval is ever required beyond the Vendor Client calling `/verify` itself.
- `VendorLicenseResolver::toSignedPayload()` reading `software_version` from the now-real
  `installations.software_version` (it's available since this task, but wiring it into the license
  payload is LIC-CP-002's file to touch, not this task's — recorded in `PROJECT-STATUS.md`).
- `SYNC-001` — package/feature entitlement sync, the next real consumer of a confirmed `ACTIVE`
  installation.

## Next

**SYNC-001** — `GET /packages/{id}` + `/packages/{id}/features` + `POST /packages/sync` +
`/packages/acknowledge`, now that both the `installations` table and its register/verify/heartbeat
lifecycle exist for a sync call to bind against.

---

# INST-001 — `installations` table (SRS §57)

## Task

**INST-001** — [CP] `installations` table (customer_id, product_id, package_id, installation_id,
domain, environment, vendor_client_version, software_version, status, last_heartbeat/verified/
deployed_at) — supersede `license_activations` additively (Phase 6 — Vendor Client & Sync; SRS §57).

## Date

2026-09-28

## Why this task, now

Directly named by the user, and also the next unblocked task in checklist order for Phase 6 —
`INST-002`'s register/verify/heartbeat endpoints need somewhere to write to. Per WORK-INSTRUCTIONS §3
("smallest complete change"), scoped to schema + model + factory only, mirroring how `CUST-001` /
`PROD-001` / `FEAT-001` each landed their table before a later task added CRUD/endpoints on top.

## What landed

- **Migration `2026_09_09_000009_create_installations_table`** — creates `installations`:
  - `customer_id` / `product_id` — real nullable FKs (`nullOnDelete`) onto the canonical `customers`
    (CUST-001) / `products` (PROD-001) tables — both already exist, unlike when `licenses` added its
    own `installation_id` (LIC-CP-001), so these could be real FKs from day one.
  - `licensed_app_package_id` — nullable FK onto `licensed_app_packages`. SRS §57 calls this column
    `package_id`, conceptually pointing at the SRS §52 `customer_packages` table — which doesn't exist
    yet (Phase 2, `PKG-001`). Per WORK-INSTRUCTIONS §8 ("search existing code first — reuse, don't
    duplicate"), this reuses the pre-SRS flat `licensed_app_packages` table exactly the way
    `licenses.licensed_app_package_id` already does, rather than either inventing a FK to a
    not-yet-existing table or leaving a meaningless unconstrained integer column. `PKG-001` can add a
    real `customer_package_id` column alongside this one later without touching it.
  - `installation_id` (string 64, **unique**) — the SRS §38 business identity (e.g. `INS-100001`),
    auto-generated by the model, not the internal auto-increment `id`.
  - `domain`, `environment` (default `production`), `vendor_client_version`, `software_version`,
    `status` (default `pending`), `last_heartbeat_at` / `last_verified_at` / `last_deployed_at`
    (all nullable timestamps) — exactly the remaining SRS §57 columns.
  - Indexes on `domain` and `status` (SRS §136's indexing list).
- **Backfill from `license_activations`**, run in the same migration's `up()`: for each existing
  `license_activations` row (domain + fingerprint per license), resolves `customer_id` via the
  license's `license_customer_id` → `LicenseCustomer.customer_id` bridge and `product_id` via
  `licensed_app_id` → `LicensedApp.product_id`, carries `licensed_app_package_id` straight across, and
  inserts one `installations` row (`status = active`, `last_heartbeat_at`/`last_verified_at` from the
  activation/license) if one for that domain+package doesn't already exist. Verified directly against
  the MySQL dev DB via `tinker`: 2 real pre-existing `license_activations` rows produced 2
  correctly-populated `installations` rows with the right `customer_id` (1), `product_id` (6), and
  `licensed_app_package_id` (2).
- **`App\Models\Installation`** (`Auditable`, `HasFactory`):
  - `STATUS_PENDING` / `_ACTIVE` / `_INACTIVE` / `_SUSPENDED` + `STATUSES` — no SRS-specified enum for
    this column, so a 4-state set was chosen for consistency with `Customer::STATUSES`'s shape rather
    than invented from nothing; `INST-002` can rename/extend these once real registration/heartbeat
    semantics are defined without needing new columns.
  - `ENVIRONMENT_PRODUCTION` / `_STAGING` / `_DEVELOPMENT` + `ENVIRONMENTS`.
  - casts: the three timestamp columns → `datetime`.
  - `creating` hook: auto-generates `installation_id` when blank (`generateIdentifier()`, collision
    -checked the same way `License::generateKey()` is), and defaults `status`/`environment` when blank
    (covers direct `Installation::create()` calls that bypass the column defaults, e.g. from a factory
    state that explicitly nulls them out).
  - `getRouteKeyName(): 'installation_id'` — SRS §65's `GET /installations/{id}` addresses the business
    key, not the internal id; set up now the same way `Feature`/`Product` established their route key
    ahead of their own CRUD/endpoint tasks.
  - `customer()` / `product()` belongsTo; `package()` belongsTo `LicensedAppPackage` (the stand-in
    documented above).
  - `status()` / `active()` / `environment()` query scopes.
- **`database/factories/InstallationFactory`** — active installation by default (real
  `customer`/`product` factories, `environment production`, `status active`, all three timestamps
  `now()`); states `pending()` (nulls the timestamps too), `inactive()`, `suspended()`,
  `environment($env)`.
- **`Admin\AuditLogController::ENTITY_TYPES`** — `'Installation' => Installation::class` added (import
  too), so installation changes are filterable in the AUDIT-002 audit viewer — no admin CRUD screen
  exists yet (that arrives with a later task if the SRS calls for one; `INST-002`'s scope is API
  endpoints, not an admin UI).

## Decisions (recorded)

- **"Supersede `license_activations` additively" = leave it alone + one-time backfill, not a rename or
  a live dual-write.** `license_activations` (domain+fingerprint per license) is a different shape for
  a different purpose (device-activation binding for the flat pre-SRS licensing model) than
  `installations` (customer/product/package/environment/version/heartbeat lifecycle tracking for the
  control plane). Renaming or repurposing it would break the still-live legacy unsigned
  `/api/v1/license/activate` endpoint, which is explicitly out of scope to touch here. Instead:
  `license_activations` stays exactly as released; the new `installations` table is what `INST-002`'s
  Vendor API endpoints will actually create/update going forward; and any *existing* activation data is
  backfilled once so it isn't invisible to admins looking at the new table.
- **`licensed_app_package_id`, not a bare unconstrained `package_id` integer.** SRS §57's `package_id`
  conceptually targets `customer_packages`, which doesn't exist. Leaving an FK-less integer column
  would be write-only and untestable until Phase 2; reusing the existing `licensed_app_packages` table
  (exactly as `licenses` already does) keeps the column meaningful today and gives `PKG-001` a working
  precedent to follow (add a real `customer_package_id` alongside, don't touch this one).
- **`licenses.installation_id` is deliberately left untouched.** It's a plain nullable string since
  `LIC-CP-001`, and a full-codebase grep confirms nothing writes to it anywhere today. Binding a
  license to a real `Installation` row (by matching business keys, or converting the column into a
  proper FK) is registration/verification business logic that belongs to `INST-002`, not to a
  schema-only table-creation task — doing it here would be scope creep past WORK-INSTRUCTIONS §18.
- **Installation `status`/`environment` enums are this task's own choice, not SRS-specified.** The SRS
  §57 table just says `status` and `environment` with no enumerated values. Chose a small, consistent
  set now (mirroring `Customer::STATUSES`) rather than leaving the column enum-less, on the basis that
  `INST-002` can freely rename/extend these constants later without any migration, since the column
  itself is a plain string.
- **No admin CRUD screen.** This task's checklist line is schema-only; a management UI for
  installations (if the SRS calls for one beyond the Vendor API) is out of scope here — `Installation`
  was still registered in the audit viewer's `ENTITY_TYPES` since that costs nothing and every other
  aggregate model does the same as soon as it's `Auditable`.

## Tests

`php artisan test` → **261 passed (919 assertions)** (all threads). +12 for INST-001:

- `Unit/InstallationTest` (9) — `installation_id` auto-generates when blank (correct `INS-XXXXXXXX`
  shape) and is kept when explicit; `installation_id` is unique (duplicate → `QueryException`);
  `status`/`environment` default to `pending`/`production` when explicitly nulled; the three
  timestamp columns cast to `Carbon`; `customer()`/`product()` relations resolve to the right rows;
  `package()` resolves to a real `LicensedAppPackage`; `status()`/`active()`/`environment()` scopes
  filter correctly; `getRouteKeyName()` returns `installation_id`.
- `Feature/Foundation/InstallationCatalogTest` (3) — the table carries every SRS §57 column;
  `Installation` is `Auditable` (a create writes an `audit_logs` row); the model is filterable in the
  audit viewer (`?entity_type=Installation`).

`pint --dirty` → auto-fixed import ordering in the new `tests/Unit/InstallationTest.php` (fully
qualified `\Illuminate\Support\Carbon` references replaced with a proper `use` import); clean after.

## Migration / upgrade

`php artisan migrate` — `2026_09_09_000009` applied on the MySQL dev DB. Verified via `tinker`:
`Schema::getColumnListing('installations')` lists all 15 columns; the 2 pre-existing
`license_activations` rows backfilled into 2 correctly-populated `installations` rows (`customer_id`
1, `product_id` 6, `licensed_app_package_id` 2, real domains, real timestamps). No seeder, no new
Composer dependency.

## Out of scope

- `INST-002` — `POST /installations/register` + `/verify` + `GET /installations/{id}` +
  `POST /installations/heartbeat`; binding an installation to a customer/product/domain/identity;
  binding `licenses.installation_id` to a real `Installation` row; domain-change revalidation.
- Admin CRUD / management screen for installations (if ever needed beyond the Vendor API).
- `PKG-001` adding a real `customer_package_id` alongside the temporary `licensed_app_package_id`.

## Next

**INST-002** — the register/verify/heartbeat endpoints that actually create and update `installations`
rows, now that the table exists for them to operate on.

---

# LIC-CP-002 — Signed license verify/renew/status

## Task

**LIC-CP-002** — [CP] `POST /licenses/verify` + `/renew` + `/status` — signed license response
(Phase 6 — Vendor Client & Sync; SRS §61, §66).

## Date

2026-09-28

## Why now

Direct continuation on explicit user instruction, next in checklist order for Phase 6 now that both
prerequisites exist: `licenses` carries the full SRS §39 lifecycle (`LIC-CP-001`) and every success
response is genuinely signed (`APISEC-002`/`APISEC-003`). This is the first *real* endpoint group
under `/api/vendor/v1` — everything before it (`/ping`) was diagnostic-only.

## What landed

- **`App\Services\License\VendorLicenseResolver`** (new domain service, shared by all three
  controllers):
  - `findByKey(string): ?License` — plus the same ACTIVE→EXPIRED staleness safety net the legacy
    `LicenseApiController` already has (a status may simply have gone stale since it was last
    checked), narrowly scoped to that one transition exactly like that precedent — a fuller
    automatic sweep across every state is `LIC-001` (Phase 9, not built).
  - `businessErrorFor(License): ?VendorApiErrorCode` — `null` when operational (SRS §45/§46 —
    `payment_due`/`grace_period` are *not* errors); otherwise maps `suspended`→`LICENSE_SUSPENDED`,
    `revoked`→`LICENSE_REVOKED`, `expired`→`LICENSE_EXPIRED`, `pending`→`PAYMENT_DUE`.
  - `businessMessageFor(License): string` — the matching human message per status.
  - `toSignedPayload(License, string $requestInstallationId): array` — the SRS §61 shape:
    `license_key`, `installation_id`, `license_status`, `package` (`{id, name}`), `expires_at`,
    `grace_until` (`next_payment_due` + `grace_period_days`), `ownership_status`, `last_verified_at`.
  - `renewIfPaymentRecorded(License): bool` — renew-only. If a `PAYMENT_DUE`/`GRACE_PERIOD` license's
    `next_payment_due` is already in the future (payment recorded elsewhere — e.g. an admin advanced
    it via LIC-CP-001's "Move to Status" control), transitions it to `ACTIVE` via `LicenseLifecycle`
    immediately, instead of making the Vendor Client wait for `LIC-001`'s scheduled job (Phase 9, not
    built). Never itself records a payment or moves `next_payment_due`.
- **`App\Http\Requests\VendorApi\License\LicenseKeyRequest`** — shared by all three; the only
  endpoint-specific field is `license_key` (string, required, max 64).
- **Three thin controllers** under `App\Http\Controllers\Api\Vendor\License\`:
  - `VerifyLicenseController` — code `LICENSE_VERIFIED`. Updates `licenses.last_verified_at` on
    every call — exactly why `License::auditExclude()` already excludes that column (added at
    AUDIT-002, anticipating this endpoint).
  - `LicenseStatusController` — code `LICENSE_STATUS`. **Pure read** — does not touch
    `last_verified_at` (that's the deliberate difference from `/verify`: an on-demand status peek,
    e.g. for a "My Package" screen refresh, shouldn't count as a verification heartbeat). The
    staleness safety net still applies (a data-correctness fix, not a heartbeat side effect).
  - `RenewLicenseController` — code `LICENSE_RENEWAL_CHECKED`, `data.renewed` (bool). A
    non-operational license (e.g. suspended) is still rejected before any renewal is attempted.
- **Routes** — `vendor-api.licenses.{verify,renew,status}` under a `licenses` prefix, inside the same
  `vendor-api.throttle` + `vendor-api.verify` middleware group as every other route.
- **`config/vendor_api.php`** — 3 new `rate_limits` entries (SRS §120 "License Verification"
  category): `verify`/`status` 30/min, `renew` 10/min.
- **`doc/backend/API-CONTRACT.md`** — Endpoints table row flips to ✅; new "Licenses" section
  documenting the payload field-by-field (with sources), all three endpoints' distinct semantics, the
  business-error mapping table, and the scope decisions below; updated the legacy-pair relationship
  note.

## Decisions (recorded)

- **`pending` → `PAYMENT_DUE`.** The SRS §72 catalog has no code for "license created but never
  activated." The pre-SRS `LicenseApiController` already frames this exact case as "awaiting payment
  confirmation" — `PAYMENT_DUE` is the closest honest fit, not a mismatch papered over.
- **`software_version` and package `features`/entitlements are deliberately excluded** from the
  signed payload, even though the SRS §61 example implies a fuller shape. `software_version` needs a
  real installation record (`INST-002`, not built); `features`/entitlements are explicitly
  `SYNC-001`'s job (`GET /packages/{id}/features`, `POST /packages/sync`) — folding them into the
  license response here would duplicate a responsibility that task already owns.
- **`package` is the pre-SRS flat `licensed_app_packages` row**, not the SRS §52 `customer_packages`
  shape (which needs Phase 2, not built) — `{id, name}` only, no `package_version` (doesn't exist
  anywhere yet pre-Phase-2).
- **`/renew`'s real behavior, scoped honestly.** Rather than fabricate a billing action that doesn't
  exist yet (no invoicing, Phase 3; no automatic license-state job, Phase 9), `/renew` does exactly
  one real, defensible thing: close the gap between "payment already recorded some other way" and the
  stored status catching up. This is genuinely useful today (an admin can flip a license via
  LIC-CP-001's status control and the Vendor Client's next `/renew` call reflects it immediately) and
  doesn't pretend to be more than it is.
- **`GET /licenses/{id}` not built.** SRS §66 lists it, but this task's own checklist line (and
  `API-CONTRACT.md`'s endpoint table, written at APISEC-001) scoped LIC-CP-002 to verify/renew/status
  only. Nothing needs a by-id lookup yet — the Vendor Client always has its own `license_key`.
- **`installation_id` is read-only here, not a binding action.** If `licenses.installation_id` is
  unset, the response just echoes whatever the caller's envelope claims — it is **not** written back
  to the license row. Doing so would be a partial, inconsistent implementation of `INST-002`'s actual
  job (real installation registration + domain/identity binding).

## Tests

`php artisan test` → **249 passed (885 assertions)** (all threads). +24 for LIC-CP-002
(`tests/Feature/Api/Vendor/License/`, new `Tests\Concerns\MakesTestLicenses` helper mirroring the
existing `LicenseApiLifecycleTest` license-builder pattern):
- `VerifyLicenseTest` (11 incl. data sets) — every operational status (active/payment_due/
  grace_period/reactivated/completed) verifies successfully and updates `last_verified_at`; every
  non-operational status (pending/suspended/revoked) returns its correct business code and does
  *not* update `last_verified_at`; unknown key → 404; an active-but-past-expiry license flips to
  expired and is rejected (staleness safety net); an unsigned/forged request is still rejected by the
  existing APISEC-002 security middleware (route-level wiring check).
- `RenewLicenseTest` (8) — payment_due/grace_period with a future `next_payment_due` renews to
  active; payment_due with no/past `next_payment_due` does not renew; an already-active license
  reports no renewal needed; a suspended license is rejected and never renewed even with a future
  `next_payment_due`; unknown key → 404; renewal updates `last_verified_at`.
- `LicenseStatusTest` (5) — reports status for an operational license; does **not** update
  `last_verified_at`; a suspended license still returns its business error; unknown key → 404; the
  staleness safety net still applies on a pure read.

`pint` clean on all changed/new files.

## Migration / upgrade

No migration, no seeder, no new dependency — every column already existed from `LIC-CP-001`.
`php artisan route:list --path=api/vendor/v1/licenses` → 3 routes.

## Out of scope

- `GET /licenses/{id}` — not part of this task's scope (see Decisions).
- Package/feature sync (`SYNC-001`), installation registration/binding (`INST-002`), real payment
  processing and the automatic license-state job (Phase 3 / `LIC-001`, Phase 9) — all their own tasks.
- Retiring the legacy `/api/v1/license/{activate,verify}` pair — happens once the Vendor Client
  (`ecom-single-vendor`) has moved onto the new signed endpoints, a `[VC]` decision.

## Next

**INST-001** — `installations` table (SRS §57), superseding `license_activations` additively — the
next unblocked task in checklist order for Phase 6.

---

# APISEC-003 — Response signing & key management/rotation

## Task

**APISEC-003** — [CP] Response signing + key management/rotation (Phase 6 — Vendor Client & Sync;
SRS §61, §63).

## Date

2026-09-28

## Why now

The last of the three Vendor API security tasks (`APISEC-001` skeleton → `APISEC-002` request
signing/replay/dedupe/rate-limit → this one), on the same user instruction to finish the licensing
side of Phase 6 before other work. Closes the "response signing" and "key rotation" rows in
`API-CONTRACT.md`'s Status table that every later real endpoint (`LIC-CP-002`, `SYNC-001`, `UPD-001`)
was waiting on.

## What landed

- **`App\Support\VendorApi\VendorApiResponseSigner`** — asymmetric (RSA) response signing, the
  deliberate counterpart to `VendorApiSigner`'s symmetric (HMAC) request signing: the server signs
  with a private key, a Vendor Client verifies with the matching public key, so nothing the client
  holds could ever be used to forge a response.
  - `sign(array $payload): string` — canonicalizes `$payload` minus `signature` using
    `VendorApiSigner::canonicalize()` (the *same* recursive-`ksort` + `json_encode` routine request
    signing already uses — one canonicalization algorithm, both directions), signs it with
    `openssl_sign(..., OPENSSL_ALGO_SHA256)` under the active key, returns
    `"{key_id}:" . base64(raw_signature)`.
  - `verify(array $payload, string $signature): bool` — splits the `key_id` prefix, looks up that
    key's public key from config, `openssl_verify`s. Fails closed (`false`) on any malformed
    signature string, unknown key id, or missing key file — never throws for a bad *input*.
  - `activeKeyId()` / private key-loading helpers throw a clear `RuntimeException` for a genuinely
    broken *deployment* (e.g. the active key's private key file is missing) — that's a startup-time
    misconfiguration, not a per-request condition, so it's allowed to fail loudly rather than be
    silently swallowed into a fake success response.
- **`config/vendor_api.php`** gains `response_signing`:
  - `active_key_id` (env `VENDOR_API_RESPONSE_SIGNING_KEY_ID`, default `v1`) — which key *signs* new
    responses.
  - `keys` — a map of every key id ever used, each `{private_key_path, public_key_path}`. Rotation is
    additive: add a new entry, then flip `active_key_id` — old entries stay so anything still relying
    on an older key can still be verified against it. Documented step-by-step in `API-CONTRACT.md`.
- **`App\Console\Commands\GenerateVendorApiSigningKey`** —
  `php artisan vendor-api:generate-signing-key {--key-id=v1} {--force}`. Generates an RSA-2048
  keypair via `openssl_pkey_new()`, writes to
  `storage/keys/vendor-api/{key-id}-{private,public}.pem` (`0600` on the private key), refuses to
  overwrite existing files without `--force`. Run once locally for `v1`; its output PEM files are
  gitignored (`.gitignore` gains `/storage/keys/vendor-api`) — private keys are never committed.
  Hit (and documented) a local snag: `openssl_pkey_new()` failed with
  `error:80000003:system library::No such process` on this machine's stock Windows/XAMPP PHP, whose
  default `openssl.cnf` path (`C:\Program Files\Common Files\SSL\openssl.cnf`) doesn't exist — fixed
  by setting `OPENSSL_CONF` to XAMPP's own `php\extras\openssl\openssl.cnf` for that one command
  invocation. Confirmed this is **key-generation-only** — `openssl_sign`/`openssl_verify` against an
  already-loaded PEM (i.e. every real request at runtime) needed no such workaround, verified against
  a live `artisan serve` process signing a real HTTP response correctly with zero special environment.
- **`App\Support\VendorApi\VendorApiResponse::success()`** now calls the signer and puts a real
  signature in every success response — no change needed in any controller (`PingController` and
  every future endpoint get this for free). `error()` is untouched — still no `signature` key at all,
  per the APISEC-001 asymmetry decision (nothing sensitive to protect in an error body).
- **`doc/backend/API-CONTRACT.md`** — Status table's last two ⬜ rows flip to ✅; new "Response
  signing & key rotation" section spelling out the exact algorithm + the rotation procedure
  step-by-step; the success-response example now shows a real signature instead of `null`; security
  checklist's "Signed responses" / "Key rotation" rows flip to ✅.
- **`.env`** gets a commented `VENDOR_API_RESPONSE_SIGNING_KEY_ID=v1` (defaults already point at the
  generated key, no override needed); `.env.example` documents the var + the generation command.

## Decisions (recorded)

- **RSA over HMAC for responses, deliberately asymmetric from request signing.** Request signing
  (`APISEC-002`) uses one shared HMAC secret both sides hold — fine there, since only the *server*
  needs to trust the *client's* claim. Response signing needs the opposite trust direction: the
  client must be able to verify without holding anything that could forge a response, which only an
  asymmetric scheme provides. Reusing the existing `firebase/php-jwt` dependency (already used by
  `LicenseTokenService`) was considered but rejected — wrapping the response in a full JWT would
  duplicate the payload inside the JWT's own claims, which is architecturally inconsistent with how
  request signing already works (a detached signature over the visible content, not a
  self-describing token). Raw `openssl_sign`/`openssl_verify` keeps the same "detached signature over
  canonical content" model in both directions, sharing one canonicalization routine.
- **`"{key_id}:base64(sig)"` format, not a bare base64 string.** The whole point of "key management /
  rotation" as its own checklist item is that more than one key can be valid over time — a bare
  signature gives a verifier no way to know *which* public key to check it against once rotation
  happens. The prefix is the minimal addition that makes rotation actually work.
- **Public-key distribution is an open, recorded gap, not solved here.** No SRS §65-§69 endpoint
  exists for "fetch the current public key," and `INST-002`/`VC-001` (which would define how a
  Vendor Client is provisioned in the first place) haven't landed. Building a speculative new public
  endpoint now would be scope creep beyond this task's actual ask ("signing + key management"), and
  the pre-existing `LicenseTokenService` RS256 setup already has the identical gap (no key-fetch
  endpoint) as precedent for leaving it out-of-band/file-based for now. Recorded in three places
  (`API-CONTRACT.md`, `PROJECT-STATUS.md`, here) so `INST-002`/`VC-001` picks it up deliberately
  rather than by accident.
- **Fail loud on a broken deployment, fail closed on a bad input.** `sign()` throws if the active
  key's private key file is missing/unreadable (an ops misconfiguration affecting *every* request,
  not a per-request condition — same philosophy `VerifyVendorApiSignature` used in APISEC-002 for a
  missing request-signing secret, just surfaced as an exception here rather than a `ServerError`
  response, since the signer is a plain support class with no access to the request/response cycle
  to build one). `verify()` never throws — every failure mode (malformed string, unknown key,
  tampered payload) just returns `false`, since a verifier (tests here, a Vendor Client later)
  should be able to treat "is this signature valid" as a pure boolean question.
- **Test keys are a committed fixture, not runtime-generated.** Originally tried generating a fresh
  ephemeral keypair per test class via `openssl_pkey_new()` in a trait — hit the exact same
  `OPENSSL_CONF` snag the generator command did, and a full-suite run (`php artisan test`, no
  workaround) failed 24 tests on it. A test suite must not depend on the host's OpenSSL config
  discovery to pass. Switched to a small, clearly-labelled, genuinely-non-secret test-only fixture
  keypair committed at `tests/fixtures/vendor-api/{test,other}-{private,public}.pem` (+ a README.md
  explaining what they're for and how to regenerate them) — `Tests\Concerns\UsesEphemeralVendorApiSigningKey`
  just points config at them. Faster too (no per-class RSA keygen) and fully portable.

## Tests

`php artisan test` → **225 passed (808 assertions)** (all threads, confirmed clean with **no**
`OPENSSL_CONF` workaround needed). +10 for APISEC-003:
- `Unit/Support/VendorApi/VendorApiResponseSignerTest` (6) — sign/verify round-trip; the signature
  string is prefixed with the active key id; verify fails when the payload is tampered with, for an
  unknown key id, for a malformed signature string, and against a genuinely different key's public
  key (a second fixture keypair, not the same key mislabelled).
- `Feature/Api/Vendor/VendorApiResponseSigningTest` (4) — a live `/ping` response's `signature`
  actually verifies against its own body; two different requests get two different signatures (not a
  constant); a tampered copy of a real response body fails verification; an error response still
  carries no `signature` key at all.
- `PingEndpointTest` / `VendorApiEnvelopeTest` / `VendorApiSecurityTest` updated: each now configures
  the fixture signing key in `setUp()`, and `PingEndpointTest` asserts a real verifiable signature
  instead of the old `assertNull($response->json('signature'))`.

`pint` clean on all changed/new files.

## Migration / upgrade

No migration, no seeder, no new Composer dependency (`openssl_*` functions are PHP core). New
generated artifacts: `storage/keys/vendor-api/v1-{private,public}.pem` (gitignored, local-only —
anyone else running this repo needs to run `php artisan vendor-api:generate-signing-key` themselves
before the Vendor API can serve a success response). `.gitignore` gains `/storage/keys/vendor-api`.

## Out of scope

- Public-key *distribution* to a Vendor Client (no fetch endpoint exists) — recorded gap, revisit
  with `INST-002`/`VC-001`.
- Every real endpoint (`installations/*`, `licenses/*`, `packages/*`, `updates/*`) — their own tasks.
- Domain/installation binding, per-installation request-signing credentials — `INST-002`.

## Next

**LIC-CP-002** — `POST /licenses/verify` + `/renew` + `/status` on the now fully-secured
`/api/vendor/v1` prefix, now that `licenses` carries the full lifecycle (`LIC-CP-001`) and every
response is genuinely signed (`APISEC-003`). Then `INST-001/002`.

---

# LIC-CP-001 — Extend `licenses` to the SRS §59 shape + the full §39 lifecycle

## Task

**LIC-CP-001** — [CP] Extend `licenses` to the SRS §59 shape (+ `installation_id`, issued/starts/
expires_at, grace_period_days, next_payment_due, ownership_status) + the full 9-state SRS §39
lifecycle (pending → active → payment_due → grace_period → suspended → reactivated → completed →
revoked → expired) (Phase 6 — Vendor Client & Sync; SRS §39, §40, §45, §46, §59).

## Date

2026-09-28

## Why this task, now

The user pointed directly at this checklist line to work next. It also happens to be next in
checklist order for Phase 6 once `APISEC-001`/`APISEC-002` land the request-security layer — the
license lifecycle schema was the next concrete building block toward `LIC-CP-002` (signed
verify/renew/status endpoints) and `INST-002` (which will turn `installation_id` into a real FK).

## What landed

- **Migration `2026_09_09_000008_extend_licenses_table_for_lifecycle`** — additive
  (WORK-INSTRUCTIONS §16): `license_customer_id` / `licensed_app_id` / `licensed_app_package_id` are
  untouched. Adds:
  - `installation_id` (string 64, nullable, indexed) — SRS §59 asks for this, but `installations`
    (Phase 6 — `INST-001`) doesn't exist yet, so it's stored as a plain business-key string for now,
    not a FK. `INST-001` can turn it into one additively later.
  - `issued_at` / `starts_at` (timestamp, nullable).
  - `grace_period_days` (unsigned smallint, nullable).
  - `next_payment_due` (date, nullable, indexed).
  - `ownership_status` (string 30, nullable).
  - Widens `status` from the released 5-value MySQL `enum` to a plain indexed `string(20)` via
    Laravel 11+'s native column `->change()` (no doctrine/dbal dependency needed — confirmed not
    installed in this project) so the full 9-state SRS §39 set fits.
  - SRS §59's `customer_id` / `package_id` FKs (onto `customers` / `customer_packages`) are **not**
    added — `customer_packages` doesn't exist yet (Phase 2 — `PKG-001`), and `licenses` already
    reaches a canonical `Customer` via the CUST-002 `license_customer_id` → `LicenseCustomer.customer_id`
    bridge. Recorded as a gap for a later task once `customer_packages` exists.
- **`App\Models\License`** —
  - `STATUS_PENDING` / `_ACTIVE` / `_PAYMENT_DUE` / `_GRACE_PERIOD` / `_SUSPENDED` / `_REACTIVATED` /
    `_COMPLETED` / `_REVOKED` / `_EXPIRED` + `STATUSES` (all 9, SRS §39 order).
  - `OPERATIONAL_STATUSES` = ACTIVE / PAYMENT_DUE / GRACE_PERIOD / REACTIVATED / COMPLETED — per SRS
    §45/§46 only SUSPENDED / REVOKED / EXPIRED lock the customer site; PAYMENT_DUE / GRACE_PERIOD are
    intentionally still usable (that's the entire point of a grace period).
  - Casts added for `issued_at` / `starts_at` (datetime), `next_payment_due` (date),
    `grace_period_days` (integer).
  - `creating` hook now also defaults `issued_at`/`starts_at` to `now()` and `grace_period_days` from
    `config('licensing.default_grace_hours')` (existing config, converted hours → days) when left
    blank — mirrors the auto-derive convention already used for `Product::key` / `Feature::key`.
  - `isOperational(): bool` (new) + `isUsable(): bool` (redefined in terms of it — was dead code,
    unused anywhere in the app, so redefining carried no behavior-change risk).
- **`App\Services\License\LicenseLifecycle`** (new domain service, SRS §130 layout) — a transition
  graph guard: `canTransition($from, $to)`, `transitions($from): list<string>`,
  `transition(License $license, string $to, array $attributes = [])` (throws
  `InvalidArgumentException` on an illegal move, otherwise `$license->update()`s and returns it).
  Deliberately **permissive** of the manual admin overrides the pre-SRS UI already relied on
  (ACTIVE→SUSPENDED direct, SUSPENDED→ACTIVE direct) — it only rejects transitions that make no sense
  at all: reactivating a REVOKED license, leaving a terminal state other than COMPLETED→REVOKED
  (an admin correcting a mistake), a same-state no-op, or an unknown status string. Building the
  *automatic* state-machine job (payment-due detection, grace-period countdown, auto-suspend) that
  will mostly drive this graph is explicitly **LIC-001** (Phase 9) — this task only adds the guard +
  the ability to move manually.
- **`Admin\Licensing\LicenseController`** — constructor now injects `LicenseLifecycle`.
  `suspend()` / `reactivate()` / `revoke()` keep their existing routes/behavior (including
  `reactivate()`'s pre-existing future-expiry guard) but now go through
  `$this->lifecycle->transition()`, flashing a validation error instead of silently no-op'ing on an
  illegal call (e.g. revoking an already-revoked license). New `updateStatus()` →
  `PATCH admin/licensing/licenses/{license}/status` (body: `status` ∈ `License::STATUSES`) reaches
  the states with no dedicated button — PAYMENT_DUE / GRACE_PERIOD / COMPLETED — via a generic "Move
  to Status" `<select>` on the show page, populated from `$lifecycle->transitions($license->status)`
  so it can never render an illegal option.
- **Views** — `licensing/licenses/show.blade.php` (badge map covers the 4 new statuses; new stat
  cards for Starts / Grace Period / Next Payment Due; the "Move to Status" form), `licenses/index.blade.php`
  and the CUST-003 `customers/show.blade.php` License tab (badge maps + `ucwords(str_replace('_', '
  ', $status))` labels instead of `ucfirst`, so e.g. `payment_due` renders "Payment Due" not
  "Payment_due").
- **`App\Http\Controllers\Api\LicenseApiController::resolve()`** (the legacy unsigned
  `/api/v1/license/{activate,verify}` pair, still live until `LIC-CP-002` migrates its functionality)
  — the expiry-timeout check and the lockout check now use `License::STATUS_*` constants and
  `isOperational()` instead of a hardcoded `status === 'active'` string comparison, so PAYMENT_DUE /
  GRACE_PERIOD / REACTIVATED / COMPLETED correctly keep verifying successfully per SRS §45/§46 instead
  of being locked out the instant a license leaves literal `active` — this was necessary for the new
  states to be *functionally* meaningful through the one endpoint that's actually live today, not
  just inert schema. The success response now echoes `$license->status` instead of a hardcoded
  `'active'` string.

## Decisions (recorded)

- **Scope is additive, not the full SRS §59 reshape.** The checklist line's parenthetical
  (`installation_id`, issued/starts/expires_at, grace_period_days, next_payment_due,
  ownership_status) plus "full lifecycle states" is what this task adds. `licenses.customer_id` /
  `package_id` pointing directly at `customers` / `customer_packages` is **not** done here —
  `customer_packages` doesn't exist (Phase 2), and reshaping the customer FK is a bigger, separately
  reviewable change than this task's scope. Recorded in Known Issues for a future task.
- **`installation_id` is a plain string, not a FK**, since `installations` doesn't exist yet
  (`INST-001`, Phase 6, still pending). Turning it into a real FK is `INST-001`'s job additively.
- **The admin lifecycle guard is permissive of existing manual overrides**, not a strict mirror of
  the SRS §40 "normal recurring customer" diagram — an admin can still suspend directly from ACTIVE
  or reactivate directly to ACTIVE without visiting PAYMENT_DUE/GRACE_PERIOD/REACTIVATED first,
  because the pre-SRS UI already worked that way and nothing asked for that behavior to change. The
  guard exists to reject *nonsensical* transitions, not to force every manual action through the full
  diagram — that enforcement naturally falls out once `LIC-001`'s automatic job is the one driving
  routine transitions.
- **`COMPLETED` allows one outgoing transition, to `REVOKED`** — an admin correcting a mistake (e.g.
  a license marked ownership-completed in error) shouldn't be permanently stuck; every other terminal
  state (`REVOKED`) has none.
- **The legacy `/api/v1/license/*` endpoint was updated, not left alone**, despite `PROJECT-STATUS.md`
  previously describing it as "left untouched" — leaving its hardcoded `status === 'active'` check in
  place would have meant the four new states (added by this very task) were unreachable through the
  only endpoint that's actually live today, defeating the point of extending the lifecycle. The change
  is narrowly scoped to the status comparison and the echoed status string; nothing else about the
  endpoint (auth, activation-limit logic, token issuance) changed.

## Tests

`php artisan test` → **215 passed (795 assertions)** at landing time (all concurrent threads combined;
re-running showed 1–7 intermittent failures under `Feature/Api/Vendor/*`, traced to a concurrent
`APISEC-003` thread's signing key not being generated yet in this shared dev environment — confirmed
via the exact exception message, unrelated to this task, not fixed here). +54 for LIC-CP-001:

- `Unit/LicenseTest` (11) — SRS §59 columns exist; `STATUSES` covers all 9 SRS §39 states;
  `issued_at`/`starts_at` default to now (and an explicit value is kept); `grace_period_days` defaults
  from `config('licensing.default_grace_hours')` (and an explicit value is kept); `next_payment_due`
  casts to a date; `isOperational()` for all 9 statuses (data provider); `isUsable()` also requires a
  future expiry when one is set, but not when there is none.
- `Unit/License/LicenseLifecycleTest` (19) — 14 valid transitions (data provider) persist; 4 invalid
  transitions (data provider: revoked→active, completed→active, pending→grace_period, same-state
  no-op) throw and don't persist; an unknown status string throws; `REVOKED` has zero outgoing
  transitions, `COMPLETED` has exactly one (`REVOKED`).
- `Feature/Admin/LicenseLifecycleAdminTest` (8) — suspend moves active→suspended; reactivate still
  enforces its pre-existing future-expiry guard; reactivate moves suspended→active with a future
  expiry; revoke on an already-revoked license is rejected with a validation error (previously a
  silent no-op); the new status endpoint reaches PAYMENT_DUE and GRACE_PERIOD; the status endpoint
  rejects an illegal jump (pending→grace_period) and an unknown status string; the show page renders
  the "Move to Status" control with the correct next-state options.
- `Feature/Api/LicenseApiLifecycleTest` (16, incl. data sets) — every operational status (active,
  payment_due, grace_period, reactivated, completed) returns a token via `/api/v1/license/verify` and
  echoes its real status; every locked status (pending, suspended, revoked) is rejected with 403 and
  echoes its status; an active license past its expiry flips to expired and is rejected.

`pint --dirty` → clean (also incidentally cleaned pre-existing style nits in
`LicenseApiController.php` while this task edited it).

## Migration / upgrade

`php artisan migrate` — `2026_09_09_000008` applied on the MySQL dev DB. Verified via `tinker`:
`Schema::getColumnListing('licenses')` shows all new columns; the one pre-existing seeded license
row survived the `status` enum→string conversion with its value intact. No seeder, no new Composer
dependency.

## Out of scope

- `LIC-CP-002` — `POST /licenses/verify` + `/renew` + `/status` on the signed `/api/vendor/v1` prefix
  (the legacy `/api/v1/license/*` pair stays live, now status-aware, until that migration happens).
- `LIC-001` (Phase 9) — the *automatic* license-state job (payment-due detection, grace-period
  countdown, auto-suspend) that drives most of this lifecycle for real in production; today every
  transition beyond the 3 existing admin buttons is manual via the new status endpoint.
- Reshaping `licenses.license_customer_id` / adding a direct `customer_id`/`package_id` onto
  `customers`/`customer_packages` — needs `customer_packages` (Phase 2 — `PKG-001`) first.
- Turning `installation_id` into a real FK onto `installations` — `INST-001`.
- `ownership_status` value validation / an ownership-completion workflow around it — Phase 10
  (`OWN-*`).

## Next

**LIC-CP-002** — `POST /licenses/verify` + `/renew` + `/status` on the signed Vendor API (SRS §61,
§66), now that `licenses` carries the full lifecycle for it to report. `INST-001`/`INST-002` or a
concurrent `APISEC-003` thread may land first — check `PROJECT-STATUS.md` before starting.

---

# APISEC-002 — Request signing, replay protection & rate limiting

## Task

**APISEC-002** — [CP] Request signing + verification, timestamp/replay-window validation, request-id
dedupe, per-endpoint rate limits (Phase 6 — Vendor Client & Sync; SRS §63, §70, §120).

## Date

2026-09-28

## Why now

Direct continuation of `APISEC-001` on explicit user instruction — the skeleton validated envelope
*shape* only; nothing under `/api/vendor/v1` was authenticated or replay-safe. This task is the
first item in `API-CONTRACT.md`'s Status table still marked ⬜ that later endpoint tasks (`INST-002`,
`LIC-CP-002`, `SYNC-001`, `UPD-001`) all depend on trusting.

## What landed

- **`App\Support\VendorApi\VendorApiSigner`** — the signing algorithm, single source of truth:
  `sign(array $payload, string $secret)` / `verify(array $payload, string $signature, string $secret)`.
  Canonicalizes by removing the `signature` key, recursively `ksort`-ing every array (top-level and
  nested), `json_encode`-ing with `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`, then
  `hash_hmac('sha256', ..., $secret)`. Key-order independence (top-level and nested) is unit-tested
  directly since the `[VC]` side must reproduce this exactly.
- **`config/vendor_api.php`** (new):
  - `signing_secret` ← `VENDOR_API_SIGNING_SECRET` — **one shared secret for every installation**,
    since no per-installation credential exists yet (`INST-002`). Documented as temporary in the
    file, `API-CONTRACT.md`, and here.
  - `replay_window_seconds` (default 300) — how far `timestamp` may drift from server time.
  - `request_id_dedupe_ttl_seconds` (default 600, ≥ the replay window by convention) — how long a
    seen `request_id` is remembered per `installation_id`.
  - `rate_limits` — `route_name => {max_attempts, decay_seconds}` map with a `default` (60/60s);
    each later endpoint task should add its own SRS §120 category entry.
- **`App\Http\Middleware\VendorApi\ThrottleVendorApiRequest`** — applied first (cheapest check).
  Keys `Illuminate\Cache\RateLimiter` by `route_name:ip|installation_id` (installation_id not yet
  verified at this point — that's fine, this guards volumetric abuse, not identity). Over limit →
  `RATE_LIMIT_EXCEEDED` (429, `data.retry_after_seconds`).
- **`App\Http\Middleware\VendorApi\VerifyVendorApiSignature`** — applied second, in this internal
  order: (1) if the 4 fields it needs (`installation_id`/`request_id`/`timestamp`/`signature`)
  aren't all present as non-empty strings, **pass through untouched** — no duplicate validation, the
  normal `VendorApiRequest` 422 `VALIDATION_ERROR` still reports the specifics; (2) verify the HMAC
  signature over the full request body → `SIGNATURE_ERROR` (401) on mismatch; (3) parse `timestamp`
  — unparseable → pass through to the FormRequest's `date` rule instead of judging it here; (4)
  freshness — outside `replay_window_seconds` → `REQUEST_EXPIRED` (401); (5) dedupe — `request_id`
  already seen for this `installation_id` → `DUPLICATE_REQUEST` (401), else cache it and proceed.
  Signature is checked **before** freshness/dedupe deliberately — an unsigned/forged request must
  never get to pollute the dedupe cache or learn timing information from later checks.
- **3 new `VendorApiErrorCode` cases** — `REQUEST_EXPIRED`, `DUPLICATE_REQUEST`,
  `RATE_LIMIT_EXCEEDED`, all classify `technical` (the default match arm already covers this — no
  `classification()` change needed). `VendorApiResponse::defaultStatusFor()` maps them to 401/401/429.
- **Middleware registration** — `bootstrap/app.php` gains aliases `vendor-api.throttle` /
  `vendor-api.verify`; `routes/vendor-api.php` wraps its group in
  `Route::middleware(['vendor-api.throttle', 'vendor-api.verify'])` and names `/ping` →
  `vendor-api.ping` (needed as the rate-limit config key).
- **`doc/backend/API-CONTRACT.md`** updated: Status table (4 rows flip ⬜ → ✅), a new "Request
  signing, replay protection & rate limiting" section spelling out the exact canonicalization
  algorithm step-by-step (for the `[VC]` side to mirror byte-for-byte), the 3 new error codes, and
  the SRS §63 security checklist (API authentication / Request IDs / Replay protection / Timestamp
  validation / Rate limiting flip to ✅, each noting what's still temporary).
- **`.env`** gets a generated dev-only `VENDOR_API_SIGNING_SECRET` (gitignored); `.env.example`
  documents the var with a generation command; `phpunit.xml` sets a fixed
  `VENDOR_API_SIGNING_SECRET=testing-vendor-api-secret` so tests are deterministic.

## Decisions (recorded)

- **Shared secret, not per-installation.** `INST-001`/`INST-002` (which would carry a real
  installation credential) haven't landed. Rather than block signing entirely, this uses one
  environment-wide secret — a real security *authentication* mechanism (only someone with the
  secret can produce a valid signature), just not yet a real *identity* mechanism (every
  installation looks the same to the server). `INST-002` swaps the secret lookup to per-installation
  without touching `VendorApiSigner` or the middleware's shape.
- **Middleware, not FormRequest, for the security layer.** Considered doing this inside
  `VendorApiRequest::withValidator()` instead (keeps everything in one file), but rejected it: a
  signature/replay/rate-limit failure needs its own HTTP status (401/429) and error code, which
  doesn't fit Laravel's validator-failure model (always 422) without fighting the framework.
  Middleware also matches `VendorApiController`'s own docblock, written at `APISEC-001`, which
  already named this as the intended design.
- **Minimal, deliberate duplication at the boundary.** The security middleware re-checks presence of
  the 4 fields it needs (not full shape — no length/format rules) before trusting them for crypto
  work. A request that fails *this* minimal check is passed through untouched rather than given its
  own error format, so `VendorApiRequest`'s existing per-field 422 response is still the one and only
  shape for "your request is malformed" — verified directly:
  `test_missing_envelope_fields_return_standard_error_envelope` (unchanged from APISEC-001) still
  passes byte-for-byte.
- **Request-id dedupe is a hard reject, not idempotent replay.** A duplicate `request_id` returns
  `DUPLICATE_REQUEST`; it does not return a cached copy of the original response. This is simplest
  and matches the SRS's framing as a *replay-protection* security control, not a client-retry
  convenience. An endpoint with real business-level idempotency needs (e.g. `SYNC-001` package sync,
  SRS §117) builds that separately at the endpoint layer.
- **Dedupe key includes `installation_id`.** The same `request_id` from two different installations
  is not a collision — each client generates its own ids independently. Verified directly
  (`test_the_same_request_id_from_a_different_installation_is_not_treated_as_a_duplicate`).
- **Rate limiting is keyed by IP + claimed installation_id, before signature verification.** Since
  it runs first (cheapest check, abuse protection), the `installation_id` it sees isn't verified yet
  — acceptable, since this bucket exists to blunt volumetric abuse, not to enforce identity.

## Tests

`php artisan test` → **161 passed (669 assertions)** (all threads). +15 for APISEC-002:
- `Unit/Support/VendorApi/VendorApiSignerTest` (6) — sign/verify round-trip; wrong secret fails;
  tampering with any field invalidates the signature; signature is independent of top-level *and*
  nested key order; the `signature` field itself is excluded from what gets signed.
- `Feature/Api/Vendor/VendorApiSecurityTest` (9) — a correctly signed request is accepted; a request
  with no `signature` field falls through to `VALIDATION_ERROR` (422), not `SIGNATURE_ERROR`; a
  forged signature and a post-signing field tamper both → `SIGNATURE_ERROR` (401); a timestamp
  outside/inside the replay window is rejected/accepted; replaying the same `request_id` →
  `DUPLICATE_REQUEST` (401); the same `request_id` from two installations is not a duplicate;
  exceeding a (test-lowered) rate limit → `RATE_LIMIT_EXCEEDED` (429) with `retry_after_seconds`.
- `VendorApiEnvelopeTest` / `PingEndpointTest` (APISEC-001, unchanged assertions) updated to send
  *real* signed payloads via a new `Tests\Concerns\SignsVendorApiRequests` trait
  (`signedVendorPayload()`), instead of the old placeholder `'sig-placeholder'` string, which would
  now correctly fail signature verification.

`pint` clean on all changed/new files (`app/Support/VendorApi`, `app/Http/Middleware/VendorApi`,
`app/Http/Controllers/Api/Vendor`, `app/Http/Requests/VendorApi`, `routes/vendor-api.php`,
`bootstrap/app.php`, `config/vendor_api.php`, `tests/**/Vendor*`, `tests/Concerns`).

## Migration / upgrade

No migration, no seeder, no new Composer dependency (uses Laravel's built-in
`Illuminate\Cache\RateLimiter` + the cache facade — already available). `.env`/`.env.example`/
`phpunit.xml` gain `VENDOR_API_SIGNING_SECRET`.

## Out of scope

- Response signing + key management/rotation (`signature` on a success response stays `null`) —
  **APISEC-003**.
- Per-installation signing credentials, domain binding, installation binding — **INST-002**.
- Every real endpoint (`installations/*`, `licenses/*`, `packages/*`, `updates/*`) — their own tasks.
- Business-level idempotent retry (returning a cached response for a legitimate retry, as opposed to
  rejecting an outright replay) — endpoint-specific, e.g. `SYNC-001`.

## Next

**APISEC-003** — response signing + key management/rotation. Then `INST-001/002` and `LIC-CP-001/002`.

---

# APISEC-001 — Vendor API skeleton `/api/vendor/v1`

## Task

**APISEC-001** — [CP] Vendor API skeleton `/api/vendor/v1` + `doc/backend/API-CONTRACT.md` — request
envelope, standard signed response, business-vs-technical error classification (Phase 6 — Vendor
Client & Sync; SRS §64, §70, §71, §72).

## Date

2026-09-28

## Why this jumped ahead of Phase 1

Pulled forward on explicit user instruction: the user does not want an automated git-deploy pipeline
(Phase 5/7/8 + `GIT-001`/`GIT-002` are descoped, recorded 2026-09-28 — deployment is manual), but does
want the license/entitlement side (Phase 6 + Phase 9) built out next. `APISEC-001` is the first task
of that scope since every later Vendor API endpoint (`INST-002`, `LIC-CP-002`, `SYNC-001`, `UPD-001`)
builds on this envelope/response skeleton. Per WORK-INSTRUCTIONS §5 (explicit instruction outranks
phase order) and §20 (only ask when a choice changes architecture) — this scope shift is settled, not
a fresh question.

## What landed

- **`routes/vendor-api.php`** — new route file, `Route::post('ping', PingController::class)`.
  Required from `routes/api.php` under `Route::prefix('vendor/v1')->group(...)`, so the live prefix
  is `/api/vendor/v1`. The legacy `/api/v1/license/{activate,verify}` pair (`LicenseApiController`)
  is untouched — it stays live until `LIC-CP-001/002` migrate its functionality.
- **`App\Support\VendorApi\VendorApiErrorCode`** — backed string enum, the SRS §72 catalog:
  - Business: `LICENSE_SUSPENDED`, `LICENSE_EXPIRED`, `PAYMENT_DUE`, `LICENSE_REVOKED`.
  - Technical: `API_TIMEOUT`, `NETWORK_ERROR`, `INVALID_RESPONSE`, `SERVER_ERROR`,
    `SIGNATURE_ERROR`, plus two skeleton-only additions needed to return a well-formed error today —
    `VALIDATION_ERROR` (envelope/field validation) and `NOT_FOUND` (unknown installation/license/etc.,
    reserved for later endpoints).
  - `classification(): string` (`'business'|'technical'`), `isBusiness()`, `isTechnical()`.
- **`App\Support\VendorApi\VendorApiResponse`** — static `success()`/`error()` building the SRS §71
  shapes exactly:
  - Success: `{success:true, request_id, code, message, data, signature}` — `signature` is always
    `null` (reserved key; real signing is `APISEC-003`).
  - Error: `{success:false, request_id, code, message, data}` — **no `signature` key**, matching the
    SRS §71 example literally (nothing sensitive to protect in an error body).
  - `error()` picks a sane default HTTP status per code (422 validation, 404 not-found, 401 signature,
    403 the four business codes, 500 otherwise) unless the caller overrides it.
- **`App\Http\Requests\VendorApi\Concerns\HasVendorEnvelope`** (trait) — the SRS §70 envelope rules:
  `installation_id`/`request_id` (string, ≤64), `timestamp` (date), `client_version` (string, ≤20),
  `signature` (string — **presence/shape only**, no cryptographic check yet).
- **`App\Http\Requests\VendorApi\VendorApiRequest`** (abstract Form Request) — merges
  `envelopeRules()` with each concrete request's `endpointRules()`; overrides `failedValidation()` to
  throw an `HttpResponseException` carrying the *standard error envelope* (`VALIDATION_ERROR`, 422,
  `data.errors` = the validator's per-field messages) instead of Laravel's default `{message, errors}`
  shape — every Vendor API response, success or failure, now has exactly one JSON shape.
- **`App\Http\Controllers\Api\Vendor\VendorApiController`** (abstract base) — `success()`/`error()`
  helpers that auto-echo `request_id` from the request.
- **`App\Http\Requests\VendorApi\PingRequest`** + **`App\Http\Controllers\Api\Vendor\PingController`**
  — `POST /ping`, envelope-only (no extra fields), returns `PING_OK` + `server_time`. **Diagnostic
  only** — not an SRS endpoint; exists to prove the skeleton end-to-end and give later tasks a
  concrete pattern to copy.
- **`doc/backend/API-CONTRACT.md`** (new — per WORK-INSTRUCTIONS §23, "created at APISEC-001/INST-001"):
  prefix, envelope field table, both response-shape examples, the full error-code table (code /
  classification / HTTP / meaning), an endpoint status table (every SRS §65-§69 endpoint group listed
  against the task that will add it — `INST-002`, `LIC-CP-002`, `SYNC-001`, `UPD-001`; `DEP-004`
  marked **descoped**), the SRS §63 security-requirement checklist with a per-item status column, and
  a note on how this relates to the still-live legacy `/api/v1/license/*` pair.

## Decisions (recorded)

- **Skeleton, not authentication.** This task validates envelope *shape*, not *authenticity* — a
  request with a fake `signature` string still passes today. `APISEC-002` adds real verification,
  replay/timestamp rejection, request-id dedupe, and rate limiting; nothing under `/api/vendor/v1`
  should be treated as trustworthy input until then. Documented prominently at the top of
  `API-CONTRACT.md`'s Status table so this isn't missed by a later task.
- **`signature` present-but-null on success, absent on error.** Matches the SRS §71 examples exactly
  rather than normalizing to one shape — an intentional asymmetry, recorded so a future task doesn't
  "fix" it into inconsistency with the SRS.
- **Two extra error codes beyond the SRS §72 list** (`VALIDATION_ERROR`, `NOT_FOUND`) — the SRS only
  enumerates the codes needed for its own worked examples (license state + technical failures); a
  working API also needs to reject a malformed request and report a missing resource. Both classify
  as `technical` (never license state).
- **`/ping` is not a real endpoint.** Kept clearly labelled as diagnostic-only in the controller
  docblock, the route file's comment, and `API-CONTRACT.md`'s endpoint table, so it doesn't get
  mistaken for an SRS-required capability later.

## Tests

`php artisan test` → **146 passed (641 assertions)** (all threads). +16 for APISEC-001:
- `Unit/Support/VendorApi/VendorApiErrorCodeTest` (11, data-provider driven) — every business code
  classifies `business`; every technical code (including the two additions) classifies `technical`;
  `isBusiness()`/`isTechnical()` agree with `classification()`.
- `Feature/Api/Vendor/VendorApiEnvelopeTest` (4) — an empty payload → 422 with the standard error
  envelope (`code: VALIDATION_ERROR`, no top-level `signature` key, per-field messages under
  `data.errors`); `request_id` is echoed back on a validation error when the client did send one; a
  malformed `timestamp` is rejected; a fully valid envelope passes through to the controller.
- `Feature/Api/Vendor/PingEndpointTest` (1) — a valid envelope returns 200 with the standard success
  envelope, `code: PING_OK`, and a `null` `signature`.

`pint` clean on all changed files.

## Migration / upgrade

No migration, no seeder, no new Composer dependency. `php artisan route:list --path=api/vendor` →
`POST api/vendor/v1/ping`.

## Out of scope

- Real signature verification, replay/timestamp-window rejection, request-id dedupe, per-endpoint
  rate limiting — **APISEC-002**.
- Response signing + key management/rotation (the `signature` field stays `null` until then) —
  **APISEC-003**.
- Every real endpoint (`installations/*`, `licenses/*`, `packages/*`, `updates/*`) — their own tasks,
  all listed with owners in `API-CONTRACT.md`'s endpoint table.
- Retiring `/api/v1/license/{activate,verify}` — **LIC-CP-001/002**, once the Vendor Client has moved
  over (a `[VC]` task, needs `ecom-single-vendor`).

## Next

**APISEC-002** — request signing + verification, replay/timestamp validation, request-id dedupe,
per-endpoint rate limits. Then `INST-001/002` and `LIC-CP-001/002`.

---

# FEAT-002 — Feature admin CRUD + dependency resolution + config-schema validator

## Task

**FEAT-002** — [CP] Feature admin CRUD + dependency resolution + config-schema editor & validator
(Phase 1 — Foundation; SRS §7 FR-003, §79, §105).

## Date

2026-09-09

## What landed

- **`App\Http\Controllers\Admin\FeatureController`** (`implements HasMiddleware`):
  `can:features.view` on `index`, `can:features.manage` on every write. Constructor-injects
  `FeatureDependencyResolver`.
  - `index` — search across `name` / `key` / `description`, `type` + `status` filters (validated
    against `Feature::TYPES` / `STATUSES`), sortable `name` / `key` / `type` / `status` /
    `default_price` / `created_at`, paginate 15, `withQueryString()`.
  - `create` / `edit` — pass `featureOptions` (other catalog rows for the dependency picker; `edit`
    excludes self) and, for `edit`, `dependents` (rows that list this feature).
  - `store` / `update` — shared `validated()`: normalises `key` (`Str::slug($v, '_')`, blank → the
    existing key on edit / derived from `name` on create), a `Validator::make` with an `after()`
    hook, then returns the clean attribute array (`dependencies` → `null` when empty, `config_schema`
    → decoded array or `null`).
  - `updateStatus` — `PATCH {feature}/status`, body `status` ∈ `Feature::STATUSES` (active /
    inactive / deprecated).
  - `destroy` — **blocked** with a validation error while any other feature lists this one in its
    `dependencies`; otherwise deletes.
- **`App\Services\Feature\FeatureDependencyResolver`** (domain service, SRS §130 layout):
  - `missing(array $keys): list<string>` — keys with no catalog row.
  - `detectCycle(string $featureKey, array $dependencyKeys): ?array` — DFS over the whole catalog
    adjacency list with the proposed edges substituted for `$featureKey`; returns the offending
    path (`['a','b','a']`) or `null`.
  - `resolve(Feature $feature): Collection` — full transitive dependency set (never includes the
    feature itself; dangling keys skipped).
- **Validation rules** (in the controller `after()` hook so messages land on the field):
  - `config_schema` — must be valid JSON, a JSON **object** (not a list), and declare `type` or
    `properties`. Per-package *value* validation against the schema is deferred to PKG-003 / PKG-009
    (SRS §79).
  - `dependencies` — rejects self-reference, unknown keys, and any edge set that would make the
    catalog graph cyclic.
  - `key` — `regex:/^[a-z0-9_]+$/`, `unique:features,key` (self-ignored).
- **Routes** — `admin.features.{index,create,store,edit,update,destroy,status}` in a new
  `admin/features` group; wildcard `{feature}` (route-bound by `key` via `Feature::getRouteKeyName`)
  declared **after** the static `create` path.
- **Views** — `resources/views/admin/pages/features/{index,_form,create,edit}.blade.php`. Index uses
  `<x-admin::data-table>` with an inline per-row status `<select>` for `features.manage` users /
  a read-only badge otherwise. `_form` has a `<select multiple>` dependency picker over other
  catalog keys and a monospace `config_schema` textarea (pretty-printed on edit).
- **Sidebar** — new "Feature Catalog" item (`bi-puzzle-fill`, `'can' => 'features.view'`) after
  "Product Management".

## Decisions (recorded)

- **No new permission keys.** `features.view` / `features.manage` already exist in
  `PermissionCatalog` (from RBAC-001/003) and are already granted to `package-manager` (both),
  `sales-manager` / `developer` (view). The controller just consumes them.
- **Dependency picker is a multi-select of existing keys**, not free text — avoids typos and lets
  the resolver assume every submitted key either exists or is caught by `missing()`.
- **Config-schema editor is a raw JSON textarea**, not a form-builder UI. FEAT-002 validates the
  schema is well-formed and object-shaped; a structured editor and value-level validation belong
  with the package builder (PKG-003 / PKG-009).
- `Feature` is already `Auditable` and already in `AuditLogController::ENTITY_TYPES` (FEAT-001), so
  create / update / delete from this screen is audited with no extra wiring.

## Tests

`php artisan test` → **130 passed (577 assertions)** (all threads). +16 for FEAT-002:
- `Unit/FeatureDependencyResolverTest` (6) — `missing()` reports only unknown keys; `detectCycle()`
  flags a direct 2-node loop, a transitive loop, returns `null` for an acyclic graph; `resolve()`
  returns the full transitive set and skips dangling / self keys.
- `Feature/Admin/FeatureAdminTest` (10) — guest → login; `qa` (no `features.view`) → 403;
  `developer` (view only) lists but can't create; create/edit forms render; `package-manager`
  creates a feature + `key` derives from `name` + an audit row is written; explicit `key` is
  slugified with `_` and must be unique; `config_schema` rejects non-JSON / a JSON list / accepts a
  valid object; dependencies reject unknown / self / circular and persist a valid set; a feature
  can't be deleted while another depends on it; status changes from the list.

`pint` clean on all changed files.

## Migration / upgrade

No migration, no seeder, no new dependency. `php artisan route:list --path=admin/features` → 7 routes.

## Out of scope

- Seeding the Ecommerce feature catalog — **FEAT-003**.
- `customer_package_features` (feature ↔ package pivot, per-package custom price + config) and
  validating a package's config *values* against `config_schema` — **PKG-003** / **PKG-009**.
- Encryption of sensitive config values at rest — **PKG-009** (SRS §79).

## Next

**FEAT-003** — seed the 15 SRS §7 Ecommerce features.

---

# FEAT-001 — Reusable feature catalog

## Task

**FEAT-001** — [CP] `features` catalog table + model + factory (Phase 1 — Foundation; SRS §7, §52, §79).

## Date

2026-09-09

## Decision (recorded)

The reusable feature catalog is a **distinct new table**, not a fold-in. The only pre-existing
`*_features` tables are `product_features` and `service_features` — per-product / per-service
marketing bullet-list repeaters (icon + text, no key, no price, no schema, owned by one marketing
`Product` / `Service`). They have nothing to do with a reusable entitlement catalog that is picked
into customer packages, priced per customer, configured against a schema, versioned and synced by
machine key. So `features` gets its own table; the marketing repeaters are untouched.

Column name: the checklist calls the machine id `machine_key`; SRS §52's `features` table calls it
`key`, matching `products.key` (PROD-001) and the coming `services.key` (SVC-001). Went with **`key`**
for catalog-wide consistency — the Vendor API / package-sync payloads bind to it.

## What landed

- **Migration `2026_09_09_000007_create_features_table`** — `features`:
  `name`, `key` (**unique**), `type` (string 40, default `module`, indexed), `description` (text
  nullable), `default_price` (decimal 12,2, default 0), `version` (string 20, default `1.0.0`),
  `status` (string 20, default `active`, indexed), `dependencies` (json nullable — feature-key list),
  `config_schema` (json nullable — SRS §79 per-package config shape), `documentation` (text nullable),
  `timestamps`.
- **`App\Models\Feature`** — `use Auditable, HasFactory`.
  - `STATUS_ACTIVE` / `STATUS_INACTIVE` / `STATUS_DEPRECATED` + `STATUSES`.
  - `TYPE_CORE` / `TYPE_MODULE` / `TYPE_INTEGRATION` / `TYPE_ANALYTICS` / `TYPE_MARKETING` /
    `TYPE_CUSTOM` + `TYPES` (free string in the DB — no CHECK constraint; FEAT-002 owns the form picker).
  - casts: `default_price` → `decimal:2`, `dependencies` / `config_schema` → `array`.
  - `booted()` `creating` hook: fills `key` ← `Str::slug($name, '_')` when blank (explicit key kept).
  - `getRouteKeyName()` → `key`.
  - scopes: `status($s)`, `active()`, `type($t)`.
  - `dependencyFeatures(): Collection` — resolves the stored key list to catalog rows (missing keys
    simply absent; full dependency validation is FEAT-002).
- **`database/factories/FeatureFactory`** — active `module` feature by default; states
  `inactive()`, `deprecated()`, `type($t)`, `dependsOn([$keys])`, `withConfigSchema()`.
- **`Admin\AuditLogController::ENTITY_TYPES`** — `'Feature' => Feature::class` added (import too), so
  feature changes are filterable in the AUDIT-002 audit viewer.

## Out of scope

- Feature admin CRUD, the dependency-resolution UI, the config-schema editor + validator, and
  encryption of sensitive config values — **FEAT-002** (SRS §79).
- Seeding the Ecommerce feature catalog (payment_gateway, courier, seo, …) — **FEAT-003**.
- `customer_package_features` (feature ↔ package pivot, custom price, per-package config) — **PKG-003**.

## Tests

`php artisan test` → **114 passed (527 assertions)** (all threads). +11 for FEAT-001:
- `Unit/FeatureTest` (8) — key derived from name when blank; explicit key kept; key unique;
  `dependencies` / `config_schema` cast to arrays; `default_price` DB default `0.00` + `decimal:2`
  cast; `active()` / `status()` / `type()` scopes; `dependencyFeatures()` resolves keys (and skips
  missing ones); empty without dependencies.
- `Feature/Foundation/FeatureCatalogTest` (3) — table carries every SRS column; `Feature` is
  `Auditable` (a create writes an `audit_logs` row); the model is filterable in the audit viewer
  (`?entity_type=Feature`).

`pint` clean on all changed files.

## Migration / upgrade

`php artisan migrate` — `2026_09_09_000007` applied on the MySQL dev DB. No seeder (FEAT-003 seeds
the catalog), no data change, no new dependency.

## Next

**FEAT-002** — Feature admin CRUD + dependency resolution + config-schema editor & validator.

---

# PROD-002 — Product admin key field + `products.*` gate migration

## Task

**PROD-002** — [CP] Product admin CRUD + product-version field (Phase 1 — Foundation; SRS §6, §52, §105).

## Date

2026-09-09

## Starting point

The marketing Product module (built pre-SRS) already gave `products` a **complete admin CRUD**:
`Admin\ProductController` (index with search / type / status / category filters + sortable columns,
create / edit / update / destroy, one-click `status` + `featured` toggles, a features repeater, cover
image + screenshots) and `Admin\ProductCategoryController`, plus a **`version`** field already on the
form. PROD-001 then made this same table the SRS §52 control-plane product and added `products.key`.
So PROD-002's real work was the two gaps below — not a new CRUD.

## What landed

- **`key` on the admin form** — `admin/pages/products/_form.blade.php` gains a "Control-plane Key"
  input beside Slug (help text: *stable machine id — packages, offers, releases and the Vendor API
  bind to this*). `ProductController::validateProduct()` now `$request->merge()`s a normalised
  `slug` + `key` **before** validation (so `Rule::unique('products','key')->ignore($id)` checks the
  slugified value that will actually be stored), and a blank `key` falls back to the current key on
  edit / `null` on create (the `Product` model's `creating` hook then derives it from `slug`).
  Also made `slug`/`key` handling null-safe for requests that omit the field.
- **Gate migration `product.*` → `products.*`** —
  - `PermissionCatalog` drops the entire `product` group (`product.view/create/edit/delete`):
    **51 → 47** keys. `team` / `licensing` singular groups stay.
  - `Admin\ProductController` + `Admin\ProductCategoryController` `middleware()` now use
    `can:products.view` (index) and `can:products.manage` (every write — create / store / edit /
    update / destroy / status / featured / deleteImage / category writes). `ProductController::deleteImage`'s
    inline `Gate::authorize('product.edit')` → `products.manage`.
  - `resources/views/admin/partials/sidebar.blade.php` — "Product Management" `can` key
    `product.view` → `products.view`.
  - Effect: **Package Manager** (SRS §105 — holds `products.view` + `products.manage`) can now
    manage the product catalog; `support` / `finance` etc. still can't; `super-admin` unaffected.
- **`RolePermissionSeeder`** — new prune step: `Permission::whereNotIn('name', PermissionCatalog::all())->delete()`
  (cascades clear `permission_role`). Re-run on the MySQL dev DB → 47 permissions, 0 `product.*`.

## Tests

`php artisan test` → **103 passed / 498 assertions**. +6 for PROD-002 (`Feature/Admin/ProductAdminTest`):
- legacy `product.view` is gone from the catalog; `products.view` / `products.manage` present.
- a role with no `products.view` (`finance-manager`) → 403 on the product index.
- a view-only role (`sales-manager` — `products.view`, no `products.manage`) sees the list but 403s
  on create / store.
- `package-manager` creates a product; a blank `key` derives from the slug.
- an explicit `key` is slugified and rejected when it collides (case-insensitively).
- editing with a blank `key` keeps the existing value.

`pint` clean on the changed files.

## Migration / upgrade

No schema migration. `php artisan db:seed --class=RolePermissionSeeder` on an existing DB now
**deletes** the 4 legacy `product.*` permission rows (they were only ever attached to `super-admin`,
which keeps full access via `Gate::before`). Done on the dev DB.

## Out of scope

- A dedicated product-version **history** table — Phase 2 (`PKG-006` covers package versions);
  a single `version` string per product is enough for Phase 1.
- Reshaping `licenses` / the Vendor API onto `products` — **LIC-CP-001** / **INST-002**.
- Physical-product fields (SKU, stock) — the inventory module.

## Next

**FEAT-001** — `features` table + model + factory (SRS §7 / §52).

---

# PROD-001 — Control-plane product (marketing `products` fold-in)

## Task

**PROD-001** — [CP] Control-plane `products` table + model + factory; seed the `ecommerce` product;
relate to / supersede `licensed_apps` additively (Phase 1 — Foundation; SRS §6, §52).

## Date

2026-09-09

## Decision (user-confirmed)

The existing marketing `products` table (`App\Models\Product`) **is** the SRS §52 control-plane
product — **no new table**. It already carries `version`, `status`, `description` and (SRS "name")
`title`, plus a `type` = `software` | `physical` split the user asked to keep. The one gap is a
stable machine identifier, so `key` is added. `licensed_apps` is kept as released schema and demoted
to a licensing-domain link row onto `products` (identical to the CUST-001 `license_customers` →
`customers` fold-in). The unsigned Vendor API (`LicenseApiController`) is **not touched** — it still
matches on `licensed_apps.slug`; LIC-CP-001 / INST-002 move it onto the product.

## What landed

- **Migration `2026_09_09_000005_add_control_plane_key_to_products_table`** — `products.key`
  (`string`, nullable, **unique**, after `slug`). Backfill: `key = slug` for existing rows.
- **Migration `2026_09_09_000006_add_product_id_to_licensed_apps_table`** — `licensed_apps.product_id`
  (nullable `foreignId`, `constrained()->nullOnDelete()`, after `id`). Backfill links each row to a
  product by **exact `slug`/`key` match only** — an unmatched licensing row stays `null`.
- **`App\Models\Product`** — `+ HasFactory`; `key` added to `$fillable`; `booted()` `creating` hook
  fills `key` ← `slug` when blank (runs after `HasUniqueSlug`'s own hook); `licensedApps()` hasMany;
  class docblock documenting the dual marketing / control-plane role.
- **`App\Models\LicensedApp`** — `product_id` in `$fillable`; `product()` belongsTo; docblock notes
  the demotion. (Keeps the AUDIT-002 `Auditable` trait.)
- **`App\Models\ProductCategory`** — `+ HasFactory`.
- **`database/factories/ProductFactory`** — software product by default; `physical()` / `inactive()`
  / `featured()` states. **`database/factories/ProductCategoryFactory`** — `physical()` state.
- **`ProductSeeder`** — new "E-Commerce" category + the SRS §6 **`ecommerce`** product
  ("CodexeCommerce", `key = ecommerce`, 6 features). Every seeded product now also gets an explicit
  `key` (`$data['key'] ?? $data['slug']`).
- **`LicensingSeeder`** — resolves `Product::where('key', 'ecommerce')` and sets `product_id` on the
  `ecommerce-laravel` licensed-app bridge row (ProductSeeder runs first in `DatabaseSeeder`).

## Out of scope

- Surfacing `key` / the product-version field on the admin form, and moving the Product controller's
  gates from the legacy `product.*` keys to `products.view` / `products.manage` — **PROD-002**.
- Reshaping `licenses` / the Vendor API onto `products` — **LIC-CP-001** / **INST-002**.
- Physical-product management (SKU, stock, …) — the inventory module.

## Tests

`php artisan test` → **97 passed (494 assertions)**. +9 for PROD-001:
- `Unit/ProductTest` (4) — `key` defaults to `slug` on create; an explicit `key` is kept; `key` is
  unique; `software()` / `physical()` scopes.
- `Feature/Foundation/ControlPlaneProductTest` (5) — `products.key` + `licensed_apps.product_id`
  columns exist; `LicensedApp::product()` ↔ `Product::licensedApps()`; an unmatched licensed app is
  unlinked; deleting a product nulls the link but keeps the licensed app; the seeders create + link
  the `ecommerce` product.

`pint` clean on the changed files.

## Migration / upgrade

`php artisan migrate` — `2026_09_09_000005` + `2026_09_09_000006` applied on the MySQL dev DB;
`ProductSeeder` + `LicensingSeeder` re-run (5 products; `ecommerce-laravel` → `ecommerce` product).
No new dependency.

## Next

**PROD-002** — Product admin CRUD refinement (surface `key` / version, align gates), then FEAT-001 /
SVC-001.

---

# CUST-003 — Customer detail page shell

## Task

**CUST-003** — [CP] Customer detail page shell — tabbed per SRS §77 (Phase 1 — Foundation).

## Date

2026-09-09

## What landed

- **`App\Http\Controllers\Admin\CustomerController::show(Customer $customer)`** — added to the
  `can:customers.view` middleware group (alongside `index`). Eager-loads `licenses` (hasManyThrough
  `LicenseCustomer`) with `licensedApp` + `package`, ordered `licenses.created_at` desc; loads the
  last 50 `AuditLog::forEntity($customer)` rows. `use App\Models\AuditLog`.
- **Route** — `Route::get('{customer}', ...)->name('show')` in the `admin/customers` group,
  declared **before** `{customer}/edit` / `{customer}` PUT/DELETE (all still after the static
  `create`). `admin.customers.show`.
- **`resources/views/admin/pages/customers/show.blade.php`** — heading (name / company / email /
  phone / status badge / Edit [`@can customers.manage`] / All Customers) + a Bootstrap `nav-tabs`
  panel. 15 tabs in SRS §77 order: Information, Products, Packages, Package Features, Services,
  Requirements, Custom Development, Invoices, Payments, License, Installation, Software Version,
  Deployments, Ownership, Audit History.
  - **Information** — 2-col `<dl>` of the customer fields + Added / Updated + Notes.
  - **License** — `<x-admin::data-table>` of the customer's licenses (key / app+package / status
    badge / expires / last verified), `@forelse` empty state; a note that the SRS §39 lifecycle +
    installation binding arrive with LIC-CP-001.
  - **Audit History** — `<x-admin::data-table>` of the loaded `AuditLog` rows (when / action /
    changed keys / actor label, "System" when null).
  - The other 12 tabs render a shared centered placeholder ("<label> for this customer will appear
    here. Arrives in <Phase N — task>.") driven by a `phase` key in the `$tabs` array.
- **`admin/pages/customers/index.blade.php`** — the customer name is now a link to
  `admin.customers.show`; an eye action was added to the row actions (visible to any
  `customers.view` user); the old "View only" span for non-managers was dropped (the eye covers it).

## Decision (recorded)

CUST-003 is the **shell** only. Tabs whose backing tables don't exist yet (products, packages,
features, services, requirements, custom-dev, invoices, payments, installation, version, deployments,
ownership) render a placeholder that names the phase/task that fills them — each later phase wires
its own pane into this view. Information / License / Audit use the data that already exists today.
No new `customers` columns, no new permission (`customers.view` already gates the detail read).

## Tests

`php artisan test` → **88 passed** (both threads). +5 for CUST-003
(`Feature/Admin/CustomerDetailPageTest`):
- guest → login redirect; `qa` (no `customers.view`) → 403.
- detail page renders the Information tab data (name / email / phone / notes) + every SRS §77 tab
  label + the phase-placeholder strings for Phases 2 / 3 / 7 / 10.
- License tab lists a real license key issued to the customer (via a `LicenseCustomer` bridge row).
- Audit tab shows a changed-key (`company_name`) after an update.

`pint` clean on `CustomerController.php`, `CustomerDetailPageTest.php`, `routes/admin.php`.

## Migration / upgrade

No migration. `php artisan route:list --path=admin/customers` → 8 routes (adds `admin.customers.show`).

## Out of scope

- Filling any phase-gated tab with real data — each is its own later task (PROD-*, PKG-*, INV-*,
  PAY-*, REQ-*, CDEV-*, INST-*, DEP-*, OWN-*).
- A customer-scoped audit viewer with filters — the global one is AUDIT-002's `admin.audit.index`.

## Next

**PROD-001** — control-plane `products` table + model + factory (record the fold-in vs.
distinct-table decision against `licensed_apps`).

---

# AUDIT-002 — Audit rollout + admin viewer

## Task

**AUDIT-002** — [CP] Roll the `Auditable` trait across every admin-managed module + build the admin
audit-log viewer behind `audit.view` (closes the cross-cutting foundation 6 / 6; SRS §75).

## Date

2026-09-09

## What landed

- **`Auditable` trait** added to 14 models (`Role` already had it from AUDIT-001):
  `Customer`, `Product`, `ProductCategory`, `Service`, `Portfolio`, `PortfolioCategory`,
  `TeamMember`, `Consultation`, `User`, `LicensedApp`, `LicensedAppPackage`, `License`,
  `LicenseCustomer`, `LicenseTransaction`. Each now writes `created` / `updated` / `deleted` rows
  to `audit_logs` via the AUDIT-001 `AuditLogger`. `User`'s default `auditExclude()` already hides
  `password` / `remember_token`.
- **`License::auditExclude()`** override adds `last_verified_at` — `LicenseApiController` writes it
  on every Vendor-API verify, so auditing it would bury real lifecycle changes under verification
  noise.
- **`App\Http\Controllers\Admin\AuditLogController`** (`implements HasMiddleware`, `can:audit.view`
  on `index`). Filters: `action` (distinct values from the table), `entity_type` (a curated
  `ENTITY_TYPES` basename→FQCN map of the audited models), `actor` (`system` = null actor, or a
  user id), `date_from` / `date_to` (`whereDate`). `latest('created_at')->latest('id')`, paginate
  30, `withQueryString()`.
- **`App\Models\AuditLog`** — two view accessors: `entity_name` (`"Customer #12"`, null when
  account-wide) and `change_set` (per-key `['from' => …, 'to' => …]` pairs merged from
  `old_values` / `new_values`).
- **Route** `admin.audit.index` — `GET admin/audit-log`, in the `auth`+`admin` group, before the
  `blog` placeholder.
- **View** `resources/views/admin/pages/audit/index.blade.php` — filter toolbar +
  `<x-admin::data-table>` (When / Actor / Action / Entity / Changes); action badge colour-coded;
  Changes column renders `change_set` as a `field: from → to` list (null → "—", bool/json/120-char
  trim formatting).
- **Sidebar** — new "Audit Log" item (`bi-clock-history`, `'can' => 'audit.view'`) above "Blog
  Management".

## Decisions (recorded)

- **Only aggregate models are audited.** Child / repeater / image rows (`ProductFeature`,
  `ServiceFeature`, `*Image`, `PortfolioHighlight` / `Result` / `Technology`, `PortfolioSetting`,
  `LicenseActivation`) are delete-and-recreate synced inside their parent's edit — the parent's
  `updated` row is the meaningful record.
- **Adding a new audited model** requires adding it to `AuditLogController::ENTITY_TYPES` for it to
  appear in the viewer's entity filter.
- Full seeds stay audit-silent: `DatabaseSeeder` uses `WithoutModelEvents`, and
  `RolePermissionSeeder` is additionally `withoutAuditing`-wrapped (AUDIT-001).

## Tests

`php artisan test` → **88 passed (482 assertions)** (both threads). +10 for AUDIT-002:
- `Feature/Admin/AuditLogViewerTest` (7) — guest → login; `qa` (no `audit.view`) → 403; viewer
  lists entries; filter by action / entity type / actor (`system` vs user) / date range.
- `Feature/Foundation/AuditableRolloutTest` (3) — all 15 admin-managed models carry the trait;
  Customer create/update/delete audited with the right entity type + changed keys; a `License`
  `last_verified_at`-only update writes nothing while a real `status` change still audits.

`pint` clean on the changed files.

## Migration / upgrade

No migration, no new dependency. `audit_logs` already exists from AUDIT-001 (`2026_09_09_000004`).

## Out of scope

- Auth events (login / logout / password reset) and the license state-machine transitions — their
  own later tasks.
- Retention / pruning of `audit_logs`.

## Next

**PROD-001** — [CP] control-plane `products` table + model + factory (record the fold-in vs.
distinct-table decision against `licensed_apps`).

---

# AUDIT-001 (earlier — foundation for AUDIT-002)

## Task

**AUDIT-001** — [CP] Audit log infrastructure — `audit_logs` table + `AuditLogger` service +
`Auditable` model trait (cross-cutting foundation; SRS §75).

## Date

2026-09-09

## What landed

- **Migration `2026_09_09_000004_create_audit_logs_table`** — `audit_logs`:
  `actor_type` / `actor_id` / `actor_label` (all nullable — null actor = system/scheduled),
  `action` (indexed), `entity_type` / `entity_id` (nullable — null = account-wide event),
  `old_values` / `new_values` (json), `ip_address` (45), `user_agent` (text),
  **`created_at` only** (append-only — no `updated_at`). Indexes: `action`, `created_at`,
  `(entity_type, entity_id)`, `(actor_type, actor_id)`.
- **`App\Models\AuditLog`** — `const UPDATED_AT = null`, `$guarded = []`, array casts,
  `actor()` / `entity()` morphs, `scopeForEntity(Model)`.
- **`App\Services\Audit\AuditLogger`** (singleton, no ctor deps):
  - `log(string $action, ?Model $entity = null, ?array $old = null, ?array $new = null, ?Authenticatable $actor = null): ?AuditLog`
    — actor defaults to `auth()->user()`; ip / user-agent from `request()` (null-safe for console);
    empty `$old` / `$new` stored as null.
  - `withoutAuditing(callable): mixed` (restores the flag in a `finally`), `disable()`, `enable()`.
- **`App\Models\Concerns\Auditable`** trait — `bootAuditable()` hooks `created` / `updated` /
  `deleted` (and `restored` when the model has SoftDeletes) → `AuditLogger`. `updated` records only
  changed keys and returns early if the only changes were excluded (`auditExclude()` defaults:
  `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`). `auditableAttributes(?array $only)`
  does the filtering.
- **`Role`** now `use Auditable`. **`RolePermissionSeeder::run()`** wrapped in
  `AuditLogger::withoutAuditing()` so a full `db:seed` writes no audit rows.
- **Explicit domain events** (model observers can't see pivot syncs):
  - `RoleController@update` → `role.permissions_updated` with `{permissions: [...]}` old/new
    (only when the set actually changed).
  - `UserController@update` → `user.roles_updated` with `{roles: [...]}` old/new, entity = the user.
- **`AppServiceProvider::register()`** — `$this->app->singleton(AuditLogger::class)`.

## Tests

`php artisan test` → **72 passed (411 assertions)** (both threads). +9 for AUDIT-001:
- `Unit/AuditLoggerTest` (2) — `withoutAuditing` returns the callback value; restores the flag even
  when the callback throws.
- `Feature/Foundation/AuditLoggingTest` (7) — logger records actor / action / entity / old / new;
  `withoutAuditing` suppresses writes; `Auditable` records create + update (old/new = changed keys)
  + delete; a `touch()`-only change writes nothing; the seeder writes zero audit rows;
  `role.permissions_updated` is written from the admin screen (and a no-op re-save is not);
  `user.roles_updated` is written with the acting admin as actor.

`pint` clean on all changed files.

## Migration / upgrade

`php artisan migrate` — `2026_09_09_000004` applied on the MySQL dev DB. No data change, no new
dependency. `Auditable` currently only on `Role`; AUDIT-002 rolls it out.

## Out of scope (→ AUDIT-002)

- Applying `Auditable` / explicit logging to every module (customers, licensing, products, team,
  offers, payments, deployments, licenses, ownership …).
- The admin **audit-log viewer** (filter by actor / entity / action / date) behind `audit.view`.
- Retention / pruning of `audit_logs`.
- Auditing auth events (login / logout / password reset) and the license state-machine transitions.

## Next

**AUDIT-002** — [CP] Emit audit events from every existing module + build the admin audit-log
viewer. Closes the cross-cutting foundation (6 / 6).

---

# CUST-002 — Customer admin CRUD

## Task

**CUST-002** — [CP] Customer admin CRUD (search / status filter, create / edit / delete, status
transitions) behind `customers.*`; repoint the licensing customer screens + license form at
`customers` (Phase 1 — Foundation).

## Date

2026-09-09

## What landed

- **`App\Http\Controllers\Admin\CustomerController`** (`implements HasMiddleware`):
  `new Middleware('can:customers.view', only: ['index'])` +
  `new Middleware('can:customers.manage', only: ['create','store','edit','update','destroy','updateStatus'])`.
  - `index` — `withCount('licenses')`, search across `name` / `company_name` / `email`, status
    filter (validated against `Customer::STATUSES`), sortable `name` / `company_name` / `email` /
    `status` / `created_at`, paginate 15, `withQueryString()`.
  - `store` / `update` — shared `validateCustomer()` (`email` unique on `customers`, ignoring self;
    `status` in `Customer::STATUSES`).
  - `updateStatus` — `PATCH {customer}/status`, body `status` ∈ `Customer::STATUSES`.
  - `destroy` — **blocked** with a validation error while `licenses()->exists()`; otherwise deletes.
- **Routes** — `admin.customers.{index,create,store,edit,update,destroy,status}` (wildcard
  `{customer}` last, after `create`).
- **Views** — `admin/pages/customers/{index,_form,create,edit}.blade.php`. Index uses
  `<x-admin::data-table>`, an inline per-row status `<select>` (submits the PATCH) for
  `customers.manage` users / a read-only badge otherwise, and `@can`-gated create/edit/delete
  controls. `_form` = contact name, company name, email, phone, status, notes.
- **Sidebar** — new "Customer Management" item (`bi-person-vcard-fill`, `'can' => 'customers.view'`).
- **Licensing repointed at `customers`:**
  - `Admin\Licensing\LicenseCustomerController` + `admin.licensing.customers.*` resource route +
    `resources/views/admin/pages/licensing/customers/*` — **deleted**.
  - `LicenseController::create/edit` now pass `Customer::orderBy('name')->get()`; `edit` also passes
    `selectedCustomerId` (`$license->customer?->customer_id`).
  - `store/update` validate `customer_id` ∈ `customers`, then
    `resolveLicenseCustomer()` — `LicenseCustomer::firstOrCreate(['customer_id' => …], [name,email,phone])`
    — yields the `license_customer_id` for the released `licenses` FK. One link row per customer.
  - `licensing/_nav.blade.php` "Customers" → `admin.customers.index`; license `_form` picker field
    renamed `license_customer_id` → `customer_id`, "Add a customer" link → `admin.customers.create`.

## Decision (recorded)

`licenses.license_customer_id` stays pointing at `license_customers` (released schema). Rather than
a second customer CRUD, the licensing UI now speaks the canonical `Customer`, and the controller
keeps exactly one `LicenseCustomer` bridge row per customer (created lazily). **LIC-CP-001** reshapes
`licenses` to reference `customers` directly and deletes `resolveLicenseCustomer()` + the bridge.

## Tests

`php artisan test` → **73 passed** (both threads). +14 for CUST-002:
- `Unit/CustomerTest` +1 — create/edit form render (kept with the model unit test file).
- `Feature/Admin/CustomerCrudTest` (11) — guest → login; `qa` (no `customers.view`) → 403; `support`
  (view-only) sees the list but `store` → 403; forms render; `sales-manager` creates / updates;
  unique-email rejected; status-transition endpoint (valid + invalid); search + status filter;
  delete blocked with licenses / allowed without.
- `Feature/Admin/LicenseCustomerBridgeTest` (2) — creating two licenses for one customer makes a
  single `LicenseCustomer` link row + two `licenses`; the license create form lists canonical
  customers and renders `name="customer_id"`.

`pint` clean on the changed files.

## Migration / upgrade

No migration. `php artisan route:list --path=admin/customers` → 7 routes. The removed
`admin.licensing.customers.*` route names are gone — no code references them (grep clean outside
`CLAUDE.md` history).

## Out of scope

- Customer **detail page** (SRS §77 tabs) — **CUST-003**.
- Auditing customer create/update/delete — picked up by **AUDIT-002**'s module rollout.
- Reshaping `licenses` onto `customers` — **LIC-CP-001**.

## Next

**CUST-003** — Customer detail page shell (tabbed per SRS §77), or **AUDIT-002** first to close the
cross-cutting foundation.
