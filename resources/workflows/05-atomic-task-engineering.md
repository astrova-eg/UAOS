# Workflow: Stage 05: Atomic Task Engineering
**Workflow ID:** WF-STAGE-05  
**Stage Owner:** workflow-orchestrator (ORCH-001)  
**Gate Authority:** technical-standards-manager (STD-001)  
**Classification:** Standard Engineering Lifecycle Phase  
**Status:** Active  

---

## 1. Stage Objective
Decompose the Gate 1 approved System Requirements Specification (SRS) into self-contained, atomic engineering tasks formatted using the canonical atomic task template, establishing explicit file boundaries, agent assignments, and verifiable acceptance criteria.

---

## 2. Participating Agents & Collaborators
- **Primary Owner (Task Author):** workflow-orchestrator (ORCH-001)
- **Gate Authority (Independent Reviewer):** technical-standards-manager (STD-001)
- **Key Collaborators:** laravel-core-engineer (LCOR-001), migration-schema-engineer (MIGR-001), frontend-architect (FARCH-001)

---

## 3. Entry Preconditions
- Stage 04 completed with valid Gate 1 — Specification Approval Certificate issued by RVAL-001.
- Baselined SRS locked under Change Management Policy.
- Zero unresolved specification blockers.

---

## 4. Step-by-Step Operational Instructions

### Step 4.1: Specification Analysis & Work Breakdown Structure
1. ORCH-001 analyzes functional requirements (`FR-001`, `FR-002`) and decomposes them into discrete, single-responsibility units of engineering work.
2. Ensure each task represents an atomic, isolated unit of work that can be executed, verified, and committed independently without breaking the build.

### Step 4.2: Canonical Atomic Task Authoring
1. ORCH-001 authors each task using the canonical template (`.agents/templates/atomic-task-template.md`).
2. Populate all mandatory task sections:
   - Unique Task Identifier (`TASK-[000]`) and SRS reference.
   - Assigned Implementer Agent ID and Reviewer Agent ID.
   - Explicit target file paths to create/modify and strictly prohibited file paths.
   - Deterministic Given/When/Then acceptance criteria.
   - Mandatory standards: `declare(strict_types=1);`, 300-line code file limit (human-approved exceptions only), PHPStan Level 8+.

### Step 4.3: Dependency Graph & Execution Order
1. ORCH-001 generates the Task Dependency Graph (DAG) establishing linear execution phases (migrations -> domain models -> services -> API/controllers -> UI -> tests).
2. Verify that the DAG contains zero circular dependencies or race hazards.

### Step 4.4: Task Set Assembly & Pre-Submission Validation
1. Assemble the complete Atomic Task Breakdown List (`TASKS-[PROJECT]-[000]`).
2. Verify that 100% of functional requirements in the SRS are covered by assigned tasks.

### Step 4.5: Gate 2 Submission
1. ORCH-001 submits the completed task breakdown to `technical-standards-manager` (STD-001) for independent audit in Stage 06.
2. **Constitutional Invariant:** ORCH-001 is strictly prohibited from self-approving the task set. Gate 2 approval resides exclusively with STD-001.

---

## 5. Mandatory Deliverables
- Atomic Task Breakdown List (`TASKS-[PROJECT]-[000]`)
- Individual Atomic Task Specifications (formatted via `.agents/templates/atomic-task-template.md`)
- Task Dependency Graph (DAG) and Verification Criteria

---

## 6. Exit Criteria
- Complete set of atomic tasks authored and committed to `.agents/tasks/`.
- 100% requirement coverage verified against the SRS.
- Package submitted to STD-001 for Stage 06 Gate 2 review.
