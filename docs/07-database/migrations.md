# Migration Protocols (Expand-Contract)

**Document ID:** DB-DOC-004  
**Version:** 2.0.0  
**Classification:** Database Standard  
**Status:** [Active Reference]  

---

## 1. Zero-Downtime Expand-Contract Phase
1. **Expand Phase:** Add new nullable columns or tables in migration. Deploy code that writes to both old and new columns.
2. **Backfill Phase:** Background worker backfills legacy data to new columns.
3. **Contract Phase:** Drop legacy column in a subsequent scheduled release.