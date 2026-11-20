# Database Development Standards

**Document ID:** DEV-DOC-005  
**Version:** 2.0.0  
**Classification:** Database Standard  
**Status:** [Active Reference]  

---

## 1. Migration Rules
- Every migration must have deterministic `up()` and `down()` methods.
- Column additions to large production tables must be non-blocking.
- Foreign key constraints must be enforced at the MySQL engine level (`InnoDB`).
- Multi-column queries must be supported by composite indexes matching query filter order.