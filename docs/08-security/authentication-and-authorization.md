# Authentication & Authorization Standards

**Document ID:** SEC-DOC-002  
**Version:** 2.0.0  
**Classification:** Security Standard  
**Status:** [Active Reference]  

---

## 1. Standards
- Passwords hashed using Argon2id or Bcrypt (cost factor >= 12).
- Session cookies configured `Secure`, `HttpOnly`, `SameSite=Lax`.
- API tokens signed and hashed in persistent storage.