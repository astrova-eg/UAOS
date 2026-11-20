# Document Precedence Hierarchy Policy

**Document ID:** GOV-DOC-022  
**Version:** 1.0.0  
**Classification:** Foundational Governance Policy (Precedence Tier 3)  
**Effective Date:** 2026-11-15  
**Binding Architecture:** ARCH-GOV-DES-001 (1.1.0-DESIGN-REVIEWED)  

---

## 1. Purpose and Authority

This policy defines the authoritative 12-Tier Document Precedence Hierarchy governing the Universal Agent Operating System and consuming projects. It guarantees architectural consistency, prevents requirement drift, and establishes deterministic conflict resolution.

---

## 2. The 12-Tier Precedence Hierarchy

In the event of any contradiction or ambiguity between documents, directives, or code, the higher-numbered tier in this list takes absolute precedence over all lower tiers:

| Tier | Tier Name | Canonical Scope & Examples |
|:---:|:---|:---|
| **Tier 1** | **Human Sovereign Directives & Authorizations** | Direct instructions, decisions, and approvals from the Human Project Sponsor; Gate 4 Authorization Records; explicit human exception approvals. |
| **Tier 2** | **Engineering Constitution** | `ENGINEERING-CONSTITUTION.md` — Foundational constitutional invariants, sovereign authority, anti-weakening, separation of duties. |
| **Tier 3** | **Core Governance Policies** | Foundational policies including `HUMAN-AUTHORITY-POLICY.md`, `DOCUMENT-HIERARCHY-POLICY.md`, `APPROVAL-GATES-POLICY.md`, `COMPLETENESS-AND-NO-MVP-POLICY.md`, `SECURITY-GOVERNANCE-POLICY.md`, `QUALITY-GOVERNANCE-POLICY.md`, `DATABASE-GOVERNANCE-POLICY.md`. |
| **Tier 4** | **Shared Agent Governance Core** | `.agents/governance/SHARED-AGENT-GOVERNANCE-CORE.md` — The 7 universal invariants inherited by all 112 agents. |
| **Tier 5** | **Canonical Registries** | `agent-registry.json`, `skill-registry.json`, `department-registry.json`, authority and separation matrices. |
| **Tier 6** | **Architecture Decision Records & Design Specs** | Approved ADRs (`docs/15-decisions/adr/`), system architecture baselines (`ARCH-GOV-DES-001`). |
| **Tier 7** | **System Requirements Specifications (SRSs)** | Approved functional and non-functional specifications produced in Stage 03 and signed at Gate 1. |
| **Tier 8** | **Department Charters & Operational Handbooks** | Department charters (`.agents/departments/dept-*.md`) defining operational domains and responsibilities. |
| **Tier 9** | **Shared Engineering Standards & Skills** | The 23 shared engineering standards in `.agents/skills/*.md`. |
| **Tier 10** | **Lifecycle Workflows & Orchestration Protocols** | The 14 lifecycle stage workflows (`.agents/workflows/*.md`) and orchestration matrices. |
| **Tier 11** | **Task Breakdowns & Implementation Plans** | Atomic task DAGs produced in Stage 05 (Gate 2) and implementation plans produced in Stage 07 (Gate 3). |
| **Tier 12** | **Source Code, Test Suites & Runtime State** | Working application code, migrations, unit/integration test suites, and ephemeral runtime logs. |

---

## 3. Anti-Weakening and Conflict Resolution Rules

1. **Anti-Weakening Principle:** A document at a lower tier may specify implementation details or impose stricter local constraints, but must NEVER relax, contradict, dilute, or omit a requirement defined in a higher tier.
2. **Conflict Resolution:** When two documents conflict:
   - Identify the tier of each document.
   - The higher tier controls the requirement.
   - If documents are in the same tier, the more specific architectural or technical specification governs, provided it does not contradict general policy.
   - If ambiguity persists, work halts immediately and escalates to the Human Project Sponsor (Tier 1).
3. **Immutability of Governance:** Agents are prohibited from modifying Tiers 1 through 5 without explicit prior human approval.
