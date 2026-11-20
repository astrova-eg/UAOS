# Relational Constraints & Cardinality

**Document ID:** DB-DOC-003  
**Version:** 2.0.0  
**Classification:** Database Specification  
**Status:** [Active Reference]  

---

## 1. Foreign Key Invariants
- Foreign keys must be declared at the relational engine level.
- Multi-tenant persistence models must enforce `tenant_id` foreign keys referencing `tenants.id` with `ON DELETE RESTRICT` or `ON DELETE CASCADE` based on domain invariants.