# CHECKLIST — Codexehub Control Plane & Licensing Platform

**Purpose:** Whole-platform checklist to complete the SRS (v1.2).
**Source of truth:** the SRS — `D:\Personal\Drive\Obsidian\Projects\Codexehub\Codexehub Customer-Specific Software, Package, Licensing, Development & Remote Deployment Platform.md`.
**Workflow:** `doc/backend/WORK-INSTRUCTIONS.md`. Operational detail: `doc/backend/PROJECT-STATUS.md`.
**Maintenance:** tick a box + recompute Progress in the same step that finishes the task (WORK-INSTRUCTIONS §10).
**Last updated:** 2026-09-09 (FEAT-002 — Feature admin CRUD + dependency resolver + config-schema validator)

Tags: **[CP]** = this repo (`codexehub` control plane) · **[VC]** = customer product repo (`ecom-single-vendor`, Vendor Client).

---

## Progress

| Scope | Done | Total | % |
| --- | --- | --- | --- |
| **Whole platform** | **13** | **130** | **10%** |
| Cross-cutting foundation | 6 | 6 | 100% |
| Phase 1 — Foundation | 7 | 12 | 58% |
| Phase 2 — Package Management | 0 | 10 | 0% |
| Phase 3 — Offer & Billing | 0 | 14 | 0% |
| Phase 4 — Requirement & Development | 0 | 8 | 0% |
| Phase 5 — Release Management | 0 | 9 | 0% |
| Phase 6 — Vendor Client & Sync | 0 | 20 | 0% |
| Phase 7 — Deployment | 0 | 13 | 0% |
| Phase 8 — cPanel | 0 | 6 | 0% |
| Phase 9 — License Enforcement | 0 | 8 | 0% |
| Phase 10 — Ownership | 0 | 7 | 0% |
| Phase 11 — Production Hardening | 0 | 14 | 0% |
| GA — Acceptance | 0 | 3 | 0% |

Progress bar: `[██···················] 10%`


---

## Existing foundation (pre-SRS — already in the repo)

Not counted as tasks (delivered under `doc/backend/Project-overview.md` and commit `1db4faf`):

- Admin auth (login / forgot / reset / profile), admin dashboard shell + Chart.js, User Management,
  and the marketing-site modules (Service / Product / Portfolio / Team / Consultation).
- A **primitive licensing subsystem**: `licensed_apps`, `licensed_app_packages` (flat/global),
  `license_customers`, `licenses` (status enum), `license_activations` (domain+fingerprint),
  `license_transactions` (manual bKash); `LicenseTokenService` (RS256 JWT); Admin\Licensing CRUD +
  suspend/reactivate/revoke + transaction approve/reject; unsigned `POST /api/v1/license/{activate,verify}`.

These slices **partially** anticipate — and will be evolved additively (WORK-INSTRUCTIONS §8, §16) by —
`PROD-001`, `FEAT-001`, `PKG-001`, `PAY-001`, `APISEC-001`, `INST-002`, `LIC-CP-001`, `REL-004`.
None fully meets its SRS spec, so all boxes below start unticked.

---

## Cross-cutting foundation  (6 / 6)

- [x] HARD-000 — [CP] Test harness. PHPUnit 11 on SQLite `:memory:` (no Pest — matches the sibling project, no new dep). Scaffold `ExampleTest`s removed. `UserFactory` gains an explicit `role` default + `nonAdmin()` state. New: `tests/Unit/UserTest` (`isAdmin()`), `tests/Feature/Foundation/AdminAccessTest` (guest→login / admin→200 / non-admin→403), `tests/Feature/Foundation/SmokeTest` (7 public pages render on an empty DB). `php artisan test` → **12 passed**. *(SRS §108; WORK-INSTRUCTIONS §13)*
- [x] RBAC-001 — [CP] Roles & permissions schema. Migration `2026_09_09_000001` (`roles`, `permissions` [name/label/group], `permission_role`, `role_user` — composite PKs, cascade). `Role` / `Permission` models; `App\Models\Concerns\HasRoles` on `User` (`hasRole` / `hasPermissionTo` / `assignRole` / `syncRoles`, eager-loaded + memoised). `App\Support\Authorization\PermissionCatalog` — 39 control-plane keys in 19 groups (plural nouns; legacy singular `product.*`/`team.*`/`licensing.*` gates untouched, RBAC-003 folds them in). `AppServiceProvider`: `Gate::before` super-admin blanket pass + `Gate::define` loop over the catalog. `RolePermissionSeeder` (idempotent) — upserts permissions + `super-admin` role (all perms) + attaches it to every `role='admin'` user; wired into `DatabaseSeeder`. +10 tests (`Unit/PermissionCatalogTest` 4, `Feature/Foundation/RolesPermissionsTest` 6; suite 22). Migrated + seeded on the MySQL dev DB — both admin users carry `super-admin`. *(SRS §105)*
- [x] RBAC-002 — [CP] SRS §105 role seed. `App\Support\Authorization\RoleCatalog` — the 8 roles (`super-admin` = `'*'`, plus `sales-manager` 14 / `package-manager` 13 / `finance-manager` 12 / `developer` 11 / `qa` 6 / `deployment-manager` 9 / `support` 9 permissions) with `permissionsFor()` / `names()`. `RolePermissionSeeder` now iterates `RoleCatalog` (still idempotent, still attaches `super-admin` to `role='admin'` users). `UserFactory::asRole(...$roles)` helper. No non-super role gets `roles.manage` / `settings.manage`. +9 tests (`Unit/RoleCatalogTest` 5, `Feature/Foundation/RoleSeedingTest` 4; suite 31). Seeded on the MySQL dev DB.
- [x] RBAC-003 — [CP] Legacy gates folded in + admin Roles & Permission screen. `PermissionCatalog` gains `product` / `team` / `licensing` groups (39 → 51 keys); `AppServiceProvider`'s legacy `isAdmin()` gate loop deleted — every ability now permission-backed via the catalog loop. `User::isStaff()` (legacy `admin` string role **or** any RBAC role); `EnsureUserIsAdmin` uses it. New `Admin\RoleController` (index / edit / update, `can:roles.view` + `can:roles.manage`; `super-admin` role locked from editing) + `admin/pages/roles/{index,edit}` views; `admin.roles.*` routes replace `PlaceholderController@roles` (method removed). `UserController@edit/update` gains RBAC role assignment (`roles[]` synced) + a "cannot remove the last Super Admin" guard. Sidebar links gated by `@can` (roles / team / product / licensing). `licensing` route group gets `can:licensing.view`. +13 tests (`Feature/Admin/RolesPermissionScreenTest` 12, `Unit/UserTest` +1; suite 51 with the concurrent CUST work). MySQL dev DB re-seeded (51 permissions).
- [x] AUDIT-001 — [CP] Audit log infrastructure. Migration `2026_09_09_000004` `audit_logs` (actor_type/id/label, action, entity_type/id, old/new_values json, ip, user_agent, `created_at` only — append-only; indexes on action / created_at / (entity) / (actor)). `App\Models\AuditLog` (`scopeForEntity`, morphs). `App\Services\Audit\AuditLogger` — `log(action, ?entity, ?old, ?new, ?actor)` resolves actor from `auth()` + ip/UA from `request()`; `withoutAuditing(fn)` / `disable()` / `enable()` static toggle; singleton. `App\Models\Concerns\Auditable` trait — `created`/`updated`/`deleted`/`restored` model events → `AuditLogger`, `auditExclude()` (password/tokens/timestamps), `updated` skips a no-op change. Applied to `Role`; `RolePermissionSeeder` wrapped in `withoutAuditing`. `RoleController@update` logs `role.permissions_updated` (old/new permission arrays — pivot syncs aren't caught by model events); `UserController@update` logs `user.roles_updated`. +9 tests (`Unit/AuditLoggerTest` 2, `Feature/Foundation/AuditLoggingTest` 7; suite 72). MySQL dev DB migrated. *(SRS §75)*
- [x] AUDIT-002 — [CP] `Auditable` trait rolled out to 14 more models (Customer, Product, ProductCategory, Service, Portfolio, PortfolioCategory, TeamMember, Consultation, User, LicensedApp, LicensedAppPackage, License, LicenseCustomer, LicenseTransaction — `Role` already had it). `License::auditExclude()` adds `last_verified_at` (Vendor-API verify noise). New `Admin\AuditLogController` (`can:audit.view`) + `admin/pages/audit/index` viewer (filter by action / entity type / actor incl. "system" / date-from / date-to; `AuditLog` gets `entity_name` + `change_set` accessors) + `admin.audit.index` route + sidebar entry (`@can audit.view`). Child/repeater/image models (`*Feature`, `*Image`, `PortfolioSetting`, `LicenseActivation`) left unaudited — synced as part of the parent edit; noise not signal. Auth events + license state-machine transitions deferred to their own tasks. +10 tests (`Feature/Admin/AuditLogViewerTest` 7, `Feature/Foundation/AuditableRolloutTest` 3; suite 88 with concurrent CUST-003). No migration. *(SRS §75)*

---

## Phase 1 — Foundation  (7 / 12)

- [x] CUST-001 — [CP] Canonical `customers` table — SRS §52 columns (name, company_name, email [unique], phone, status [default `prospect`]) + `notes`. `Customer` model (`STATUSES` = prospect/active/inactive/suspended, `status()` / `active()` scopes, `licenseCustomers()` hasMany, `licenses()` hasManyThrough) + `CustomerFactory`. **Fold-in decision:** `license_customers` kept as released schema (nothing renamed/dropped) and demoted to a licensing-domain link row — migration `2026_09_09_000003` adds nullable `license_customers.customer_id` (FK, `nullOnDelete`) and backfills it (each legacy row → a `customers` row, deduped by email). `LicenseCustomer` gains `customer()` belongsTo + `HasFactory`. New code targets `customers`; Admin\Licensing screens unchanged (migrate to the FK in CUST-002). +8 tests (suite 39). *(SRS §52)*
- [x] CUST-002 — [CP] Customer admin CRUD. `Admin\CustomerController` (`HasMiddleware` — `can:customers.view` index / `can:customers.manage` writes): search (name / company / email), status filter, sortable columns, create / edit / update, `PATCH {customer}/status` transition (any of the 4 `Customer::STATUSES`), delete (blocked while the customer has licenses). `admin.customers.*` routes + `admin/pages/customers/*` views + sidebar entry (`@can customers.view`). **Licensing repointed at `customers`:** the old `Admin\Licensing\LicenseCustomerController` + `admin.licensing.customers.*` routes + `admin/pages/licensing/customers/*` views **removed**; `LicenseController` now lists `Customer` and bridges to the released `licenses.license_customer_id` FK via `resolveLicenseCustomer()` (one `LicenseCustomer` link row per customer, `firstOrCreate` — retired by LIC-CP-001). Licensing `_nav` + license `_form` picker (`name="customer_id"`) updated. +14 tests (`Feature/Admin/CustomerCrudTest` 11, `Feature/Admin/LicenseCustomerBridgeTest` 2, `Unit/CustomerTest` +1). No migration.
- [x] CUST-003 — [CP] Customer detail page shell — `CustomerController@show` (`can:customers.view`) + `GET admin/customers/{customer}` (`admin.customers.show`, wildcard before `{customer}/edit`) + `admin/pages/customers/show.blade.php`: Bootstrap `nav-tabs` in SRS §77 order (Information / Products / Packages / Package Features / Services / Requirements / Custom Development / Invoices / Payments / License / Installation / Software Version / Deployments / Ownership / Audit History). Information / License / Audit render real data (customer fields + notes; `licenses()` hasManyThrough w/ app+package+status+expiry; `AuditLog::forEntity` last 50) — every phase-gated tab shows a shared placeholder naming the phase/task that fills it. Index links the name + an eye action to the page. +5 tests (`Feature/Admin/CustomerDetailPageTest`). No migration. *(SRS §77)*
- [x] PROD-001 — [CP] Control-plane product. **Decision (user-confirmed):** the existing marketing `products` table (`App\Models\Product`, with its `type` = software|physical split) *is* the SRS §52 control-plane product — no new table. Migration `2026_09_09_000005` adds `products.key` (nullable unique, backfilled ← `slug`; the stable machine id packages/offers/releases/Vendor API bind to, decoupled from the editable marketing `slug`); model auto-fills `key` ← `slug` on create. `licensed_apps` demoted to a link row — migration `2026_09_09_000006` adds nullable `licensed_apps.product_id` (FK, `nullOnDelete`, backfilled by exact slug/key match; unmatched rows stay null). `LicensedApp::product()` belongsTo + `Product::licensedApps()` hasMany. `Product` gains `HasFactory`; new `ProductFactory` (`physical()`/`inactive()`/`featured()` states) + `ProductCategoryFactory`. `ProductSeeder` adds the SRS §6 `ecommerce` product (key `ecommerce`, "CodexeCommerce", + an "E-Commerce" category); `LicensingSeeder` links the `ecommerce-laravel` licensed app to it. Vendor API (`LicenseApiController`) untouched — still resolves on `licensed_apps.slug` (LIC-CP-001 / INST-002 move it onto the product). Admin form's `key`/version surfacing → PROD-002. +9 tests (`Unit/ProductTest` 4, `Feature/Foundation/ControlPlaneProductTest` 5; suite 97). Migrated + seeded on the MySQL dev DB. *(SRS §6, §52)*
- [x] PROD-002 — [CP] Product admin CRUD + product-version field. The marketing Product admin already covered full CRUD (index / create / edit / delete / status + featured toggles / categories / features repeater / images) and a `version` field. PROD-002 (a) surfaces the PROD-001 **`key`** (control-plane machine id) on `admin/pages/products/_form.blade.php` next to `slug`, with `ProductController` slugifying + `Rule::unique('products','key')` on it and defaulting a blank key to the current key (edit) / the model's slug-derivation (create); (b) retires the legacy `product.*` permission group — `PermissionCatalog` drops it (51 → 47 keys), `Admin\ProductController` + `Admin\ProductCategoryController` now gate on `products.view` (read) / `products.manage` (all writes) so **Package Manager** (SRS §105) can manage the catalog; `RolePermissionSeeder` gained a prune step (`Permission::whereNotIn('name', catalog)->delete()`) and was re-run on the dev DB (47 perms, 0 `product.*`). Sidebar `can` key updated. +6 tests (`Feature/Admin/ProductAdminTest`; suite 103). No migration. *(SRS §6, §52, §105)*
- [x] FEAT-001 — [CP] Reusable feature catalog. **Distinct-table decision (recorded):** the marketing `product_features` / `service_features` tables are per-product bullet-list repeaters (icon + text, no key/price/schema) — unrelated to an entitlement catalog, so `features` is its own table, no fold-in. Migration `2026_09_09_000007` `features` (`name`, `key` [unique machine id — SRS §52 calls it `key`, the checklist's `machine_key`; packages/sync/Vendor API bind to it], `type` [default `module`, indexed], `description`, `default_price` decimal(12,2) default 0, `version` default `1.0.0`, `status` [default `active`, indexed], `dependencies` json [feature-key list], `config_schema` json [SRS §79 per-package config shape], `documentation` text). `App\Models\Feature` (`use Auditable, HasFactory`; `STATUSES` active/inactive/deprecated, `TYPES` core/module/integration/analytics/marketing/custom; `array` casts for `dependencies`/`config_schema`, `decimal:2` for `default_price`; `key` auto-derived from `name` via `Str::slug(_)` on create when blank; `status()` / `active()` / `type()` scopes; `dependencyFeatures()` resolves the stored keys to catalog rows; route key = `key`). `FeatureFactory` (`inactive()` / `deprecated()` / `type()` / `dependsOn([])` / `withConfigSchema()` states). Registered in `AuditLogController::ENTITY_TYPES` so it's filterable in the audit viewer. Admin CRUD + dependency/schema validation → FEAT-002; catalog seed → FEAT-003. +11 tests (`Unit/FeatureTest` 8, `Feature/Foundation/FeatureCatalogTest` 3; suite 114). Migrated on the MySQL dev DB. *(SRS §7, §52, §79)*
- [x] FEAT-002 — [CP] Feature admin CRUD + dependency resolution + config-schema editor & validator. `Admin\FeatureController` (`HasMiddleware` — `can:features.view` index / `can:features.manage` writes; **Package Manager** holds both, SRS §105) — search (name/key/description), type + status filters, sortable columns, create/edit/update, `PATCH {feature}/status` (any of `Feature::STATUSES`), `destroy` (**blocked** while another feature lists it in `dependencies`). `admin.features.*` routes (wildcard `{feature}` — bound by `key` — last, after `create`) + `admin/pages/features/{index,_form,create,edit}.blade.php` + "Feature Catalog" sidebar entry (`bi-puzzle-fill`, `@can features.view`). Form: name/key (auto-slug `_`, `regex:/^[a-z0-9_]+$/`, unique)/type/status/version/default_price/description, a **multi-select dependency picker** over other catalog keys, a monospace **config-schema JSON textarea** (pretty-printed on edit), documentation. New `App\Services\Feature\FeatureDependencyResolver` (`missing()` — unknown keys; `detectCycle()` — DFS over the catalog graph with the proposed edges, returns the offending path; `resolve()` — full transitive dependency set). Controller `after()` hook rejects self-deps, unknown keys and circular deps on their field; config-schema must be valid JSON, an object (not a list), and declare `type` or `properties` (SRS §79 — per-package *value* validation against the schema is PKG-003/PKG-009). No new permission keys (`features.view`/`features.manage` already in `PermissionCatalog`, already on `package-manager`/`sales-manager`/`developer` per RBAC). `Feature` already `Auditable` + in `ENTITY_TYPES` (FEAT-001). +16 tests (`Unit/FeatureDependencyResolverTest` 6, `Feature/Admin/FeatureAdminTest` 10; suite 130). No migration. *(SRS §7 FR-003, §79, §105)*- [ ] FEAT-003 — [CP] Seed the Ecommerce feature catalog (payment_gateway, courier, advanced_inventory, seo, facebook_pixel, google_analytics, sms_gateway, whatsapp, custom_checkout, custom_invoice, restaurant_module, multi_vendor, wishlist, coupon, advanced_reporting). *(SRS §7)*
- [ ] SVC-001 — [CP] Service catalog table (name, key, type, description, default_price, billing_type, status) + model + factory — use a distinct name/discriminator vs. the marketing `services` table (record the decision). *(SRS §7, §90)*
- [ ] SVC-002 — [CP] Service catalog admin CRUD (dynamic add; one-time vs recurring billing).
- [ ] SVC-003 — [CP] Seed the service catalog (domain, hosting, ssl, backup, maintenance, support, seo_service, custom_development, payment_gateway_setup, courier_setup).
- [ ] P1-GATE — [CP] Phase 1 gate (WORK-INSTRUCTIONS §9).

---

## Phase 2 — Package Management  (0 / 10)

- [ ] PKG-001 — [CP] `customer_packages` table (customer_id, product_id, package_code, name, description, status, package_version, billing_type, billing_amount, billing_cycle, contract_start/end_date, ownership_type, ownership_status) + model + factory. *(SRS §8, §52)*
- [ ] PKG-002 — [CP] Package builder screen — create / clone / edit a customer package. *(SRS §78)*
- [ ] PKG-003 — [CP] `customer_package_features` (package_id, feature_id, custom_price, enabled, configuration json, feature_version) + transactional `PackageBuilderService::syncFeatures()` + feature picker with dependency enforcement.
- [ ] PKG-004 — [CP] `customer_package_services` (package_id, service_id, custom_price, billing_type, enabled, configuration) + sync + service picker.
- [ ] PKG-005 — [CP] Package pricing model — default vs customer-specific price, discount, tax, one-time / recurring / installment; computed totals. *(SRS §91)*
- [ ] PKG-006 — [CP] `package_versions` table (package_id, version, change_type, change_summary, snapshot json, created_by) + auto-increment + snapshot on every config change. *(SRS §74)*
- [ ] PKG-007 — [CP] Package lifecycle states + valid transitions (draft → active → suspended / completed / cancelled). *(SRS §78)*
- [ ] PKG-008 — [CP] Package amendment tracking (upgrade / downgrade / additional service / contract revision) — never overwrite the accepted baseline. *(SRS §93)*
- [ ] PKG-009 — [CP] Feature-config schema validation + encryption of sensitive config values at rest. *(SRS §79)*
- [ ] P2-GATE — [CP] Phase 2 gate.

---

## Phase 3 — Offer & Billing  (0 / 14)

- [ ] OFFER-001 — [CP] `package_offers` + `package_offer_items` tables. *(SRS §53)*
- [ ] OFFER-002 — [CP] Package card / offer generator — all SRS §9.1 card fields, snapshotted line items. *(SRS §9, §10)*
- [ ] OFFER-003 — [CP] Offer lifecycle (draft → sent → viewed → accepted → payment_pending → partially_paid → development → ready_for_deployment → deployed → active → completed; + cancelled / rejected / expired) with valid-transition enforcement + audit. *(SRS §11)*
- [ ] OFFER-004 — [CP] Customer-facing offer view + acceptance (tokenized link, "viewed" tracking, accept action).
- [ ] OFFER-005 — [CP] Contract snapshot on acceptance — freeze package / features / services / prices / terms / version / date; global price changes never mutate an accepted contract. *(SRS §91, §92)*
- [ ] INV-001 — [CP] `invoices` + `invoice_items` tables + invoice states (draft → issued → partially_paid → paid → overdue → cancelled → refunded). *(SRS §42)*
- [ ] INV-002 — [CP] Invoice generation per billable event (package purchase, development, customization, monthly license, hosting, maintenance, additional feature, upgrade, renewal) + printable/PDF. *(SRS §42)*
- [ ] PAY-001 — [CP] `payments` table (customer_id, package_id, invoice_id, payment_reference, amount, currency, payment_type, payment_method, status, due_date, paid_at, transaction_reference, notes) — evolve `license_transactions` additively. *(SRS §41)*
- [ ] PAY-002 — [CP] Payment states + transitions (pending → processing → paid / partially_paid / failed / cancelled / refunded / overdue) + idempotent confirmation. *(SRS §41, §117)*
- [ ] PAY-003 — [CP] Payment recording UI + receipt + audit; wire `ManualBkashPaymentProvider` behind the new model.
- [ ] BILL-001 — [CP] Billing configuration on a package (billing_cycle, amount, next_due_date, grace_period_days, contract dates, total/completed installments, remaining_amount). *(SRS §44)*
- [ ] BILL-002 — [CP] Recurring invoice generation job — scheduled, idempotent, per active subscription. *(SRS §97)*
- [ ] BILL-003 — [CP] Separate one-time (setup / dev) vs recurring (software / maintenance) revenue lines. *(SRS §43)*
- [ ] P3-GATE — [CP] Phase 3 gate (SRS §109 payment testing).

---

## Phase 4 — Requirement & Development  (0 / 8)

- [ ] REQ-001 — [CP] `customer_requirements` table (customer_id, product_id, package_id, title, description, priority, estimated_cost, estimated_days, status, requested/approved/completed_at). *(SRS §54)*
- [ ] REQ-002 — [CP] Requirement lifecycle (requested → analysis → quoted → customer_approval → payment → development → testing → released → deployed → completed) + admin CRUD. *(SRS §12)*
- [ ] REQ-003 — [CP] Customer-facing "request a feature / change" form → requirement; quote → offer / amendment link. *(SRS §125)*
- [ ] CDEV-001 — [CP] `custom_developments` table (requirement_id, customer_id, package_id, branch_name, repository, base_version, target_version, developer, status, started/completed_at). *(SRS §55)*
- [ ] CDEV-002 — [CP] Custom-dev tracking UI — link every modification to requirement / customer / package / developer / branch / commit / version / release / test status / deploy status / rollback version. *(SRS §13)*
- [ ] GIT-001 — [CP] Git integration config + branch-naming enforcement (`feature/customer-<id>-*`, `customer/<id>/*`) + commit/branch capture on a custom-dev record. *(SRS §26)*
- [ ] GIT-002 — [CP] Git provider link (repo URL, commit lookup, optional webhook to advance custom-dev status).
- [ ] P4-GATE — [CP] Phase 4 gate.

---

## Phase 5 — Release Management  (0 / 9)

- [ ] REL-001 — [CP] `releases` table (product_id, version, release_type, git_repository, git_branch, git_commit, release_notes, checksum, signature, migration_required, minimum_vendor_client_version, status, created_by). *(SRS §56)*
- [ ] REL-002 — [CP] Release creation workflow (code → review → tests → security checks → build → RC → QA → production release); no deploy from an arbitrary working tree. *(SRS §27, §106)*
- [ ] REL-003 — [CP] Release artifact builder (app source + lockfiles + compiled assets + migrations + manifest + checksum + signature + metadata; **exclude** env secrets). *(SRS §28)*
- [ ] REL-004 — [CP] Release signing + checksum verification helpers (shared with the Vendor Client). *(SRS §28, §121)*
- [ ] REL-005 — [CP] Release channels (stable / beta / customer_specific / hotfix) + hotfix flow. *(SRS §123, §124)*
- [ ] REL-006 — [CP] Release ↔ package compatibility matrix (product / package / current / target version / vendor-client version / DB version / dependencies) checked before an install is offered. *(SRS §122, §138)*
- [ ] REL-007 — [CP] Customer-specific release isolation — a release is authorized for a specific customer/package; unauthorized installs cannot download or activate it. *(SRS §81, §82)*
- [ ] REL-008 — [CP] Short-lived signed release-download authorization — releases never permanently public. *(SRS §121)*
- [ ] P5-GATE — [CP] Phase 5 gate.

---

## Phase 6 — Vendor Client & Sync  (0 / 20)

- [ ] APISEC-001 — [CP] Vendor API skeleton `/api/vendor/v1` + `doc/backend/API-CONTRACT.md` — request envelope (installation_id, request_id, timestamp, client_version, signature), standard signed response, business-vs-technical error classification. *(SRS §64, §71, §72)*
- [ ] APISEC-002 — [CP] Request signing + verification, timestamp / replay-window validation, request-id dedupe, per-endpoint rate limits. *(SRS §63, §70, §120)*
- [ ] APISEC-003 — [CP] Response signing + key management + key rotation. *(SRS §61, §63)*
- [ ] INST-001 — [CP] `installations` table (customer_id, product_id, package_id, installation_id, domain, environment, vendor_client_version, software_version, status, last_heartbeat/verified/deployed_at) — supersede `license_activations` additively. *(SRS §57)*
- [ ] INST-002 — [CP] `POST /installations/register` + `/verify` + `GET /installations/{id}` + `POST /installations/heartbeat`; installation binding to customer / product / domain / identity; domain change requires revalidation. *(SRS §38, §65)*
- [ ] LIC-CP-001 — [CP] Extend `licenses` to the SRS §59 shape (+ installation_id, issued/starts/expires_at, grace_period_days, next_payment_due, ownership_status) + full lifecycle states (pending → active → payment_due → grace_period → suspended → reactivated → completed → revoked → expired). *(SRS §39, §40)*
- [ ] LIC-CP-002 — [CP] `POST /licenses/verify` + `/renew` + `/status` — signed license response. *(SRS §61, §66)*
- [ ] SYNC-001 — [CP] `GET /packages/{id}` + `/packages/{id}/features` + `POST /packages/sync` + `/packages/acknowledge` — signed, versioned, idempotent package-sync payload. *(SRS §19, §67, §117)*
- [ ] SYNC-002 — [CP] Package-version bump → sync trigger + acknowledgement tracking; metadata/config change needs no source deploy. *(SRS §18, §24, §74)*
- [ ] UPD-001 — [CP] `GET /updates/check` + `GET /releases/{id}` + `POST /releases/{id}/download-authorize` + `/verify`. *(SRS §68)*
- [ ] VC-001 — [VC] Vendor Client package / module scaffold in the customer product (`codexehub/vendor-client` or internal Laravel module) — config, service provider, contracts. *(SRS §15, §131, §132)*
- [ ] VC-002 — [VC] Installation registration + license-verification client + signed-response verification + local verified cache. *(SRS §16, §60)*
- [ ] VC-003 — [VC] Offline / grace verification — signed local cache; "API unavailable ≠ payment failure"; configurable verification grace. *(SRS §62)*
- [ ] VC-004 — [VC] `codexehub_*` local tables (`codexehub_installations`, `codexehub_packages`, `codexehub_package_features`, `codexehub_feature_configs`, `codexehub_sync_logs`) + transactional sync + safe cache refresh. *(SRS §20, §115, §116)*
- [ ] VC-005 — [VC] Central entitlement service — `Codexehub::featureEnabled()` / `featureConfig()` / `featureLimit()` off the local verified cache. *(SRS §17, §23)*
- [ ] VC-006 — [VC] Heartbeat + health reporting job. *(SRS §16, §87)*
- [ ] ENT-001 — [VC] Ecommerce "Codexehub" admin menu (My Package / Features / License / Subscription / Software Version / Updates / Custom Requests / Support). *(SRS §21)*
- [ ] ENT-002 — [VC] "My Package" screen (package, status, version, billing, next payment, ownership, features, last verification). *(SRS §22)*
- [ ] ENT-003 — [VC] Feature enforcement across the ecommerce app — disabled features unusable; config + limits from the cache. *(SRS §23)*
- [ ] P6-GATE — [CP]+[VC] Phase 6 gate (SRS §110 license testing, §111 package-sync testing).

---

## Phase 7 — Deployment  (0 / 13)

- [ ] DEP-001 — [CP] `deployments` table (installation_id, release_id, deployment_method, deployment_status, started/completed_at, previous/new_release, backup_id, error_message, triggered_by). *(SRS §58)*
- [ ] DEP-002 — [CP] Deployment state machine (pending → authorized → downloading → verifying → backup → preparing → installing → migrating → health_check → activating → success / failed → rolling_back → rolled_back). *(SRS §88)*
- [ ] DEP-003 — [CP] `DeploymentDriverInterface` — `check / backup / deploy / activate / rollback / healthCheck` → `DeploymentResult`. *(SRS §36)*
- [ ] DEP-004 — [CP] `POST /deployments/authorize` + `/report` + `/health` + `/rollback`; update authorization checks customer + installation + package + license + release compatibility. *(SRS §69, §82)*
- [ ] DEP-005 — [CP] Deployment approval modes (automatic / manual / scheduled / approval_required) per package. *(SRS §83)*
- [ ] DEP-006 — [VC] `VpsGitDeploymentDriver` — release-based `current → releases/N` + `shared/` + `backups/`; 16-step deploy workflow. *(SRS §31, §32)*
- [ ] DEP-007 — [VC] Backup step (app + DB + uploads + config) + `backups` records with checksum + retention. *(SRS §34)*
- [ ] DEP-008 — [VC] Migration runner + maintenance mode; versioned, logged, per-migration rollback stance. *(SRS §84, §85)*
- [ ] DEP-009 — [VC] Health check `GET /health` (HTTP, DB, boot, config, storage, cache, queue, vendor client, license, package, critical feature). *(SRS §87)*
- [ ] DEP-010 — [VC] Activation gate — a site becomes ACTIVE only after all validation checks pass, not on file upload. *(SRS §37)*
- [ ] DEP-011 — [VC] Rollback — deactivate new release, restore previous, DB rollback where safe, health check, report. *(SRS §33, §140)*
- [ ] DEP-012 — [VC] Queue / worker + scheduler refresh after deploy. *(SRS §86)*
- [ ] P7-GATE — [CP]+[VC] Phase 7 gate (SRS §112 deployment acceptance, §113 rollback acceptance).

---

## Phase 8 — cPanel  (0 / 6)

- [ ] CPANEL-001 — [VC] `CpanelGitDeploymentDriver` (Method A) — cPanel Git → controlled deploy script. *(SRS §35)*
- [ ] CPANEL-002 — [VC] `PackageDeploymentDriver` (Method B) — signed release package → verify → extract to `releases/` → migrate → health → activate. *(SRS §35)*
- [ ] CPANEL-003 — [VC] cPanel cron / queue mechanism support. *(SRS §86)*
- [ ] CPANEL-004 — [VC] cPanel storage / SSL / domain checks folded into activation + health. *(SRS §114)*
- [ ] CPANEL-005 — [CP] Deployment-driver selection per installation (`deployment_driver` config). *(SRS §132)*
- [ ] P8-GATE — [CP]+[VC] Phase 8 gate (SRS §114 cPanel acceptance).

---

## Phase 9 — License Enforcement  (0 / 8)

- [ ] LIC-001 — [CP] Automatic license-state job — per active subscription: current → active; due passed → payment_due; within grace → grace_period; past grace → suspended; every transition logged + audited. *(SRS §98)*
- [ ] LIC-002 — [CP] Payment-due detection + grace-period calculation from `next_payment_due` + `grace_period_days`. *(SRS §45)*
- [ ] LIC-003 — [CP] Suspension processing job + `PaymentOverdue` / `LicenseSuspended` events + notifications. *(SRS §89, §97)*
- [ ] LIC-004 — [CP] Reactivation flow — payment confirmed → license active → sync → entitlement refresh → unlock → health check; auditable. *(SRS §47, §100)*
- [ ] LIC-005 — [VC] Configurable site-lock policy (full_lock / storefront_lock / admin_lock / read_only / maintenance_mode); **never deletes customer data**; clear customer-facing message. *(SRS §46, §99)*
- [ ] LIC-006 — [VC] Lock / unlock driven by the signed license state from the cache; technical error ≠ lock. *(SRS §62, §72)*
- [ ] LIC-007 — [CP] Payment-recovery audit trail + reactivation history. *(SRS §47)*
- [ ] P9-GATE — [CP]+[VC] Phase 9 gate (SRS §109, §110).

---

## Phase 10 — Ownership  (0 / 7)

- [ ] OWN-001 — [CP] `ownership_records` table (customer_id, package_id, ownership_type, ownership_status, total_contract_amount, total_paid, remaining_amount, required/completed_payment_count, ownership_effective_date, contract_reference, completed_by/at). *(SRS §51)*
- [ ] OWN-002 — [CP] Ownership models on a package (license_only / perpetual_license / installment_to_own / full_ownership_transfer / custom). *(SRS §49)*
- [ ] OWN-003 — [CP] Installment tracking (N/12 payments, outstanding balance, per package). *(SRS §48)*
- [ ] OWN-004 — [CP] Ownership-completion job — outstanding = 0 AND required payments completed AND contract conditions satisfied → ownership_status = completed → generate the completion record; idempotent. *(SRS §101, §117)*
- [ ] OWN-005 — [CP] Ownership-completion workflow per model — remove recurring fees / mark customer-owned / provide source access / deployment package / docs / disable enforcement where contractually applicable; **never transfer IP automatically** unless the package terms require it. *(SRS §50, §94, §102)*
- [ ] OWN-006 — [CP]+[VC] Ownership-completion UI — admin summary + the ecommerce-side "Customer Owned / recurring: none" state. *(SRS §102)*
- [ ] P10-GATE — [CP] Phase 10 gate (SRS §142 AC-028 / AC-029).

---

## Phase 11 — Production Hardening  (0 / 14)

- [ ] CPDASH-001 — [CP] Codexehub admin dashboard — total customers, active/suspended sites, payment due, grace, active packages, pending offers, custom requests, active developments, pending/failed deployments, upcoming renewals, MRR, outstanding payments, completed ownerships. *(SRS §76)*
- [ ] CPDASH-002 — [CP] Customer detail page — fill every SRS §77 tab with real data.
- [ ] NOTIF-001 — [CP] Notification system — offer created/expiring, payment due/received, feature activated, custom-dev completed, deployment completed/failed, update available, license grace/suspended/reactivated, ownership completed; channels email / dashboard / SMS / WhatsApp per enabled services. *(SRS §89)*
- [ ] SCHED-001 — [CP] Scheduler wiring — payment reminders, invoice generation, overdue detection, grace calc, license-state changes, package sync, deployment checks, heartbeat monitoring, release checks, backup retention, notification processing. *(SRS §97, §135)*
- [ ] MON-001 — [CP] Monitoring views — offline installations, unverified licenses, failed deployments, suspended sites, overdue sites, outdated vendor clients, outdated software, failed package syncs. *(SRS §141)*
- [ ] OBS-001 — [CP] Observability — application / API / deployment / license / payment / package-sync / audit / health logs, each with a correlation ID. *(SRS §118)*
- [ ] REP-001 — [CP] Reporting — customer, revenue (one-time / recurring / outstanding / overdue / completed contracts), deployment, license reports. *(SRS §119)*
- [ ] HARD-001 — [CP]+[VC] Full security test suite — authN, authZ, API signature, domain validation, installation binding, SQLi, XSS, CSRF, file upload, rate limiting, sensitive-info exposure. *(SRS §63, §70, §108)*
- [ ] HARD-002 — [CP]+[VC] Performance pass — lightweight API responses, indexed queries, local-cache entitlement checks, large-file release download path. *(SRS §129, §136)*
- [ ] HARD-003 — [CP] Disaster recovery — backups of package / payment / license / release / deployment / audit records; documented restore. *(SRS §139)*
- [ ] HARD-004 — [CP] Environment separation (local → dev → staging → production); customer production never a dev environment. *(SRS §107)*
- [ ] HARD-005 — [CP]+[VC] Documentation set — admin guide, API contract, vendor-client install guide, deployment runbook, upgrade notes, ownership-transfer runbook. *(SRS §144 P11)*
- [ ] VC-VER-001 — [CP]+[VC] Vendor Client versioning + minimum-supported-version enforcement. *(SRS §103, §104)*
- [ ] P11-GATE — [CP] Phase 11 gate.

---

## GA — Acceptance  (0 / 3)

- [ ] GA-E2E-001 — [CP]+[VC] SRS §143 end-to-end acceptance scenario (create customer → assign product → build package → add features → set prices → generate offer → accept → record payment → requirement → develop → commit → QA → release → deploy → validate → activate → package sync → ecommerce shows package → feature checks → monthly billing → non-payment → grace → suspend → site lock → payment → reactivate → complete payments → ownership completed) as an automated + manually-verified journey.
- [ ] GA-AC-001 — [CP]+[VC] SRS §142 AC-001..AC-030 traceability matrix — every AC demonstrably passing.
- [ ] GA-GATE — [CP]+[VC] v1.2 production-ready sign-off (SRS §145 Definition of Done).

---

## Notes

- Task IDs beyond the current phase are provisional — split per WORK-INSTRUCTIONS §3 / §4 when work
  begins on that phase.
- **[VC]** tasks execute in the customer product repo (`ecom-single-vendor`) against its own trackers,
  but their API contract + acceptance criteria are owned here (`doc/backend/API-CONTRACT.md`).
- Do not jump ahead to a later phase while an earlier phase's critical work is incomplete unless the
  developer explicitly changes the plan (WORK-INSTRUCTIONS §6).
- "GATE" items are the per-phase release-gate checklist of WORK-INSTRUCTIONS §9.
- The SRS is v1.2 and may change — any SRS edit that adds/moves scope must bump the SRS version +
  changelog and be reflected here in the same session.
