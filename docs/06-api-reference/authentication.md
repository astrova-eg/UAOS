# API Authentication Guide

**Document ID:** API-DOC-002  
**Version:** 2.0.0  
**Classification:** Security Specification  
**Status:** [Active Reference]  

---

## 1. Bearer Token Authentication
API requests authenticate via HTTP `Authorization` header:
`Authorization: Bearer <sanctum-token>`

Tokens are issued via `POST /api/v1/auth/tokens` and validate active tenant boundaries and expiration timestamps.