# Workflow: Stage 06: Atomic Task Review
**Workflow ID:** WF-STAGE-06  
**Stage Owner:** technical-standards-manager (STD-001)  
**Gate Authority:** technical-standards-manager (STD-001)  
**Classification:** Standard Engineering Lifecycle Phase  
**Status:** Active  

---

## 1. Stage Objective
Independently audit the atomic task breakdown authored by `workflow-orchestrator` (ORCH-001), verify strict atomicity, file boundary encapsulation, absence of scope overlaps, and testability, and formally issue the Gate 2 — Atomic Task Approval Certificate.

---

## 2. Participating Agents & Collaborators
- **Primary Owner & Gate Authority:** technical-standards-manager (STD-001)
- **Task Author (Submitting Agent):** workflow-orchestrator (ORCH-001)
- **Key Collaborators:** independent-code-quality-auditor (AUD-002), requirements-traceability-specialist (TRAC-001)

---

## 3. Entry Preconditions
- Stage 05 completed with Atomic Task Breakdown List (`TASKS-[PROJECT]-[000]`) submitted by ORCH-001.
- All tasks formatted using `.agents/templates/atomic-task-template.md`.
- Upstream Gate 1 Certificate confirmed valid.

---

## 4. Step-by-Step Operational Instructions

### Step 4.1: Separation of Duties & Authorship Verification
1. STD-001 verifies that the submitted task breakdown was authored by ORCH-001 and has not been self-certified.
2. Confirm strict separation between authorship (Stage 05) and independent approval (Stage 06).

### Step 4.2: Atomicity & Scope Boundary Audit
1. Audit each individual task specification:
   - Verify single-responsibility principle: each task must address exactly one cohesive objective.
   - Verify file scope encapsulation: target files and prohibited directories must be explicitly enumerated.
   - Verify zero overlap: no two concurrent tasks may target the same source file without explicit merge coordination.

### Step 4.3: Standards & Acceptance Verification
1. AUD-002 audits tasks for mandatory inclusion of:
   - `declare(strict_types=1);` mandate.
   - 300-line code file limit ceiling (with human-approved exception governance).
   - PHPStan Level 8+ verification criteria.
   - Unit or feature test verification requirements.
2. TRAC-001 confirms that every functional requirement in the SRS is mapped to at least one atomic task.

### Step 4.4: Gate 2 Formal Evaluation & Signoff
1. STD-001 compiles the **Gate 2 — Atomic Task Approval Certificate** using the canonical template (`.agents/templates/gate-2-atomic-task-approval-certificate-template.md`).
2. Record explicit verdict: `APPROVED`, `REJECTED`, or `REMANDED_FOR_REWORK`. Blank or ambiguous fields constitute rejection.
3. Upon approval, sign the certificate with digital hash and timestamp, formally unlocking **Stage 07: Implementation Planning**.

### Step 4.5: Rejection and Rework Escalation
1. If tasks fail atomicity, file boundaries, or standard checks, STD-001 remands the task set to ORCH-001 in Stage 05 with a formal Audit Findings Matrix.
2. Stage 07 remains strictly locked until an updated task breakdown is approved.

---

## 5. Mandatory Deliverables
- Gate 2 — Atomic Task Approval Certificate (`CERT-GATE-2-[PROJECT]-[000]` using `.agents/templates/gate-2-atomic-task-approval-certificate-template.md`)
- Atomic Task Audit Report & Findings Log

---

## 6. Exit Criteria
- Gate 2 Certificate formally issued with explicit `APPROVED` verdict and digital signature of STD-001.
- Zero outstanding BLOCKER or CRITICAL task defects.
- Approved task list locked under Change Management Policy.
