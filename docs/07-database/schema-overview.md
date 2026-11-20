# Schema Overview & Entity Classifications

**Document ID:** DB-DOC-002  
**Version:** 2.0.0  
**Classification:** Database Schema  
**Status:** [Active Reference]  

---

## 1. Table Classifications
- **Identity & Auth:** `users`, `personal_access_tokens`, `roles`, `permissions`.
- **Multi-Tenant:** `tenants`, `tenant_users`.
- **Operations & Jobs:** `jobs`, `failed_jobs`, `job_batches`.
- **Audit:** `audit_events`.