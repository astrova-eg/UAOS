# Cryptography & Envelope Encryption Standards

**Document ID:** SEC-DOC-004  
**Version:** 2.0.0  
**Classification:** Cryptography Standard  
**Status:** [Active Reference]  

---

## 1. Envelope Encryption Specification
- Cipher: AES-256-GCM.
- Sensitive columns encrypted with a unique Data Encryption Key (DEK).
- DEK is encrypted using a Master Key (Key Encryption Key) managed outside the database.