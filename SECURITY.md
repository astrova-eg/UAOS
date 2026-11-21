# Security Policy & Vulnerability Disclosure

Universal Agent Operating System (UAOS) is an enterprise-grade autonomous engineering organization with built-in cryptographic clearance protocols and application-level tool sandboxing. We take security vulnerabilities with utmost seriousness.

---

## Supported Versions

Only the latest major/minor release is actively maintained with security patches:

| Version | Supported          |
| :------ | :----------------- |
| `2.0.x` | :white_check_mark: |
| `< 2.0` | :x:                |

---

## Reporting a Security Vulnerability

If you believe you have found a security vulnerability in UAOS (including but not limited to: Gate 4 HMAC bypasses, SSRF in MCP/RDEI retrievers, path confinement escapes, or command injection vulnerabilities):

**PLEASE DO NOT FILE A PUBLIC ISSUE OR PULL REQUEST.**

Publicly disclosing a vulnerability could put production environments and automated agent systems at risk before a fix is released.

### Reporting Channels

Please disclose the vulnerability responsibly through one of the following private channels:

1. **Email:** [hossam.mohammed2985@gmail.com](mailto:hossam.mohammed2985@gmail.com) with the subject `[SECURITY-UAOS] Vulnerability Report`
2. **Direct WhatsApp (Urgent):** [+20 106 735 7431](https://wa.me/201067357431)
3. **GitHub Security Advisory:** Use the private [Security Advisories tab](https://github.com/astrova-eg/UAOS/security/advisories) on GitHub.

### What to Include in Your Report

To help us investigate and triage quickly, please include:
* Description of the vulnerability and its potential impact.
* Specific component involved (e.g., `Gate4Verifier`, `ToolGateway`, `StreamableHttpTransport`, `PersistentNonceStore`).
* Minimal, reproducible proof-of-concept (PoC) script or task envelope.
* Relevant environment details (PHP version, OS, SQLite driver).

---

## Response & Disclosure Process

* **Acknowledgment:** We will acknowledge receipt of your vulnerability report within **24 hours**.
* **Investigation:** The Astrova Agency security engineering team will investigate and determine severity within **48 hours**.
* **Patch Development:** A cryptographic or runtime patch will be developed, tested, and validated against the full 1,219-assertion automated regression suite.
* **Release:** We will release a security patch release and publish an advisory crediting the researcher (unless anonymity is requested).

---

## Security Invariants

As an architectural reminder, UAOS enforces the following non-negotiable security boundaries:
1. **Path Confinement:** Zero escapes outside the host application directory.
2. **Zero Vendor Mutability:** `vendor/` and core files are immutable.
3. **Command Sanitization:** Destructive and arbitrary shell executions are rejected.
4. **Authenticity:** No placeholder or mocked production functions.
5. **Gate 4 Clearance:** All destructive and mutation actions require cryptographically verified HMAC-SHA256 signatures bound to atomic nonces.

Thank you for helping us keep the AI engineering ecosystem safe and verifiable!
