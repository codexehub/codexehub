# WORK-INSTRUCTIONS.md
# AI Development Execution Rules — Codexehub Control Plane & Licensing Platform

**Document Version:** 1.0.0
**Last Updated:** 2026-09-09
**Primary Command:** `next`
**Source of Truth (SRS):** `D:\Personal\Drive\Obsidian\Projects\Codexehub\Codexehub Customer-Specific Software, Package, Licensing, Development & Remote Deployment Platform.md` (v1.2)
**Operational tracker:** `doc/backend/PROJECT-STATUS.md`
**Whole-project checklist:** `doc/backend/CHECKLIST.md`
**Last task detail:** `doc/backend/LAST-TASK-SUMMARY.md`
**Project history:** `CLAUDE.md` (History Summary + Last 5 Actions — repo root)

> Every edit to this file MUST bump **Document Version** + **Last Updated** and add a §26 Change Log line
> (semver: patch = wording, minor = new/expanded rule, major = rule removed/reversed).

---

## 1. What this project is

`codexehub` is a **Laravel 12 marketing site + admin panel** that is being grown into the
**Codexehub Control Plane** described in the SRS: a centralized platform for selling one reusable
software product (first: **Ecommerce**) to many customers, each with an individually configured
**package** (features, services, pricing, payment plan, license terms, ownership model), plus the
**Vendor API** that customer installations talk to for licensing, package sync, updates and
deployment reporting.

The SRS spans **two codebases**:

| Layer | Repo | Role |
|---|---|---|
| **Control Plane** | `codexehub` (this repo) | Business control plane — customers, packages, offers, billing, releases, deployments, licenses, ownership, Vendor API, admin. |
| **Vendor Client** | the customer product repo(s) — e.g. `ecom-single-vendor` | Secure communication + execution layer inside the customer app: registration, license verification, entitlement cache, package/feature sync, deploy/rollback, health, site-lock. |

Tasks in `doc/backend/CHECKLIST.md` are tagged **[CP]** (this repo) or **[VC]** (customer product repo).
A **[VC]** task is executed in that product's repo against its own trackers, but its API contract and
acceptance criteria are owned here.

Core architectural principle (SRS §147): **Codexehub is the source of truth for package, entitlement,
licensing, billing, release authorization and ownership. The customer server is the execution
environment.** Package/config changes flow through the Vendor API; source changes flow through the
Git → Release → Deployment pipeline; payment state controls licensing per the customer's agreed terms.

---

## 2. Always take the last working summary

Before doing anything on a `next`:

1. Read `CLAUDE.md` → **History Summary** + **Last 5 Actions** (repo root — the running narrative).
2. Read `doc/backend/PROJECT-STATUS.md` (current phase / current task / test status / known issues).
3. Read `doc/backend/LAST-TASK-SUMMARY.md` (full detail of the most recent task).
4. Read only the relevant SRS section(s) and only the code files the task touches.

Do **not** re-read the whole SRS, whole source files, or the whole checklist on every `next`.

---

## 3. The `next` command

`next` = execute **exactly one** meaningful task, then stop.

```
next
 → read the trackers (§2)
 → find the first unfinished task in phase order (§6) whose dependencies are done
 → read only the relevant SRS section + code
 → implement the smallest complete change
 → run focused tests
 → update trackers (§10) + CLAUDE.md (§11)
 → short report
 → STOP
```

`next` is **not**: build a whole phase, build a whole module, refactor broadly, or emit a 30-task plan.
If a task is too big for one focused cycle, split it (record the sub-tasks in the checklist) and do
only the first sub-task.

Other single-word commands: `status` (§12), `plan` (planning allowed, no code), `skip` (mark `[~]`
with a reason, never delete), `test`, `fix`, `release` (run the phase gate — §9), `deploy` (never run
destructive production commands without explicit authorization).

---

## 4. Task IDs

Every task has an ID: `<PREFIX>-<nnn>`. Prefixes by area (see CHECKLIST for the full map):

```
Phase 1  CUST- PROD- FEAT- SVC- RBAC-
Phase 2  PKG-
Phase 3  OFFER- INV- PAY- BILL-
Phase 4  REQ- CDEV- GIT-
Phase 5  REL-
Phase 6  INST- VC- SYNC-
Phase 7  DEP-
Phase 8  CPANEL-
Phase 9  LIC-
Phase 10 OWN-
Phase 11 HARD-
Cross    AUDIT- NOTIF- APISEC- SCHED- CPDASH- (control-plane dashboard) ENT- (ecom entitlement/UI/site-lock, [VC])
```

Never delete a task line. Split → replace with sub-tasks and adjust totals. Skip → `[~] reason`.

---

## 5. Source-of-truth hierarchy

```
1. Explicit current user instruction
2. SRS
3. Current phase scope (§6)
4. doc/backend/PROJECT-STATUS.md
5. Existing architecture / code (esp. the existing licensing subsystem — §8)
6. Other project docs
7. AI judgment
```

Never override an explicit user instruction with an assumption. If the SRS conflicts with an explicit
instruction, follow the instruction and note it in the trackers.

---

## 6. Phase order (SRS §144)

```
P1  Foundation          → Customer, Product, Feature, Service, Auth, Roles, Permissions
P2  Package Management   → Package builder, customer package, feature/service assignment, pricing, versioning
P3  Offer & Billing      → Package card, offer lifecycle, invoices, payments, schedules, recurring billing
P4  Requirement & Dev    → Customer requests, custom-dev tracking, Git traceability
P5  Release Management    → Releases, versions, checksum, signature, compatibility, channels
P6  Vendor Client        → Installation registration, license verification, package/feature sync, health, heartbeat
P7  Deployment           → VPS/Git driver, release-based deploy, backup, migration, health check, rollback
P8  cPanel               → cPanel Git + release-package drivers, cron/queue, storage, SSL, health
P9  License Enforcement   → Payment-due detection, grace period, suspension, site-lock policy, reactivation
P10 Ownership            → Installment tracking, final payment, ownership completion + records
P11 Production Hardening  → Security, performance, monitoring, logging, backup, DR, docs
```

Cross-cutting tracks (audit log, notifications, Vendor API security, scheduler, control-plane
dashboard) advance alongside the phases — a phase's gate requires its cross-cutting slice done.

Do not jump to a later phase while an earlier phase's critical work is unfinished unless the user
explicitly changes the plan.

---

## 7. `next` task-selection algorithm

1. Read trackers (§2).
2. Determine current phase from `PROJECT-STATUS.md`.
3. Find the first unfinished task in that phase (checklist order) whose dependencies are complete.
4. If every task in the phase is done → run the phase gate (§9); if the gate passes, advance the phase.
5. Check the SRS section for that task.
6. Search existing code first (§8) — reuse, don't duplicate.
7. Implement only that task, smallest complete change.
8. Run focused tests (§13).
9. Update trackers (§10) + CLAUDE.md (§11).
10. Short report (§12), stop.

---

## 8. Existing licensing subsystem — reuse, don't rebuild

A first-pass licensing system already exists (commit `1db4faf` "license key add"). **Extend it toward
the SRS; do not throw it away without a recorded decision.**

Present today:

- Tables: `licensed_apps`, `licensed_app_packages` (flat, global — `price`, `duration_days`,
  `max_activations`, `features` JSON), `license_customers`, `licenses`
  (`status` enum pending/active/suspended/expired/revoked), `license_activations`
  (domain + fingerprint, unique per license), `license_transactions` (manual bKash, approve/reject).
- Models for all of the above; `App\Services\LicenseTokenService` (RS256 JWT, `config/licensing.php`);
  `App\Services\Payments\{PaymentProvider, ManualBkashPaymentProvider}`.
- Admin: `Admin\Licensing\{LicensedApp, LicensedAppPackage, LicenseCustomer, License, LicenseTransaction}Controller`
  under `/admin/licensing/*` (license suspend / reactivate / revoke; transaction approve / reject).
- API: `POST /api/v1/license/activate` + `POST /api/v1/license/verify` (`LicenseApiController`) —
  key + product_slug + domain + fingerprint → signed token + package features.

Gap vs. SRS (drives the checklist): no customer-specific packages or package versioning; no offers;
no invoices / payment states / recurring billing / schedules; no requirements or custom-dev tracking;
no releases or deployment pipeline; vendor "client" is ad-hoc calls, not a versioned client with
registration / heartbeat / health / package-sync / offline-grace; license lifecycle missing
`payment_due` / `grace_period` / automated transitions / configurable site-lock modes; no ownership
model; no audit log; no request signing / replay protection / rate limiting on the Vendor API; no
tests; no scheduler.

When a phase task supersedes an existing table/flow, prefer an **additive migration + a documented
migration path** over a destructive rewrite (§16).

---

## 9. Phase gate (`release` / end of a phase)

A phase is not done until, for its scope:

```
Feature complete
  → Code review (thin controllers, services own rules, no duplication)
  → Tests (focused + phase regression; see §13)
  → Security review (SRS §63 / §70 / §108 — signing, replay, domain+installation binding,
      rate limiting, authorization, customer isolation §80/§81, no arbitrary remote shell §30)
  → Idempotency review (SRS §117 — sync / payment / deploy-report / renewal / ownership must not double-apply)
  → Data-safety review (transactional sync §115; additive migrations §16; no customer business data copied centrally §95)
  → Docs updated (API contract, admin guide, CLAUDE.md history)
  → PROJECT-STATUS phase entry marked gate-passed
```

The **GA gate** additionally requires the SRS §143 end-to-end acceptance scenario to pass and every
SRS §142 AC-001..AC-030 to be demonstrable.

---

## 10. Tracker updates (every task)

In the same step that finishes the code:

1. `doc/backend/CHECKLIST.md` — tick the box; recompute the **Progress** table
   (`round(done / total * 100)` for each scope row).
2. `doc/backend/PROJECT-STATUS.md` — move Current Task → prior, set the new Current Task, update
   Test Status / Known Issues / Next Recommended Task.
3. `doc/backend/LAST-TASK-SUMMARY.md` — replace with this task's detail
   (what landed, schema/API changes, security notes, tests, migration/upgrade notes, out-of-scope).
4. `doc/backend/CHANGELOG-BACKEND.md` — one line per task under the current phase heading.

If two Claude sessions run in this repo at once, make tracker edits append/prepend-style, re-read
immediately before editing, and flag "working tree needs review before commit" in the report.

---

## 11. CLAUDE.md maintenance (this project's own rule)

`CLAUDE.md` says: *"Summarize History everytime in Claude.md. Also Update store last 5 Action you take."*
After each task:

- Append a paragraph to **## History Summary** describing what changed and why (same voice as the
  existing entries — dense, specific, decisions + rationale).
- Rewrite **## Last 5 Actions** so item 1 is this task and the oldest drops off.

This is in addition to §10, not instead of it.

---

## 12. Reporting

Success:

```
Completed: PKG-003 — Customer package feature assignment

Changed:
- migration: customer_package_features (custom_price, enabled, configuration json, feature_version)
- CustomerPackage::features() ; PackageBuilderService::syncFeatures() (transactional)
- admin: package builder feature picker

Tests: Feature/Package/CustomerPackageFeatureTest — PASS (n new)

Next: PKG-004 — Customer package service assignment
```

`status`:

```
Phase:  Current task:  Completed (phase):  Remaining (phase):  Blocked:  Tests:  Next:
```

Blocked:

```
Blocked: <ID>
Reason: <what is missing — credential, decision, upstream task>
Already done: <...>
No unrelated changes made.
```

Keep it short. The trackers + CLAUDE.md hold the detail.

---

## 13. Testing

- This repo currently has **only scaffold tests** (`tests/{Feature,Unit}/ExampleTest.php`). The first
  hardening-track task (`HARD-000`) stands up Pest/PHPUnit properly and a CI-runnable `php artisan test`.
- From then on, **every task ships focused tests**: model/service unit tests + a feature test through
  the real route/middleware.
- **Vendor API tasks** additionally get contract tests: signature verify/reject, replay rejection,
  domain/installation binding, rate-limit, idempotency, offline-grace behaviour.
- Run focused tests during development; run the phase regression suite at the gate (§9).
- Never report "done" while the task's tests fail. Unrelated pre-existing failures → record in
  PROJECT-STATUS Known Issues, don't hide.

---

## 14. Architecture rules

- **Controllers thin** — `Request → Controller → Service/Action → Model → Response`. Business
  workflows live in `app/Services/*` (SRS §130), not controllers or models.
- **Domain layout** (SRS §130): group services by domain — `Customer`, `Package`, `Feature`,
  `Service`, `Requirement`, `Billing`, `License`, `Ownership`, `Release`, `Deployment`, `Installation`.
- **Form Requests** for all external input. Server-side validation is mandatory; feature config is
  validated against the feature's schema (SRS §79).
- **Events + Jobs** (SRS §133 / §134) — long-running or fan-out operations (sync, verify, download,
  deploy, health check, payment reminder, suspension, ownership completion) are queued jobs; state
  changes emit events.
- **Policies / Gates + `permission:` middleware** for every admin and Vendor-API operation
  (SRS §105 roles: Super Admin, Sales, Package, Finance, Developer, QA, Deployment, Support — least
  privilege). Do not rely on a hidden button. The current ad-hoc `$user->isAdmin()` Gates are
  replaced by the RBAC module in Phase 1 (`RBAC-*`).
- **Idempotency keys** on sync / payment-confirm / deploy-report / renewal / ownership endpoints
  (SRS §117).
- **Indexes** (SRS §136) on `customer_id`, `product_id`, `package_id`, `installation_id`,
  `license_key`, `domain`, `status`, `*_due_date`, `version`, `created_at`; composite where the
  query pattern justifies it.

---

## 15. Vendor API rules (SRS §63–§72, §120, §121)

- Prefix `/api/vendor/v1`. Version the API; keep backward compatibility per the SRS §138 matrix.
- Every request carries `installation_id`, `request_id`, `timestamp`, `client_version`, signature.
  The server validates installation + customer + product + domain + license + request freshness
  (replay window) + auth + authorization before acting.
- Responses are **signed**; the client verifies the signature before trusting them (SRS §61, §71).
- Distinguish **business state** (`LICENSE_SUSPENDED`, `PAYMENT_DUE`, …) from **technical error**
  (`API_TIMEOUT`, `NETWORK_ERROR`, `SIGNATURE_ERROR`, …). A technical error must never be treated as
  suspension (SRS §72, §62).
- Per-endpoint rate limits (SRS §120). Large release files are **not** streamed through JSON —
  short-lived signed download authorization instead (SRS §121).
- **No arbitrary remote shell.** The Vendor Client exposes only the controlled operations in
  SRS §30 (`CHECK_UPDATE`, `DOWNLOAD_RELEASE`, `VERIFY_RELEASE`, `BACKUP`, `PREPARE_RELEASE`,
  `INSTALL_RELEASE`, `RUN_MIGRATION`, `OPTIMIZE`, `HEALTH_CHECK`, `ACTIVATE_RELEASE`, `ROLLBACK`,
  `REPORT_STATUS`).
- The Vendor Client initiates **outbound** calls wherever practical — Codexehub should not need
  inbound access to customer servers.

---

## 16. Database & migration safety

- New tables: migration → model → relationships → factory (+ seeder only if it adds value) → tests.
- The existing `licensed_*` / `license_*` tables are **released schema** in spirit — evolve them with
  **new additive migrations**, not edits to the original files. Don't silently rename/drop columns a
  customer install may depend on. Document every schema change in `Log`/`CHANGELOG-BACKEND.md` and, if
  it affects an upgrade path, in an `UPGRADE-NOTES` section.
- Package synchronization on the client side must be **transactional** — never leave a half-synced
  entitlement set (SRS §115). Destructive migrations in a release need extra safeguards and a
  documented rollback stance (SRS §85, §140).

---

## 17. Security-first checklist (per task)

Ask internally: Can an unauthorized actor reach this? Can input be manipulated? Can data leak across
customers (SRS §80/§81)? Can a duplicate/replayed request cause double state (SRS §117)? Is every
secret encrypted at rest (SRS §79, §63)? Is the signature verified before the payload is trusted?
Does a network failure degrade safely (grace, not lock — SRS §62/§72)?

A severe vulnerability found mid-task: stop feature expansion, record it, fix + test, update security
docs, then resume (SRS §58).

---

## 18. No feature creep / no unrelated refactor

Stay inside the current task and phase. A small refactor needed to land the task safely is fine; a
broad rewrite is a separate recorded task. Prefer a small number of relevant files per `next`.

---

## 19. Environment & secrets

- Dev: `APP_ENV=local`, `APP_DEBUG=true`, MySQL `codexehub` on `127.0.0.1:3306`
  (CLAUDE.md — avoid SQLite-only SQL; branch on `DB::connection()->getDriverName()` for raw date SQL).
- Never commit production secrets. The JWT signing key (`config/licensing.php`) and any Vendor-API
  signing keys stay out of version control; document how to generate them in `INSTALLATION`.
- `.env` currently has a **placeholder** `MAIL_PASSWORD` — mail-dependent tasks (offers, invoices,
  reminders, notifications) are testable via `MAIL_MAILER=log` + `tinker`; real delivery is blocked
  until the user supplies the Gmail App Password.

---

## 20. Do not ask unnecessary questions

If the SRS defines it, implement it. Ask only when: a choice materially changes the DB schema, the
Vendor API contract, the payment/licensing architecture, ownership/IP handling, or security; a
destructive operation needs confirmation; or a required external credential/service is unavailable.

---

## 21. Browser / verification quirks (from CLAUDE.md)

The sandboxed Browser pane in this environment: swaps `https://`→`http://` on `localhost` sub-paths
(use `127.0.0.1`), disables native `confirm()`/`alert()` (verify delete-confirm flows via `tinker`),
and sometimes fails to composite frames (fall back to computed-style / DOM JS checks, or `curl`
against a local `php artisan serve`). Google Fonts is blocked in-sandbox on every page — a
pre-existing console error, not a regression.

---

## 22. Definition of Done (SRS §145)

The platform is complete only when every §145 line is real: customer / product / feature / service
management; customer-specific package + card + offer + approval; payment + recurring billing;
requirement + custom-dev workflow + Git integration; release management; Vendor Client; package +
feature sync; license + installation validation; VPS + cPanel deployment; backup + rollback + health
check; automatic payment status + grace + suspension + reactivation; final-payment tracking +
ownership completion; ecommerce package UI + feature enforcement; audit logging; and the full
security / deployment / payment / license / end-to-end test suites (SRS §108–§114, §142, §143).

---

## 23. Files this workflow maintains

```
doc/backend/WORK-INSTRUCTIONS.md   (this file — workflow rules)
doc/backend/CHECKLIST.md            (whole-platform checklist + progress)
doc/backend/PROJECT-STATUS.md       (current phase, operational detail)
doc/backend/LAST-TASK-SUMMARY.md    (most recent task, full detail)
doc/backend/CHANGELOG-BACKEND.md    (one line per completed task)
doc/backend/API-CONTRACT.md         (Vendor API — created at APISEC-001 / INST-001)
CLAUDE.md                           (repo root — History Summary + Last 5 Actions)
```

---

## 24. Reference material

- SRS (v1.2) — the Obsidian file named at the top.
- Existing code: `app/Models/License*.php`, `app/Services/LicenseTokenService.php`,
  `app/Services/Payments/*`, `app/Http/Controllers/Admin/Licensing/*`,
  `app/Http/Controllers/Api/LicenseApiController.php`, `config/licensing.php`,
  `database/migrations/2026_08_24_15000*`.
- The customer product (Vendor Client target): `C:\xampp\htdocs\laravel_projects\ecom-single-vendor`
  (its own `doc/` + `docs/` trackers; SRS §16 there is unrelated to this SRS).
- `doc/backend/Project-overview.md` — the admin-dashboard boilerplate brief (already delivered).

---

## 25. Initial state

See `doc/backend/PROJECT-STATUS.md`. Summary: Phase 1 partially done (auth, admin shell, user
management, a primitive global-package licensing subsystem). Current task: **RBAC-001** — real roles
& permissions (replace the placeholder + ad-hoc `isAdmin()` Gates), because every later admin/API
task depends on it. `HARD-000` (test harness) may be pulled forward first on user instruction.

---

## 26. Change Log

- **v1.0.0 — 2026-09-09** — Initial execution-rules document for the Codexehub control-plane /
  licensing platform build-out (§1–§25).
