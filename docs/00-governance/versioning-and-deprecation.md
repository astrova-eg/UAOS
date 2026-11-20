# Documentation Versioning and Deprecation Policy

**Document ID:** GOV-DOC-105  
**Version:** 2.0.0  
**Classification:** Versioning Policy  
**Status:** [Active Governance]  

---

## 1. Semantic Versioning Rules
Documentation adheres to Semantic Versioning 2.0.0 (`MAJOR.MINOR.PATCH`):
- `MAJOR`: Structural reorganization of documentation directories, breaking API contract changes, or architectural rewrites.
- `MINOR`: New core system specifications, complete implementation guides, or new endpoint references.
- `PATCH`: Typo corrections, clarified text, updated code examples, and repaired links.

## 2. Deprecation & Archival Procedure
1. Mark document header with `[Deprecated]`.
2. State target sunset release and provide migration guide in `docs/12-release-management/upgrade-guides/`.
3. Upon removal from codebase, move document to `docs/17-archive/` to preserve historical traceability.