# Project Structure Overview

**Document ID:** GS-DOC-005  
**Version:** 2.0.0  
**Classification:** Architecture Guide  
**Status:** [Active Reference]  

---

## 1. High-Level Directory Layout
```text
C:\laragon\www\core\
├── .agents/             # Enterprise AI engineering governance, skills, and agents
│   ├── governance/      # Supreme constitutional policies
│   ├── skills/          # 23 reusable shared engineering skills
│   └── workflows/       # 14-stage lifecycle workflows
├── app/                 # Application source code
│   ├── Core/            # Universal Core domain aggregates and services
│   ├── Http/            # Controllers, FormRequests, Middleware
│   ├── Models/          # Eloquent persistence entities
│   └── Providers/       # Service providers and container bindings
├── config/              # Application configuration files
├── database/            # Migrations, seeders, and factories
├── docs/                # Canonical 18-domain enterprise documentation
├── routes/              # API and web routing definitions
└── tests/               # Pest and PHPUnit automated test suites
```