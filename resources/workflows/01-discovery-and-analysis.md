# Workflow: Stage 01: Discovery and Analysis
**Workflow ID:** WF-STAGE-01  
**Stage Owner:** project-discovery-specialist (DISC-001)  
**Gate Authority:** engineering-governance-manager (GOV-001)  
**Classification:** Standard Engineering Lifecycle Phase  
**Status:** Active  

---

## 1. Stage Objective
Perform deep static and architectural inspection of the target codebase (or greenfield scope mandate), extract business domains and processes, audit third-party dependencies, and synthesize a fully validated Project Technology Profile.

---

## 2. Participating Agents & Collaborators
- **Primary Owner:** project-discovery-specialist (DISC-001)
- **Gate Authority:** engineering-governance-manager (GOV-001)
- **Key Collaborators:** requirements-engineer (REQ-001), systems-analyst (SYS-001), dependency-risk-analyst (RISK-001)

---

## 3. Entry Preconditions
- Project mandate, problem statement, or repository intake request received from Human Sponsor.
- Read access established to repository root and configuration files.
- Clean working directory state verified.

---

## 4. Step-by-Step Operational Instructions

### Step 4.1: Codebase & Infrastructure Intake Inspection
1. DISC-001 inspects `composer.json`, `package.json`, `.env.example`, and framework configuration files to detect PHP/Laravel versions, database engines, and runtime dependencies.
2. Determine architectural profile type (Greenfield SaaS Filament, Legacy Monolith Blade, API React SPA, etc.).

### Step 4.2: Dependency & Risk Assessment
1. RISK-001 executes security auditing on composer dependencies (`composer audit` or static package analysis).
2. Catalog all third-party libraries, unsupported packages, license constraints, and outdated dependencies.

### Step 4.3: Domain Boundary & Workflow Extraction
1. SYS-001 and REQ-001 extract existing business domain entities, database models, controller entry points, and event dispatch patterns.
2. Outline the initial Domain Boundary Map and service dependency topology.

### Step 4.4: Technology Profile Compilation
1. DISC-001 synthesizes the Active Project Technology Profile (`active-profile.json`) conforming strictly to `.agents/schemas/project-profile.schema.json`.
2. Compile findings into the formal Project Discovery Report.

### Step 4.5: Governance Review & Advisory Gate Signoff
1. GOV-001 verifies the profile against schema requirements and checks for completeness and unmitigated security risks.
2. Upon successful review, GOV-001 issues Advisory Clearance, unlocking **Stage 02: Architecture and Design**.
3. If defects or unprofiled dependencies exist, the report is remanded to DISC-001 for re-inspection.

---

## 5. Mandatory Deliverables
- Project Discovery Report (`DISC-REP-[PROJECT]-[000]`)
- Active Project Technology Profile (`active-profile.json`)
- Initial Domain Boundary Map

---

## 6. Exit Criteria
- `active-profile.json` successfully validated against `project-profile.schema.json`.
- Zero unaddressed CVEs or prohibited licensing blockers in dependency tree.
- Advisory clearance issued by GOV-001.
