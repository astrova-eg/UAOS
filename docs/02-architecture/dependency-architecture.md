# Dependency Architecture & Inversion of Control

**Document ID:** ARCH-DOC-004  
**Version:** 2.0.0  
**Classification:** Architecture Specification  
**Status:** [Active Architecture]  

---

## 1. Dependency Inversion Rules
- Application Actions and Domain Services MUST depend on interface contracts, never on concrete infrastructure adapters.
- Service container bindings in `AppServiceProvider` or domain service providers bind interfaces to implementations.
- Avoid global facade access inside domain logic; inject dependencies through class constructors.