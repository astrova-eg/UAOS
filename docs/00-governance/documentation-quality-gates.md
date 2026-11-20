# Documentation Quality Gates

**Document ID:** GOV-DOC-106  
**Version:** 2.0.0  
**Classification:** CI/CD Quality Gating Standard  
**Status:** [Active Governance]  

---

## 1. Automated CI Quality Gates
1. **Markdown Syntax Linting:** Checks for GFM compliance, heading hierarchies, table formatting.
2. **Relative Link Integrity:** Verifies that all relative markdown links (`target-file.md`) resolve to physical files.
3. **Code Block Validation:** Ensures all code blocks define valid language specifiers (`php`, `json`, `yaml`, `bash`, `powershell`, `sql`, `mermaid`).
4. **Secret Scanning:** Scans for high-entropy tokens, passwords, private keys, or unhashed credentials.
5. **Peer Review & Audit Sign-Off:** Independent verification by Department N or Department M.