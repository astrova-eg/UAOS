# Shared Engineering Skill: Cryptography and Key Lifecycle Management Standards

**Skill ID:** SKILL-CRYP-001  
**Version:** 2.0.0  
**Classification:** Reusable Engineering Skill  
**Status:** Active  

---

## 1. Skill Identity and Purpose

The **Cryptography and Key Lifecycle Management Standards** skill establishes the authoritative enterprise standard for cryptographic primitives, data encryption at rest and in transit, key lifecycle management, password security, and tenant cryptographic isolation across Universal Core.

Its primary purpose is to safeguard sensitive financial, personal (PII), and proprietary data throughout its complete lifecycle. It guarantees data confidentiality, authenticity, and integrity by mandating authenticated encryption (AEAD), secure key derivation, automated key rotation, and zero-leakage error handling, while eliminating insecure custom cryptography, broken algorithms, and key exposure.

---

## 2. Scope and Applicability

This standard applies to all cryptographic operations across application code, background jobs, database storage, file handling, API authentication, and operational infrastructure:
- **Data at Rest:** Application-level field encryption, database tablespace encryption, file/object storage encryption, and database backup encryption.
- **Data in Transit:** Enforced TLS 1.3 transport security, webhook payload signatures, and inter-service authentication.
- **Authentication & Secrets:** Password hashing (Argon2id / bcrypt), API token generation and hashing, HMAC signatures, and session security.
- **Key Lifecycle:** Generation, storage, segregation, rotation, versioning, revocation, and post-compromise disaster recovery.
- **Tenancy Boundaries:** Tenant-specific key derivation (HKDF) and envelope encryption preventing cross-tenant data exposure.

---

## 3. Required Knowledge and Competencies

Engineers and agents applying this skill must demonstrate verified expertise in:
1. **Cryptographic Primitives & Boundaries:** Clear distinctions between reversible encryption, one-way cryptographic hashing, salted password hashing, digital signatures, and non-cryptographic encoding (Base64, Hex).
2. **Authenticated Encryption with Associated Data (AEAD):** Mathematical and practical mechanics of AES-256-GCM and ChaCha20-Poly1305, including Initialization Vector (IV)/nonce uniqueness, authentication tags, and ciphertext integrity.
3. **Key Derivation Functions (KDF):** HKDF (RFC 5869) for pseudo-random key expansion and PBKDF2 / Argon2id for password-based key derivation.
4. **Envelope Encryption Architecture:** Segregation of Data Encryption Keys (DEKs) and Key Encryption Keys (KEKs).
5. **Side-Channel & Oracle Attack Mitigation:** Constant-time comparisons (`hash_equals()`), padding oracle prevention, and timing-attack-safe error handling.
6. **Searchable Encrypted Data:** Blind indexing architectures using HMAC with dedicated secrets to permit exact-match database lookups without compromising plaintext confidentiality.

---

## 4. Core Engineering Principles

### 4.1 Never Invent Custom Cryptography
Engineers and AI agents are strictly prohibited from designing custom encryption algorithms, proprietary hashing schemes, or home-grown padding mechanisms. Rely exclusively on industry-standard, battle-tested cryptographic primitives implemented in audited libraries (PHP native `ext-sodium` and `ext-openssl`).

### 4.2 Authenticated Encryption by Default (AEAD)
Unauthenticated encryption modes (such as AES-CBC without an HMAC integrity tag or raw AES-ECB) are strictly forbidden. All symmetric encryption must employ Authenticated Encryption with Associated Data (AEAD)—specifically AES-256-GCM or ChaCha20-Poly1305—ensuring that any modification to ciphertext or associated data causes immediate decryption rejection before data processing.

### 4.3 Separation of Concerns: Encryption vs Hashing vs Passwords
- **Reversible Encryption:** Used exclusively when the original plaintext must be retrieved (e.g., third-party API credentials, PII display fields).
- **Cryptographic Hashing (SHA-256, SHA-512):** Used exclusively for fixed-length fingerprinting, data integrity verification, and lookup token hashing. Never used for passwords.
- **Salted Password Hashing (Argon2id, bcrypt):** Computationally intensive, memory-hard one-way functions designed explicitly to resist brute-force hardware attacks. Never used for reversible data.
- **Encoding (Base64, Hex, JSON):** Reversible representations of binary data with zero security or confidentiality guarantees.

### 4.4 Envelope Encryption and Key Hierarchy
Directly encrypting large volumes of application data with a single root master key (`APP_KEY`) creates severe blast radius risks and makes key rotation nearly impossible. Universal Core enforces an envelope encryption model:
1. **Master Key / Key Encryption Key (KEK):** Held securely in environment vaults, HSMs, or secret managers. Master key material is strictly separated and **never** stored in database tables or filesystems alongside encrypted tenant data.
2. **Data Encryption Keys (DEK):** Unique keys generated or derived per entity or tenant, used to encrypt data payloads, and stored alongside ciphertext in wrapped (encrypted by KEK) form or derived deterministically on-demand via context-separated HKDF.

---

## 5. Mandatory Standards

### 5.1 Algorithm Selection and Cryptographic Strength
| Cryptographic Operation | Approved Enterprise Standard | Prohibited / Deprecated |
| :--- | :--- | :--- |
| **Symmetric Encryption (AEAD)** | AES-256-GCM, ChaCha20-Poly1305 | AES-ECB, AES-CBC without HMAC, DES, 3DES, RC4 |
| **Password Hashing** | Argon2id (`memory: 64MB, time: 4, threads: 1`), bcrypt (`cost >= 12`) | MD5, SHA-1, plain SHA-256, SHA-512, NTLM |
| **Data Integrity & Fingerprints** | SHA-256, SHA-512, SHA-3 | MD5, SHA-1 |
| **Message Authentication (MAC)** | HMAC-SHA256, HMAC-SHA512 | Unkeyed hashes, MD5-HMAC |
| **Digital Signatures** | Ed25519, ECDSA (P-256 / P-384), RSA-PSS ($\ge 3072$-bit) | RSA PKCS#1 v1.5, DSA |
| **Key Derivation (KDF)** | HKDF-SHA256 (RFC 5869), Argon2id | Custom hashing concatenation |
| **Random Number Generation** | `random_bytes()`, `random_int()` (CSPRNG) | `rand()`, `mt_rand()`, `uniqid()`, `lcg_value()` |

### 5.2 Application-Level Field Encryption and Casts
1. **Model Cast Governance:** Sensitive Eloquent model attributes (e.g., tax identifiers, medical records, bank accounts) must declare the `'encrypted'` cast or a custom AEAD cast:
   ```php
   protected function casts(): array
   {
       return [
           'tax_identification_number' => 'encrypted',
           'bank_account_details' => 'encrypted:array',
       ];
   }
   ```
2. **Searchable Encrypted Fields (Blind Indexing):**
   - Direct SQL `WHERE` queries on encrypted columns are impossible because non-deterministic AEAD generates a unique ciphertext for every encryption call.
   - For searchable fields, generate a separate blind index column using HMAC-SHA256 with a dedicated, isolated salt:
     $$\text{blind\_index} = \text{HMAC-SHA256}(\text{normalize}(\text{plaintext}), \text{BLIND\_INDEX\_KEY})$$
   - Store the blind index in an indexed column and query against the HMAC value. Deterministic encryption of the primary ciphertext column is strictly prohibited.

### 5.3 Key Lifecycle Management

#### 5.3.1 Key Generation and Master Key Protection
- 256-bit symmetric keys must be generated using cryptographically secure random bytes:
  ```php
  $key = random_bytes(32);
  ```
- Master key material must reside strictly in protected environment secrets, AWS KMS, or HashiCorp Vault. Master keys must **never** be stored in application source code, committed to version control, or stored in database tables alongside the ciphertexts they protect.
- Cryptographic keys must be separated by domain: the Laravel `APP_KEY` must never be reused for payment signature generation, webhook signing, database field encryption, or backup encryption.

#### 5.3.2 Key Versioning Metadata and Rotation
- All encrypted payloads must incorporate an explicit key version header (e.g., `v1:base64(iv.tag.ciphertext)`).
- This version metadata enables the decryption service to select the exact historical key from the active key ring or re-derive the historical key without trial-and-error.
- **Dual-Read Single-Write Rotation:** During rotation, the key ring maintains access to historical key versions (`v1`) while all new write operations encrypt exclusively with the active key version (`v2`).
- A background migration worker systematically reads historical `v1` records, decrypts them with `v1`, re-encrypts with `v2`, and updates the database row.

#### 5.3.3 Key Compromise and Safe Recovery Procedure
If an encryption key is suspected or confirmed compromised:
1. **Revoke Active Key:** Immediately remove the compromised key version from active write status in the key ring and provision a fresh key version (`v_next`).
2. **Quarantine Compromised Scope:** Identify all database records, tokens, or sessions encrypted under the compromised key version using the version metadata header.
3. **Secure Recovery Re-Encryption:** In an isolated recovery environment, retrieve historical keys held in secure escrow, decrypt affected ciphertexts, immediately re-encrypt them under `v_next`, and commit the updated records.
4. **Decommission:** Decommission and purge the compromised key version. Invalidate all active user sessions, API tokens, and webhook secrets.

### 5.4 Password Security and Verification
1. **Hashing Invariants:**
   - Passwords must be hashed using PHP's native `password_hash()` or Laravel's `Hash` facade with Argon2id or bcrypt (cost factor $\ge 12$).
   - Salt generation must be delegated entirely to the hashing algorithm; manual salt concatenation is prohibited.
2. **Automatic Rehash Upgrades:**
   - Application authentication logic must inspect whether an authenticated password requires a cost or algorithm upgrade:
     ```php
     if (Hash::needsRehash($user->password)) {
         $user->forceFill(['password' => Hash::make($credentials['password'])])->save();
     }
     ```
3. **API Token Storage:**
   - Raw bearer tokens must be displayed to the user exactly once upon generation.
   - The database must store strictly the SHA-256 hash of the token:
     ```php
     $plainToken = Str::random(40);
     $tokenHash = hash('sha256', $plainToken);
     ```
   - Direct storage of reversible or plain API tokens in the database is prohibited.

### 5.5 Multi-Tenant Isolation and Cryptographic Boundaries

#### 5.5.1 Context-Separated HKDF Key Derivation
In multi-tenant architectures, tenant data must be cryptographically isolated. Rather than sharing a static key, tenant Data Encryption Keys (DEKs) are derived from the master key using HKDF (RFC 5869).

To ensure complete domain separation and prevent key interchangeability across different purposes:
- The `$key` parameter (Input Keying Material) is the master key from secure environment storage.
- The `$salt` parameter is a domain-wide cryptographic salt (`config('app.master_kdf_salt')`).
- The `$info` parameter **must** explicitly bind the system namespace, tenant identifier, cryptographic purpose, and key version:
  ```php
  $info = "universal-core:purpose={$purpose}:tenant={$tenantId}:version={$keyVersion}";
  ```
- Because `$info` incorporates the specific purpose (e.g. `field-encryption`, `blind-indexing`, `token-signing`), keys derived for field encryption cannot be interchangeably used for token signing or any other purpose, even for the same tenant.

```php
$tenantDek = hash_hkdf(
    algo: 'sha256',
    key: config('app.master_encryption_key'),
    length: 32,
    info: "universal-core:purpose=field-encryption:tenant={$tenantId}:version={$keyVersion}",
    salt: config('app.master_kdf_salt')
);
```

### 5.6 Telemetry and Log Sanitization
1. **Zero Secret Leakage:**
   - Cryptographic keys, raw passwords, authentication tokens, and unencrypted PII must never appear in application logs, exception traces, error monitoring tools, or debug dumps.
   - Enforce Laravel hidden parameters and context redaction in logging configurations:
     ```php
     $request->validate([...]); // Strip sensitive inputs from error context
     ```
2. **Constant-Time Verification:**
   - All hash and signature comparisons must utilize `hash_equals()` to eliminate side-channel timing attack vectors:
     ```php
     if (! hash_equals($expectedHmac, $providedHmac)) {
         throw new SecurityException('Invalid message authentication code.');
     }
     ```

---

## 6. Implementation Procedures and Practices

### 6.1 Enterprise AEAD Field Encryption Service (AES-256-GCM with Versioning)

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Security\Exceptions\CryptographicException;
use RuntimeException;

final readonly class VersionedAeadEncryptor
{
    private const string CIPHER = 'aes-256-gcm';
    private const int IV_LENGTH = 12; // 96-bit nonce for GCM
    private const int TAG_LENGTH = 16; // 128-bit authentication tag

    /**
     * @param array<string, string> $keys Key ring mapping version ID to binary 256-bit key
     * @param string $activeVersion Currently active key version for new encryptions
     */
    public function __construct(
        private array $keys,
        private string $activeVersion
    ) {
        if (! isset($this->keys[$this->activeVersion])) {
            throw new RuntimeException("Active key version '{$this->activeVersion}' missing from key ring.");
        }
    }

    /**
     * Encrypts plaintext using AES-256-GCM with key versioning and authentication tag.
     * Output format: version:base64(iv . tag . ciphertext)
     */
    public function encrypt(string $plaintext, string $associatedData = ''): string
    {
        $key = $this->keys[$this->activeVersion];
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';

        $ciphertext = openssl_encrypt(
            data: $plaintext,
            cipher_algo: self::CIPHER,
            passphrase: $key,
            options: OPENSSL_RAW_DATA,
            iv: $iv,
            tag: $tag,
            aad: $associatedData,
            tag_length: self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new CryptographicException('Authenticated encryption operation failed.');
        }

        $payload = base64_encode($iv . $tag . $ciphertext);

        return "{$this->activeVersion}:{$payload}";
    }

    /**
     * Decrypts versioned payload and verifies authentication tag.
     */
    public function decrypt(string $payload, string $associatedData = ''): string
    {
        $segments = explode(':', $payload, 2);
        if (count($segments) !== 2) {
            throw new CryptographicException('Malformed encrypted payload structure.');
        }

        [$version, $encodedData] = $segments;

        if (! isset($this->keys[$version])) {
            throw new CryptographicException("Unknown key version '{$version}'. Key may be decommissioned.");
        }

        $raw = base64_decode($encodedData, true);
        if ($raw === false || strlen($raw) < (self::IV_LENGTH + self::TAG_LENGTH)) {
            throw new CryptographicException('Invalid or truncated ciphertext payload.');
        }

        $iv = substr($raw, 0, self::IV_LENGTH);
        $tag = substr($raw, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($raw, self::IV_LENGTH + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            data: $ciphertext,
            cipher_algo: self::CIPHER,
            passphrase: $this->keys[$version],
            options: OPENSSL_RAW_DATA,
            iv: $iv,
            tag: $tag,
            aad: $associatedData
        );

        if ($plaintext === false) {
            // Constant-time failure message; never disclose whether tag or data was invalid
            throw new CryptographicException('Decryption or authentication tag verification failed.');
        }

        return $plaintext;
    }
}
```

### 6.2 Context-Separated Tenant Key Derivation Service

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use InvalidArgumentException;

final readonly class TenantKeyDerivationService
{
    public function __construct(
        private string $masterKey,
        private string $masterKdfSalt
    ) {
        if (strlen($this->masterKey) < 32) {
            throw new InvalidArgumentException('Master key must be at least 256 bits (32 bytes).');
        }
    }

    /**
     * Derives a cryptographically isolated tenant Data Encryption Key (DEK).
     * Binds tenant ID, cryptographic purpose, and key version into HKDF info context.
     */
    public function deriveTenantKey(string $tenantId, string $purpose, string $keyVersion = 'v1'): string
    {
        $contextInfo = sprintf(
            'universal-core:purpose=%s:tenant=%s:version=%s',
            $purpose,
            $tenantId,
            $keyVersion
        );

        return hash_hkdf(
            algo: 'sha256',
            key: $this->masterKey,
            length: 32,
            info: $contextInfo,
            salt: $this->masterKdfSalt
        );
    }
}
```

### 6.3 Searchable Blind Index and Secure Token Generation

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final readonly class SearchableCryptoService
{
    /**
     * Generates a deterministic blind index HMAC for exact-match database searching.
     */
    public static function generateBlindIndex(string $value, string $blindIndexSalt): string
    {
        $normalized = mb_strtolower(trim($value), 'UTF-8');

        return hash_hmac('sha256', $normalized, $blindIndexSalt);
    }

    /**
     * Generates a cryptographically secure API token pair:
     * - plaintext: returned to user once
     * - hash: stored in database
     *
     * @return array{plain: string, hash: string}
     */
    public static function generateApiToken(): array
    {
        $plain = bin2hex(random_bytes(32)); // 64-character hex string
        $hash = hash('sha256', $plain);

        return [
            'plain' => $plain,
            'hash' => $hash,
        ];
    }
}
```

---

## 7. Prohibited Practices and Their Rationale

1. **PROHIBITED: Designing Custom Cryptography or Padding.** Home-grown algorithms inevitably contain subtle vulnerabilities (e.g., non-constant-time branching, poor entropy).
2. **PROHIBITED: Using MD5 or SHA-1 for Security Operations.** Both are cryptographically broken due to practical collision attacks.
3. **PROHIBITED: Using Unauthenticated Encryption (AES-ECB or AES-CBC without HMAC).** Susceptible to ciphertext manipulation, bit-flipping attacks, and padding oracle exploits.
4. **PROHIBITED: Using `rand()`, `mt_rand()`, or `uniqid()` for Cryptographic Secrets.** These PRNGs are non-cryptographic and predictable, allowing attackers to reconstruct keys and tokens.
5. **PROHIBITED: Hardcoding Secrets or Keys in Source Repositories.** Keys in version control are permanently compromised upon commit and accessible to all repository readers.
6. **PROHIBITED: Storing Plaintext or Reversibly Encrypted Passwords.** Compromise of the database leads to immediate credential exposure across all user accounts.
7. **PROHIBITED: Reusing Nonces/IVs in AES-GCM.** Nonce reuse in GCM mode destroys the authenticity guarantee and allows recovery of the authentication subkey.
8. **PROHIBITED: Storing Master Keys in Database Tables Alongside Encrypted Data.** If the database is compromised, the encryption keys are immediately exposed alongside the ciphertexts.
9. **PROHIBITED: Omitting Purpose or Tenant Context in Key Derivation.** Deriving keys with generic info strings allows interchangeable key usage across different domains or tenants.
10. **PROHIBITED: Using Insecure Non-Constant Time Comparison (`==` or `===`) on Hashes.** Vulnerable to timing attacks where execution duration reveals matching prefix bytes.

---

## 8. Security and Reliability Requirements

1. **Entropy Verification:** Cryptographic keys and nonces must source randomness exclusively from system entropy via `random_bytes()`.
2. **Encrypted Storage Volumes:** Database servers and backup repositories must utilize hardware or OS-level storage encryption (e.g., BitLocker, LUKS, AWS EBS Encryption).
3. **Database Tablespace Encryption:** MySQL production schemas storing sensitive customer data must enforce InnoDB tablespace encryption (`ENCRYPTION='Y'`).
4. **Transport Layer Security:** All HTTP endpoints must strictly enforce TLS 1.3 (with fallback to TLS 1.2 minimum); insecure SSLv3, TLS 1.0, and TLS 1.1 must be disabled at the web server and load balancer.
5. **Memory Sanitation:** While PHP's garbage collector manages memory lifecycle, sensitive variables holding plaintext secrets should be unset (`unset($secret)`) immediately after cryptographic processing.

---

## 9. Testing and Verification

### 9.1 Verification Hierarchy
1. **Round-Trip Encryption Correctness:**
   Verify that encrypting arbitrary UTF-8 strings, JSON objects, and binary data produces valid plaintext upon decryption across all supported key versions.
2. **Tamper Resistance & Authentication Failure:**
   Modify a single bit of the ciphertext or authentication tag; verify that decryption immediately throws `CryptographicException` without leaking plaintext or detailed oracle diagnostics.
3. **Key Rotation Verification:**
   Encrypt a record using key version `v1`. Add `v2` to the key ring as active. Verify that `v1` data still decrypts successfully, new records encrypt with `v2`, and background re-encryption updates records to `v2`.
4. **Timing-Safe Equality:**
   Audit codebases using static analysis (PHPStan Level 8+) to ensure no HMAC or token comparisons utilize `===` or `==`; enforce `hash_equals()`.
5. **Context Separation Verification:**
   Verify that HKDF derivation with differing purpose parameters (e.g., `field-encryption` vs `blind-indexing`) or differing tenant IDs produces distinct, non-interchangeable keys.

---

## 10. Common Failure Scenarios and Prevention

| Failure Scenario | Root Cause | Engineering Prevention Rule |
| :--- | :--- | :--- |
| **GCM Nonce Reuse Catastrophe** | Static or counter-based IV in AES-256-GCM. | Generate a fresh 12-byte CSPRNG IV via `random_bytes(12)` on every encryption. |
| **Padding Oracle Attack** | Decrypting CBC mode ciphertext and returning distinct padding vs MAC errors. | Mandate AEAD (AES-256-GCM); return uniform generic error on all decryption failures. |
| **Credential Breach via Database Leak** | Passwords hashed with raw SHA-256 or MD5. | Enforce Argon2id or bcrypt ($\text{cost} \ge 12$); reject non-password-hashing algorithms. |
| **Cross-Tenant Data Decryption** | All tenants share a single static data encryption key. | Derive tenant-specific DEKs via HKDF with tenant ID and purpose bound in context info. |
| **Broken Key Rotation** | Ciphertext stored without key version metadata; rotation breaks legacy records. | Prefix all ciphertexts with key version identifier (`v1:...`); maintain key ring. |
| **Search Functionality Blocked on Encrypted Field** | Encrypted column cannot be searched via SQL `WHERE`. | Implement blind indexing using HMAC-SHA256 with an isolated salt on normalized input. |

---

## 11. Acceptance Criteria

Before approving any cryptographic or security-sensitive deliverable:
- [ ] No custom cryptographic algorithms, ciphers, or padding logic exist in the codebase.
- [ ] Symmetric encryption utilizes authenticated encryption (AES-256-GCM or ChaCha20-Poly1305).
- [ ] Randomness is generated exclusively via `random_bytes()` or `random_int()`; zero instances of `rand()`, `mt_rand()`, or `uniqid()`.
- [ ] Master key material is stored exclusively in secret managers / KMS and never stored alongside encrypted tenant data.
- [ ] HKDF key derivation strictly binds tenant identity, cryptographic purpose, and key version into the context info parameter.
- [ ] Passwords are processed exclusively using `Hash::make()` with Argon2id or bcrypt ($\text{cost} \ge 12$).
- [ ] API tokens are hashed (SHA-256) prior to database persistence; zero raw tokens in database storage.
- [ ] Hash and signature comparisons strictly use `hash_equals()`.
- [ ] Encrypted payloads incorporate key versioning to enable zero-downtime key rotation.
- [ ] Decryption failures throw generic exceptions with zero oracle or internal detail leakage.
- [ ] Sensitive secrets are excluded from logs, error contexts, and exception traces.

---

## 12. References and Related Skills

- **Related Skills:**
  - `SKILL-SEC-001` (Secure Coding and Defensive Engineering Standards)
  - `SKILL-DEVOPS-001` (CI/CD Pipeline and DevOps Infrastructure Standards)
  - `SKILL-VER-001` (Framework and PHP Version Compatibility Standards)
  - `SKILL-DB-001` (Database Engineering and Schema Governance Standards)
- **Authoritative Specifications:**
  - NIST SP 800-38D (Recommendation for Block Cipher Modes of Operation: Galois/Counter Mode)
  - RFC 5869 (HMAC-based Extract-and-Expand Key Derivation Function - HKDF)
  - RFC 8439 (ChaCha20 and Poly1305 for IETF Protocols)
  - RFC 9106 (Argon2 Memory-Hard Function for Password Hashing)
  - OWASP Cryptographic Storage Cheat Sheet
