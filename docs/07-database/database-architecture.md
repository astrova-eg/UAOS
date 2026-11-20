# Database Architecture Specification

**Document ID:** DB-DOC-001  
**Version:** 2.0.0  
**Classification:** Database Architecture  
**Status:** [Active Architecture]  

---

## 1. Engine & Configuration Standards
- **Relational Engine:** MySQL 8.4 LTS (or MySQL 8.0+).
- **Storage Engine:** InnoDB exclusively.
- **Character Set & Collation:** `utf8mb4` with `utf8mb4_unicode_ci`.
- **SQL Mode:** Strict SQL mode enabled (`STRICT_TRANS_TABLES`, `NO_ZERO_IN_DATE`).