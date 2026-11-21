## Description of Contribution
<!-- Provide a clear summary of what this Pull Request introduces, fixes, or optimizes. -->

## Type of Change
- [ ] New Specialized Agent (Department: _________)
- [ ] New Canonical Skill (`.agents/skills/`)
- [ ] New IDE / Platform Adapter (`src/Adapters/`)
- [ ] Runtime or Core Optimization (`src/`)
- [ ] Security Enhancement (Gate 4 / Tool Gateway / RDEI)
- [ ] Test Suite Addition (`tests/`)
- [ ] Documentation / ADR Update (`docs/`)

## Verification & Invariant Compliance
Please confirm that your submission satisfies all architectural requirements:

- [ ] **Tests Passed:** I have executed `php tests/run-all-tests.php` and verified that all 1,219+ assertions pass without errors.
- [ ] **New Tests Added:** Appropriate automated unit/feature tests have been included for new code.
- [ ] **Invariant 1 (Path Confinement):** No operations access or mutate paths outside the host application directory.
- [ ] **Invariant 2 (Vendor Immutability):** No modifications have been made to `vendor/`, `composer.lock`, or framework internals.
- [ ] **Invariant 3 (Command Sanitization):** No unsafe or arbitrary shell commands have been introduced.
- [ ] **Invariant 4 (Authenticity):** The code contains zero placeholder comments (`// TODO`), dummy stubs, or mock production implementations.
- [ ] **Invariant 5 (Gate 4 Clearance):** Any destructive/mutating actions adhere to the Gate 4 HMAC clearance protocol.

## Related Issues
<!-- Link any related issue: Fixes #123 -->
