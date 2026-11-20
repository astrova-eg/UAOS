# Documentation Engineering Standards Summary

**Document ID:** DOC-STD-001  
**Version:** 2.0.0  
**Classification:** Operational Standard  
**Authoritative Reference:** [SKILL-DOC-001](../.agents/skills/documentation-standards.md)  
**Last Verified:** 2026-11-15  

---

## 1. Purpose and Authority
This document summarizes the core operational rules, quality gates, and authoring guidelines enforced across all documentation within Universal Core. It derives directly from **[SKILL-DOC-001](../.agents/skills/documentation-standards.md)**.

Every software engineer and autonomous agent contributing documentation to `docs/` must strictly comply with these directives.

---

## 2. Fundamental Directives

### 2.1 Code-Backed Documentation (Empirical Truth)
- Documentation MUST reflect verified, executable source code whenever implementation exists.
- Speculative claims, fictional API endpoints, fabricated database tables, or nonexistent configurations are strictly prohibited.
- When implementation does not exist, components MUST be explicitly designated as `[Designed but Not Implemented]` or `[Planned]`.

### 2.2 Status Classification Tags
Every technical document, API specification, and system catalog entry MUST display one of eight standardized status tags:
1. `[Implemented]`: Active, merged, executable code in the repository.
2. `[Verified]`: Code exists and has passed automated tests, static analysis, and independent audit.
3. `[Partially Implemented]`: Foundational code exists, but some components or edge cases remain in progress.
4. `[Designed but Not Implemented]`: Architectural specifications or ADRs exist, but application code is not yet written.
5. `[Planned]`: Conceptually scheduled on roadmap; detailed specs not yet authored.
6. `[Deprecated]`: Scheduled for future removal; migration path must be provided.
7. `[Unsupported]`: Workflows or patterns deliberately prohibited.
8. `[Unknown / Requires Verification]`: Legacy or unverified code requiring empirical testing.

### 2.3 Source-of-Truth Precedence Hierarchy
When discrepancies arise, resolve them according to this strict hierarchy:
1. **Executable Source Code:** Authoritative for actual runtime behavior.
2. **Database Migrations:** Authoritative for implemented schema structure.
3. **API Contracts (OpenAPI 3.1.0):** Authoritative for public API surfaces.
4. **Approved Architecture Specifications & ADRs:** Authoritative for planned architecture.
5. **Automated Tests:** Empirical evidence of tested conditions under specific inputs.
6. **Informative Guides & Historical Notes:** Informative reference only.

*Discrepancy Rule:* Never silently modify code to make documentation match. Identify the discrepancy, determine the true requirement, and execute an authorized PR.

---

## 3. Authoring & Quality Gates

### 3.1 Markdown and Code Blocks
- All documents must be authored in standard GitHub Flavored Markdown (GFM).
- Heading hierarchy must be strictly sequential (`#` -> `##` -> `###` -> `####`).
- Every code block MUST declare a language syntax identifier (`php`, `json`, `yaml`, `bash`, `powershell`, `sql`, `mermaid`).
- Mermaid diagrams must be syntactically valid; all node labels with special characters must be enclosed in double quotes.

### 3.2 Automated CI Quality Gates
Before any documentation PR is merged:
1. **Markdown Linting:** 100% compliance with markdownlint rules.
2. **Relative Link Integrity:** Zero broken relative paths or missing anchor links.
3. **No Secrets:** Scanned for zero committed credentials, tokens, or private keys.
4. **Peer & Audit Sign-Off:** Verified by Department N (Documentation Closure) or Department M (Independent Audit).

---

## 4. Git Integration
- Documentation changes MUST be committed atomically with the code changes they describe.
- Commit messages MUST adhere to Conventional Commits: `docs(<scope>): <description>`.
- Dedicated documentation branches must follow: `docs/<ticket-id>-<description>`.
