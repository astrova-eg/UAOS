# Contributing to Universal Agent Operating System (UAOS)

First of all, thank you for considering contributing to **UAOS**! We welcome contributions from developers, AI engineers, and security researchers worldwide.

UAOS is governed by strict institutional engineering standards and mathematical invariants. To ensure reliability, security, and deterministic execution across enterprise PHP and Laravel environments, all contributions must adhere to the standards outlined in this document.

---

## 📑 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Constitutional Invariants (Non-Negotiable)](#constitutional-invariants-non-negotiable)
- [How Can I Contribute?](#how-can-i-contribute)
  - [Reporting Bugs](#reporting-bugs)
  - [Suggesting Enhancements](#suggesting-enhancements)
  - [Contributing a New Agent](#contributing-a-new-agent)
  - [Contributing a New Canonical Skill](#contributing-a-new-canonical-skill)
  - [Contributing an IDE/Platform Adapter](#contributing-an-ideplatform-adapter)
- [Local Development Setup](#local-development-setup)
- [Running Automated Tests](#running-automated-tests)
- [Pull Request & Review Workflow](#pull-request--review-workflow)
- [Commit Message Conventions](#commit-message-conventions)
- [Governance & Stewardship](#governance--stewardship)

---

## Code of Conduct

This project is governed by the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating in UAOS development, you are expected to uphold this code. Please report unacceptable behavior directly to [hossam.mohammed2985@gmail.com](mailto:hossam.mohammed2985@gmail.com).

---

## Constitutional Invariants (Non-Negotiable)

Every contribution must strictly comply with the **5 Constitutional Invariants** enforced by the UAOS `ToolGateway`:

1. **Invariant 1 — Path Confinement:** No code may access, read, or mutate paths outside the host application root. Directory traversal (`../`) and symlink escapes are strictly forbidden.
2. **Invariant 2 — Zero Vendor & Core Pollution:** Never modify files inside `vendor/`, `composer.lock`, or Laravel/PHP runtime core files.
3. **Invariant 3 — Shell Command Sanitization:** No unverified shell commands, background daemons, or dangerous commands (`rm -rf`, raw database drops).
4. **Invariant 4 — Zero Placeholder Implementations:** Placeholder code (e.g., `// TODO: implement later`, empty dummy functions, mock production classes) is strictly rejected. All code must be complete, functional, and verifiable.
5. **Invariant 5 — Gate 4 Cryptographic Clearance:** Any tool, adapter, or agent that performs state mutation must integrate with the Gate 4 HMAC-SHA256 clearance engine.

---

## How Can I Contribute?

### Reporting Bugs
If you discover an unexpected error, unexpected tool behavior, or runtime crash:
1. Ensure the bug has not already been reported by searching [GitHub Issues](https://github.com/astrova-eg/UAOS/issues).
2. Open a new issue using the **Bug Report** template.
3. Include your exact PHP version, operating system, Laravel version (if applicable), and minimal reproduction steps.
4. If the bug relates to a security vulnerability, **do not** open a public issue. Follow our [Security Policy](SECURITY.md).

### Contributing a New Agent
UAOS organizes capabilities into 14 functional departments (Dept A through Dept N). When contributing a new specialized agent:
1. Identify the appropriate department (e.g., Dept D for Laravel/PHP, Dept E for Database, Dept F for Security).
2. Author the agent specification in `.agents/agents/<department_code>/<agent-name>.md`.
3. Provide complete Frontmatter metadata:
   - `id`: Unique agent identifier (e.g., `SEC-NEW-01`).
   - `department`: Functional division.
   - `role`: Clear 2–5 word description.
   - `skills`: Whitelist of canonical skills used.
   - `clearance_level`: Gate 0 / Gate 4 clearance requirements.
4. Ensure the system prompt enforces Invariants 1–5 and deterministic output.

### Contributing a New Canonical Skill
Skills reside in `.agents/skills/<skill-name>/SKILL.md`:
1. Focus on a single engineering domain (e.g., `redis-cluster-caching`, `grpc-service-contracts`).
2. Provide explicit step-by-step instructions, negative constraints (what NOT to do), and verifiable acceptance criteria.
3. Include real, enterprise-grade PHP/Laravel examples—no placeholders.

### Contributing an IDE/Platform Adapter
Adapters reside in `src/Adapters/` and implement `AdapterInterface`:
1. Extend `AbstractAdapter`.
2. Map agent definitions and system instructions to the IDE's native format (e.g., `.sublime/`, `.fleet/`, or webhooks).
3. Add a corresponding test under `tests/Adapters/`.

---

## Local Development Setup

### Prerequisites
* PHP `^8.2 || ^8.3 || ^8.4`
* Composer 2.x
* Required extensions: `ext-json`, `ext-hash`, `ext-mbstring`
* Recommended extensions: `ext-pdo_sqlite`, `ext-curl`

### Steps
1. Fork the repository on GitHub: [https://github.com/astrova-eg/UAOS](https://github.com/astrova-eg/UAOS).
2. Clone your fork locally:
   ```bash
   git clone https://github.com/<your-username>/UAOS.git
   cd UAOS
   ```
3. Install dependencies:
   ```bash
   composer install
   ```
4. Verify the test suite:
   ```bash
   php tests/run-all-tests.php
   ```

---

## Running Automated Tests

UAOS enforces 100% test pass rate across all 1,219 assertions. Before opening a Pull Request, run the automated test suite:

```bash
php tests/run-all-tests.php
```

If you added new PHP code in `src/`, you must provide automated test coverage in `tests/`:
* **Unit Tests:** `tests/Unit/`
* **Security & Invariants:** `tests/Security/`
* **Adapters & MCP:** `tests/Adapters/` or `tests/Mcp/`
* **Features:** `tests/Feature/`

---

## Pull Request & Review Workflow

1. Create a descriptive feature branch from `main`:
   ```bash
   git checkout -b feat/add-graphql-agent
   ```
2. Commit your changes following [Conventional Commits](#commit-message-conventions).
3. Ensure all tests pass with zero failures:
   ```bash
   php tests/run-all-tests.php
   ```
4. Push your branch to your fork:
   ```bash
   git push origin feat/add-graphql-agent
   ```
5. Open a Pull Request to `astrova-eg/UAOS:main`.
6. Complete the mandatory PR checklist.
7. An **Astrova Agency** core maintainer and independent reviewer will review your submission.

---

## Commit Message Conventions

We adhere to [Conventional Commits 1.0.0](https://www.conventionalcommits.org/):

```
<type>(<scope>): <short description>
```

### Types:
* `feat`: A new agent, skill, adapter, or runtime capability.
* `fix`: A bug fix or error mitigation.
* `docs`: Documentation updates or additions to ADRs.
* `test`: Adding or updating test suites.
* `refactor`: Code changes that neither fix a bug nor add a feature.
* `perf`: Performance optimization.
* `sec`: Cryptographic, Gate 4, or Tool Gateway security enhancements.

---

## Governance & Stewardship

UAOS is maintained and governed by **Astrova Agency**. While UAOS is an open-source framework, all code merges, releases, and architectural directions are overseen by the core engineering stewardship team to ensure mathematical integrity, zero runtime regression, and strict enterprise security.

Thank you for contributing to the future of governed autonomous engineering!
