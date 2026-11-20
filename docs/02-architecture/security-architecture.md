# Security Architecture

**Document ID:** ARCH-DOC-006  
**Version:** 2.0.0  
**Classification:** Security Architecture  
**Status:** [Active Architecture]  

---

## 1. Defense-in-Depth Model
- **Boundary Defense:** Rate throttling, CORS policy, CSRF protection, and TLS termination.
- **Identity Defense:** Sanctum API tokens with scoped abilities and expiration.
- **Data Defense:** Envelope encryption (AES-256-GCM) for sensitive fields in database columns.
- **Tenant Defense:** Global tenant scope enforcing `where('tenant_id', $tenantId)` on all persistence models.