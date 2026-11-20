# Introduction to Universal Core

**Document ID:** GS-DOC-001  
**Version:** 2.0.0  
**Classification:** Developer Guide  
**Status:** [Active Reference]  

---

## 1. Architectural Philosophy
Universal Core is a domain-neutral, enterprise-grade architecture engineered for large-scale Laravel applications.

### Key Tenets
1. **Strict Typing:** All PHP code enforces `declare(strict_types=1);` with explicit parameter and return types.
2. **Hexagonal & Clean Architecture:** Domain logic is decoupled from framework transport layers (controllers, commands).
3. **Action Pattern:** Business workflows are encapsulated in single-responsibility Action classes.
4. **Native Performance:** Optimized for native Windows Laragon locally, and high-throughput Linux/OPcache/Redis in production.