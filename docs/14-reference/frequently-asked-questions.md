# Frequently Asked Questions (FAQ)

**Document ID:** REF-DOC-006  
**Version:** 2.0.0  
**Classification:** FAQ  
**Status:** [Active Reference]  

---

## FAQs
- **Q: Can I use Docker locally?**  
  A: Docker installed on your workstation does not violate policy, but making local development or testing dependent on Docker or Sail is strictly prohibited. Use native Windows Laragon and PowerShell.
- **Q: Why are Action classes preferred over controllers?**  
  A: Action classes enforce single responsibility, are directly testable, and can be reused by CLI commands and queue jobs without HTTP transport coupling.