# Scalability & Performance Architecture

**Document ID:** ARCH-DOC-007  
**Version:** 2.0.0  
**Classification:** Performance Architecture  
**Status:** [Active Architecture]  

---

## 1. Scalability Invariants
1. **Stateless HTTP Runtime:** Sessions stored in Redis or database; web servers can scale horizontally behind load balancers.
2. **Redis Workload Segregation:** Multi-instance Redis architecture separating cache (`allkeys-lru`) from queue (`noeviction`).
3. **Database Read/Write Segregation:** Support for primary read/write MySQL cluster with read replicas.
4. **Asynchronous Deferral:** Heavy operations (emails, webhooks, PDF generation) dispatched to Redis queues.