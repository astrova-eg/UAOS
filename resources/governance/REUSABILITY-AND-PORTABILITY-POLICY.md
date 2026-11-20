# Reusability and Portability Policy
**Document ID:** GOV-DOC-021  
**Version:** 1.0.0  
**Classification:** Universal Organization Standard  

## 1. Zero Project Coupling
The entire `.agents/` directory is architected as an autonomous engineering asset. It can be copied into any Laravel project root and instantiated immediately by populating `profiles/active-profile.json`.

## 2. Portability Constraints
- No absolute host file paths inside agent specifications.
- No project-specific brand names or proprietary domain entities inside shared skills or governance documents.
- All technology decisions defer to the active Project Technology Profile.