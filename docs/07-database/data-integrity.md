# Data Integrity, Transactions & Locking

**Document ID:** DB-DOC-006  
**Version:** 2.0.0  
**Classification:** Database Standard  
**Status:** [Active Reference]  

---

## 1. Transaction Boundaries
- Multi-table mutations must execute within `DB::transaction()`.
- Use pessimistic row-level locking (`lockForUpdate()`) to prevent race conditions during financial settlements or counter increments.