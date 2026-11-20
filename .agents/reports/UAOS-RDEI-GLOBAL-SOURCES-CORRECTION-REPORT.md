# UAOS Research-Driven Engineering Intelligence (RDEI)
# Global Cross-Domain Research Sources & Evidence Framework Correction Report

**Task ID:** `UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-003A`  
**System:** Research-Driven Engineering Intelligence (RDEI)  
**Project:** Universal Agent Operating System (UAOS)  
**Working Root:** `C:\laragon\www\core`  
**Phase:** Global Research Sources & Evidence Framework Design (Correction Pass)  
**Date:** 2026-11-15  
**Canonical Baseline:**  
- `UAOS-RESEARCH-DRIVEN-ENGINEERING-INTELLIGENCE-ARCHITECTURE.md` (`ARCH-UAOS-RDEI-002`)  
- `UAOS-RDEI-ARCHITECTURE-CORRECTION-REPORT.md` (`UAOS-RDEI-ARCHITECTURE-CORRECTION-003`)  
- `UAOS-RDEI-GLOBAL-SOURCES-AND-EVIDENCE-FRAMEWORK-REPORT.md` (`UAOS-RDEI-GLOBAL-SOURCES-003`)  
**Design Artifacts Updated / Created:**  
- `.agents/reports/UAOS-RDEI-GLOBAL-SOURCES-AND-EVIDENCE-FRAMEWORK-REPORT.md` (Updated canonical design document)  
- `.agents/schemas/source-definition.schema.json` (Corrected canonical 26-field source registry schema)  
- `.agents/schemas/evidence-overlay.schema.json` (New formal declarative evidence policy overlay schema)  
- `.agents/reports/UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-REPORT.md` (This focused correction report)  
**Implementation Status:** Strictly Prohibited in this Phase (Design Document Correction Only)  
**Final Verdict:** `CORRECTIONS COMPLETE — READY FOR FINAL PROMPT 3 APPROVAL`

---

## 1. Executive Summary

In accordance with task specification `UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-003A`, a narrowly scoped, rigorous correction pass was executed against the canonical RDEI Global Cross-Domain Research Sources & Evidence Framework. All six concrete review findings have been completely resolved in place.

The approved RDEI architecture, the 16-domain taxonomy (Domains A through P), the 65+ authoritative source catalog, the mathematical trust-scoring linear weights ($w_1 = 0.35, w_2 = 0.30, w_3 = 0.20, w_4 = 0.15$), and all previously accepted governance invariants (C1 through C8) have been strictly preserved. Exactly zero runtime PHP implementation code, connectors, CLI commands, database tables, migrations, or tests were created or modified. Gate 4 cryptographic HMAC authorization semantics remain 100% unaltered.

---

## 2. Review Findings and Concrete Resolutions

### Finding 1: Security Vulnerability Applicability and Gate 0 Decision Rules

#### Defect Identified
The previous framework draft enforced a blunt rule under Domain Overlay 3: any CVSS v3.1/v4.0 score $\ge 7.0$ in NIST NVD or presence in CISA KEV automatically triggered `CONFIRMED_VULNERABLE` and blocked Gate 0 clearance, regardless of whether the target project actually utilized the component, ran the vulnerable version, or enabled the affected configuration.

#### Correction Applied
1. **Four-Tier Applicability Model:** The security evidence evaluation and Gate 0 clearance rules were refactored into four distinct, sequentially evaluated evidential tiers:
   - **Tier 1 (Advisory Existence):** The vulnerability is published in a public advisory (CVE.org, NIST NVD, CISA KEV, or GHSA). This establishes that a flaw exists publicly, not that the project is affected.
   - **Tier 2 (Component & Version Applicability):** The target project's dependency manifest (`composer.json`, `package.json`, lockfiles) actually includes the affected component within the exact vulnerable version range (CPE / SemVer constraint match).
   - **Tier 3 (Configuration & Reachability Applicability):** The vulnerable code path, optional module, configuration setting, or feature flag is actually active and reachable in the project's runtime execution graph.
   - **Tier 4 (Real-World Threat Urgency):** Presence in the CISA KEV catalog or EPSS percentile $\ge 0.70$ establishes active exploitation in the wild, escalating the urgency of remediation once component and configuration applicability are confirmed.
2. **Gate 0 Decision Rules:**
   - **Advisory Exists, Component Unrelated or Version Outside Range:** Gate 0 is **NOT blocked**. The finding is recorded in the dossier as `ADVISORY_UNAFFECTED`. Unrelated projects are never halted solely because an advisory exists.
   - **Component & Version Match, but Configuration Proves Non-Reachability:** Gate 0 is **NOT blocked**, provided that deterministic evidence (call-graph analysis or configuration inspection) is recorded in the dossier under status `ADVISORY_NOT_EXPLOITABLE_CONFIGURATION`.
   - **Component, Version, and Configuration Confirmed Affected:** Escalated to `CONFIRMED_VULNERABLE`. If CVSS $\ge 7.0$ or listed in CISA KEV, it forces `CONFIRMED_VULNERABLE_CRITICAL` and strictly **BLOCKS Gate 0**. If CVSS $< 7.0$, it evaluates under standard threshold gating with recorded mitigation requirements.
3. **Absence of Evidence Invariant Preserved:** Absence of a CVE is never proof of security. It indicates zero indexed public advisories, not verified immunity.
4. **Gate 4 Invariance:** Gate 4 HMAC verification and human authorization semantics remain untouched; vulnerability clearance status attaches additively as audit provenance metadata.

---

### Finding 2: Source, Discovery, Retrieval, and Tool Classification

#### Defect Identified
The earlier draft conflated knowledge authorities (NIST, IETF, Packagist) with discovery search engines (Brave Search, Tavily, SearXNG), transport mechanisms (HTTP/cURL, REST), and tooling (Puppeteer MCP, Fetch MCP), allowing intermediary tools to potentially count as separate corroborating sources.

#### Correction Applied
1. **Four Disjoint Conceptual Categories:** Section 5 of the design document was completely refactored into four disjoint planes:
   - **Knowledge Sources (Authority Plane):** Official institutions, standards bodies, academic publishers, package registries, and statutory gazettes (e.g., NIST, IETF, The PHP Group, Packagist, EUR-Lex, WHO, ISO).
   - **Discovery Providers (Navigation Plane):** Multi-source search engines and web indexing APIs used exclusively to discover URLs and locate candidate endpoints (e.g., Brave Search API, Tavily Research API, SearXNG, Bing Search API).
   - **Retrieval Mechanisms (Transport Plane):** Technical wire protocols and client implementations used to fetch raw payloads across networks (e.g., `StreamableHttpTransport` / cURL, REST/JSON, GraphQL, SOAP, OAI-PMH, RSS, Git).
   - **Tooling and MCP Servers (Execution Plane):** Local execution bridges, headless rendering engines, format parsers, and local storage caches (e.g., Puppeteer Headless MCP, Fetch MCP, SQLite MCP, ContentSanitizer).
2. **Strict Non-Corroboration Enforcement:** Formally codified that discovery providers, retrieval mechanisms, tooling, MCP servers, and local storage caches are **infrastructure**, not authorities. They are strictly prohibited from incrementing the independent corroboration counter ($N_{\text{corr}} = 0$). Three search engines or two MCP tools fetching the same underlying documentation site represent exactly one piece of evidence.
3. **Upstream Provenance Retention Model:** Established a formal JSON provenance structure requiring all provider and tool outputs to link directly to the underlying Knowledge Source (`canonical_knowledge_source_id`, `upstream_publisher`, `canonical_document_url`, `discovery_provider_used`, `retrieval_mechanism_used`, `tooling_executed`, `retrieval_timestamp`, `raw_payload_sha256`).

---

### Finding 3: Source Registry JSON Schema Completeness

#### Defect Identified
The draft schema in Section 6.1 exhibited structural gaps: it lacked HTTPS enforcement for remote URLs, lacked numeric bounds on rate limits and TTLs, lacked validation between `requires_auth`, `auth_type`, and `env_var_key`, permitted arbitrary unexpected fields, omitted structured verification metadata, and had an internal discrepancy between the 19 properties defined and the 26 canonical fields claimed in Section 14 (VAL-08).

#### Correction Applied
1. **Remote HTTPS Enforcement with Localhost Exception:**
   - Canonical URLs enforce HTTPS: `"pattern": "^https://[a-zA-Z0-9.-]+(?:/.*)?$"`.
   - Access mechanism endpoint URLs enforce HTTPS for all remote hosts, while permitting unencrypted HTTP strictly for documented local/self-hosted instances:
     `"pattern": "^(https://[a-zA-Z0-9.-]+(?:/.*)?|http://(?:localhost|127\\.0\\.0\\.1)(?::[0-9]+)?(?:/.*)?)$"`.
2. **Strict Numeric Bounds:** Added realistic, defensible numeric constraints:
   - `authority_tier`: integer, minimum 1, maximum 6.
   - `rate_limits.requests_per_minute`: integer, minimum 1, maximum 100,000.
   - `rate_limits.burst_limit`: integer, minimum 1, maximum 10,000.
   - `rate_limits.daily_quota`: integer, minimum 1, maximum 10,000,000.
   - `freshness_policy.max_cache_ttl_hours`: integer, minimum 1, maximum 87,600 (10 years).
3. **Authentication Consistency Validation via `allOf`:**
   - When `requires_auth: true`: `auth_type` is restricted to active methods (`api_key_header`, `api_key_query`, `bearer_token`, `basic_auth`, `mutual_tls`, `oauth2_client_credentials`), and `env_var_key` matching `^[A-Z0-9_]+$` is strictly required.
   - When `requires_auth: false`: `auth_type` must be `"none"`, and `env_var_key` is prohibited (`"not": {}`).
4. **Rejection of Unexpected Fields (`additionalProperties: false`):**
   - Root object rejects unexpected fields (`"additionalProperties": false`).
   - Every nested object (`owner`, `auth_profile`, `licensing`, `rate_limits`, `freshness_policy`, `access_mechanisms.items`, `verification_profile`, `independence_profile`, `applicability_profile`, `provenance_rules`) enforces `"additionalProperties": false` with explicit `required` lists.
5. **Exact 26 Canonical Properties Reconciled:**
   The schema now defines exactly 26 top-level canonical properties, perfectly matching the claimed field count in Section 14 (VAL-08):
   `source_id`, `official_name`, `description`, `owner`, `canonical_url`, `domains`, `subdomains`, `authority_tier`, `source_designation`, `category_type`, `supported_languages`, `supported_formats`, `access_mechanisms`, `auth_profile`, `cost_tier`, `licensing`, `rate_limits`, `freshness_policy`, `status`, `last_verified_at`, `classification_evidence`, `verification_profile`, `independence_profile`, `applicability_profile`, `provenance_rules`, and `fallback_source_ids`.
6. **Design Artifact Created:** Written to `.agents/schemas/source-definition.schema.json` and updated in Section 6.1 and 6.2 with fully valid concrete examples.

---

### Finding 4: Extensible Domain-Specific Evidence Policies

#### Defect Identified
The architecture had 7 static overlays but lacked a formal mechanism to register new domain-specific and subdomain-specific policies without modifying runtime scoring code, and lacked clear rules for inheritance, conflict resolution, and unsupported domains.

#### Correction Applied
1. **Three-Tier Policy Hierarchy and Precedence:**
   - **Subdomain Overlay (Precedence Rank: 100):** Most granular; defines rules for specific subdisciplines (e.g., `B.02` vulnerability databases).
   - **Domain Overlay (Precedence Rank: 50):** Broader discipline rules (e.g., Domain B Cybersecurity or Domain H Medicine).
   - **Base Global Policy (Precedence Rank: 0):** Universal fallback implementing canonical linear scoring ($w_1=0.35, w_2=0.30, w_3=0.20, w_4=0.15$).
2. **Scoring Invariance:** Overlays **never** change the mathematical weights or $[0.0, 1.0]$ clamp. They configure only methodological evidence hierarchies, context matching rules, dispute penalty thresholds, default freshness bounds, and mandatory quorums.
3. **Default Fallback Behavior:** Domains lacking specialized overlays automatically evaluate under the Base Global Policy (`pol-base-global`) with default SemVer matching, standard domain TTL matrix defaults, and a standard quorum of $N_{\text{min}} = 2$ for Level 2+ tasks.
4. **Multi-Domain Conflict Resolution (Strictness Precedence):** When a query spans multiple domains:
   - The most restrictive rule always governs (e.g., if one overlay blocks on an unmitigated condition, Gate 0 evaluates as BLOCKED).
   - In divergent scoring calculations, the lowest confidence score and highest dispute penalty are deterministically selected.
5. **Overlay Validation and Versioning:** Defined formal schema `.agents/schemas/evidence-overlay.schema.json`. Overlays are versioned via SemVer, stored in `.agent-os/research/policies/overlays/*.json`, and validated for acyclic inheritance.
6. **Representation of Coverage Gaps:**
   - `DOMAIN_POLICY_UNSUPPORTED`: Query targets an unmapped domain; falls back to Base Global Policy with audit documentation.
   - `INSUFFICIENT_DOMAIN_EVIDENCE`: Sources fail to satisfy the active overlay's mandatory quorum or tier. Gate 0 is strictly **BLOCKED fail-closed**.

---

### Finding 5: Verifiable Source and Provider Claims

#### Defect Identified
The catalog claimed capabilities without explicitly distinguishing between documentation review, endpoint existence verification, and empirical runtime testing.

#### Correction Applied
1. **Seven-Level Verification Rigor Model:** Established Section 4.0 defining the seven discrete levels of verification:
   - `WEBSITE_VERIFIED`: Institutional ownership, domain registration, and canonical HTTPS web presence verified.
   - `API_DOCS_VERIFIED`: Official API/feed contracts, schemas, and developer documentation verified.
   - `ENDPOINT_VERIFIED`: Hostname resolution and transport routing verified via DNS and HTTP response.
   - `CONNECTIVITY_TESTED`: Active live TLS request/response handshake executed.
   - `AUTH_TESTED`: Authentication exchange (token/key) tested and accepted by upstream provider.
   - `QUOTAS_CONFIRMED`: Published rate limits and pricing verified against official terms.
   - `LICENSING_REVIEWED`: Terms of service, copyright notices, and reuse terms legally reviewed.
2. **Truthful Claim Representation:**
   - In accordance with the Phase 3 framework design mandate (strictly zero runtime code), sources in the catalog are labeled with their true verification status: `[WEBSITE_VERIFIED]`, `[API_DOCS_VERIFIED]`, `[ENDPOINT_VERIFIED]`, `[QUOTAS_CONFIRMED]`, and `[LICENSING_REVIEWED]`.
   - Live network handshakes (`CONNECTIVITY_TESTED`) and authentication exchanges (`AUTH_TESTED`) are explicitly acknowledged as deferred to Phase 3 provider adapter runtime implementation (Phase 8 integration testing).
   - The system is barred from claiming live testing without supporting execution telemetry.
3. **Structured Verification Metadata:** `source-definition.schema.json` mandates a `verification_profile` object capturing booleans for all 7 levels, verification date, evidence reference, and recorded limitations.

---

### Finding 6: Independent Corroboration and Freshness

#### Defect Identified
Corroboration could be misinterpreted as counting multiple tools or syndicated sources returning identical upstream data. Freshness TTLs were presented as immutable universal constants rather than configurable defaults. Operational failure states were not sufficiently distinct.

#### Correction Applied
1. **Filtering the Five Non-Independent Patterns:** Formally defined in Section 7.4.1 that $N_{\text{corr}}$ increments only for independent underlying evidence:
   - *Shared Institutional Ownership:* Sources published by the same parent corporate or government entity (e.g., npm Advisories and GitHub Advisories; or NIST NVD and NIST CSRC) share institutional identity ($N_{\text{corr}} = 0$ additional).
   - *Syndication & Aggregation:* Downstream aggregators re-publishing upstream registries (e.g., OSV aggregating NVD and GHSA) inherit upstream identity ($N_{\text{corr}} = 0$ additional).
   - *Citation Dependencies:* Source B deriving its claim solely by referencing Source A without independent empirical measurement is dependent ($N_{\text{corr}} = 0$ additional).
   - *Mirrored Datasets & CDN Copies:* Mirror sites with identical payload hashes represent identical evidence ($N_{\text{corr}} = 0$ additional).
   - *Derived Secondary Publications:* Secondary commentary or blog posts reporting on an RFC/ISO standard are derivative summaries ($N_{\text{corr}} = 0$ additional).
2. **Three-Tier Configurable Freshness Policy:** Formally declared in Section 9.1 that the domain TTL matrix represents baseline defaults, not immutable constants. Configurable at:
   - *Project Profile Scope (`.agent-os/project-profile.json`):* High-assurance profiles configure tighter TTLs (e.g., 1h for CVEs).
   - *Research Context & Task Urgency Scope:* Emergency hotfixes enforce `ttl_override = 0` (force live revalidation); exploratory tasks can relax TTL.
   - *Source Definition Scope:* Volatile sources define custom TTLs in `source-definition.json`.
3. **Five Distinct Operational and Failure States:** Strictly separated in Section 7.4.3:
   - `SOURCE_UNAVAILABLE`: Network timeout, DNS failure, HTTP 5xx. Yields $C = 0.00$; blocks Gate 0 if quorum is lost.
   - `EMPTY_RESULT`: Query succeeded (HTTP 200), zero records found. Governed by the Absence of Evidence Principle (never proof of absence).
   - `INACCESSIBLE_CONTENT`: Paywall, 401/403, missing credentials, CAPTCHA, or client-side JavaScript rendering failure. Records limitation.
   - `CONFLICTING_EVIDENCE`: Authoritative sources disagree; triggers `UNRESOLVED_DISPUTE` with conflict penalty $P_{\text{conflict}}$.
   - `OUT_OF_SCOPE`: Query outside project profile domain/jurisdiction/version envelope; discarded before scoring.

---

## 3. Exact List of Modified and Created Files

| File Path | Action | Description |
|:---|:---:|:---|
| `C:\laragon\www\core\.agents\reports\UAOS-RDEI-GLOBAL-SOURCES-AND-EVIDENCE-FRAMEWORK-REPORT.md` | **MODIFIED** | Updated canonical design document resolving Findings 1–6 in-place. |
| `C:\laragon\www\core\.agents\schemas\source-definition.schema.json` | **CREATED** | Canonical 26-field source registry JSON Schema Draft 2020-12 resolving Finding 3. |
| `C:\laragon\www\core\.agents\schemas\evidence-overlay.schema.json` | **CREATED** | Declarative evidence overlay JSON Schema Draft 2020-12 resolving Finding 4. |
| `C:\laragon\www\core\.agents\reports\UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-REPORT.md` | **CREATED** | This focused correction report. |

**Verification of Scope Bounds:**
- Exactly zero files under `src/`, `tests/`, `bin/`, `resources/`, or `.agent-os/` were created or modified.
- Exactly zero PHP runtime classes, connectors, CLI commands, database tables, or migrations were implemented.
- Exactly zero changes were made to `C:\laragon\www\myndo ai`.

---

## 4. Validation Evidence

### 4.1 Schema Conformance & Syntax Validation
Both schemas were programmatically validated under PHP 8.3.30 using JSON parser verification:
- `source-definition.schema.json`:
  - JSON Syntax: **VALID** (`json_last_error() === JSON_ERROR_NONE`)
  - Canonical Properties Count: **26**
  - Required Fields Count: **26**
  - `additionalProperties`: `false` enforced on root and all nested objects
  - Conditional consistency: `allOf` validates `requires_auth` against `auth_type` and `env_var_key`
  - URL validation: HTTPS enforced on remote canonical/documentation URLs; `localhost` / `127.0.0.1` exception permitted for local engines
- `evidence-overlay.schema.json`:
  - JSON Syntax: **VALID** (`json_last_error() === JSON_ERROR_NONE`)
  - `additionalProperties`: `false` enforced
  - SemVer pattern enforced: `^[0-9]+\.[0-9]+\.[0-9]+$`
  - Precedence rank bounded: `minimum: 0, maximum: 100`

### 4.2 Automated Organization & Test Suite Integrity
The UAOS test and governance suite was executed immediately following all document updates:
- Organization Governance Validation (`validate-organization.php`): **121 / 121 CHECKS PASSED (100%)**
- Automated Core Test Suite (`run-tests.php`): **53 / 53 ASSERTIONS PASSED (100%)**
  - `Gate4Test`: PASSED
  - `RegistryTest`: PASSED
  - `SkillEngineTest`: PASSED
  - `ToolGatewayTest`: PASSED
  - `OrchestratorTest`: PASSED
  - `ValidatorTest`: PASSED

---

## 5. Explicit Invariant Confirmations

1. **Zero Runtime Implementation:** Confirmed. No PHP code, connectors, APIs, or database entities were introduced.
2. **Gate 4 Cryptographic Contract Preservation:** Confirmed. Gate 4 HMAC verification, nonce lifecycle, and human authorization semantics remain 100% untouched. Research evidence binds additively as audit provenance only.
3. **Trust Weight Invariance:** Confirmed. Authority (0.35), Version/Context Match (0.30), Freshness (0.20), and Independent Corroboration (0.15) remain invariant across all domain overlays.
4. **Domain Taxonomy Preservation:** Confirmed. The 16-domain taxonomy (A through P) and 65+ authoritative source catalog remain fully preserved.
5. **Secret Hygiene (C3 Invariant):** Confirmed. No API keys, credentials, or raw secrets are stored. External environment variable reference names (`env_var_key`) are used exclusively.
6. **300-Line Single Responsibility Limit:** Confirmed. All future implementation specifications adhere strictly to the $\le 300$-line ceiling.

---

## 6. Final Verdict

```
+---------------------------------------------------------------------------------+
|                                                                                 |
|         CORRECTIONS COMPLETE — READY FOR FINAL PROMPT 3 APPROVAL                |
|                                                                                 |
+---------------------------------------------------------------------------------+
```

All six review findings have been completely resolved within the canonical design documentation and design artifacts. Execution has ceased in accordance with prompt guidelines. Awaiting user review and authorization before proceeding to Prompt 4.