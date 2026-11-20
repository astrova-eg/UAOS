# Agent Registry Specification
**Document ID:** GOV-DOC-013  
**Version:** 1.0.0  
**Classification:** Operational Directory Standard  

## 1. Registry Architecture
The `agent-registry.json` serves as the centralized machine-readable index for all active engineering agents. Every agent must be registered with its unique ID, formal name, department, role, access tier, and relative specification path.

## 2. Integrity Constraints
- Every registered agent must possess a physical specification markdown file.
- No two agents may share the same ID or name.
- Every registered agent must map to exactly one primary department.