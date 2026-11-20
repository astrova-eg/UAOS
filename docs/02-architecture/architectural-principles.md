# Core Architectural Principles

**Document ID:** ARCH-DOC-002  
**Version:** 2.0.0  
**Classification:** Architectural Principles  
**Status:** [Active Architecture]  

---

## 1. Guiding Principles
1. **Strict Typing Everywhere:** Line 3 strict types declaration. Zero untyped parameters or return values.
2. **Single Responsibility Actions:** Prefer invokable Action classes over fat controllers or God models.
3. **Immutability by Default:** DTOs and Value Objects are declared `readonly`.
4. **Database-Enforced Invariants:** Foreign keys, cascades, and check constraints enforced at the relational engine level.
5. **Fail-Fast Defense:** Validate all inputs at the perimeter using Form Requests before executing application actions.