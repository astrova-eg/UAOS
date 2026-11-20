# Quality Gates & Mutation Analysis

**Document ID:** QA-DOC-008  
**Version:** 2.0.0  
**Classification:** Quality Gate Standard  
**Status:** [Active Reference]  

---

## 1. Gating Criteria
- **Static Analysis:** PHPStan / Larastan Level 8 or max with zero errors.
- **Code Style:** Laravel Pint PSR-12 clean.
- **Mutation Score Indicator (MSI):** Infection PHP MSI >= 80% on core domain aggregates.