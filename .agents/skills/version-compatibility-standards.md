# Shared Engineering Skill: Framework and PHP Version Compatibility Standards

**Skill ID:** SKILL-VER-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Framework and PHP Version Compatibility Standards** skill establishes the authoritative enterprise standard for auditing, verifying, and enforcing version compatibility and upstream support lifecycles across language engines, core frameworks, third-party libraries, database servers, and runtime execution environments across Universal Core.

Its primary purpose is to guarantee seamless runtime execution, prevent silent deprecation failures, eliminate transitive dependency conflicts, and govern technical upgrades with rigorous verification. It enforces alignment with the Universal Core target matrix: **PHP 8.3+**, **Laravel 11.x**, **Filament 5.x** with **Livewire 4.x**, **MySQL 8.4 LTS** (preserving **MySQL 8.0+** compatibility), mandatory **Redis 7.x**, **Composer 2.x**, and **Node.js 20+**.

This standard strictly governs the development boundary: **local development is conducted natively on Windows Laragon with PowerShell**; **Docker and Laravel Sail are strictly prohibited as local development requirements**.

---

## 2. Scope and Applicability

This standard governs all dependency management, framework updates, engine upgrades, and platform-specific runtime behaviors:
- **Language Engine:** PHP 8.3+ runtime invariants, typed class constants, `#[\Override]` attributes, dynamic class constant fetches, and core deprecation mitigations.
- **Application Framework:** Laravel 11.x architecture (`bootstrap/app.php`, removal of `Kernel.php` files, default model casts via `casts()` method, and updated exception handling).
- **Administration & Dynamic UI:** Filament 5.x coupled with Livewire 4.x for backoffices; Vue 3 Composition API with Inertia.js for server-driven monoliths; React 18+ with TypeScript 5+ for decoupled client SPAs where applicable.
- **Database & In-Memory Services:** MySQL 8.4 LTS dialect, default authentication plugin shifts (`caching_sha2_password`), reserved keywords, Instant DDL algorithms, and MySQL 8.0+ backward compatibility; Redis 7.x protocol and service architecture (distinguishing Redis Open Source from Redis Enterprise).
- **Dependency Toolchains:** Composer 2.x platform locking and continuous stable updates; npm 10+ reproducible builds (`npm ci`).
- **Platform Traversal:** Windows NTFS filesystem case-insensitivity during local development versus Linux ext4 case-sensitive execution in production; directory separator portability.

---

## 3. Required Knowledge and Competencies

Engineers and agents applying this skill must demonstrate verified expertise in:
1. **Semantic Versioning (SemVer 2.0.0):** Precise interpretation of version constraints: caret (`^`), tilde (`~`), wildcard (`*`), exact pinning, and stability flags (`@dev`, `@stable`).
2. **Upstream Lifecycle Verification:** Distinguishing technical compatibility from vendor active support, security-only support, and end-of-life status using authoritative vendor lifecycle matrices.
3. **PHP 8.3 Technical Delta:** Typed class constants, dynamic class constant fetch (`ClassName::{$var}`), `#[\Override]` attribute, `json_validate()` function, and deprecation of dynamic properties.
4. **Laravel 11.x Skeleton Architecture:** Streamlined `bootstrap/app.php` configuration, routing, middleware registration via `withMiddleware()`, exception handling via `withExceptions()`, and removal of legacy service providers.
5. **Filament 5.x & Livewire 4.x Integration:** Panel provider lifecycle, Livewire 4 reactive component properties, event dispatching, and Tailwind CSS v4 asset compilation.
6. **Database Driver & Authentication Compatibility:** MySQL 8.4 LTS removal of legacy `mysql_native_password` authentication by default, requiring `caching_sha2_password`, and strict SQL mode handling.
7. **Cross-Platform Compatibility Mechanics:** Eliminating hardcoded directory separators (`/` vs `\`), enforcing `DIRECTORY_SEPARATOR`, and preventing autoloading defects caused by file-casing mismatches between Windows and Linux.
8. **Automated Compatibility Verification:** Tooling with Rector (`LevelSetList::UP_TO_PHP_83`, `LaravelSetList::LARAVEL_110`), PHPStan Level 8+, and `composer check-platform-reqs`.

---

## 4. Core Engineering Principles

### 4.1 Strict Platform Pinning (Never Guess Versions)
Dependencies must be vetted against the established architecture before introduction. Composer must explicitly lock the runtime platform PHP and extension versions in `composer.json` (`"platform": {"php": "8.3.0"}`) to prevent resolving packages that require higher engines or unsupported extensions.

### 4.2 The Six Compatibility Boundaries
To prevent false claims of compatibility, engineers and agents must categorize compatibility across six distinct levels:
1. **Level 1 — Officially Supported Compatibility:** Vendor explicitly documents compatibility in official release notes.
2. **Level 2 — Declared Package Compatibility:** `composer.json` or `package.json` constraints allow installation.
3. **Level 3 — Documentation-Based Compatibility:** Community or third-party documentation asserts compatibility.
4. **Level 4 — Test-Verified Compatibility:** Automated unit and feature test suites execute with 100% pass rate.
5. **Level 5 — Runtime/Integration Verified Compatibility:** Tested against live database, Redis, and actual network infrastructure under realistic conditions.
6. **Level 6 — Untested or Uncertain Compatibility:** Any version combination lacking Level 4 or Level 5 verification.

*Never describe a dependency combination as verified or production-ready merely because package constraints (Level 2) permit installation.*

### 4.3 Technical Compatibility vs Upstream Support Lifecycle
Technical compatibility (whether a piece of software executes without crashing) must **never** be conflated with upstream vendor support (whether the software is receiving patches and security fixes).

Universal Core establishes six distinct lifecycle states:
1. **Technically Compatible:** The software compiles, runs, and passes test suites on the targeted runtime without engine or syntax crashes.
2. **Officially Supported:** The version falls within the vendor's declared, active maintenance window.
3. **Receiving Upstream Security Updates:** The vendor still produces security patches for disclosed vulnerabilities, even if general bug fixes have ceased (Security Support phase).
4. **Receiving Upstream Bug Fixes:** The vendor actively resolves functional defects, performance regressions, and security advisories (Active Support phase).
5. **End-of-Life (EOL) / Unsupported:** The upstream maintainer no longer provides security patches, bug fixes, or official support. Disclosed vulnerabilities remain unpatched upstream.
6. **Tested by Project:** The specific version combination has been empirically executed and validated against the project's own automated test pyramid and CI/CD pipelines.

#### 4.3.1 Operational and Security Risks of Unsupported Versions
Operating a technically compatible but upstream End-of-Life version introduces severe risks:
- **Unpatched Vulnerabilities:** Disclosed CVEs will not receive official vendor patches, creating permanent attack surfaces unless patched in-house.
- **Compliance & Regulatory Violations:** Standards such as PCI-DSS, SOC 2, and ISO 27001 mandate that production systems run supported software.
- **Cascading Dependency Abandonment:** Upstream package maintainers drop support for EOL frameworks, blocking security updates across the entire dependency tree.

#### 4.3.2 Policy for Version Selection and Risk Acceptance
- **New Deployments:** Must target versions in **Active Support** or **Security Support** phases. Selecting an End-of-Life version for greenfield projects is prohibited.
- **Existing Baseline Retention (Legacy/Current Profiles):** Existing systems built on baseline versions that have reached upstream EOL (e.g., Laravel 11.x, Node.js 20.x) may continue operation only under a formal **Security Risk Acceptance (SRA)** process. This requires:
  1. Documented technical justification for retaining the baseline.
  2. Scheduled milestone roadmap for upgrading to the next supported major version.
  3. Continuous supply-chain monitoring (`composer audit`, Dependabot/Renovate) and commitment to manually backport critical security patches if necessary.

### 4.4 Dynamic Lifecycle Verification Policy
Technology lifecycle information is time-sensitive and must never be treated as permanently valid or statically hardcoded.

Engineers and AI agents must adhere to this mandatory policy:
1. **Verify Before Recommending:** Check official vendor lifecycle sources before making new deployment recommendations or proposing framework upgrades.
2. **Record Verification Date:** Record the exact date of verification on all lifecycle-sensitive documentation and Architecture Decision Records (ADRs).
3. **Identify Exact Product & Release Branch:** Disambiguate community open-source releases from commercial enterprise distributions (specifically Redis Open Source vs Redis Enterprise Software).
4. **Distinguish Release Stability:** Distinguish officially released stable versions from previews, alpha/beta releases, release candidates, and unsupported branches.
5. **Document Official EOL Dates:** Record upstream vendor EOL dates rather than estimating or assuming longevity.
6. **Revalidate During Upgrades:** Revalidate lifecycle status during every major upgrade, dependency refresh, and deployment milestone planning.
7. **Never Copy Stale Claims:** Avoid copying outdated lifecycle claims from older documentation, historical commits, or past project decisions.
8. **Require Authoritative Evidence:** Never claim active bug-fix or security support without verified, authoritative vendor evidence.

### 4.5 Native Local Environment Isolation
Universal Core requires local execution on Windows Laragon using native PowerShell. Docker and Laravel Sail are strictly prohibited as local development requirements. Do not introduce container dependencies or Dockerfiles into local workflows.

### 4.6 The 10-Step Version Compatibility Verification Framework
```
Step 1: Check Target Matrix Requirements in active-profile.json
Step 2: Inspect Upstream Lifecycle Status via Official Vendor Matrices
Step 3: Inspect composer.json & package.json Platform Configurations
Step 4: Verify PHP 8.3 Syntax, Attributes & Type Declarations
Step 5: Verify Laravel 11.x Architectural Conventions (bootstrap/app.php)
Step 6: Verify Filament 5.x & Livewire 4.x Compatibility
Step 7: Audit MySQL 8.4 LTS Dialect, Index DDL & Authentication Modes
Step 8: Run Rector in Dry-Run Mode to Detect Deprecated APIs
Step 9: Execute Composer Platform Checks (composer check-platform-reqs)
Step 10: Run Automated Test Suites & Produce Verifiable Audit Evidence
```

---

## 5. Mandatory Standards

### 5.1 Universal Core Ecosystem Support and Compatibility Matrix
*(Lifecycle status verified as of **November 14, 2026** against official vendor release documentation)*

| Technology | Baseline Target | Upstream Vendor Support Status (as of 2026-11-14) | Recommended for New Deployments | Upstream EOL Date & Official Notes |
| :--- | :--- | :--- | :--- | :--- |
| **PHP** | **8.3+** | **Security Support Only** for PHP 8.3 (active bug fixes ended Dec 31, 2025; security fixes through Dec 31, 2027). PHP 8.4 is in **Active Support** (active bug fixes through Dec 31, 2026; security through Dec 31, 2028). PHP 8.5 is in **Active Support** as the latest stable release (active bug fixes through Dec 31, 2027; security through Dec 31, 2029). | **PHP 8.5** (Latest Stable) or **PHP 8.4** (Actively Supported). Minimum compatibility baseline remains **PHP 8.3+**. | PHP 8.3 EOL: Dec 31, 2027; PHP 8.4 EOL: Dec 31, 2028; PHP 8.5 EOL: Dec 31, 2029 (`php.net/supported-versions.php`, verified 2026-11-14). Baseline 8.3+ remains technically compatible. |
| **Laravel** | **11.x** | **End-of-Life (EOL)** (Bug fixes ended Sep 12, 2025; security fixes ended Mar 12, 2026) | **Laravel 12.x / 13.x** (Actively supported) | EOL: Mar 12, 2026 (`laravel.com/docs/releases`). Universal Core baseline retains 11.x under documented SRA policy. |
| **MySQL** | **8.4 LTS** | **Actively Supported (Premier LTS)** (Premier support through April 2029; Extended through April 2032) | **MySQL 8.4 LTS** | EOL: April 2032 (Oracle Lifetime Support). Native `caching_sha2_password` enforced. |
| **Redis (Community & Source-Available)** | **7.x Protocol & Service** | **Actively Maintained Upstream**: Redis 7.2.x is legacy BSD-3-Clause open source; Redis 7.4.x is source-available under RSALv2/SSPLv1 (actively supported and maintained by Redis Ltd.); Redis 8.x is tri-licensed under AGPLv3/RSALv2/SSPLv1 with built-in modules. | **Redis 7.4+ / 8.x** (Source-Available / AGPLv3) or **Redis 7.2.x** (BSD-3-Clause). Valkey 7/8 is an independent, protocol-compatible alternative (not a Redis product). | Community-driven lifecycle (standard: 6 mos post-next minor; extended: up to 5 yrs). No commercial SLA (`redis.io/legal/licenses/`, verified 2026-11-14). Mandatory 7.x protocol baseline. |
| **Redis Enterprise** | **7.x / 8.x** | **Commercial Vendor Supported** under subscription contract. Support availability and SLAs depend on purchased Support Plan (24x7 coverage on Business and higher tiers; business hours on Basic/Developer). | **Redis Enterprise 7.x / 8.x** (under commercial subscription and designated Support Plan) | Product Lifecycle EOL: 24 months post-subsequent major release. Support Services Period: 18 months post-subsequent major release (Redis Support Services Policy effective Nov 1, 2026; `redis.io/legal/`, verified 2026-11-14). |
| **Composer** | **2.x** | **Actively Supported** (Continuous stable release line on GitHub; no formal LTS lifecycle published by Composer maintainers). | **Composer 2.11.2** (Verified latest stable release as of 2026-11-14; update via `composer self-update --2`). | Continuous stable release model without formal LTS (`getcomposer.org`, `github.com/composer/composer/releases`, verified 2026-11-14). Baseline Composer 2.x required; Composer 1.x is obsolete. |
| **Node.js** | **20+ (LTS)** | **End-of-Life (EOL)** for Node.js 20 (Maintenance ended April 30, 2026). Node.js 22 is in **Maintenance LTS** (Active ended Oct 21, 2025; EOL April 30, 2027). Node.js 24 is in **Maintenance LTS** (Active ended Oct 20, 2026; EOL April 30, 2028). Node.js 26 is **Active LTS** (Active LTS started Oct 28, 2026; Maintenance starts Oct 20, 2027; EOL April 30, 2029). | **Node.js 26 LTS (Active LTS)**. Existing baselines on Node.js 20.x operate under documented SRA policy; EOL Node.js 20 is never recommended for greenfield deployments. | Node 20 EOL: April 30, 2026; Node 22 EOL: April 30, 2027; Node 24 EOL: April 30, 2028; Node 26 EOL: April 30, 2029 (`nodejs.org/en/about/previous-releases`, verified 2026-11-14). Baseline Node.js 20+ preserved. |
| **Filament** | **5.x** | **Architectural Target Adapter** (Universal Core forward adapter target) | **Filament 5.x** (or active stable release) | Production builds must pin to tagged, stable releases. |
| **Livewire** | **4.x** | **Architectural Target Adapter** (Universal Core forward adapter target) | **Livewire 4.x** (or active stable release) | Integrated with Filament 5.x panels. |
| **React / TS** | **React 18+ / TS 5+** | **Actively Supported** | **React 18+ / TS 5+** | For decoupled client SPAs. |
| **Vue / Inertia**| **Vue 3 / Inertia.js** | **Actively Supported** | **Vue 3.x / Inertia.js 1.x+** | For server-driven monoliths. |

### 5.1.1 Authoritative Lifecycle & Licensing Specifications (Verified November 14, 2026)

#### 1. PHP Engine Lifecycle & Version Distinction
- **Authoritative Source:** PHP Supported Versions Matrix (`https://www.php.net/supported-versions.php`), verified November 14, 2026.
- **Vendor Support Model:** 2 years of active support (regular bug fixes and point releases) followed by 2 years of security fixes only (4-year total branch lifecycle).
- **Branch Lifecycle Breakdown:**
  - **PHP 8.3:** Initial release November 23, 2023. Active support (bug fixes) ended December 31, 2025. Currently in **Security Support Only** through December 31, 2027 (EOL).
  - **PHP 8.4:** Initial release November 21, 2024. In **Active Support** through December 31, 2026; Security Support through December 31, 2028 (EOL).
  - **PHP 8.5:** Initial release November 20, 2025. In **Active Support** through December 31, 2027; Security Support through December 31, 2029 (EOL). Represents the **latest stable release** branch.
- **Universal Core Governance:**
  - The minimum compatibility baseline remains strictly **PHP 8.3+**. Existing applications and packages must retain Level 4 / Level 5 compatibility with PHP 8.3.0.
  - New deployments and greenfield services must target actively supported branches: **PHP 8.5** (recommended latest stable) or **PHP 8.4** (actively supported).
  - The minimum compatibility baseline (`PHP 8.3+`) must never be conflated with the recommended deployment target (`PHP 8.5` / `PHP 8.4`).

#### 2. Node.js Runtime Lifecycle & Version Distinction
- **Authoritative Source:** Node.js Release Working Group Lifecycle Schedule (`https://nodejs.org/en/about/previous-releases` and `nodejs/Release schedule.json`), verified November 14, 2026.
- **Branch Lifecycle Breakdown:**
  - **Node.js 20 (Iron):** Initial release April 18, 2023. Active LTS began October 24, 2023. Maintenance LTS began October 22, 2024. Reached **End-of-Life (EOL)** on **April 30, 2026**.
  - **Node.js 22 (Jod):** Initial release April 24, 2024. Active LTS began October 29, 2024. Transitioned to **Maintenance LTS** on **October 21, 2025**. EOL date: **April 30, 2027**.
  - **Node.js 24 (Krypton):** Initial release May 6, 2025. Active LTS began October 28, 2025. Transitioned to **Maintenance LTS** on **October 20, 2026**. EOL date: **April 30, 2028**.
  - **Node.js 26 (Lithium):** Initial release May 5, 2026. Transitioned from Current to **Active LTS** on **October 28, 2026**. Maintenance LTS scheduled for October 20, 2027. EOL date: **April 30, 2029**.
- **Universal Core Governance:**
  - The compatibility baseline is preserved as **Node.js 20+** for backward compatibility with existing legacy toolchains and repositories operating under documented SRA policy.
  - An EOL baseline must **never** be described as a recommended deployment version. Greenfield builds and new deployments must target **Node.js 26 LTS (Active LTS)**.

#### 3. Composer Toolchain Lifecycle & Current Stable Release
- **Authoritative Sources:** Composer Official Site (`https://getcomposer.org/`) and GitHub Releases (`https://github.com/composer/composer/releases`), verified November 14, 2026.
- **Verified Stable Release:** **Composer 2.11.2** (released November 10, 2026).
- **Lifecycle & Maintenance Model:** The Composer maintainers do not publish a formal Long-Term Support (LTS) policy or fixed deprecation timeline for general 2.x minor branches. Maintenance is continuous across the active 2.x release stream.
- **Universal Core Governance:**
  - The compatibility baseline remains strictly **Composer 2.x**; Composer 1.x is obsolete and strictly prohibited.
  - Environments must use the official Composer 2.x self-update mechanism: `composer self-update --2`.

#### 4. Redis Product Architecture, Licensing & Lifecycle Disambiguation
- **Authoritative Sources:** Redis Licenses (`https://redis.io/legal/licenses/`), Redis OSS Management (`https://redis.io/docs/latest/operate/oss_and_stack/management/`), Redis Stack with Enterprise (`https://redis.io/docs/latest/operate/oss_and_stack/stack-with-enterprise/`), Redis Enterprise Release Notes (`https://redis.io/docs/latest/operate/rs/7.8/release-notes/`), and Redis Support Services Policy (`https://redis.io/legal/`), verified November 14, 2026.
- **Licensing Architecture by Version:**
  - **Redis 7.2 and Earlier:** Licensed under the permissive, OSI-approved **3-Clause BSD License**. Version 7.2.4 / 7.2.x represents the final minor release line distributed under BSD-3-Clause licensing.
  - **Redis 7.4:** Distributed under dual **source-available licensing**: the Redis Source Available License v2 (**RSALv2**) or the Server Side Public License v1 (**SSPLv1**). Redis 7.4 is **actively maintained and supported** by Redis Ltd. under this source-available model, receiving regular maintenance updates and security patches. It is not unsupported.
  - **Redis 8.x and Later:** Distributed under a **tri-license model**: users can select between **AGPLv3** (an OSI-approved copyleft open-source license), **RSALv2**, or **SSPLv1**. Starting with Redis 8.0, advanced data structures formerly packaged separately in Redis Stack (Search, JSON, TimeSeries, Probabilistic) are integrated directly into the core engine in version lockstep.
- **Lifecycle Classification & Support Commitment Separation:**
  - **Redis Community & Source-Available Lifecycle:** Governed by vendor release streams. Standard releases receive critical patches until 6 months after the next minor release; extended releases receive maintenance for up to 5 years. Community/source-available releases carry no commercial SLA, guaranteed response times, or vendor support commitments.
  - **Redis Enterprise Product Lifecycle (Product Lifecycle EOL):** Governed by the official Redis Enterprise Product Lifecycle policy (`redis.io/docs/latest/operate/rs/7.8/release-notes/`). For Redis Software cluster versions 6.2 and later, **Product Lifecycle EOL** for each major release occurs **24 months after the formal general availability of the subsequent major version**. Monthly maintenance is provided on the last minor release of that major version throughout this period to allow a predictable 24-month upgrade window. This constitutes the product lifecycle rule, distinct from the commercial support services period.
  - **Redis Enterprise Support Services (Support Services Period):** Governed by the official Redis Support Services Policy effective November 1, 2026 (`https://redis.io/legal/`). Under Section 2.6 (End of Life Policy), Redis Support Services cover each Major Release for an **18-month Support Services Period** following the general release of the subsequent Major Release. During this 18-month Support Services Period, Redis provides minor releases, updates to existing functionality, and bug fixes for the latest minor release; upon expiration of the Support Services Period, Redis ceases all support services for that major release. This 18-month Support Services Period must be strictly distinguished from the 24-month Product Lifecycle EOL.
  - **Redis Enterprise Support Plans and Service Level Agreements (SLAs):** Support availability, communication channels, response times, and SLAs depend strictly on the customer's purchased **Support Plan**. 24/7 support is **not** universal and is not included for every customer or subscription tier:
    - *Basic and Developer Plans:* Support coverage is restricted to standard Business Hours via web portal/ticketing.
    - *Business and Higher Support Plans:* Provide **24x7 coverage** for production environments, phone support, ticket escalation, and contractual response SLAs (e.g., 1-hour target response for P1 Critical issues, 4-hour for P2 High issues, with Root Cause Analysis investigations).
- **Ecosystem Boundary Regarding Valkey:**
  - Valkey (7.x / 8.x) is an independent open-source fork (BSD-3-Clause) maintained under the Linux Foundation.
  - Valkey is **not** a Redis product; it is documented solely as a separate, protocol-compatible open-source alternative where permissive licensing is mandated.
- **Universal Core Governance:**
  - Universal Core maintains mandatory **Redis 7.x** protocol and service compatibility across all deployment profiles.
  - Blurring or combining Redis Open Source / Source-Available lifecycle policies with Redis Enterprise SLA commitments remains strictly prohibited.

### 5.2 Dependency Management Governance

#### 5.2.1 Composer SemVer Invariants
1. **Constraint Selection:**
   - Production framework and core libraries must use caret constraints: `"laravel/framework": "^11.0"`.
   - Experimental or volatile packages must use minor-pinned constraints: `"vendor/package": "~1.2.0"`.
   - Never use wildcard constraints (`"*"`) or stability flags like `@dev` in production dependencies.
2. **Platform Pinning in `composer.json`:**
   ```json
   {
       "config": {
           "platform": {
               "php": "8.3.0",
               "ext-redis": "6.0.0",
               "ext-pdo_mysql": "8.3.0",
               "ext-sodium": "8.3.0",
               "ext-bcmath": "8.3.0",
               "ext-mbstring": "8.3.0"
           },
           "optimize-autoloader": true,
           "preferred-install": "dist",
           "sort-packages": true
       }
   }
   ```
3. **Lockfile Integrity & Installation:**
   - `composer.lock` and `package-lock.json` must be committed to version control.
   - Production and CI builds must use `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`. Never run `composer update` in automated production deployment pipelines.
   - Composer CLI updates must follow the official maintainer command: `composer self-update --2`.

#### 5.2.2 Transitive Dependency Conflict Resolution
- Before introducing a new package, inspect transitive dependencies using:
  ```bash
  composer why-not <vendor/package> <version>
  composer prohibits <vendor/package> <version>
  ```
- If two packages require conflicting versions of a shared transitive library, do not force installation via `--ignore-platform-reqs`. Resolve conflicts through upstream package upgrades, community PRs, or formal Architecture Decision Records (ADRs).

### 5.3 PHP 8.3 & Laravel 11 Language and Framework Governance
1. **Dynamic Class Constant Fetch:**
   Use PHP 8.3 dynamic constant syntax instead of `constant()`:
   ```php
   $status = OrderStatus::{$statusName};
   ```
2. **`#[\Override]` Attribute:**
   Methods that implement an interface method or override a parent class method must specify the `#[\Override]` attribute:
   ```php
   #[\Override]
   public function handle(): void
   {
       // Implementation
   }
   ```
3. **Model Cast Invariants:**
   Laravel 11 model casting must be declared via the `casts()` method, not the legacy `$casts` property:
   ```php
   protected function casts(): array
   {
       return [
           'is_active' => 'boolean',
           'options' => 'encrypted:array',
       ];
   }
   ```

### 5.4 Cross-Platform Traversal Standards
1. **Filesystem Separator Portability:**
   Hardcoding backslashes (`\`) or forward slashes (`/`) into filesystem paths causes runtime failures across Windows Laragon and Linux servers. Use `DIRECTORY_SEPARATOR` or path joining utilities:
   ```php
   $filePath = storage_path('app' . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . $filename);
   ```
2. **Autoloading Case Sensitivity:**
   Windows NTFS is case-insensitive, while Linux production filesystems (ext4) are case-sensitive. Namespace declarations and filenames must match class names exactly to prevent fatal class-not-found errors during production deployment.

### 5.5 Controlled Upgrade Engineering Workflow
Upgrades must follow a structured, phased methodology:
1. **Phase 1: Dynamic Lifecycle & Impact Analysis:** Review official vendor lifecycle matrices, verify that the target upgrade is in active support, check breaking changes, and run `composer outdated --direct`.
2. **Phase 2: Deprecation Scanning:** Execute Rector in dry-run mode and PHPStan Level 8+ to flag deprecated methods.
3. **Phase 3: Sequencing:** Upgrade in order: Language Engine -> Framework Core -> First-Party Extensions -> Third-Party Libraries -> Frontend Packages.
4. **Phase 4: Automated Verification:** Execute the entire test suite on the target platform.
5. **Phase 5: Atomic Commit:** Commit `composer.json` and `composer.lock` together with clear semantic release notes.

---

## 6. Implementation Procedures and Practices

### 6.1 Enterprise Runtime Compatibility Validator (PHP 8.3, Extensions, MySQL & Redis)

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Compatibility;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use RuntimeException;

final readonly class RuntimeCompatibilityValidator
{
    private const string MIN_PHP_VERSION = '8.3.0';
    private const array REQUIRED_EXTENSIONS = [
        'pdo_mysql',
        'redis',
        'opcache',
        'bcmath',
        'mbstring',
        'sodium',
        'intl',
    ];

    /**
     * Verifies platform engine, extensions, database, and Redis runtime compatibility.
     *
     * @return array{status: 'ok'|'error', details: array<string, mixed>}
     */
    public function validate(): array
    {
        $details = [];

        // 1. PHP Engine Check
        $phpVersionOk = version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>=');
        $details['php'] = [
            'version' => PHP_VERSION,
            'compatible' => $phpVersionOk,
            'required' => self::MIN_PHP_VERSION,
        ];

        if (! $phpVersionOk) {
            throw new RuntimeException("Incompatible PHP version: " . PHP_VERSION . ". Minimum required: " . self::MIN_PHP_VERSION);
        }

        // 2. Extensions Verification
        $missingExtensions = [];
        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            $loaded = extension_loaded($ext);
            $details['extensions'][$ext] = $loaded;
            if (! $loaded) {
                $missingExtensions[] = $ext;
            }
        }

        if (! empty($missingExtensions)) {
            throw new RuntimeException("Missing mandatory PHP extensions: " . implode(', ', $missingExtensions));
        }

        // 3. MySQL Server Dialect Check
        try {
            $versionString = (string) DB::selectOne("SELECT VERSION() as v")?->v;
            $details['database'] = [
                'engine' => 'MySQL',
                'raw_version' => $versionString,
                'accessible' => true,
            ];
        } catch (\Throwable $e) {
            $details['database'] = [
                'accessible' => false,
                'error' => $e->getMessage(),
            ];
        }

        // 4. Redis Availability Check
        try {
            $redisPong = Redis::ping();
            $details['redis'] = [
                'accessible' => true,
                'response' => (string) $redisPong,
            ];
        } catch (\Throwable $e) {
            $details['redis'] = [
                'accessible' => false,
                'error' => $e->getMessage(),
            ];
        }

        return [
            'status' => 'ok',
            'details' => $details,
        ];
    }
}
```

### 6.2 Pre-Deployment Automated Compatibility Check Routine

```bash
# 1. Strict Composer validation
composer validate --strict --check-lock

# 2. Verify platform requirements against current runtime
composer check-platform-reqs --no-dev

# 3. Rector dry-run to identify deprecated framework and language usage
vendor/bin/rector process --dry-run

# 4. PHPStan static analysis for type and attribute validity
vendor/bin/phpstan analyse --level=8
```

---

## 7. Prohibited Practices and Their Rationale

1. **PROHIBITED: Bypassing Composer Platform Requirements (`--ignore-platform-reqs`).** Resolves dependency trees that fail or crash at runtime due to missing extensions or incompatible PHP versions.
2. **PROHIBITED: Running `composer update` Directly in Production.** Alters lockfiles unpredictably on live servers, introducing untested transient dependency versions.
3. **PROHIBITED: Enforcing Docker or Laravel Sail for Local Development.** Windows Laragon provides native, high-performance tooling; forcing Docker introduces hypervisor overhead and violates workspace conventions.
4. **PROHIBITED: Selecting End-of-Life Versions for Greenfield Projects.** Introducing unsupported technologies without upstream security patches creates immediate compliance and security vulnerabilities.
5. **PROHIBITED: Combining Redis Open Source and Redis Enterprise Lifecycle Policies.** Blurring community OSS maintenance with commercial Redis Enterprise SLA schedules leads to incorrect support and maintenance assumptions.
6. **PROHIBITED: Wildcard or Blind Dependency Constraints (`"*"`).** Leaves systems vulnerable to unexpected breaking changes in upstream dependencies.
7. **PROHIBITED: Modifying Files Inside `vendor/` or `node_modules/`.** Direct edits are wiped on every dependency reinstall and cannot be tracked in version control.
8. **PROHIBITED: Hardcoding OS-Specific Path Separators (`/` or `\`).** Breaks cross-platform portability between Windows Laragon local machines and Linux production servers.
9. **PROHIBITED: Upgrading Framework Majors Without Pre-Upgrade Impact Audits.** Major versions contain breaking architectural changes that require formal ADR sign-off and phased testing.
10. **PROHIBITED: Ignoring `composer audit` or `npm audit` Vulnerability Advisories.** Unpatched vulnerabilities expose applications to supply-chain attacks and known exploits.

---

## 8. Security and Reliability Requirements

1. **Lockfile Governance:** Every pull request altering `composer.json` or `package.json` must include the corresponding `composer.lock` or `package-lock.json` in the same commit.
2. **Platform Extension Verification:** Production deployments must verify required extensions (`ext-sodium`, `ext-redis`, `ext-pdo_mysql`) using `composer check-platform-reqs` before taking traffic.
3. **MySQL 8.4 LTS Authentication:** Production connection strings must support `caching_sha2_password` authentication plugin natively via PHP 8.3 PDO MySQL.
4. **Security Risk Acceptance (SRA) Documentation:** If an existing application operates on a baseline version whose upstream security support has expired (e.g., Laravel 11.x, Node.js 20.x), the architecture team must maintain an active SRA document with an explicit patch mitigation strategy and upgrade roadmap.

---

## 9. Testing and Verification

### 9.1 Verification Hierarchy
1. **Local Platform Verification (Windows PowerShell):**
   ```powershell
   composer validate --strict --check-lock
   composer check-platform-reqs
   vendor/bin/phpstan analyse --level=8
   php artisan test --parallel
   ```
2. **Hosted CI Matrix Testing:**
   Hosted GitHub Actions pipelines must run against PHP 8.3, MySQL 8.4, and Redis 7.x, verifying full integration.
3. **Rector Automated Scans:**
   CI checks must run Rector in dry-run mode to detect deprecation regressions prior to merge approval.

---

## 10. Common Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Engineering Prevention Rule |
| :--- | :--- | :--- |
| **Unpatched CVE on EOL Framework** | Operating an EOL framework without security patches. | Require Active or Security Support for new projects; enforce SRA for legacy baselines. |
| **Class Not Found on Linux Deploy** | Case mismatch between filename and class namespace (silent on Windows). | Enforce PSR-4 strict casing; test in CI on Linux runners. |
| **Missing Driver Extension in Production** | `ext-redis` not installed on server, but bypassed during build. | Run `composer check-platform-reqs --no-dev` during deployment. |
| **Broken Database Connection on MySQL 8.4** | Server mandates `caching_sha2_password`; legacy driver fails handshake. | Enforce PHP 8.3 native PDO MySQL drivers with SSL support. |
| **Fatal Type Error on Method Override** | Child class signature drifts from updated parent package method. | Use PHP 8.3 `#[\Override]` attribute to catch drifts at compile-time. |
| **Unintended Dependency Breakage** | Developer ran `composer update` instead of `composer require`. | Require lockfile diff reviews in PR gating; ban unconstrained updates. |
| **Local Windows Container Friction** | Attempting to run Docker / Sail on Windows Laragon. | Prohibit Docker locally; enforce native Laragon PHP 8.3 + MySQL 8.4 tooling. |

---

## 11. Acceptance Criteria

Before approving any version compatibility deliverable:
- [ ] Code targets PHP 8.3+ with `declare(strict_types=1);` and `#[\Override]` on overrides.
- [ ] Upstream support status is distinguished from technical compatibility across all referenced technologies.
- [ ] Greenfield projects target actively supported versions; baseline retentions have documented SRA records.
- [ ] Redis Open Source and Redis Enterprise lifecycle classifications are strictly separated.
- [ ] Dynamic Lifecycle Verification Policy is enforced for all upgrade recommendations.
- [ ] Framework conventions strictly conform to Laravel 11.x architecture (`bootstrap/app.php`).
- [ ] Filament components target Filament 5.x with Livewire 4.x.
- [ ] Relational schema and queries are compatible with MySQL 8.4 LTS and 8.0+.
- [ ] Redis 7.x connectivity and mandatory availability are verified.
- [ ] `composer.json` includes strict platform pinning in the `config` block.
- [ ] `composer check-platform-reqs` and `composer validate --strict` pass with zero errors.
- [ ] Local development workflow instructions reference native Windows Laragon and PowerShell with zero Docker requirements.
- [ ] Compatibility boundary is explicitly classified (Levels 1–6); no unverified claims.
- [ ] Zero unpatched high or critical vulnerabilities reported by `composer audit` and `npm audit`.

---

## 12. References and Related Skills

- **Related Skills:**
  - `SKILL-LARAVEL-001` (Laravel Engineering Standards)
  - `SKILL-PHP-001` (PHP Modern Engineering Standards)
  - `SKILL-DEVOPS-001` (CI/CD Pipeline and DevOps Infrastructure Standards)
  - `SKILL-CRYP-001` (Cryptography and Key Lifecycle Management Standards)
  - `SKILL-DB-001` (Database Engineering and Schema Governance Standards)
  - `SKILL-FILA-001` (Filament 5.x Panel and Livewire Standards)
- **Authoritative Specifications & Lifecycle Sources:**
  - PHP Supported Versions Matrix: `https://www.php.net/supported-versions.php`
  - Node.js Release Working Group Lifecycle Schedule: `https://nodejs.org/en/about/previous-releases`
  - Composer Official Documentation and Downloads: `https://getcomposer.org/`
  - Composer GitHub Releases: `https://github.com/composer/composer/releases`
  - Redis Open Source & Enterprise Documentation: `https://redis.io/docs/latest/operate/oss_and_stack/stack-with-enterprise/`
  - Redis Enterprise Release Notes & Product Lifecycle: `https://redis.io/docs/latest/operate/rs/7.8/release-notes/`
  - Redis Legal & Support Policies (Support Services Policy effective Nov 1, 2026): `https://redis.io/legal/`
  - Redis Management & OSS Lifecycle: `https://redis.io/docs/latest/operate/oss_and_stack/management/`
  - Redis Official Licensing Documentation: `https://redis.io/legal/licenses/`
  - Oracle Lifetime Support Policy for MySQL Releases: `https://www.oracle.com/us/assets/lifetime-support-technology-069183.pdf`
  - Laravel Release and Support Timeline: `https://laravel.com/docs/releases`
