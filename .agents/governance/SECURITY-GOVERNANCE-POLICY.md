# Security Governance Policy

**Document ID:** GOV-DOC-007  
**Version:** 1.1.0  
**Classification:** Enterprise Security Standard (Precedence Tier 3)  
**Effective Date:** 2026-11-15  
**Binding Architecture:** ARCH-GOV-DES-001 (1.1.0-DESIGN-REVIEWED)  

---

## 1. Zero-Trust Security Baseline
All software components developed by this organization must operate under the assumption that networks are hostile, external inputs are adversarial, and perimeter defenses alone are insufficient.

---

## 2. Core Cryptographic Invariants
1. **AEAD Nonce Uniqueness:** Symmetric encryption must use AES-256-GCM or ChaCha20-Poly1305. A 96-bit initialization vector (nonce) must NEVER be reused with the same encryption key. Nonces must be generated using cryptographically secure random bytes (`random_bytes()`).
2. **Password Hashing:** Passwords must be hashed using Argon2id (`PASSWORD_ARGON2ID`) or bcrypt with work factor ≥ 12. MD5, SHA-1, SHA-256 without salt, and DES are strictly forbidden.
3. **Secret Isolation & Key Management:**
   - Production secrets, signing keys, and certificates must NEVER be committed to source code or embedded in AI agent prompt contexts.
   - Master keys must be stored in external secret managers or isolated environment configurations.
   - The Gate 4 Human HMAC-SHA256 signing secret is strictly reserved to the Human Sponsor and must never be exposed to or readable by agent processes.

---

## 3. Defense-in-Depth Implementation Standards
1. **SQL Parameterization & Identifier Whitelisting:**
   - All SQL queries must use parameterized prepared statements via PDO/Eloquent.
   - Dynamic table names, column names, and sort directions must be strictly checked against static whitelists; string concatenation of user input into SQL clauses is completely prohibited.
2. **Context-Aware XSS Prevention:**
   - Blade auto-escaping `{{ }}` is mandatory.
   - Unescaped `{!! !!}` requires explicit justification and prior sanitization via approved HTML purifiers.
   - Content-Security-Policy (CSP) headers must be enforced on all web responses.
3. **SSRF Defense-in-Depth:**
   - Outbound HTTP requests must validate target URLs against an explicit domain whitelist.
   - Private, link-local, loopback, and metadata ranges (RFC 1918, 127.0.0.0/8, 169.254.169.254) must be blocked before socket connection.
   - Automatic HTTP redirect following must be restricted or re-validated on each hop.
4. **Multi-Tenant Isolation:**
   - Multi-tenant data structures must enforce strict tenant boundaries at the database layer (e.g. global scopes or tenant-specific schemas).
   - Every read and write query must explicitly verify tenant ownership. Cross-tenant leakage is classified as a Severity 1 Security Incident.
