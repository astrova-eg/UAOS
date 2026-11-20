# Workflow: Stage 10: Code Implementation
**Workflow ID:** WF-STAGE-10  
**Stage Owner:** Assigned Engineering Agents (Depts D, E, F, H, I)  
**Gate Authority:** quality-governance-manager (QGOV-001)  
**Classification:** Standard Engineering Lifecycle Phase  
**Status:** Active  

---

## 1. Stage Objective
Author strictly typed, high-performance, secure production source code, migrations, controllers, domain actions, and UI components in strict conformance with the approved atomic tasks, bounded scopes, and engineering standards.

---

## 2. Participating Agents & Collaborators
- **Primary Implementers:** Assigned Engineering Agents from Department D (PHP/Laravel), Department E (Database), Department F (Security), Department H (Frontend), Department I (API)
- **Quality Gatekeeper:** quality-governance-manager (QGOV-001)
- **Key Collaborators:** laravel-core-engineer (LCOR-001), migration-schema-engineer (MIGR-001), frontend-architect (FARCH-001), api-endpoint-engineer (EP-001)

---

## 3. Entry Preconditions
- **MANDATORY HUMAN AUTHORIZATION:** Stage 09 completed with a physically verified, signed **Gate 4 — Human Implementation Authorization Record** (`AUTH-GATE-4-[PROJECT]-[000]`).
- Under no circumstances may any agent create or modify production code without explicit human authorization.
- Approved Implementation Plan and Atomic Task set locked and verified.

---

## 4. Step-by-Step Operational Instructions

### Step 4.1: Gate 4 Human Authorization Inspection
1. Every assigned engineering agent must inspect `AUTH-GATE-4-[PROJECT]-[000]` before initiating work.
2. Verify that the task being executed falls strictly within the authorized scope (files, migrations, contracts). If authorization is absent, ambiguous, or invalid, **HALT** immediately.

### Step 4.2: Bounded Task Execution & Code Authoring
1. Implementers execute assigned atomic tasks following the linear sequence defined in the Implementation Plan.
2. Strictly respect file boundaries: create/modify only files explicitly designated in the task specification; touch no files outside the bounded context.

### Step 4.3: Engineering Standards & Invariants Enforcement
1. Every authored PHP file must declare strict types: `declare(strict_types=1);`.
2. Code file length default ceiling: Every code file must contain no more than 300 lines by default. Classes approaching or exceeding 300 lines must be decomposed into cohesive, maintainable units (focused service/action classes). Any exception above 300 lines requires specific technical justification, documented explanation why decomposition is unsuitable, explicit prior human user approval, and a recorded exception scope and affected file. No agent, automated process, validator, or department manager may grant itself an exception. Approval for one file does not establish a general exception for other files. Unauthorized oversized files must be treated as non-compliant.
3. Apply explicit type declarations for all method arguments, return types, and class properties.
4. Database migrations must follow expand-and-contract additive rules.

### Step 4.4: Concurrent Unit & Feature Test Authoring
1. Implementers write corresponding unit and feature tests concurrently with production code.
2. Cover happy paths, validation failure boundaries, and domain exception edge cases.

### Step 4.5: Self-Review & Linter Verification
1. Each implementer executes their agent specification's mandatory 55-Point Self-Review Checklist.
2. Execute code formatting: `vendor/bin/pint` or `vendor/bin/php-cs-fixer`.
3. Execute local static analysis: `vendor/bin/phpstan analyse --level=8`. Resolve 100% of errors.

### Step 4.6: Internal Quality Gate Submission
1. QGOV-001 reviews self-review logs, code style reports, and static analysis outputs.
2. Upon verification, QGOV-001 clears the implementation package for **Stage 11: Testing and Verification**.

---

## 5. Mandatory Deliverables
- Production Source Code (Controllers, Actions, Models, Resources)
- Database Migrations (conforming to zero-downtime rules)
- Unit and Feature Tests (Pest / PHPUnit)
- Self-Review Evidence Logs & Static Analysis Receipts

---

## 6. Exit Criteria
- 100% of tasks in the approved scope implemented within bounded file constraints.
- Zero PHPStan errors at Level 8+.
- PSR-12 coding standard compliance verified.
- Package cleared by QGOV-001 for comprehensive automated testing.
