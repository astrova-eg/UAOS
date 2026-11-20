# API Development Standards

**Document ID:** DEV-DOC-006  
**Version:** 2.0.0  
**Classification:** API Standard  
**Status:** [Active Reference]  

---

## 1. RESTful API Directives
- Controllers must never perform inline validation; all validation resides in authorization-aware `FormRequest` classes.
- Standard response envelopes: Successful responses wrap data in `{ "data": ... }`.
- Error responses adhere to RFC 7807 Problem Details: `{ "type": "...", "title": "...", "status": 422, "detail": "...", "errors": [...] }`.