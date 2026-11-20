# API Error Handling (RFC 7807)

**Document ID:** API-DOC-004  
**Version:** 2.0.0  
**Classification:** API Specification  
**Status:** [Active Reference]  

---

## 1. RFC 7807 Problem Details Schema
```json
{
  "type": "https://api.core.test/errors/validation-failed",
  "title": "Validation Failed",
  "status": 422,
  "detail": "The provided payload failed schema validation.",
  "errors": {
    "email": [
      "The email field must be a valid email address."
    ]
  }
}
```