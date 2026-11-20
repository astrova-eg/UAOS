# Documentation Review and Approval Policy

**Document ID:** GOV-DOC-104  
**Version:** 2.0.0  
**Classification:** Review Policy  
**Status:** [Active Governance]  

---

## 1. Review Directives
Every documentation update must be reviewed for:
1. **Empirical Accuracy:** Confirmed against real classes, migrations, and routes.
2. **Completeness:** Answers the 12 core component questions (What, Why, Where, How, Inputs, Outputs, Errors, etc.).
3. **Structure & Links:** Sequential headings, valid syntax, zero broken relative links.
4. **Security Integrity:** Zero committed secrets, private keys, or passwords.

### Review Comment Severity Tags
- `[BLOCKER]`: Technical inaccuracy, fictional endpoint, broken link, missing code fence tag, committed secret.
- `[IMPORTANT]`: Missing prerequisite, ambiguous instructions, unhandled failure mode.
- `[SUGGESTION]`: Recommendation for cleaner wording or diagram improvement.
- `[NITPICK]`: Minor spelling or formatting adjustment. Non-blocking.
- `[QUESTION]`: Inquiry regarding architectural rationale.