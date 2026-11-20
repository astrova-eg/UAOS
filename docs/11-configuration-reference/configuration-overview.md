# Configuration Architecture & Immutability

**Document ID:** CONF-DOC-001  
**Version:** 2.0.0  
**Classification:** Configuration Standard  
**Status:** [Active Reference]  

---

## 1. Invariants
- Code must NEVER access `env()` outside of `config/*.php` files.
- In production, configuration is pre-cached via `php artisan config:cache`.