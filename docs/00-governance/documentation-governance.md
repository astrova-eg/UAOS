# Technical Documentation Governance Standards

**Document ID:** GOV-DOC-101  
**Version:** 2.0.0  
**Classification:** Authoritative Operational Standard  
**Status:** [Active Governance]  

---

## 1. Supreme Architectural Principle: Code-Grounded Truth
Documentation MUST be derived from actual, verified source code whenever implementation exists. 

### Mandatory Source-of-Truth Hierarchy
1. **Executable Source Code:** Authoritative for actual runtime behavior.
2. **Database Migrations & Schema Definitions:** Authoritative for persistent storage structures.
3. **API Contracts (OpenAPI 3.1.0):** Authoritative for public and internal HTTP service surfaces.
4. **Approved Architecture Specifications & ADRs:** Authoritative for planned architecture where implementation has not yet begun.
5. **Automated Test Suites:** Empirical evidence of tested behavior under designated assertions.
6. **Informational Guides:** Informative and instructional context.

### Discrepancy Invariants
- If executable code differs from documentation, the code is authoritative regarding runtime behavior.
- Authors MUST NOT silently modify source code to make documentation appear correct.
- Authors MUST NOT invent missing behavior, parameters, or schema columns.
- Discrepancies must be reported via formal defect issues, investigated, and remediated through authorized Pull Requests.

---

## 2. Mandatory Implementation Status Labeling
Every technical specification, architecture document, and system catalog entry MUST display an explicit status banner:
- `[Implemented]`: Active, merged, executable code in the repository.
- `[Verified]`: Code exists and has passed automated tests, static analysis, and independent audit.
- `[Partially Implemented]`: Foundational code exists, but some components or edge cases remain in progress.
- `[Designed but Not Implemented]`: Architectural specifications or ADRs exist, but application code is not yet written.
- `[Planned]`: Conceptually scheduled on roadmap; detailed specs not yet authored.
- `[Deprecated]`: Scheduled for future removal; migration path must be provided.
- `[Unsupported]`: Workflows or patterns deliberately prohibited.
- `[Unknown / Requires Verification]`: Legacy or unverified code requiring empirical testing.

---

## 3. Normative Language
All documentation authored under this standard MUST utilize RFC 2119 precision language: `MUST`, `MUST NOT`, `SHOULD`, `SHOULD NOT`, and `MAY`.