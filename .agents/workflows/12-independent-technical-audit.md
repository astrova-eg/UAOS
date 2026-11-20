# Workflow: Stage 12: Independent Technical Audit
**Workflow ID:** WF-STAGE-12  
**Stage Owner:** Department M Independent Auditors  
**Gate Authority:** release-readiness-auditor (AUD-008)  
**Classification:** Standard Engineering Lifecycle Phase  
**Status:** Active  

---

## 1. Stage Objective
Conduct rigorous, autonomous, adversarial multi-domain technical audit of the complete codebase, architecture, security, database topology, UI/UX accessibility, and test suites with absolute constitutional veto authority, formally issuing the Gate 5 — Production Readiness Certificate.

---

## 2. Participating Agents & Collaborators
- **Lead Audit Authority:** release-readiness-auditor (AUD-008)
- **Domain Independent Auditors (Dept M):**
  - independent-architecture-auditor (AUD-001)
  - independent-code-quality-auditor (AUD-002)
  - independent-security-auditor (AUD-003)
  - independent-database-auditor (AUD-004)
  - independent-ui-ux-auditor (AUD-005)
  - independent-api-auditor (AUD-006)
  - independent-test-suite-auditor (AUD-007)

---

## 3. Entry Preconditions
- Stage 11 completed with verified Test Execution Report showing 100% test pass rate.
- All source files, migrations, tests, and documentation committed to target branch.
- Complete audit dossier submitted by QA-001.

---

## 4. Step-by-Step Operational Instructions

### Step 4.1: Autonomous Multi-Domain Audit Execution
Department M auditors independently and simultaneously inspect the codebase:
1. **AUD-001 (Architecture):** Verifies domain boundaries, layer isolation, absence of circular references, and adherence to approved ADRs.
2. **AUD-002 (Code Quality):** Enforces strict types (`declare(strict_types=1);`), 300-line default code-file ceiling (verifying explicit prior human approval for any recorded exceptions), explicit return types, and runs PHPStan Level 8+.
3. **AUD-003 (Security):** Audits for OWASP Top 10 vulnerabilities, timing attacks, authentication bypasses, secure session handling, and cryptographic key safety.
4. **AUD-004 (Database):** Inspects queries for N+1 hazards, missing foreign key indexes, lock contention risks, and migration reversibility.
5. **AUD-005 (UI/UX & Accessibility):** Audits frontend markup against WCAG 2.1 AA accessibility guidelines, color contrast, and design system tokens.
6. **AUD-006 (API Integrity):** Validates OpenAPI 3.1.0 contract conformance, breaking change absence, and standard error formats.
7. **AUD-007 (Test Suite Rigor):** Audits test suite quality, verifying that assertions validate actual domain logic and mutation score thresholds are met.
8. **AUD-008 (Release Readiness):** Validates operational readiness, rollback procedures, configuration secrets isolation, and deployment scripts.

### Step 4.2: Findings Matrix & Severity Classification
1. Every identified deficiency is logged into the formal **Independent Technical Audit Report** using the canonical template (`.agents/templates/audit-report-template.md`).
2. Classify findings strictly: `BLOCKER`, `CRITICAL`, `MAJOR`, or `MINOR`.

### Step 4.3: Constitutional Veto Enforcement
1. **Absolute Veto Invariant:** If even a single `BLOCKER` or `CRITICAL` finding exists, AUD-008 is constitutionally required to issue a **VETO**.
2. No agent, manager, or executive may override a Department M veto.
3. In the event of a veto, the entire implementation package is remanded to the respective engineering departments for remediation.

### Step 4.4: Gate 5 Formal Evaluation & Signoff
1. If all 8 domains achieve zero blockers and zero criticals, AUD-008 compiles the **Gate 5 — Production Readiness Certificate** using the canonical template (`.agents/templates/gate-5-production-readiness-certificate-template.md`).
2. Record explicit verdict: `APPROVED`, `REJECTED`, or `CONDITIONALLY_APPROVED`.
3. Sign certificate with digital hash and timestamp, formally unlocking **Stage 13: Acceptance Verification**.

---

## 5. Mandatory Deliverables
- Gate 5 — Production Readiness Certificate (`CERT-GATE-5-[PROJECT]-[000]` using `.agents/templates/gate-5-production-readiness-certificate-template.md`)
- Independent Technical Audit Report (`AUD-REP-[PROJECT]-[000]` using `.agents/templates/audit-report-template.md`)
- Multi-Domain Audit Findings & Severity Matrix

---

## 6. Exit Criteria
- Gate 5 Certificate formally issued with explicit `APPROVED` verdict and digital signature of AUD-008.
- Exactly zero unresolved BLOCKER or CRITICAL audit findings.
- Implementation cleared for Stage 13 Definition of Done acceptance verification.
