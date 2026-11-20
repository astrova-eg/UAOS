# Indexing & Query Performance Standards

**Document ID:** DB-DOC-005  
**Version:** 2.0.0  
**Classification:** Database Performance  
**Status:** [Active Reference]  

---

## 1. Indexing Rules
- Every foreign key column must have an explicit B-tree index.
- Composite indexes must order columns by cardinality and query equality filters first, range filters second.
- Regularly analyze query execution plans using `EXPLAIN ANALYZE`.