# API Architecture Overview

**Document ID:** API-DOC-001  
**Version:** 2.0.0  
**Classification:** API Specification  
**Status:** [Active Reference]  

---

## 1. Standard Protocol
- Transport: HTTPS only.
- Format: JSON request bodies and JSON responses.
- Enveloping: Data payload wrapped under `{ "data": ... }`.
- Status Codes: Standard HTTP semantics (`200 OK`, `201 Created`, `204 No Content`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `422 Unprocessable Content`, `429 Too Many Requests`, `500 Internal Error`).