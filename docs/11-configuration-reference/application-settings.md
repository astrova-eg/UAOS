# Application Dynamic Settings

**Document ID:** CONF-DOC-003  
**Version:** 2.0.0  
**Classification:** Configuration Standard  
**Status:** [Active Reference]  

---

## 1. Dynamic Tenant Settings
Settings that change at runtime (e.g., tenant logo, invoice footer, notification email) are stored in the `settings` database table and cached in Redis with tenant scoping.