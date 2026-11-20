# Workflow: Stage 11: Testing and Verification
**Workflow ID:** WF-STAGE-11  
**Stage Owner:** qa-architect (QA-001)  
**Gate Authority:** qa-architect (QA-001)  
**Classification:** Standard Engineering Lifecycle Phase  
**Status:** Active  

---

## 1. Stage Objective
Execute comprehensive automated test suites across all application layers, verify line and branch coverage metrics, validate mutation testing scores, confirm zero regressions, and generate formal test execution evidence logs.

---

## 2. Participating Agents & Collaborators
- **Primary Owner & Gate Authority:** qa-architect (QA-001)
- **Key Collaborators:** unit-testing-specialist (UTST-001), feature-testing-specialist (FTST-001), api-testing-specialist (ATST-001), browser-e2e-testing-specialist (E2E-001), code-coverage-analyst (COV-001)

---

## 3. Entry Preconditions
- Stage 10 completed with all source code, migrations, and preliminary tests implemented.
- Internal quality clearance issued by QGOV-001.
- Zero static analysis errors at PHPStan Level 8+.

---

## 4. Step-by-Step Operational Instructions

### Step 4.1: Automated Test Suite Execution
1. QA-001 orchestrates complete execution of test suites (`vendor/bin/pest` or `php artisan test`):
   - UTST-001 runs isolated unit tests against domain models, actions, and value objects.
   - FTST-001 runs HTTP feature tests, controller flows, middleware assertions, and database transactions.
   - ATST-001 executes API integration tests verifying response payloads, HTTP codes, and header contracts.
   - E2E-001 runs Dusk/Playwright browser integration scenarios for critical user flows.

### Step 4.2: Coverage & Mutation Testing Rigor
1. COV-001 generates code coverage telemetry:
   - Line coverage must achieve >= 80% across modified components.
   - Critical path coverage (authentication, financial calculations, data access controls) must achieve 100%.
2. Execute mutation testing (`vendor/bin/infection`) where supported:
   - Mutation score must achieve >= 80%, confirming tests assert meaningful behavioral invariants rather than superficial code execution.

### Step 4.3: Regression & Concurrency Audit
1. Execute full project regression test suite to verify that no pre-existing feature or subsystem was broken by new additions.
2. Verify database migration rollback dry-run in test environment (`php artisan migrate:fresh` followed by rollback verification).

### Step 4.4: Defect Triage & Remediation Loop
1. If any test fails, QA-001 immediately logs a defect ticket with stack trace and reproduction seed.
2. The failing task is remanded to the responsible implementer in Department D, E, or H. Stage 11 re-runs upon commit of fix.

### Step 4.5: Test Verification Signoff & Handoff
1. When all tests pass (0 failures, 0 errors, 0 skipped critical tests), QA-001 compiles the formal Test Execution Report.
2. QA-001 issues verification clearance and hands off the package to **Department M** for **Stage 12: Independent Technical Audit**.

---

## 5. Mandatory Deliverables
- Test Execution Report (`TEST-REP-[PROJECT]-[000]`)
- Coverage & Mutation Analysis Metrics
- Defect Triage & Resolution Log

---

## 6. Exit Criteria
- 100% automated test pass rate (zero failures or errors).
- Minimum 80% line coverage and 80% mutation score achieved.
- Clean test execution report committed and submitted to Department M.
