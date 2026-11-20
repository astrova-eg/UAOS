# Feature & Integration Testing Standards

**Document ID:** QA-DOC-004  
**Version:** 2.0.0  
**Classification:** Testing Guide  
**Status:** [Active Reference]  

---

## 1. Feature Testing Directives
- Test entire HTTP lifecycle using Pest `$this->actingAs($user)->postJson('/api/...', $payload)`.
- Assert response status, JSON envelopes, database records (`assertDatabaseHas`), and dispatched events (`Event::fake()`).