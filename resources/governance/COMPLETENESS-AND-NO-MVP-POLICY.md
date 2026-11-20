# Completeness and Zero-MVP Governance Policy

**Document ID:** GOV-DOC-024  
**Version:** 1.0.0  
**Classification:** Engineering Policy (Precedence Tier 3)  
**Effective Date:** 2026-11-15  
**Binding Architecture:** ARCH-GOV-DES-001 (1.1.0-DESIGN-REVIEWED)  

---

## 1. Complete-by-Design and Zero-MVP Principle

1. The Universal Core engineering baseline rejects "Minimum Viable Product" (MVP) shortcuts, temporary placeholders, mock endpoints in production code, and incomplete feature stubs.
2. Every component, class, method, schema migration, and test suite committed to production must be Complete-by-Design:
   - Full functional behavior implemented with explicit input/output contracts.
   - Comprehensive error handling and defensive boundary checks.
   - 100% strict typing with zero unhandled edge cases.
   - Bidirectional test traceability matching all acceptance criteria.
3. Placing `TODO`, `FIXME`, `STUB`, or empty methods in production namespaces constitutes a critical quality defect and blocks all gate progression.

---

## 2. Six-State Scope Applicability Taxonomy

Every requirement, invariant, and technical standard within specifications must be classified under one of the six approved applicability states:

| State | Classification | Description & Gate Enforcement |
|:---:|:---|:---|
| **1** | **Universal Mandatory** | Invariant applying universally across all components and subsystems (e.g. strict typing, zero hardcoded secrets, file size limits). Never waivable. |
| **2** | **Technology-Dependent** | Mandatory whenever the associated technology stack is employed (e.g. Eloquent parameterization for MySQL, Livewire hydration security). |
| **3** | **Conditional** | Mandatory when specific architectural conditions or business triggers are met (e.g. multi-tenant isolation rules when tenant data is handled). |
| **4** | **Not Applicable (N/A)** | Explicitly ruled out based on architectural context, requiring documented rationale approved by technical authority. |
| **5** | **Approved Deferred** | Formally deferred to a specified future release; requires prior explicit human approval, risk assessment, and tracking ticket. |
| **6** | **Unresolved / Incomplete** | Requirements lacking definitive scope, design, or signoff. Any item in this state blocks progression through Gates 1, 3, 4, and 5. |

---

## 3. Enforcement and Prohibitions

1. No stage may transition to the next if any deliverable remains in State 6 (Unresolved/Incomplete).
2. Production test doubles or mocks are prohibited outside designated test directories (`tests/`).
3. Independent Technical Auditors (Department M) must verify that all deliverables achieve 100% Complete-by-Design status before issuing Gate 5 clearance.
