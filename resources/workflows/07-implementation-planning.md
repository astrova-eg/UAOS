# Workflow: Stage 07: Implementation Planning
**Workflow ID:** WF-STAGE-07  
**Stage Owner:** laravel-system-architect (LARCH-001)  
**Gate Authority:** chief-software-architect (ARCH-001)  
**Classification:** Standard Engineering Lifecycle Phase  
**Status:** Active  

---

## 1. Stage Objective
Construct an ordered, phased implementation plan detailing migration rollout, domain model engineering, service layer composition, API controller routing, UI assembly, and deterministic rollback contingency strategies using the canonical implementation plan template.

---

## 2. Participating Agents & Collaborators
- **Primary Owner (Plan Author):** laravel-system-architect (LARCH-001)
- **Gate Authority (Independent Reviewer):** chief-software-architect (ARCH-001)
- **Key Collaborators:** database-architect (DBA-001), devops-architect (OPS-001), qa-architect (QA-001)

---

## 3. Entry Preconditions
- Stage 06 completed with valid Gate 2 — Atomic Task Approval Certificate issued by STD-001.
- Baselined atomic task set locked under Change Management Policy.
- Zero unresolved task breakdown blockers.

---

## 4. Step-by-Step Operational Instructions

### Step 4.1: Phase Structuring & Linear Rollout Sequence
1. LARCH-001 organizes approved atomic tasks into strict, sequential delivery phases:
   - Phase 1: Database Migrations & Schemas
   - Phase 2: Domain Entities, Models & Value Objects
   - Phase 3: Domain Actions & Business Services
   - Phase 4: API Endpoints, Form Requests & Routes
   - Phase 5: UI Views & Frontend Components
   - Phase 6: Automated End-to-End Verification
2. Ensure no downstream component is authored before its upstream dependencies exist.

### Step 4.2: Zero-Downtime Database Migration Sequencing
1. DBA-001 details migration safety rules:
   - Additive-only schema progression (columns must be nullable or have default values).
   - Expand-and-contract migration steps documented.
   - Long-running table locks prohibited; index creation sequenced before foreign key addition.

### Step 4.3: Deterministic Rollback Contingency Design
1. OPS-001 and LARCH-001 formulate step-by-step rollback procedures for every implementation phase.
2. Verify data preservation mechanisms: migrations must be confirmed reversible via `php artisan migrate:rollback` without data corruption.

### Step 4.4: Canonical Implementation Plan Compilation
1. LARCH-001 compiles the formal plan using the canonical template (`.agents/templates/implementation-plan-template.md`).
2. Populate all sections: upstream certificate references, task DAG, rollback steps, quality thresholds (strict types, 300-line code file limit, PHPStan Level 8+).

### Step 4.5: Gate 3 Submission
1. LARCH-001 signs the plan with author checksum and submits the completed Implementation Plan and Rollback Strategy to `chief-software-architect` (ARCH-001) for Gate 3 audit in Stage 08.

---

## 5. Mandatory Deliverables
- Implementation Plan Document (`PLAN-[PROJECT]-[000]` using `.agents/templates/implementation-plan-template.md`)
- Migration Sequencing & Zero-Downtime Strategy
- Deterministic Rollback Plan

---

## 6. Exit Criteria
- Canonical implementation plan fully populated and committed.
- Zero-downtime and rollback procedures verified.
- Package submitted to ARCH-001 for Stage 08 Gate 3 review.
