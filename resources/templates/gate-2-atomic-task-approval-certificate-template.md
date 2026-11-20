# Gate 2 — Atomic Task Approval Certificate
**Certificate ID:** CERT-GATE-2-[PROJECT]-[000]  
**Lifecycle Stage:** Stage 06: Atomic Task Review  
**Target Gate:** Gate 2: Atomic Task Approval Gate  
**Evaluating Authority:** technical-standards-manager (STD-001)  
**Task Author Agent:** workflow-orchestrator (ORCH-001)  
**Date of Evaluation:** YYYY-MM-DD  
**Overall Verdict:** [APPROVED | REJECTED | REMANDED_FOR_REWORK]  

> [!CRITICAL]
> **Separation of Duties & Defensive Standard:** Under constitutional governance, the author of atomic tasks (`workflow-orchestrator` / ORCH-001) is strictly forbidden from approving this certificate. Approval authority belongs exclusively to `technical-standards-manager` (STD-001). Blank or ambiguous approval fields constitute an immediate rejection.

---

## 1. Target Task Breakdown Identification
- **Project ID:** [Project Identifier]
- **Target Task Breakdown Document:** `TASKS-[PROJECT]-[000]` (Version: [X.Y.Z])
- **Task Set Checksum (SHA-256):** [Hex Hash]
- **Governing Specification:** `SRS-[PROJECT]-[000]`
- **Upstream Gate 1 Certificate:** `CERT-GATE-1-[PROJECT]-[000]` (Status: APPROVED)
- **Total Tasks in Breakdown:** [Count]

---

## 2. Standards & Atomicity Audit Criteria
| Audit Dimension | Requirement Checked | Verification Method | Status (PASS / FAIL) | Observations / Findings |
| :--- | :--- | :--- | :---: | :--- |
| **Strict Atomicity** | Each task has exactly one bounded, cohesive objective. | Task scope analysis | [PASS/FAIL] | [Notes] |
| **File Scope Boundaries** | Target files and prohibited directories explicitly listed per task. | Scope boundary check | [PASS/FAIL] | [Notes] |
| **Acceptance Criteria** | Each task contains explicit, deterministic verification checkboxes. | Testability verification | [PASS/FAIL] | [Notes] |
| **Dependency Graph** | DAG contains zero circular dependencies or race hazards. | Graph topological sort | [PASS/FAIL] | [Notes] |
| **Standards Compliance** | Tasks enforce `declare(strict_types=1);`, 300-line code file ceiling (human-approved exceptions only), PHPStan level 8+. | Technical standards review | [PASS/FAIL] | [Notes] |

---

## 3. Unresolved Findings, Blockers & Exceptions
- **Blocker / Critical Issues:** [0 Required for approval. List any active blockers.]
- **Permitted Minor Exceptions:** [List documented exceptions with remediation roadmap, if any.]

---

## 4. Formal Gate 2 Verdict & Signoff
- **Evaluation Status:** `[APPROVED | REJECTED | REMANDED_FOR_REWORK]`
- **Gatekeeper Name:** Technical Standards Manager
- **Gatekeeper Agent ID:** `STD-001`
- **Gatekeeper Digital Signature Hash:** [Signature Hash]
- **Timestamp:** YYYY-MM-DDTHH:MM:SSZ

---

## 5. Permitted Next Lifecycle Step
- **If APPROVED:** Authorized to proceed to **Stage 07: Implementation Planning** (Lead: `laravel-system-architect` / LARCH-001).
- **If REJECTED / REMANDED:** Remanded to **Stage 05: Atomic Task Engineering** for task restructuring by ORCH-001. Stage 07 remains locked.
