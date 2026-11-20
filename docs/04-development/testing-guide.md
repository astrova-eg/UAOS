# Testing Guide & Execution

**Document ID:** DEV-DOC-008  
**Version:** 2.0.0  
**Classification:** Testing Guide  
**Status:** [Active Reference]  

---

## 1. Testing Framework
- **Test Runner:** Pest v2/v3 or PHPUnit 11.
- **Database Strategy:** Use `LazilyRefreshDatabase` on feature tests to optimize performance.
- **Pure Unit Tests:** Domain Value Objects and algorithmic calculations must execute purely in memory without database or network I/O.