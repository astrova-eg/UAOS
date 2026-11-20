# Quality Governance Policy

**Document ID:** GOV-DOC-008  
**Version:** 1.1.0  
**Classification:** Quality Management Standard (Precedence Tier 3)  
**Effective Date:** 2026-11-15  
**Binding Architecture:** ARCH-GOV-DES-001 (1.1.0-DESIGN-REVIEWED)  

---

## 1. Quality Philosophy
Quality is an immutable engineering property constructed deterministically at every phase, not inspected into a codebase at the end. Incomplete implementations and unverified assumptions are strictly barred.

---

## 2. Mandatory Engineering Invariants

### 2.1 PHP Strict Typing and Explicit Contracts
1. Every PHP file must declare strict types at the very top: `declare(strict_types=1);`.
2. Explicit type declarations are mandatory for all function parameters, return types, and class properties. The use of mixed types is restricted to generic infrastructure contracts with documented justification.

### 2.2 300-Physical-Line Code File Ceiling (Binding Rule 2A)
1. Every code, script, and configuration file in the repository must not exceed 300 physical lines by default.
2. If a file approaches 300 lines, it must be refactored into cohesive, single-responsibility components.
3. Exceptions to the 300-line ceiling are strictly reserved to the Human Project Sponsor:
   - Each exception requires prior explicit human approval recorded in `.agents/governance/file-size-exceptions.json`.
   - The record must state the exact file path, current line count, architectural justification, human approver, and approval timestamp.
   - Agents, automated validators, or department managers cannot approve exceptions.
   - Broad directory or bulk exemptions are invalid.

### 2.3 Prohibition of Fake Implementations and Placeholders
1. Production files must never contain placeholder implementations, empty method bodies, temporary mock data, or `TODO`/`FIXME` stubs.
2. Test doubles, mocks, and fakes are strictly prohibited inside production namespaces (`App\` or `Core\`); they are permissible only within designated test suites (`tests/`).

### 2.4 Mutation Testing & Test Coverage
1. Static Analysis: PHPStan / Larastan Level 8+ passing with zero errors.
2. Test Coverage: Domain logic ≥ 100%, Application Services ≥ 90%, Controllers/APIs ≥ 85%.
3. Mutation Testing: Mutation Score Indicator (MSI) ≥ 80% on critical domains and authorization engines.

### 2.5 Bidirectional Requirement-to-Test Traceability
1. Every functional requirement (`FR-XXX`) in an approved SRS must trace directly to one or more automated tests.
2. Acceptance signoff (Stage 13) cannot occur without full bidirectional requirement-to-test mapping.
