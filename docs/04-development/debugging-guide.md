# Debugging & Diagnostics Guide

**Document ID:** DEV-DOC-009  
**Version:** 2.0.0  
**Classification:** Debugging Guide  
**Status:** [Active Reference]  

---

## 1. Diagnostic Tools
- **Logging:** Structured logging using `Log::channel('single')` or `Log::debug()`.
- **Query Profiling:** Inspect executed queries using `DB::listen()` or Laravel Telescope in local development.
- **Xdebug:** Supported natively via PHP 8.3 CLI with Windows Laragon.