# Dependency Management Standards

**Document ID:** DEV-DOC-004  
**Version:** 2.0.0  
**Classification:** Dependency Governance  
**Status:** [Active Reference]  

---

## 1. Dependency Governance
- Third-party packages must be vetted for security vulnerabilities via `composer audit` and `npm audit`.
- Commit both `composer.json` and `composer.lock` to guarantee deterministic builds.
- Packages introducing non-standard native C extensions or requiring Docker hypervisors are strictly prohibited.