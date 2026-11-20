# Local Development Environment Standards

**Document ID:** DEV-DOC-001  
**Version:** 2.0.0  
**Classification:** Development Standard  
**Status:** [Active Reference]  

---

## 1. Native Environment Specification
Universal Core is engineered to run directly on native Windows Laragon using PowerShell:
- **No Docker Mandate:** Running Docker or Laravel Sail is strictly prohibited as a required local workflow.
- **CLI Invocations:** Execute PHP CLI, Composer, and Artisan commands directly in PowerShell.
- **Local Services:** MySQL (port 3306), Redis (port 6379), and Apache/Nginx managed directly via Laragon.