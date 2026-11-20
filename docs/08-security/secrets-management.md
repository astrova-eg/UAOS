# Secrets Management Standards

**Document ID:** SEC-DOC-003  
**Version:** 2.0.0  
**Classification:** Security Policy  
**Status:** [Active Reference]  

---

## 1. Zero Secrets in Source Control
- Passwords, API tokens, private keys, and `.env` files MUST NEVER be committed to Git.
- Pre-commit scanning hooks scan for high-entropy secrets.
- Accidental exposure triggers immediate key revocation and repository history rewriting via `git-filter-repo`.