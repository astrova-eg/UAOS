# API Authorization & Scopes

**Document ID:** API-DOC-003  
**Version:** 2.0.0  
**Classification:** Security Specification  
**Status:** [Active Reference]  

---

## 1. Scopes & Abilities
Sanctum tokens declare granular abilities (e.g., `["invoices:read", "invoices:write"]`). FormRequests verify that the token possesses the required ability prior to processing request payloads.