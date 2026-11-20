# Feature Flags (Laravel Pennant)

**Document ID:** CONF-DOC-004  
**Version:** 2.0.0  
**Classification:** Feature Flag Standard  
**Status:** [Active Reference]  

---

## 1. Feature Flag Governance
Feature toggles use Laravel Pennant with database or Redis drivers:
- Scope: Evaluated per-tenant or per-user.
- Lifecycle: Feature flags must be removed within 60 days of full production rollout.