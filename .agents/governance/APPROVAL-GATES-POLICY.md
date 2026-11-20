# Approval Gates and Lifecycle Governance Policy

**Document ID:** GOV-DOC-015  
**Version:** 1.2.0  
**Classification:** Gatekeeping and Quality Defense (Precedence Tier 3)  
**Effective Date:** 2026-11-15  
**Binding Architecture:** ARCH-GOV-DES-001 (1.1.0-DESIGN-REVIEWED)  

---

## 1. The 14-Stage Lifecycle and Gate Semantics

The Universal Core engineering lifecycle enforces a deterministic 14-stage progression governed by five hard gates and two sovereign milestones. Progression across stages is strictly blocked until the required gate certificate is verified.

| Stage | Stage Name | Governing Gate / Milestone | Authoring Role | Designate Approval Authority | Deliverable Artifact / Certificate |
|:---:|:---|:---:|:---|:---|:---|
| **01** | Discovery & Analysis | - | `project-discovery-specialist` (DISC-001) | Department B Lead | Project Discovery Assessment |
| **02** | Architecture & Design | - | `laravel-system-architect` (LARCH-001) | `chief-software-architect` (ARCH-001) | System Architecture Baseline / ADRs |
| **03** | Specification Engineering | - | `requirements-engineer` (REQ-001) | Department B Peer Review | System Requirements Specification (SRS) |
| **04** | Specification Review | **Gate 1** | `requirements-engineer` (REQ-001) | `requirements-validation-specialist` (RVAL-001) | Gate 1 — Specification Approval Certificate |
| **05** | Atomic Task Engineering | - | `workflow-orchestrator` (ORCH-001) | Engineering Leads | Atomic Task DAG Breakdown |
| **06** | Atomic Task Review | **Gate 2** | `workflow-orchestrator` (ORCH-001) | `technical-standards-manager` (STD-001) | Gate 2 — Atomic Task Approval Certificate |
| **07** | Implementation Planning | - | `laravel-system-architect` (LARCH-001) | Department C Peer Review | Detailed Implementation Plan |
| **08** | Plan Review | **Gate 3** | `laravel-system-architect` (LARCH-001) | `chief-software-architect` (ARCH-001) | Gate 3 — Implementation Plan Certificate |
| **09** | Implementation Authorization | **Gate 4** | `engineering-governance-manager` (GOV-001) | **Human Project Sponsor (HMAC-SHA256)** | Gate 4 — Human Authorization Record |
| **10** | Code Implementation | - | Assigned Engineers (Depts D, E, F, G, H, I, L) | Department Code Reviewers | Working Code & Physical Migrations |
| **11** | Testing & Verification | - | `qa-architect` (QA-001) / Assigned Testers | Quality Lead | Automated Test Execution Results |
| **12** | Independent Technical Audit | **Gate 5** | Assigned Engineers / QA | `release-readiness-auditor` (AUD-008) [Dept M] | Gate 5 — Production Readiness Certificate |
| **13** | Acceptance Verification | **Acceptance** | `acceptance-verification-engineer` (ACPT-001) | `quality-governance-manager` (QGOV-001) | Acceptance Verification Certificate |
| **14** | System Closure & Handover | **Closure** | `lead-technical-writer` (DOC-001) | **Human Project Sponsor** | System Closure Certificate & Handover |

---

## 2. Gatekeeper Separation of Duties Invariants

1. **Gate 2 Separation:** `workflow-orchestrator` (ORCH-001) authors the Atomic Task DAG. `technical-standards-manager` (STD-001) independently evaluates and approves Gate 2. Self-approval by ORCH-001 is void.
2. **Gate 3 Separation:** `laravel-system-architect` (LARCH-001) authors the Implementation Plan. `chief-software-architect` (ARCH-001) independently evaluates and approves Gate 3.
3. **Gate 4 Human Exclusivity:** `engineering-governance-manager` (GOV-001) packages the dossier. The **Human Sponsor** alone holds signing authority via PowerShell HMAC-SHA256. Any agent-generated token is rejected.
4. **Gate 5 Independent Veto:** Department M auditors operate outside the production hierarchy. AUD-008 holds constitutional veto power over production deployment.

---

## 3. Cryptographic Gate 4 Binding & Change-Triggered Invalidation

1. Gate 4 binds the exact SHA-256 hashes of: SRS (Stage 03), Atomic Task DAG (Stage 05), Implementation Plan (Stage 07), and Git baseline.
2. **Automatic Revocation:** Any post-approval edit to an upstream prerequisite changes its SHA-256 hash, instantly invalidating the Gate 4 signature.
3. The Tool Execution Gateway must independently verify the Gate 4 token, its physical prerequisite hashes, expiration timestamp, and unique nonce before authorizing any file write to application code in Stage 10.
