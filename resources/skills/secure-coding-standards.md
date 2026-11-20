# Shared Engineering Skill: Secure Coding and Defensive Engineering Standards

**Skill ID:** SKILL-SEC-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Secure Coding and Defensive Engineering Standards** skill establishes the authoritative enterprise standard for developing secure, resilient, high-assurance software across Universal Core.

Its primary purpose is to systematically eliminate application security vulnerabilities, enforce defense-in-depth principles, guarantee strict multi-tenant and cross-project isolation, and secure data at rest, in transit, and in memory. This skill operationalizes standards from the **OWASP Application Security Verification Standard (ASVS v4.0.3)**, **OWASP Top 10**, and **CWE/SANS Top 25**, and incorporates reference controls from **NIST SP 800-53**, **ISO 27001**, **SOC 2 Type II**, and **PCI DSS v4.0** (without claiming premature compliance certification).

---

## 2. Scope and Applicability

This standard applies universally across all applications, APIs, worker pipelines, database schemas, frontend clients, and cloud infrastructure integration components in Universal Core.

- **Threat Scope:** Mitigation of injection flaws (SQLi, Command, LDAP), broken authentication, sensitive data exposure, XML External Entities (XXE), broken access control (IDOR, privilege escalation), security misconfigurations, Cross-Site Scripting (XSS), insecure deserialization, Server-Side Request Forgery (SSRF), and software supply chain vulnerabilities.
- **Data Scope:** Protection of Personally Identifiable Information (PII), authentication tokens, cryptographic secrets, financial records, and proprietary tenant assets.
- **Architectural Scope:** Request ingress, session life cycle, validation, data isolation, cryptographic boundaries, storage, logging, telemetry, and external integrations.

---

## 3. Required Knowledge and Competencies

Engineers and agents utilizing this skill must possess verified proficiency in:
1. **Threat Modeling & Attack Surface Analysis:** STRIDE and DREAD methodologies, trust boundary analysis, and data flow modeling.
2. **Defensive Cryptography:** Symmetric encryption (AES-256-GCM, XChaCha20-Poly1305), secure password hashing (Argon2id, Bcrypt with work factor $\ge 12$), authenticated key exchange, and the `#[\SensitiveParameter]` attribute.
3. **Identity & Access Management (IAM):** Role-Based Access Control (RBAC), Permission-Based Access Control (PBAC), Multi-Factor Authentication (MFA), and session fixation countermeasures.
4. **Strict Isolation Architecture:** Multi-tenant query scoping, isolated filesystem namespaces, tenant-aware cache prefixing, and cross-project data leakage prevention.
5. **Security Telemetry and Forensics:** Immutable structured audit logging, W3C trace contexts, log redaction (PII/secrets masking), and SIEM integration.

---

## 4. Core Engineering Principles

1. **Defense in Depth:** Security must never rely on a single layer of protection. Input validation, type safety, authorization policies, database constraints, and network boundaries must independently enforce security.
2. **Principle of Least Privilege:** Every component, service account, and authenticated user must operate with the absolute minimum access required to execute its immediate task.
3. **Fail Securely:** When a security check, validation rule, or authentication mechanism fails, the system must immediately default to a closed state: deny access, terminate execution, log the event, and provide a generic, safe response.
4. **Complete Multi-Tenant & Cross-Project Isolation:** A tenant or project boundary is an absolute cryptographic and relational perimeter. Zero cross-tenant data leakage is tolerated.
5. **Zero Implementation Debt:** Security controls must be genuinely implemented and tested. No mock security checks, bypassed authentication gates, or stubbed authorization handlers are permitted in production.

---

## 5. Mandatory Standards

### 5.1 Universal Code Quality Standards

#### 5.1.1 File Size and Single Responsibility Limit
- **Maximum 300 lines per code file:** All security-relevant source files (Policies, Middlewares, Guards, Value Objects, Validators) must not exceed 300 physical lines.
- **Mandatory by default:** Any exception requires documented technical justification and explicit security authority approval.
- **Decomposition:** Separate authentication handlers, token validators, and policy checks into cohesive, decoupled classes.
- **Preservation of Quality:** Never compress code, remove security comments, or sacrifice clarity to meet the line limit.

#### 5.1.2 Code Readability and Semantic Expressiveness
- **Transparent Logic:** Security policies and access checks must be immediately readable without mental gymnastics.
- **Explicit Access Rules:** State precisely what is authorized (e.g., `userCanModifyInvoiceItem()` instead of `check()`).
- **No Magic Flags:** Access control flags, roles, and permission keys must be defined in typed Enums or constants.

#### 5.1.3 Implementation Integrity
- **No Mock or Placeholder Gates:** Writing `public function authorize(): bool { return true; }` without implemented permissions is a critical vulnerability.
- **No Swallowed Security Exceptions:** Catching security exceptions without audit logging or rethrowing is strictly prohibited.
- **No Hardcoded Credentials:** Hardcoded passwords, API secrets, or test encryption keys in source repositories constitute an immediate security gating failure.

### 5.2 Attack Surface Defenses

#### 5.2.1 SQL Injection Prevention (OWASP A03:2021)
- **100% Parameterized Queries:** Never concatenate user input into raw SQL queries.
- **Safe Eloquent & Query Builder Usage:**
  ```php
  // PROHIBITED:
  DB::statement("SELECT * FROM users WHERE email = '" . $request->email . "'");

  // MANDATORY:
  DB::select('SELECT * FROM users WHERE email = :email', ['email' => $request->email]);
  ```
- **Raw SQL Gate:** Any use of `whereRaw()`, `havingRaw()`, or `DB::raw()` must be accompanied by explicit parameterized bindings.

#### 5.2.2 Cross-Site Scripting (XSS) Prevention (OWASP A03:2021)
- **Context-Aware Encoding:** Blade auto-escaping `{{ $variable }}` must be used by default.
- **Unescaped Output Prohibition:** Unescaped Blade output `{!! $html !!}` is strictly prohibited unless the string has been passed through a verified HTML purifier with an explicit allowlist of safe tags.
- **Content Security Policy (CSP):** Enforce strict CSP headers disabling inline script evaluation (`unsafe-inline`) and restricting script sources to trusted origins with cryptographic nonces.

#### 5.2.3 Cross-Site Request Forgery (CSRF) Prevention (OWASP A01:2021)
- **Synchronizer Token Pattern:** All state-changing HTTP methods (`POST`, `PUT`, `PATCH`, `DELETE`) must validate CSRF tokens via Laravel's `VerifyCsrfToken` middleware.
- **SameSite Cookies:** Session cookies must be configured with `SameSite=Lax` or `SameSite=Strict`, `Secure=true`, and `HttpOnly=true`.

#### 5.2.4 Server-Side Request Forgery (SSRF) Prevention (OWASP A10:2021)
- **URL Whitelisting & DNS Pinning:** Any external URL provided by a user or external webhook must be validated against an explicit scheme allowlist (`https` only) and resolved DNS IPs must be validated before connection.
- **Private IP Blocking:** Prohibit connections to localhost and private networks:
  - `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`
  - Loopback: `127.0.0.0/8`, `::1`
  - Cloud Metadata services: `169.254.169.254`

#### 5.2.5 Insecure Direct Object References (IDOR) & Broken Access Control (OWASP A01:2021)
- **Ownership Verification:** Every request modifying an object must verify that the authenticated actor owns or has explicit permission over that entity.
- **Scoped Route Model Binding:** Always apply `scopeBindings()` to nested routes to ensure child entities belong strictly to parent entities.

### 5.3 Multi-Tenant and Project-Level Isolation
- **Tenant Scope Enforcement:** Every query accessing tenant data must include a tenant filter. Using global scopes is mandatory for tenant-isolated models.
- **Tenant Context Verification:** Before executing any operation, the active tenant context must be verified against the authenticated user's assigned tenants.
- **Storage and Cache Isolation:**
  - File uploads must be placed in tenant-partitioned paths: `storage/tenants/{tenant_id}/documents/{uuid}`.
  - Cache entries must use tenant-prefixed keys: `Cache::tags(["tenant:{$tenantId}"])` or `tenant:{$tenantId}:key`.

### 5.4 Cryptographic Engineering and Secret Management
- **Approved Algorithms:** Use **AES-256-GCM** or **XChaCha20-Poly1305** for symmetric encryption. Use **Argon2id** (memory: 64MB, time: 4, threads: 1) or **Bcrypt** (cost $\ge 12$) for password hashing.
- **Database Column Encryption:** PII, social security numbers, banking details, and access tokens must be encrypted in the database using Laravel's `$casts = ['ssn' => 'encrypted']`.
- **Environment Variable Isolation:** Credentials and secrets must reside strictly in environment variables (`.env`). Committing `.env` files or hardcoding secrets into Git repositories is strictly prohibited.
- **Sensitive Parameter Redaction:** Annotate sensitive credentials with `#[\SensitiveParameter]` to prevent exposure in logs or stack traces.

### 5.5 Secure File Upload Handling
1. **MIME Type and Magic Byte Validation:** Validate MIME types via server-side file inspection (`finfo_file`), not user-supplied HTTP headers or extensions.
2. **Filename Sanitization:** Discard original filenames; store files using cryptographically random UUIDv7 or SHA-256 hashes.
3. **Execution Prevention:** Uploads must be stored outside the public web root or on private cloud object storage with direct execution disabled.

### 5.6 Audit Logging and Security Monitoring
1. **Security Event Logging:** Log all authentication successes, authentication failures, password resets, authorization rejections, privilege escalations, and sensitive data exports.
2. **PII and Secret Redaction:** Audit logs must never contain plain text passwords, API keys, full credit card numbers, or sensitive PII.
3. **Production Error Masking:** Production configurations must set `APP_DEBUG=false`. Detailed stack traces must never be presented to end users.

---

## 6. Implementation Procedures and Practices

### 6.1 SSRF-Safe HTTP Client Utility
The following implementation demonstrates safe outbound HTTP requests with private IP suppression:

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Security\Exceptions\SsrfAttemptException;

final class SafeHttpClient
{
    private const array BLOCKED_CIDRS = [
        '0.0.0.0/8',
        '10.0.0.0/8',
        '100.64.0.0/10',
        '127.0.0.0/8',
        '169.254.0.0/16',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '::1/128',
        'fc00::/7',
        'fe80::/10',
    ];

    public function assertSafeUrl(string $url): void
    {
        $parts = parse_url($url);
        
        if (!isset($parts['scheme'], $parts['host']) || $parts['scheme'] !== 'https') {
            throw new SsrfAttemptException("Only HTTPS scheme is permitted.");
        }

        $host = $parts['host'];
        $ips = dns_get_record($host, DNS_A + DNS_AAAA);

        if (empty($ips)) {
            throw new SsrfAttemptException("Unable to resolve DNS for host: {$host}");
        }

        foreach ($ips as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($ip && $this->isPrivateIp($ip)) {
                throw new SsrfAttemptException("Target IP {$ip} resolves to a private or restricted network.");
            }
        }
    }

    private function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
```

### 6.2 Defensive Multi-Tenant Policy Verification
```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Billing\Models\Invoice;

final class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        // Enforce tenant boundary
        if ($user->current_tenant_id !== $invoice->tenant_id) {
            return false;
        }

        return $user->hasPermissionTo('invoices.view');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        // Deny modification across tenants
        if ($user->current_tenant_id !== $invoice->tenant_id) {
            return false;
        }

        if ($invoice->isPaid()) {
            return false;
        }

        return $user->hasPermissionTo('invoices.update');
    }
}
```

---

## 7. Prohibited Practices

1. **PROHIBITED:** Concatenating raw variables into SQL queries, table names, or raw clauses.
2. **PROHIBITED:** Using `{!! !!}` in Blade templates without explicit sanitization by an approved HTML purifier.
3. **PROHIBITED:** Bypassing CSRF verification on state-changing routes without formal architectural sign-off.
4. **PROHIBITED:** Making outbound HTTP requests to user-supplied URLs without SSRF private IP validation.
5. **PROHIBITED:** Storing unencrypted passwords, credit card numbers, or cryptographic keys in database tables.
6. **PROHIBITED:** Hardcoding secrets, passwords, or tokens in codebase source files or version control.
7. **PROHIBITED:** Setting `APP_DEBUG=true` in production environments.
8. **PROHIBITED:** Allowing users to access resources of another tenant or project (cross-tenant leakage).
9. **PROHIBITED:** Executing file uploads directly into web-accessible directories with original client filenames.
10. **PROHIBITED:** Suppressing security exceptions or logging sensitive credentials in audit trails.

---

## 8. Security and Reliability Requirements

1. **Brute Force & Rate Limiting:** Enforce tiered rate limiting on all authentication, password reset, and sensitive API endpoints (e.g., 5 attempts per minute per IP/username).
2. **Session Invalidation:** On password change, privilege alteration, or logout, immediately invalidate all active sessions and refresh tokens across all devices.
3. **Dependency Scanning:** CI/CD pipelines must execute automated dependency audits (`composer audit`, `npm audit`) and fail builds upon detection of critical or high CVEs.
4. **Subresource Integrity (SRI):** When loading assets from external CDNs, include cryptographic SRI hashes (`integrity="sha384-..."`).
5. **Secure Headers:** Emit enterprise HTTP security headers:
   - `X-Content-Type-Options: nosniff`
   - `X-Frame-Options: DENY`
   - `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
   - `Referrer-Policy: strict-origin-when-cross-origin`

---

## 9. Testing and Verification

### 9.1 Static and Dynamic Verification
- **Static Application Security Testing (SAST):** Run automated scanners with security rules enabled.
- **Dependency Audit:**
  ```bash
  composer audit
  ```
- **Automated Authorization Tests:** Feature tests must assert `403 Forbidden` when attempting cross-tenant access, unauthorized parameter modifications, or missing CSRF tokens.

### 9.2 Verification Checklist
- Execute penetration testing assertions against endpoints:
  - SQL injection payloads (verify safe parameterization).
  - Cross-tenant IDOR probes (verify rejection).
  - XSS payload injection (verify sanitization/encoding).

---

## 10. Common Failure Scenarios and Prevention

| Vulnerability | Attack Vector | Engineering Prevention Rule |
| :--- | :--- | :--- |
| **Cross-Tenant IDOR** | Attacker increments `tenant_id` or invoice ID to access competitor data. | Enforce `scopeBindings()` and mandatory Policy checks matching tenant ID. |
| **SQL Injection via OrderBy** | User passes unsanitized string into `->orderBy($column)`. | Whitelist sort columns against an allowed enum array. |
| **SSRF to Cloud Metadata** | User triggers webhook pointing to `http://169.254.169.254`. | Validate IP against private CIDR blocklist before socket connection. |
| **Credential Leak in Sentry** | Exception thrown during failed login leaks password in payload. | Enforce `#[\SensitiveParameter]` on all credential parameters. |
| **Stored XSS via Markdown** | User enters `<script>` in markdown notes displayed to admin. | Sanitize markdown using a hardened purifier before rendering. |

---

## 11. Acceptance Criteria

Before signing off on any code modification with security impact:
- [ ] File has `declare(strict_types=1);` and physical line length is $\le 300$ lines.
- [ ] All database queries are 100% parameterized; zero raw variable interpolations.
- [ ] No unescaped output `{!! !!}` is used without approved sanitization.
- [ ] All state-changing routes enforce CSRF verification.
- [ ] Multi-tenant isolation is verified with explicit tenant ownership checks in Policies.
- [ ] SSRF defenses are active on any outbound HTTP request using external URLs.
- [ ] Credentials and sensitive fields are protected with `#[\SensitiveParameter]`.
- [ ] PII and cryptographic keys are encrypted at rest with AES-256-GCM.
- [ ] `composer audit` returns zero high or critical vulnerabilities.
- [ ] Automated security tests verify `401 Unauthorized` and `403 Forbidden` enforcement.

---

## 12. References and Related Skills

- **Related Skills:**
  - `SKILL-PHP-001` (PHP Modern Engineering Standards)
  - `SKILL-LARAVEL-001` (Laravel Engineering Standards)
  - `SKILL-DB-001` (Database Engineering and Schema Governance Standards)
  - `SKILL-CRYP-001` (Cryptography and Key Lifecycle Management Standards)
- **External Specifications & Standards:**
  - OWASP Application Security Verification Standard (ASVS) v4.0.3
  - OWASP Top 10 Web Application Security Risks
  - NIST Special Publication 800-53 Rev. 5
  - CWE/SANS Top 25 Most Dangerous Software Weaknesses
