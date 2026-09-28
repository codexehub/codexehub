# API-CONTRACT — Codexehub Vendor API

> Owner-of-record for the Vendor API contract (SRS §63–§72, §120, §121). A `[VC]` task in the
> customer product repo (`ecom-single-vendor`) implements *against* this document; it does not own it.
> Created at **APISEC-001**. Updated by every later Vendor API task (`INST-002`,
> `LIC-CP-002`, `SYNC-001/002`, `UPD-001`, `DEP-004`, …) in the same step that adds/changes an endpoint.

## Status

| Piece | Status |
|---|---|
| Prefix `/api/vendor/v1` | ✅ live (`routes/vendor-api.php`, required from `routes/api.php`) |
| Request envelope — presence/shape validation | ✅ live (`VendorApiRequest` + `HasVendorEnvelope`) |
| Standard response envelope (success/error) | ✅ live (`App\Support\VendorApi\VendorApiResponse`) |
| Error-code classification (business vs technical) | ✅ live (`App\Support\VendorApi\VendorApiErrorCode`) |
| Signature **verification** (reject a bad/missing signature) | ✅ live (`APISEC-002` — `VerifyVendorApiSignature`) |
| Timestamp freshness / replay-window rejection | ✅ live (`APISEC-002`) |
| Request-id dedupe | ✅ live (`APISEC-002`) |
| Per-route rate limiting | ✅ live (`APISEC-002` — `ThrottleVendorApiRequest`) |
| Response **signing** | ✅ live (`APISEC-003` — `App\Support\VendorApi\VendorApiResponseSigner`, RSA) |
| Key management / rotation | ✅ live (`APISEC-003` — `config/vendor_api.php`, `vendor-api:generate-signing-key`) |
| Installation registration/verify/heartbeat/lookup, domain+customer+product binding | ✅ live (`INST-002` — see Installations below) |
| Real endpoints (installations, licenses, packages, updates, deployments) | ⬜ see Endpoints below |

The request signing secret is still one shared environment-wide value (`VENDOR_API_SIGNING_SECRET`)
rather than a real per-installation credential — **deliberately deferred**, see Installations below.
But requests under this prefix are no longer trust-everything: a request must now carry a valid HMAC
signature, a fresh timestamp, and an unused `request_id`, and is rate-limited, or it is rejected before
it ever reaches a controller — and every success response now carries a real RSA signature a Vendor
Client can verify, and the caller's claimed `installation_id` is now cross-checked against a real
`installations` row (customer/product/domain binding) once it calls `/installations/register`.

## Prefix

```
/api/vendor/v1
```

SRS §64. Versioned; backward compatibility per the SRS §138 compatibility matrix once that exists.

## Request envelope (SRS §70)

Every request body must include:

| Field | Type | Notes |
|---|---|---|
| `installation_id` | string, ≤64 chars | Identifies the calling install — this *is* the SRS §38 installation identity. Cross-checked against a real `installations` row by `/installations/verify`, `/heartbeat`, and `GET /installations/{id}` (`INST-002`); `/installations/register` is what creates that row in the first place. Used on every route as the rate-limit/dedupe key. |
| `request_id` | string, ≤64 chars | Client-generated, echoed back on every response (success or error) for correlation, and deduped per `installation_id` (see Replay protection below). |
| `timestamp` | ISO 8601 date/time | Must be within `replay_window_seconds` of server time (see below) once the signature verifies. |
| `client_version` | string, ≤20 chars | The Vendor Client's own version — used for `SRS §103/§104` minimum-version enforcement once `VC-VER-001` lands. |
| `signature` | string | **Cryptographically verified** (HMAC-SHA256) — see Request signing below. |

A missing/invalid envelope field renders the standard **error** envelope below with
`code: "VALIDATION_ERROR"` and HTTP 422 — never Laravel's default `{message, errors}` shape.
This shape check runs in `VendorApiRequest`, *after* the security middleware below — a request with
a missing/malformed field but no `signature` to check at all passes through the middleware untouched
and is still caught here with the same `VALIDATION_ERROR` shape.

## Request signing, replay protection & rate limiting (SRS §63/§70/§120, APISEC-002)

Enforced by two middleware applied to every route in `routes/vendor-api.php`, **before** the
controller/FormRequest: `ThrottleVendorApiRequest` (rate limit) runs first, then
`VerifyVendorApiSignature` (signature → timestamp → dedupe, in that order — signature is checked
first so a forged/unsigned request can't pollute the dedupe cache or learn timing information from
the other checks).

**Signature algorithm** — `App\Support\VendorApi\VendorApiSigner`, the single source of truth. A
Vendor Client must reproduce it exactly:

1. Take the full request body as a JSON object, **remove the `signature` key**.
2. Recursively sort every object's keys (top-level and nested) alphabetically — array/list order is
   left as-is, only object keys are sorted.
3. `json_encode` the result with unescaped slashes and unicode (`JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`).
4. `signature = hex(HMAC-SHA256(that JSON string, shared_secret))`.

**Shared secret (temporary):** one value, `config('vendor_api.signing_secret')` /
`VENDOR_API_SIGNING_SECRET`, used for every installation. **Recorded decision (INST-002):** issuing a
real per-installation signing credential was considered for this task and deliberately deferred — it's
a materially larger security change (secure generation, at-rest encryption per SRS §63 "Secret
encryption", and a chicken-and-egg problem at first-ever `/register` since the client has no
per-installation secret until *after* that call succeeds) than this task's actual scope ("installation
binding to customer/product/domain/identity"). `/installations/register` now does real identity
binding (customer/product/domain, license-conflict checks) using the existing shared secret; a future
task should introduce per-installation credentials without changing the algorithm above, only the
secret lookup.

**Timestamp / replay window:** once the signature verifies, `timestamp` must be within
`vendor_api.replay_window_seconds` (`VENDOR_API_REPLAY_WINDOW_SECONDS`, default **300s**) of the
server's current time, in either direction. Outside the window → `REQUEST_EXPIRED` (401). A
`timestamp` that fails to parse as a date is *not* judged here — it falls through to the normal
`VALIDATION_ERROR` (422) from the envelope's `date` rule.

**Request-id dedupe (replay protection):** after a valid, fresh request, `request_id` is remembered
per `installation_id` (cache key `vendor-api:request-id:{installation_id}:{request_id}`) for
`vendor_api.request_id_dedupe_ttl_seconds` (`VENDOR_API_REQUEST_ID_DEDUPE_TTL_SECONDS`, default
**600s**, kept ≥ the replay window). A repeat within that window → `DUPLICATE_REQUEST` (401). This is
a **hard reject**, not an idempotent cached-response replay — a client that didn't get a response
must generate a new `request_id` and retry, not resend the same one. (An endpoint that needs its own
business-level idempotent retry semantics — e.g. package sync, SRS §117 — builds that on top, at the
endpoint layer; this is a transport-level anti-replay guard only.)

**Rate limiting:** `config('vendor_api.rate_limits')` maps a route name to
`{max_attempts, decay_seconds}`, falling back to `'default'` (`VENDOR_API_RATE_LIMIT_DEFAULT_*`,
default **60 requests / 60s**) for any route without its own entry, keyed by
`route_name:ip|installation_id`. Exceeding it → `RATE_LIMIT_EXCEEDED` (429,
`data.retry_after_seconds`). SRS §120 calls for separate limits per category (License Verification /
Heartbeat / Package Sync / Release Download / Deployment) — **each later endpoint task should add its
own `vendor_api.rate_limits` entry** for its route name rather than relying on the shared default.

## Response signing & key rotation (SRS §61/§63, APISEC-003)

Every **success** response is signed by `App\Support\VendorApi\VendorApiResponseSigner`, applied in
`VendorApiResponse::success()` — every endpoint gets this for free, nothing per-controller to wire.
Unlike request signing (shared HMAC secret both sides hold), response signing is **asymmetric**: the
server signs with a private key and a Vendor Client verifies with the matching public key it holds —
no secret the client needs is ever exposed, so a compromised/decompiled client still can't forge a
response.

**Algorithm:**

1. Take the response body minus `signature` (i.e. `{success, request_id, code, message, data}`), and
   canonicalize it with the exact same algorithm request signing uses
   (`App\Support\VendorApi\VendorApiSigner::canonicalize()` — recursive key-sort + `json_encode` with
   unescaped slashes/unicode). One canonicalization routine, shared by both directions.
2. `raw_signature = RSA-SHA256-sign(that canonical string, active private key)` (PHP `openssl_sign`
   with `OPENSSL_ALGO_SHA256`).
3. `signature = "{key_id}:" + base64(raw_signature)`.

**Verification** (what a Vendor Client does): split `signature` on the first `:` to get `key_id` and
the base64 payload; look up the public key for that `key_id` (see Key distribution below); base64-decode;
`openssl_verify` the same canonicalized body against the decoded bytes with that public key.

**Key management & rotation:** `config('vendor_api.response_signing')` —
`active_key_id` (env `VENDOR_API_RESPONSE_SIGNING_KEY_ID`, default `v1`) selects which key *signs* new
responses; `keys` is a map of every key id this server has ever signed with (`{private_key_path,
public_key_path}`, both file paths). Rotating is additive, never destructive:

1. `php artisan vendor-api:generate-signing-key --key-id=v2` — writes a fresh RSA-2048 keypair to
   `storage/keys/vendor-api/v2-{private,public}.pem` (gitignored — private keys are never committed).
2. Add a `'v2' => [...]` entry to `config/vendor_api.php`'s `response_signing.keys` map, alongside
   `v1` (which stays — a response already sent under `v1` should remain verifiable against it for as
   long as anything might still check it).
3. Once deployed, set `VENDOR_API_RESPONSE_SIGNING_KEY_ID=v2` to start signing new responses with it.

**Key distribution (open gap, recorded):** the SRS §65-§69 endpoint list has no "fetch the current
public key" endpoint, and `INST-002`/`VC-001` (which would define how a Vendor Client is provisioned)
haven't landed yet. For now the public key is a **file**
(`storage/keys/vendor-api/{active_key_id}-public.pem`) distributed out-of-band — the same pattern the
pre-existing `LicenseTokenService` RS256 setup already uses (no key-fetch endpoint either). A future
task (`INST-002` or its own, if key distribution needs to be dynamic per-rotation without a manual
redeploy of the Vendor Client) should revisit this rather than inventing a speculative endpoint now.

## Response envelope (SRS §71)

**Success:**

```json
{
    "success": true,
    "request_id": "REQ-ABC123",
    "code": "PACKAGE_SYNC_SUCCESS",
    "message": "Package synchronized successfully.",
    "data": {},
    "signature": "v1:Q2hlY2tpbmcgeW91ciBzaWduYXR1cmUgY2FyZWZ1bGx5Li4u..."
}
```

`signature` is a real RSA signature (see Response signing below) — every success response is signed,
never `null`.

**Error:**

```json
{
    "success": false,
    "request_id": "REQ-ABC123",
    "code": "LICENSE_SUSPENDED",
    "message": "License is suspended due to unpaid invoice.",
    "data": {}
}
```

Errors carry no `signature` key at all (matches the SRS §71 example) — there is nothing to trust
beyond the HTTP status + code, since a suspended/technical response has no sensitive payload to
protect with a signature.

## Error codes (SRS §72)

The Vendor Client **must** branch on classification, not just on `success: false` — a technical
failure must never be interpreted as license suspension (SRS §62, §72).

| Code | Classification | HTTP | Meaning |
|---|---|---|---|
| `LICENSE_SUSPENDED` | business | 403 | License suspended (e.g. unpaid invoice). |
| `LICENSE_EXPIRED` | business | 403 | License term ended. |
| `PAYMENT_DUE` | business | 403 | Payment due, within or past grace. |
| `LICENSE_REVOKED` | business | 403 | License permanently revoked. |
| `API_TIMEOUT` | technical | 500 | Server-side timeout processing the request. |
| `NETWORK_ERROR` | technical | 500 | Reserved for the Vendor Client's own local classification of a failed call — this API itself cannot return it (it never sees its own network failure). |
| `INVALID_RESPONSE` | technical | 500 | Reserved for the Vendor Client's own classification of an unparsable response. |
| `SERVER_ERROR` | technical | 500 | Unclassified server-side failure. |
| `SIGNATURE_ERROR` | technical | 401 | Signature missing/invalid. |
| `VALIDATION_ERROR` | technical | 422 | Envelope or endpoint-specific field failed validation. |
| `NOT_FOUND` | technical | 404 | Referenced installation/license/package/etc. does not exist. |
| `REQUEST_EXPIRED` | technical | 401 | `timestamp` is outside the replay window (stale or clock-skewed). |
| `DUPLICATE_REQUEST` | technical | 401 | `request_id` was already used by this `installation_id` within the dedupe window. |
| `RATE_LIMIT_EXCEEDED` | technical | 429 | Too many requests for this route from this caller; see `data.retry_after_seconds`. |
| `INSTALLATION_SUSPENDED` | business | 403 | Installation has been suspended (admin-set `installations.status`). Not in the SRS §72 catalog by name — added at `INST-002`, same rationale as `VALIDATION_ERROR`/`NOT_FOUND` at APISEC-001. |
| `INSTALLATION_ALREADY_BOUND` | business | 409 | `/installations/register` detected a conflicting binding — either the license is already bound to a different installation, or the installation identity is already bound to a different customer's license. |

New codes are added by the task that first needs to return them; keep this table in sync in the same
step (`App\Support\VendorApi\VendorApiErrorCode` is the source of truth for the enum itself).

## Endpoints

| Endpoint | Status |
|---|---|
| `POST /ping` | ✅ live — **diagnostic only**, not part of the SRS endpoint list. Proves the envelope/response skeleton; a Vendor Client should not depend on it for anything functional. |
| `POST /installations/register`, `/verify`, `GET /installations/{id}`, `POST /installations/heartbeat` | ✅ live (`INST-002`, SRS §65) — see Installations below. |
| `POST /licenses/verify`, `/renew`, `/status` | ✅ live (`LIC-CP-002`, SRS §66) — see Licenses below. `GET /licenses/{id}` deliberately not built (see that section). |
| `GET /packages/{id}`, `/packages/{id}/features`, `POST /packages/sync`, `/packages/acknowledge` | ⬜ `SYNC-001` (SRS §67) |
| `GET /updates/check`, `GET /releases/{id}`, `POST /releases/{id}/download-authorize`, `/verify` | ⬜ `UPD-001` (SRS §68) |
| `POST /deployments/authorize`, `/report`, `/health`, `/rollback` | ⬜ `DEP-004` — **descoped**, see `doc/backend/PROJECT-STATUS.md` (deployment is manual, 2026-09-28) |

## Installations (SRS §38/§65, INST-002)

Four endpoints, all sharing `App\Services\Installation\InstallationBinder`. The envelope's own
`installation_id` **is** the SRS §38 installation identity — there is no separate id field on any of
these endpoints.

**`POST /installations/register`** — fields beyond the envelope: `license_key` (required, proves the
caller may bind this identity to a customer/product/package — the same credential the pre-SRS
`/api/v1/license/activate` endpoint uses), `domain` (required), `environment` (optional, one of
`production`/`staging`/`development`), `software_version` (optional; `vendor_client_version` is taken
from the envelope's own `client_version`, not a separate field). Behavior:

- License must resolve (`NOT_FOUND` if the key is unknown) and be operational (the same
  `VendorLicenseResolver::businessErrorFor()` mapping `/licenses/*` uses — `LICENSE_SUSPENDED` /
  `_EXPIRED` / `_REVOKED` / `PAYMENT_DUE`).
- The license must not already be bound to a *different* installation, and the installation identity
  must not already be bound to a *different* customer's license — either conflict →
  `INSTALLATION_ALREADY_BOUND` (409), no rows changed. `licenses.license_customer_id → customers` is a
  single value (not a pivot), so this schema only supports one license ↔ one installation.
- **First-time registration** (no existing row for this `installation_id`) → creates an `installations`
  row, immediately `ACTIVE` (nothing to revalidate yet), and (re)binds `licenses.installation_id` —
  closing the gap `INST-001` deliberately left open. Success code `INSTALLATION_REGISTERED`.
- **Re-registration, same domain** → idempotent refresh of `vendor_client_version`/`software_version`/
  `environment`; status untouched (including a still-`PENDING` row — re-registering doesn't itself
  revalidate). Success code `INSTALLATION_REGISTERED`.
- **Re-registration, changed domain** — SRS §38 "Domain changes must require authorization/
  revalidation": the new domain *is* persisted, but status flips to `PENDING` rather than staying
  `ACTIVE`. Success code `INSTALLATION_PENDING_REVALIDATION`, `data.revalidation_required: true`. This
  is still a 200 success (the system correctly recorded and flagged the change), not an error.

**`POST /installations/verify`** — no fields beyond the envelope. This is the revalidation step: a
`PENDING` installation (fresh registration or a domain change) is confirmed and flipped to `ACTIVE`
here; an already-`ACTIVE`/`INACTIVE` installation just gets `last_verified_at` refreshed. `SUSPENDED` →
business error `INSTALLATION_SUSPENDED` (403), no state change. Unregistered `installation_id` →
`NOT_FOUND`. Success code `INSTALLATION_VERIFIED`.

**`POST /installations/heartbeat`** — optional `software_version`. A pure liveness signal:
`last_heartbeat_at` is refreshed (and `software_version` updated if provided) but **status is never
touched**, even when `PENDING` — a heartbeat must not be able to silently complete a domain-change
revalidation that only `/verify` is meant to confirm. Same `SUSPENDED`/`NOT_FOUND` handling as `/verify`.
Success code `INSTALLATION_HEARTBEAT_RECEIVED`.

**`GET /installations/{id}`** — a pure, self-lookup-only read (no `last_verified_at`/`last_heartbeat_at`
side effects). `{id}` must equal the caller's own envelope `installation_id`; any mismatch →
`NOT_FOUND` (SRS §80/§81 customer isolation — never confirms or denies that some *other* installation
exists). Implemented as a plain string route parameter, not implicit Eloquent route-model-binding —
binding would 404 via Laravel's own exception rendering instead of the standard Vendor API error
envelope. Success code `INSTALLATION_STATUS`.

All four return the same `data` shape on success:

| Field | Source |
|---|---|
| `installation_id`, `status`, `domain`, `environment` | `installations.*` |
| `customer` | `{id, name}` from `installations.customer_id → customers`, or `null` |
| `product` | `{id, key, title}` from `installations.product_id → products`, or `null` |
| `package` | `{id, name}` from `installations.licensed_app_package_id → licensed_app_packages` — the same pre-SRS-package stand-in `/licenses/*` uses; `null` if unset |
| `vendor_client_version`, `software_version` | `installations.*` |
| `last_heartbeat_at`, `last_verified_at` | `installations.*`, ISO 8601 or `null` |

Rate limits: `vendor-api.installations.register` 20/min, `.verify` 30/min, `.heartbeat` 120/min
(the most frequent call in the API — a periodic liveness ping), `.show` 30/min.

**Deferred, recorded:** per-installation signing credentials (see Request signing above) and the SRS
§105 "audit logging" checklist item for Vendor API calls specifically (model-level `Auditable` on
`Installation`/`License` already covers *what changed*, via the admin audit viewer — a dedicated
Vendor-API request/call log is a separate, not-yet-scoped concern).

## Licenses (SRS §61/§66, LIC-CP-002)

Three endpoints, all `POST`, all taking `license_key` (string, required) as their only field beyond
the envelope, all returning the same SRS §61-shaped `data` payload on success:

| Field | Source | Notes |
|---|---|---|
| `license_key` | `licenses.license_key` | Echoed back for correlation. |
| `installation_id` | `licenses.installation_id`, falling back to the request envelope's `installation_id` | **Not validated/bound** — real installation binding is `INST-002`. A license not yet bound to an installation just echoes whatever the caller claims. |
| `license_status` | `licenses.status` | One of the 9 SRS §39 states. |
| `package` | `{id, name}` from the pre-SRS flat `licensed_app_packages` row | SRS §52's `customer_packages` (with a real `package_version`) arrives with Phase 2 — this is a documented substitute, not the final shape. |
| `expires_at` | `licenses.expires_at` | ISO 8601, or `null`. |
| `grace_until` | `licenses.next_payment_due + licenses.grace_period_days` | `null` when `next_payment_due` is unset. |
| `ownership_status` | `licenses.ownership_status` | |
| `last_verified_at` | `licenses.last_verified_at` | |

`software_version` and package **`features`**/entitlements are deliberately **not** in this payload —
`software_version` needs `INST-002`'s installation record; feature/entitlement data is `SYNC-001`'s
job (`GET /packages/{id}/features`, `POST /packages/sync`). Mixing them into the license response
would duplicate what those tasks own.

**`POST /licenses/verify`** — the Vendor Client's periodic check-in. Success code `LICENSE_VERIFIED`.
Updates `licenses.last_verified_at` on every call.

**`POST /licenses/status`** — a pure read for on-demand display (e.g. a "My Package" screen refresh).
Success code `LICENSE_STATUS`. Does **not** update `last_verified_at` — that's what distinguishes it
from `/verify`.

**`POST /licenses/renew`** — success code `LICENSE_RENEWAL_CHECKED`, `data.renewed` (bool). Does
**not** itself process a payment or move `next_payment_due` — that's Phase 3 (invoicing) / Phase 9
(`LIC-001..007`) territory, neither built yet. What it does: if a `PAYMENT_DUE`/`GRACE_PERIOD`
license's `next_payment_due` is already in the future (e.g. an admin manually advanced it via
LIC-CP-001's "Move to Status" control), transitions it to `ACTIVE` immediately rather than making the
Vendor Client wait for `LIC-001`'s scheduled job (Phase 9). Also updates `last_verified_at`.

All three share one **business-error mapping** for a non-operational license (SRS §45/§46 —
`PAYMENT_DUE`/`GRACE_PERIOD` are operational and return success normally):

| `licenses.status` | Error code |
|---|---|
| `suspended` | `LICENSE_SUSPENDED` |
| `revoked` | `LICENSE_REVOKED` |
| `expired` | `LICENSE_EXPIRED` |
| `pending` | `PAYMENT_DUE` — no dedicated SRS §72 code exists for "not yet activated"; matches the legacy `LicenseApiController`'s identical framing ("awaiting payment confirmation"). Recorded decision. |

An unknown `license_key` → `NOT_FOUND` (404). All three also carry forward the legacy
`LicenseApiController`'s staleness safety net: an `active` license past its own `expires_at` is
flipped to `expired` (and rejected) on lookup, narrowly scoped to that one transition exactly like
the legacy behavior — a fuller automatic sweep across every state is `LIC-001` (Phase 9).

**`GET /licenses/{id}` is deliberately not built** — SRS §66 lists it, but this task's own checklist
line scoped to verify/renew/status only, and nothing yet needs a by-id lookup (the Vendor Client
always has its own `license_key`). Add it if a real caller needs it rather than pre-building it.

Rate limits: `vendor-api.licenses.verify` / `.status` at 30/min, `.renew` at 10/min
(`config('vendor_api.rate_limits')`) — SRS §120's "License Verification" category.

## Security checklist (SRS §63) — status

| Requirement | Status |
|---|---|
| HTTPS only | Infra-level (production TLS termination) — not enforced in application code yet. |
| API authentication | ✅ HMAC signature (`APISEC-002`) — shared secret today; a real per-installation credential remains deferred (see Installations above — a materially bigger security change than `INST-002`'s own scope). |
| Installation credentials | ⬜ deferred — recorded gap, see Installations above. A future task can swap the secret lookup without changing the signing algorithm. |
| Signed responses | ✅ `APISEC-003` — RSA, see Response signing above. Public-key **distribution** to the Vendor Client is still out-of-band/file-based (recorded gap, see above). |
| Request IDs | ✅ echoed + deduped (`APISEC-002`) |
| Replay protection | ✅ `APISEC-002` (request-id dedupe) |
| Timestamp validation | ✅ `APISEC-002` (replay window) |
| Domain binding | ✅ `INST-002` — `/installations/register` binds + persists the domain; a change requires `/verify` to revalidate (SRS §38). |
| Installation binding | ✅ `INST-002` — `installations` (customer/product/package/domain/identity) + `licenses.installation_id`, with conflict checks against both a license and an installation identity already bound elsewhere. |
| Rate limiting | ✅ `APISEC-002` (per-route, config-driven) |
| Audit logging | Vendor API calls are not yet audited — the existing `Auditable` trait covers admin-side model changes only. |
| Key rotation | ✅ `APISEC-003` — `vendor-api:generate-signing-key` + additive `config/vendor_api.php` key map; see Response signing above. |
| Secret encryption | Existing `LicenseTokenService` RS256 keys and the new Vendor API response-signing keys are both file-based (private keys gitignored, never committed), not DB-stored. `VENDOR_API_SIGNING_SECRET` is env-only, not DB-stored either — no change needed yet. |
| Minimum privilege | N/A until endpoints exist. |
| No unrestricted remote shell | N/A — no deployment endpoints planned (deployment is manual, descoped). |

## Relationship to the existing `/api/v1/license/*` pair

`POST /api/v1/license/{activate,verify}` (`LicenseApiController`) predates the SRS and is **not**
under `/api/vendor/v1` — it stays live and unchanged for now, still unsigned and without replay
protection or rate limiting. The SRS-shaped, secured replacement (`/api/vendor/v1/licenses/*`) now
exists (`LIC-CP-001`/`LIC-CP-002`); the old pair is retired once the Vendor Client
(`ecom-single-vendor`) has moved over to it — a `[VC]` task, not this repo's to schedule.
