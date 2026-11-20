# Master Documentation Index

**Project:** Universal Core  
**Classification:** Canonical Documentation Catalog  
**Version:** 2.0.0  
**Status:** [Active Index]  
**Last Verified:** 2026-11-15  

---

## 1. Overview

This **Master Documentation Index** provides a comprehensive, navigable catalog of all authoritative technical documentation, specifications, guidelines, runbooks, and reference manuals across Universal Core.

---

## 2. Global Index by Engineering Domain

### [00 — Governance & Lifecycle](00-governance/README.md)
- [00-governance/README.md](00-governance/README.md): Governance domain overview and policies index.
- [00-governance/documentation-governance.md](00-governance/documentation-governance.md): Standards, authority, and empirical evidence rules.
- [00-governance/documentation-lifecycle.md](00-governance/documentation-lifecycle.md): Authoring, review, publication, and archival lifecycle.
- [00-governance/ownership-and-responsibilities.md](00-governance/ownership-and-responsibilities.md): Department N, producer agents, and peer review ownership.
- [00-governance/review-and-approval-policy.md](00-governance/review-and-approval-policy.md): Gate criteria, blocking conditions, and sign-offs.
- [00-governance/versioning-and-deprecation.md](00-governance/versioning-and-deprecation.md): SemVer 2.0.0 documentation versioning and sunset rules.
- [00-governance/documentation-quality-gates.md](00-governance/documentation-quality-gates.md): Automated CI linting, link checking, and contract verification.

### [01 — Getting Started & Local Environment](01-getting-started/README.md)
- [01-getting-started/README.md](01-getting-started/README.md): Developer onboarding index.
- [01-getting-started/introduction.md](01-getting-started/introduction.md): Architectural goals, philosophy, and tech stack overview.
- [01-getting-started/prerequisites.md](01-getting-started/prerequisites.md): Software baselines (PHP 8.3+, MySQL 8.4 LTS, Redis, Composer 2.x).
- [01-getting-started/installation.md](01-getting-started/installation.md): Step-by-step repository installation commands.
- [01-getting-started/environment-setup.md](01-getting-started/environment-setup.md): Windows Laragon and PowerShell configuration (Docker strictly prohibited).
- [01-getting-started/project-structure.md](01-getting-started/project-structure.md): Directory organization and architectural namespaces.
- [01-getting-started/first-steps.md](01-getting-started/first-steps.md): Verification commands, health checks, and running tests.
- [01-getting-started/troubleshooting.md](01-getting-started/troubleshooting.md): Common local environment issues and resolutions.

### [02 — Architecture & Systems Design](02-architecture/README.md)
- [02-architecture/README.md](02-architecture/README.md): Architecture domain catalog.
- [02-architecture/architecture-overview.md](02-architecture/architecture-overview.md): High-level architectural blueprint.
- [02-architecture/architectural-principles.md](02-architecture/architectural-principles.md): Core principles (Strict typing, Hexagonal, Action pattern).
- [02-architecture/system-boundaries.md](02-architecture/system-boundaries.md): Domain aggregate boundaries and context maps.
- [02-architecture/dependency-architecture.md](02-architecture/dependency-architecture.md): Service container injection and decoupled contracts.
- [02-architecture/data-flow.md](02-architecture/data-flow.md): Inbound HTTP, async job, and event-driven data flow mechanics.
- [02-architecture/security-architecture.md](02-architecture/security-architecture.md): Defense-in-depth, cryptographic boundaries, and tenant isolation.
- [02-architecture/scalability-and-performance.md](02-architecture/scalability-and-performance.md): Caching, Redis queues, and database optimization.
- [02-architecture/architecture-decisions/README.md](02-architecture/architecture-decisions/README.md): Architecture Decision Records (ADRs) navigation.

### [03 — Core Systems Specifications](03-core-systems/README.md)
- [03-core-systems/README.md](03-core-systems/README.md): Core systems overview and index.
- [03-core-systems/system-catalog.md](03-core-systems/system-catalog.md): Matrix of all 18 core systems with status classifications.
- [03-core-systems/system-dependency-map.md](03-core-systems/system-dependency-map.md): Coupling matrix and communication topology.
- [03-core-systems/system-integration-guide.md](03-core-systems/system-integration-guide.md): Inter-system integration patterns and event contracts.
- [03-core-systems/systems/README.md](03-core-systems/systems/README.md): Detailed system specifications directory (CORE-SYS-01 to CORE-SYS-18).

### [04 — Development Guidelines](04-development/README.md)
- [04-development/README.md](04-development/README.md): Development guidelines index.
- [04-development/development-environment.md](04-development/development-environment.md): Native Windows Laragon and PowerShell workflows.
- [04-development/coding-standards.md](04-development/coding-standards.md): PSR-12, Laravel Pint, and strict typing rules.
- [04-development/architecture-guidelines.md](04-development/architecture-guidelines.md): Action classes, DTOs, and single-responsibility principles.
- [04-development/dependency-management.md](04-development/dependency-management.md): Composer 2.x and NPM supply-chain management.
- [04-development/database-development.md](04-development/database-development.md): Migration authoring, indexing, and seeders.
- [04-development/api-development.md](04-development/api-development.md): FormRequests, JSON:API envelopes, and REST standards.
- [04-development/frontend-development.md](04-development/frontend-development.md): Livewire 3, Filament, and Tailwind RTL rules.
- [04-development/testing-guide.md](04-development/testing-guide.md): Pest and PHPUnit testing conventions.
- [04-development/debugging-guide.md](04-development/debugging-guide.md): Profiling, logging, and query debugging.
- [04-development/local-workflows.md](04-development/local-workflows.md): Common daily artisan commands and PowerShell routines.

### [05 — Implementation & Troubleshooting Guides](05-guides/README.md)
- [05-guides/README.md](05-guides/README.md): Guides catalog.
- [05-guides/implementation-guides/README.md](05-guides/implementation-guides/README.md): Step-by-step implementation walk-throughs.
- [05-guides/integration-guides/README.md](05-guides/integration-guides/README.md): Third-party and payment gateway integrations.
- [05-guides/migration-guides/README.md](05-guides/migration-guides/README.md): Database and architectural migration procedures.
- [05-guides/troubleshooting-guides/README.md](05-guides/troubleshooting-guides/README.md): Diagnostic runbooks for common failures.

### [06 — API Reference & Contracts](06-api-reference/README.md)
- [06-api-reference/README.md](06-api-reference/README.md): API documentation catalog.
- [06-api-reference/api-overview.md](06-api-reference/api-overview.md): REST architectural principles and versioning.
- [06-api-reference/authentication.md](06-api-reference/authentication.md): Sanctum Bearer token flows and API key usage.
- [06-api-reference/authorization.md](06-api-reference/authorization.md): Token abilities, scopes, and tenant permission boundaries.
- [06-api-reference/error-handling.md](06-api-reference/error-handling.md): RFC 7807 Problem Details and HTTP status codes.
- [06-api-reference/endpoints/README.md](06-api-reference/endpoints/README.md): Endpoint contract specifications catalog.

### [07 — Database Architecture & Dictionaries](07-database/README.md)
- [07-database/README.md](07-database/README.md): Database architecture catalog.
- [07-database/database-architecture.md](07-database/database-architecture.md): MySQL 8.4 LTS, InnoDB engine, and utf8mb4 collation.
- [07-database/schema-overview.md](07-database/schema-overview.md): Entity relationship models and domain tables.
- [07-database/relationships.md](07-database/relationships.md): Cardinality, foreign key constraints, and cascade invariants.
- [07-database/migrations.md](07-database/migrations.md): Zero-downtime Expand-Contract migration rules.
- [07-database/indexing-and-performance.md](07-database/indexing-and-performance.md): B-tree indexes, covering indexes, and query execution plans.
- [07-database/data-integrity.md](07-database/data-integrity.md): ACID guarantees, row-level locking, and deadlock prevention.

### [08 — Security & Cryptography](08-security/README.md)
- [08-security/README.md](08-security/README.md): Security domain overview.
- [08-security/security-overview.md](08-security/security-overview.md): Defense-in-depth principles and threat boundaries.
- [08-security/authentication-and-authorization.md](08-security/authentication-and-authorization.md): Multi-factor authentication, sessions, and RBAC.
- [08-security/secrets-management.md](08-security/secrets-management.md): Zero secrets in git, `.env` security, and rotation protocols.
- [08-security/encryption-and-cryptography.md](08-security/encryption-and-cryptography.md): AES-256-GCM envelope encryption and key management.
- [08-security/isolation-and-access-boundaries.md](08-security/isolation-and-access-boundaries.md): Multi-tenant scoping and cross-tenant leakage defense.
- [08-security/secure-development.md](08-security/secure-development.md): OWASP Top 10 mitigation and secure coding practices.
- [08-security/security-incident-procedures.md](08-security/security-incident-procedures.md): Breach response, credential revocation, and post-mortems.

### [09 — Operations & Site Reliability](09-operations/README.md)
- [09-operations/README.md](09-operations/README.md): Operations domain overview.
- [09-operations/deployment.md](09-operations/deployment.md): Atomic zero-downtime symlink deployment pipelines.
- [09-operations/environment-configuration.md](09-operations/environment-configuration.md): Production PHP-FPM, OPcache, and Redis configuration.
- [09-operations/monitoring-and-observability.md](09-operations/monitoring-and-observability.md): Health probes, RED/USE metrics, and alerting.
- [09-operations/logging.md](09-operations/logging.md): Structured JSON logging with Monolog and log rotation.
- [09-operations/backup-and-recovery.md](09-operations/backup-and-recovery.md): Physical backups, point-in-time recovery, and RPO/RTO.
- [09-operations/queue-operations.md](09-operations/queue-operations.md): Supervisord daemon management and Redis queue workers.
- [09-operations/incident-response.md](09-operations/incident-response.md): Escalation paths, runbooks, and disaster mitigation.

### [10 — Testing & Quality Assurance](10-testing-and-quality/README.md)
- [10-testing-and-quality/README.md](10-testing-and-quality/README.md): Quality assurance index.
- [10-testing-and-quality/testing-strategy.md](10-testing-and-quality/testing-strategy.md): Testing pyramid and deterministic testing rules.
- [10-testing-and-quality/test-architecture.md](10-testing-and-quality/test-architecture.md): Base test cases, `LazilyRefreshDatabase`, and model factories.
- [10-testing-and-quality/unit-testing.md](10-testing-and-quality/unit-testing.md): Pure in-memory unit tests for domain logic.
- [10-testing-and-quality/integration-testing.md](10-testing-and-quality/integration-testing.md): Feature tests, HTTP lifecycles, and database interactions.
- [10-testing-and-quality/api-testing.md](10-testing-and-quality/api-testing.md): JSON contract assertions and OpenAPI schema validation.
- [10-testing-and-quality/browser-testing.md](10-testing-and-quality/browser-testing.md): Laravel Dusk automated browser tests and RTL checks.
- [10-testing-and-quality/security-testing.md](10-testing-and-quality/security-testing.md): IDOR probes, fuzzing, and permission escalation tests.
- [10-testing-and-quality/quality-gates.md](10-testing-and-quality/quality-gates.md): PHPStan Level 8+, Infection MSI >= 80%, and Pint linters.

### [11 — Configuration Reference](11-configuration-reference/README.md)
- [11-configuration-reference/README.md](11-configuration-reference/README.md): Configuration index.
- [11-configuration-reference/configuration-overview.md](11-configuration-reference/configuration-overview.md): Config caching and immutability invariants.
- [11-configuration-reference/environment-variables.md](11-configuration-reference/environment-variables.md): Exhaustive `.env` variable dictionary.
- [11-configuration-reference/application-settings.md](11-configuration-reference/application-settings.md): Dynamic database-backed tenant settings.
- [11-configuration-reference/feature-flags.md](11-configuration-reference/feature-flags.md): Laravel Pennant feature flags and canary rollouts.

### [12 — Release Management](12-release-management/README.md)
- [12-release-management/README.md](12-release-management/README.md): Release management index.
- [12-release-management/release-process.md](12-release-management/release-process.md): Release checklist, tag authoring, and verification.
- [12-release-management/versioning-policy.md](12-release-management/versioning-policy.md): Semantic Versioning 2.0.0 guidelines.
- [12-release-management/changelog.md](12-release-management/changelog.md): Release history and changelog.
- [12-release-management/compatibility-matrix.md](12-release-management/compatibility-matrix.md): PHP, MySQL, Redis, and browser compatibility.
- [12-release-management/upgrade-guides/README.md](12-release-management/upgrade-guides/README.md): Version-to-version migration guides.
- [12-release-management/deprecation-policy.md](12-release-management/deprecation-policy.md): Deprecation notice windows and removal criteria.

### [13 — Contributing & Pull Requests](13-contributing/README.md)
- [13-contributing/README.md](13-contributing/README.md): Contributing overview.
- [13-contributing/contribution-guidelines.md](13-contributing/contribution-guidelines.md): Contribution rules for engineers and AI agents.
- [13-contributing/pull-request-process.md](13-contributing/pull-request-process.md): PR lifecycle, templates, and gating.
- [13-contributing/code-review-standards.md](13-contributing/code-review-standards.md): Code review criteria and severity tags.
- [13-contributing/commit-message-conventions.md](13-contributing/commit-message-conventions.md): Conventional Commits 1.0.0 standard.
- [13-contributing/documentation-contributions.md](13-contributing/documentation-contributions.md): Authoring rules for documentation PRs.

### [14 — Reference & Standards](14-reference/README.md)
- [14-reference/README.md](14-reference/README.md): Technical reference catalog.
- [14-reference/glossary.md](14-reference/glossary.md): Canonical domain and technical glossary.
- [14-reference/terminology.md](14-reference/terminology.md): Definitions of standardized architectural terms.
- [14-reference/supported-versions.md](14-reference/supported-versions.md): Supported versions of PHP, Laravel, MySQL, and Redis.
- [14-reference/dependencies.md](14-reference/dependencies.md): Core third-party packages and libraries.
- [14-reference/standards-and-specifications.md](14-reference/standards-and-specifications.md): Referenced RFCs, PSRs, and W3C standards.
- [14-reference/frequently-asked-questions.md](14-reference/frequently-asked-questions.md): Engineering FAQs and troubleshooting answers.

### [15 — Decisions (ADRs)](15-decisions/README.md)
- [15-decisions/README.md](15-decisions/README.md): Architecture Decision Records overview and catalog.
- [15-decisions/adr/README.md](15-decisions/adr/README.md): ADR directory and records.

### [16 — Reusable Templates](16-templates/README.md)
- [16-templates/README.md](16-templates/README.md): Template library catalog.
- [16-templates/technical-document-template.md](16-templates/technical-document-template.md): Standard technical document blueprint.
- [16-templates/architecture-document-template.md](16-templates/architecture-document-template.md): Architecture specification blueprint.
- [16-templates/api-document-template.md](16-templates/api-document-template.md): API contract and endpoint blueprint.
- [16-templates/system-document-template.md](16-templates/system-document-template.md): Core system specification blueprint.
- [16-templates/implementation-guide-template.md](16-templates/implementation-guide-template.md): Step-by-step implementation guide blueprint.
- [16-templates/troubleshooting-template.md](16-templates/troubleshooting-template.md): Incident runbook blueprint.
- [16-templates/adr-template.md](16-templates/adr-template.md): Architecture Decision Record blueprint.
- [16-templates/release-notes-template.md](16-templates/release-notes-template.md): Release notes and announcement blueprint.

### [17 — Archive](17-archive/README.md)
- [17-archive/README.md](17-archive/README.md): Archival policy and catalog of superseded records.

---

## 3. Canonical Upstream Governance Links
For constitutional rules, agent charters, and organizational workflows, consult the immutable canonical authorities:
- **Constitutional Governance:** `../.agents/governance/ENGINEERING-CONSTITUTION.md`
- **Global Engineering Rules:** `../.agents/governance/GLOBAL-ENGINEERING-RULES.md`
- **Technical Documentation Policy:** `../.agents/governance/DOCUMENTATION-POLICY.md`
- **14-Stage Lifecycle Workflows:** `../.agents/workflows/workflow-orchestration-matrix.md`
- **Shared Engineering Skills:** `../.agents/skills/skill-registry.json`
