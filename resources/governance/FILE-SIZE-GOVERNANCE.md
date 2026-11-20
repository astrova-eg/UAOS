# Universal Agent Operating System: File Size Governance Policy

**Document ID:** GOV-POL-009  
**Authority:** Constitutional Invariant & Technical Architecture (ARCH-UAOS-002)  
**Standard Limit:** 300 Physical Lines per Executable Code File  

---

## 1. Authoritative Standard

The approved standard across the Universal Agent Operating System is:
**A default maximum of 300 lines per executable code file.**

This ceiling applies to all production and test executable code files, including PHP, JavaScript, TypeScript, PowerShell (`.ps1`), and shell scripts.

## 2. Rationale and Architecture

1. **High Cohesion & Single Responsibility:** Keeping files under 300 lines forces clean class decomposition, explicit collaborators, and focused domain abstractions.
2. **Preventing Monolithic Degradation:** Large files obscure architectural boundaries, introduce tight coupling, and increase the likelihood of race conditions and merge conflicts.
3. **Auditability & Verification:** Automated tools and human auditors can inspect and verify small, focused components rapidly without cognitive overload.

## 3. Scope and Exclusions

- **In Scope (Strictly Enforced):**
  - All PHP source and test files (`src/**/*.php`, `tests/**/*.php`, `bin/*`).
  - All PowerShell scripts (`scripts/*.ps1`, `templates/*.ps1`).
  - All frontend script files (`.js`, `.ts`).

- **Out of Scope (Excluded):**
  - Markdown documentation and specifications (`.md`).
  - JSON and YAML schema / configuration definitions (`.json`, `.yaml`, `.yml`).
  - Generated lockfiles (`composer.lock`, `package-lock.json`).

## 4. Exception Policy & Governance

Any executable code file exceeding 300 lines requires:
1. **Documented Technical Justification:** Architectural explanation of why decomposition into collaborators is impractical or harmful.
2. **Prior Explicit Human Approval:** Validated Gate 4 authorization token or signed exception.
3. **Auditable Exception Record:** Stored in `file-size-exceptions.json` with path, line count, justification, and approval identifier.

Blanket exceptions and silent increases to the general limit are strictly prohibited.
