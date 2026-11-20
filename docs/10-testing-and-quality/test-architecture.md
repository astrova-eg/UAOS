# Test Architecture & Harnesses

**Document ID:** QA-DOC-002  
**Version:** 2.0.0  
**Classification:** Testing Standard  
**Status:** [Active Reference]  

---

## 1. Test Harness Standards
- Feature tests extend `Tests\TestCase` and use `Illuminate\Foundation\Testing\LazilyRefreshDatabase`.
- Freeze temporal environments during date-sensitive tests via `Carbon::setTestNow()`.
- Never mock internal database query builders; use real database transactions with automatic rollback.