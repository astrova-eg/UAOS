# Documentation Changelog

All notable changes to the Universal Core documentation architecture and technical references are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0] - 2026-11-15

### Added
- Established the standardized 18-domain enterprise documentation architecture under root-level `docs/`:
  - `00-governance`: Documentation lifecycle, ownership, approval, and quality gates.
  - `01-getting-started`: Local developer onboarding for native Windows Laragon, PowerShell, PHP 8.3+, and Redis.
  - `02-architecture`: Architectural blueprint, Hexagonal boundaries, and data flows.
  - `03-core-systems`: Specifications and status classifications for CORE-SYS-01 through CORE-SYS-18.
  - `04-development`: PSR-12 coding conventions, Action classes, and local workflows.
  - `05-guides`: Practical implementation, integration, migration, and troubleshooting guides.
  - `06-api-reference`: OpenAPI 3.1.0 specifications and RFC 7807 problem details.
  - `07-database`: MySQL 8.4 LTS schema overview, migrations, and indexing strategies.
  - `08-security`: Defense-in-depth, envelope encryption, and secrets management.
  - `09-operations`: Zero-downtime deployment, Redis queue management, and disaster recovery.
  - `10-testing-and-quality`: Pest/PHPUnit test architecture, mutation testing, and CI gates.
  - `11-configuration-reference`: Exhaustive `.env` dictionary and Pennant feature flags.
  - `12-release-management`: Semantic Versioning 2.0.0, release checklists, and changelog.
  - `13-contributing`: Conventional Commits 1.0.0, PR workflows, and code review rules.
  - `14-reference`: Canonical glossaries, technology baselines, and FAQs.
  - `15-decisions`: Architecture Decision Records (ADRs) catalog.
  - `16-templates`: Nine reusable engineering document blueprints.
  - `17-archive`: Archival policy and historical index.
- Comprehensive `DOCUMENTATION-INDEX.md` cataloging all technical resources across the repository.
- Upgraded `SKILL-DOC-001` (Technical Documentation Standards) to version 2.0.0.
- Upgraded `SKILL-GIT-001` (Git Workflow and Conventional Commits) to version 2.0.0.

### Changed
- Integrated code-grounded documentation principles: all documentation must reflect verified source code whenever implementation exists.
- Enforced eight standardized implementation status classifications (`[Implemented]`, `[Verified]`, `[Designed but Not Implemented]`, etc.).
- Integrated Conventional Commits and atomic pull request governance across documentation updates.

---

## [1.0.0] - 2026-11-13

### Added
- Initial foundational documentation policy in `.agents/governance/DOCUMENTATION-POLICY.md`.
- Initial placeholder stubs for shared skills.
