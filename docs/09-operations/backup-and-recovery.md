# Backup & Disaster Recovery Standards

**Document ID:** OPS-DOC-005  
**Version:** 2.0.0  
**Classification:** Disaster Recovery Standard  
**Status:** [Active Reference]  

---

## 1. RPO & RTO Objectives
- **Recovery Point Objective (RPO):** Maximum 15 minutes of data loss (enabled via MySQL binary logs).
- **Recovery Time Objective (RTO):** Full database restore within 60 minutes.
- Automated hot backups executed daily and encrypted prior to offsite transport.