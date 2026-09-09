# Last Task Summary

> Concurrent threads. Sections below, newest first: **FEAT-002**, **FEAT-001**, **PROD-002**,
> **PROD-001**, **CUST-003**, **AUDIT-002**, **AUDIT-001**, **CUST-002**. Working tree carries all of
> them — review before commit.
>
> `codexehub-4a` briefly started PROD-001 the other way (evolve `licensed_apps`), backed it out
> cleanly after the user confirmed the `products` fold-in, then did PROD-002 on top of the
> parallel session's PROD-001 — nothing of the abandoned attempt remains.

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
