# Workflow: Stage 02: Architecture and Design
**Workflow ID:** WF-STAGE-02  
**Stage Owner:** chief-software-architect (ARCH-001)  
**Gate Authority:** engineering-decision-manager (DECM-001)  
**Classification:** Standard Engineering Lifecycle Phase  
**Status:** Active  

---

## 1. Stage Objective
Formulate foundational architectural blueprints, delineate bounded contexts, design database topology, define security and tenant isolation models, and author canonical Architecture Decision Records (ADRs) governing the solution.

---

## 2. Participating Agents & Collaborators
- **Primary Owner:** chief-software-architect (ARCH-001)
- **Gate Authority:** engineering-decision-manager (DECM-001)
- **Key Collaborators:** laravel-system-architect (LARCH-001), domain-driven-design-architect (DDD-001), database-architect (DBA-001), application-security-architect (SEC-001)

---

## 3. Entry Preconditions
- Stage 01 completed with verified Active Project Technology Profile (`active-profile.json`).
- Upstream Discovery Report reviewed with zero unresolved high-severity dependency risks.
- Target framework capabilities and constraints established.

---

## 4. Step-by-Step Operational Instructions

### Step 4.1: Bounded Context & Subsystem Delineation
1. ARCH-001 and DDD-001 define domain models, bounded contexts, and ubiquitous language, preventing cross-domain coupling and god objects.
2. Establish interface contracts between subsystems using ports & adapters or domain events.

### Step 4.2: Data Model Topology & Persistence Design
1. DBA-001 designs database entities, primary/foreign key conventions, indexing strategies, and multi-tenant isolation mechanisms.
2. Confirm zero circular foreign keys and verify that schema changes will support zero-downtime expand-and-contract migrations.

### Step 4.3: Security, Authentication & Cryptographic Architecture
1. SEC-001 defines authentication boundaries (Sanctum/Fortify/Passport), RBAC/policy structures, and cryptographic requirements (AES-256-GCM, key rotation).

### Step 4.4: Canonical ADR Authoring
1. ARCH-001 authors formal Architecture Decision Records using the canonical template (`.agents/templates/adr-template.md`).
2. Every significant architectural choice (state management, caching layer, queue drivers) must be substantiated with explicit trade-offs.

### Step 4.5: Architectural Gate Evaluation
1. DECM-001 evaluates the System Architecture Document (SAD) and ADRs against the Engineering Constitution and Global Engineering Rules.
2. Verify zero architectural anti-patterns, layer inversions, or unmitigated security vulnerabilities.
3. Upon approval, DECM-001 issues Architectural Clearance, unlocking **Stage 03: Specification Engineering**.

---

## 5. Mandatory Deliverables
- System Architecture Document (`SAD-[PROJECT]-[000]`)
- Canonical Architecture Decision Records (`ADR-[000]` using `.agents/templates/adr-template.md`)
- Component Boundary and Data Flow Diagrams

---

## 6. Exit Criteria
- System Architecture Document and ADRs committed to designated repository paths.
- Zero unresolved architectural conflicts or constitutional violations.
- Formal architectural signoff issued by DECM-001.
