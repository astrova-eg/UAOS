# Structured Logging Standards

**Document ID:** OPS-DOC-004  
**Version:** 2.0.0  
**Classification:** Logging Standard  
**Status:** [Active Reference]  

---

## 1. Structured JSON Logging
- Production logs must be formatted as single-line JSON with timestamps, log levels, message, tenant_id, and stack traces.
- Log rotation configured daily with 14-day retention.