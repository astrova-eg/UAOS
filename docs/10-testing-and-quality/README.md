# Testing & Quality Assurance Domain

**Domain:** 10-Testing & Quality  
**Classification:** Verification & Quality Engineering  
**Status:** [Active Standards]  
**Authoritative Reference:** [SKILL-TEST-001](../../.agents/skills/testing-standards.md)  

---

## 1. Overview
The **10-Testing and Quality** domain establishes testing strategies, test architecture, unit/feature/API testing protocols, and automated quality gates across Universal Core.

---

## 2. Directory Contents
- **[testing-strategy.md](testing-strategy.md):** Testing pyramid and deterministic testing rules.
- **[test-architecture.md](test-architecture.md):** Base test cases, `LazilyRefreshDatabase`, and factories.
- **[unit-testing.md](unit-testing.md):** Pure in-memory unit tests for domain logic.
- **[integration-testing.md](integration-testing.md):** Feature tests, HTTP lifecycles, and database interactions.
- **[api-testing.md](api-testing.md):** JSON contract assertions and OpenAPI schema validation.
- **[browser-testing.md](browser-testing.md):** Laravel Dusk automated browser tests and RTL checks.
- **[security-testing.md](security-testing.md):** IDOR probes, fuzzing, and permission escalation tests.
- **[quality-gates.md](quality-gates.md):** PHPStan Level 8+, Infection MSI >= 80%, and Pint linters.