# Release Process & Checklist

**Document ID:** REL-DOC-001  
**Version:** 2.0.0  
**Classification:** Release Runbook  
**Status:** [Active Reference]  

---

## 1. Release Lifecycle Checklist
1. **Pre-flight CI Pass:** All automated tests, static analysis, and security audits pass on `main`.
2. **Changelog Updated:** `docs/12-release-management/changelog.md` updated with new release notes.
3. **Annotated Tag Authored:** `git tag -a vX.Y.Z -m "Release vX.Y.Z"` authored and pushed.
4. **Deploy & Verify:** Symlink deployment pipeline executed, health probes verified (`GET /up`).