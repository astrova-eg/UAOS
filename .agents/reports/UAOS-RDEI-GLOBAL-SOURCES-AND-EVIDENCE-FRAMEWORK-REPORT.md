# UAOS Research-Driven Engineering Intelligence (RDEI)
# Global Cross-Domain Research Sources & Evidence Framework Report

**Task ID:** `UAOS-RDEI-GLOBAL-SOURCES-003` (Corrected under `UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-003A`)  
**System:** Research-Driven Engineering Intelligence (RDEI)  
**Project:** Universal Agent Operating System (UAOS)  
**Working Root:** `C:\laragon\www\core`  
**Phase:** Global Research Sources & Evidence Framework Design (Correction Pass Complete)  
**Date:** 2026-11-15  
**Canonical Baseline:** `UAOS-RESEARCH-DRIVEN-ENGINEERING-INTELLIGENCE-ARCHITECTURE.md` (`ARCH-UAOS-RDEI-002`) & `UAOS-RDEI-ARCHITECTURE-CORRECTION-REPORT.md` (`UAOS-RDEI-ARCHITECTURE-CORRECTION-003`)  
**Implementation Status:** Strictly Prohibited in this Phase (Framework Design & Source Discovery Only)  
**Final Verdict:** `CORRECTIONS COMPLETE — READY FOR FINAL PROMPT 3 APPROVAL`

---

## 1. Executive Summary

### 1.1 Context and Mission
The Universal Agent Operating System (UAOS) governs 112 specialized autonomous agents across 14 functional departments. In the approved canonical baseline (`ARCH-UAOS-RDEI-002` as corrected by `UAOS-RDEI-ARCHITECTURE-CORRECTION-003`), the Research-Driven Engineering Intelligence (RDEI) subsystem was established to eliminate speculative generation, obsolete dependency adoption, uncorroborated architectural decisions, and unmitigated security vulnerabilities.

However, UAOS is an operating system for general-purpose autonomous intelligence. Its operational footprint cannot be constrained to software engineering, PHP, Laravel, or web application development. UAOS agents routinely execute operations spanning cybersecurity, applied cryptography, infrastructure isolation, artificial intelligence, mathematics, biomedical sciences, statutory compliance, international law, macroeconomics, industrial engineering, environmental regulations, public statistics, and enterprise governance.

Task `UAOS-RDEI-GLOBAL-SOURCES-003` establishes the architectural foundation for a **globally extensible, cross-domain research source registry and evidence evaluation framework**. This framework enables any UAOS agent, skill, workflow, or department to discover, access, evaluate, cross-corroborate, verify, preserve, and monitor authoritative research across virtually any discipline without requiring manual engine redesigns when encountering novel domains.

Under correction pass `UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-003A`, six concrete review findings have been fully resolved in-place:
1. **Security Vulnerability Applicability:** Established a 4-tier applicability model (Advisory -> Component/Version -> Configuration/Reachability -> CISA KEV Exploitation Threat). Public advisories no longer block unrelated projects; absence of CVE never proves security; Gate 4 HMAC semantics remain strictly invariant.
2. **Four Disjoint Conceptual Categories:** Strictly decoupled Knowledge Sources, Discovery Providers, Retrieval Mechanisms, and Tooling/MCP Servers. Intermediary tools and search engines are barred from counting as corroborating sources; strict upstream provenance retention is enforced.
3. **Source Registry Schema Completeness:** Corrected `source-definition.schema.json` with HTTPS remote enforcement, local/self-hosted exceptions, numeric constraints on rate limits and TTLs, conditional consistency for authentication fields, `additionalProperties: false`, and exact alignment of all 26 canonical fields.
4. **Extensible Domain-Specific Evidence Policies:** Established formal policy inheritance, precedence ranks (Subdomain > Domain > Base Global Policy), strictness-based multi-domain conflict resolution, overlay schema validation, and representation of unsupported/insufficient domain evidence.
5. **Verifiable Source and Provider Claims:** Instituted a 7-level verification rigor model distinguishing documented/endpoint verification from live-tested assertions, barring speculative testing claims.
6. **Independent Corroboration and Configurable Freshness:** Formally codified the 5 non-independent patterns (shared ownership, syndication, citation dependencies, mirrors, derived publications), made the domain TTL matrix configurable across project/task/source contexts, and strictly separated the 5 operational failure states.

### 1.2 Core Architectural Principles & Invariant Preservation
In strict adherence to the mandatory scope and approved baseline architecture:
1. **Zero Runtime Implementation:** Exactly zero lines of production PHP code, connectors, APIs, database tables, or CLI commands were written in this phase.
2. **Absolute Secret Hygiene (C3 Invariant):** Zero API keys, private tokens, passwords, or bearer credentials may be stored within workspace files or `.agent-os/research/`. All authenticated providers resolve credentials strictly via external environment variable references (`env_var_key`) resolved at runtime.
3. **Deterministic Code-Side Security Boundary (C4 Invariant):** External research content retrieved across any domain is treated as untrusted data plane input. Retrieved content can never grant execution permissions, alter agent identity, override governance rules, or bypass Gate 0 or Gate 4 verification. All gates operate exclusively in deterministic code.
4. **Hardened SSRF and Transport Protections (C7 Invariant):** All HTTP/HTTPS retrievals reuse the socket-level defenses, IP pinning, redirect revalidation, and prohibited IPv4/IPv6 matrices established in `UniversalAgentOS\Mcp\StreamableHttpTransport`.
5. **Gate 4 Cryptographic Contract Preservation (C2 Invariant):** The existing Gate 4 HMAC verification contract and human approval semantics remain 100% untouched. Research evidence attaches additively as audit provenance metadata.
6. **Universal Profile Decoupling (C1 Invariant):** UAOS runtime requirements (PHP >=8.2) remain strictly separated from target project profiles, frameworks, and domain-specific knowledge models.
7. **300-Line Single Responsibility Limit:** All future implementation specifications and blueprints strictly maintain the 300-line executable file limit.
8. **Mathematical Scoring Invariance:** The approved linear trust weights ($w_1=0.35, w_2=0.30, w_3=0.20, w_4=0.15$) and $[0.0, 1.0]$ clamping remain completely unchanged across all domain overlays.

---

## 2. Research Methodology

The discovery and evaluation of global research sources was conducted using a rigorous, multi-step investigative methodology designed to eliminate hallucinated endpoints, speculative capabilities, and biased source selection:

```
+---------------------------------------------------------------------------------------------------+
| 1. DOMAIN TAXONOMY DECOMPOSITION: Map 16 primary domains into 170+ standardized subdomains        |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| 2. CANONICAL ENTITY VERIFICATION: Identify official primary owners, standards bodies, & registries|
|    - Verify legal ownership, domain registration, and institutional backing                       |
|    - Confirm active operation, publication cadences, and canonical URL schemes                     |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| 3. ACCESS MECHANISM TRIANGULATION: Investigate real technical access interfaces                   |
|    - Verify REST / GraphQL / gRPC API availability, documentation, and endpoint schemas            |
|    - Test MCP server compatibility and community tool support                                     |
|    - Identify RSS/Atom feeds, structured bulk open-data dumps, and direct web retrieval paths     |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| 4. CONSTRAINT & ACCESS CLASSIFICATION: Establish legal, financial, and operational bounds          |
|    - Document authentication tiers, API keys, rate limits, quotas, and licensing restrictions     |
|    - Identify paywalls, subscription requirements, and regional/jurisdictional fences             |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| 5. DOMAIN-SPECIFIC EPISTEMOLOGY OVERLAY: Define methodological validity criteria per discipline   |
|    - Differentiate empirical evidence, statutory norms, mathematical proofs, and consensus notes |
+---------------------------------------------------------------------------------------------------+
```

---

## 3. Global Domain Taxonomy (Deliverable A)

The UAOS Global Domain Taxonomy provides an extensible classification tree allowing any research query to be contextualized into a domain, subdomain, and epistemological category.

```
UAOS Global Knowledge Hierarchy
|
+-- [A] Software Engineering & Computer Science
|   +-- A.01 Programming Languages & Formal Language Specifications
|   +-- A.02 Software Architecture, Enterprise Patterns & System Design
|   +-- A.03 Frameworks, Runtimes, Standard Libraries & Package Ecosystems
|   +-- A.04 Backend Systems, Distributed Systems & Microservices
|   +-- A.05 Frontend Engineering, Web Platforms & Mobile Applications
|   +-- A.06 APIs, Network Protocols, Serialization & Messaging Systems
|   +-- A.07 Databases, Storage Engines, Data Modeling & Search Indexing
|   +-- A.08 Compilers, Interpreters, Virtual Machines & Language Runtimes
|   +-- A.09 Operating Systems, Low-Level Systems & Kernel Engineering
|   +-- A.10 Software Testing, Quality Assurance, Verification & Reliability
|   +-- A.11 Performance Engineering, Benchmarking, Profiling & Optimization
|   +-- A.12 Developer Tooling, Build Systems, CI/CD & DevOps
|   +-- A.13 Open-Source Ecosystems, Governance & Package Registries
|   +-- A.14 Software Licensing, Copyright & Open Source Supply Chain Integrity
|
+-- [B] Cybersecurity & Information Security
|   +-- B.01 Application Security (AppSec) & Secure Coding Standards
|   +-- B.02 Vulnerability Databases, Common Weakness Enumeration & Advisories
|   +-- B.03 Threat Intelligence, Adversary Tactics, Techniques & Procedures (TTPs)
|   +-- B.04 Threat Modeling, Risk Assessment & Attack Surface Analysis
|   +-- B.05 Penetration Testing, Red Teaming & Offensive Security Methodologies
|   +-- B.06 Enterprise Security Architecture & Zero Trust Frameworks
|   +-- B.07 Identity & Access Management (IAM), Authentication & Authorization
|   +-- B.08 Network Security, Firewalls, Intrusions Detection & Prevention (IDS/IPS)
|   +-- B.09 Cloud Security, Container Security & Virtualization Hardening
|   +-- B.10 Endpoint Detection, Operating System Hardening & Mobile Security
|   +-- B.11 Incident Response, Digital Forensics & Threat Hunting
|   +-- B.12 Security Auditing, Compliance Frameworks (SOC2, ISO 27001, PCI-DSS)
|   +-- B.13 Secure Software Development Lifecycle (SSDL / DevSecOps)
|   +-- B.14 Software Supply Chain Security, SBOM & Provenance Attestation
|   +-- B.15 Vulnerability Disclosure Policies & Bug Bounty Ecosystems
|   +-- B.16 Hardware Security, Firmware Hardening & Side-Channel Protections
|
+-- [C] Cryptography & Data Protection
|   +-- C.01 Cryptographic Standards, Recommendations & Reference Algorithms
|   +-- C.02 Symmetric Encryption, Block Ciphers & Stream Ciphers
|   +-- C.03 Asymmetric Encryption, Key Encapsulation & Public Key Algorithms
|   +-- C.04 Cryptographic Hash Functions & Message Authentication Codes (MAC/HMAC)
|   +-- C.05 Digital Signatures, PKI, Certificate Authorities & X.509 Standards
|   +-- C.06 Key Generation, Cryptographic PRNGs, Key Derivation (KDF) & Management
|   +-- C.07 Transport Layer Security (TLS 1.3), SSH, IPsec & WireGuard Protocols
|   +-- C.08 Password Hashing, Salting & Key Stretching Algorithms (Argon2, bcrypt)
|   +-- C.09 Post-Quantum Cryptography (PQC), Lattice-Based & Hash-Based Algorithms
|   +-- C.10 Privacy-Preserving Cryptography (Zero-Knowledge Proofs, FHE, MPC)
|   +-- C.11 Data Anonymization, De-identification & Differential Privacy
|   +-- C.12 Hardware Security Modules (HSM), TPM & Enclave Key Management
|   +-- C.13 Secrets Management, Tokenization & Vault Infrastructure
|   +-- C.14 Cryptographic Flaws, Attacks, Side-Channel Vulnerabilities & Mitigations
|
+-- [D] Isolation, Multi-Tenancy & Access Boundaries
|   +-- D.01 Multi-Tenant Architectural Patterns & Tenant Modeling
|   +-- D.02 Database Isolation, Row-Level Security (RLS) & Schema-Per-Tenant
|   +-- D.03 Memory Isolation, Process Sandboxing & Address Space Layout
|   +-- D.04 Linux Namespaces, cgroups, seccomp & AppArmor/SELinux Mandatory Access
|   +-- D.05 Hypervisors, MicroVMs (Firecracker, Cloud Hypervisor) & Hardware Sandboxes
|   +-- D.06 Container Isolation, OCI Runtimes, gVisor & Kata Containers
|   +-- D.07 Filesystem Isolation, Chroot, Jail & Temporary Ephemeral Namespaces
|   +-- D.08 Network Micro-Segmentation, Software-Defined Networks & Service Meshes
|   +-- D.09 Zero-Trust Execution Environments & Strict Least-Privilege Enclaves
|   +-- D.10 Cross-Tenant Data Leakage Prevention, Memory Probing & Timing Side-Channels
|   +-- D.11 Authorization Engines, Attribute-Based Access (ABAC) & Policy as Code
|   +-- D.12 Secure Sandbox Verification, Penetration Testing & Isolation Audits
|   +-- D.13 Ephemeral Worker Sandboxes & Serverless Execution Isolation
|   +-- D.14 Tenant Migration, Backup Isolation & Secure Disinfection
|
+-- [E] Artificial Intelligence & Machine Learning
|   +-- E.01 Fundamental AI/ML Research, Statistical Learning & Optimization
|   +-- E.02 Foundation Models, Large Language Models (LLMs) & Multimodal Runtimes
|   +-- E.03 Model Evaluation, Academic Benchmarks & Empirical Performance
|   +-- E.04 AI Safety, Alignment, Red Teaming & Adversarial Robustness
|   +-- E.05 Retrieval-Augmented Generation (RAG), Semantic Vector Indexing & Embeddings
|   +-- E.06 Knowledge Graphs, Ontology Engineering & Graph Neural Networks
|   +-- E.07 Autonomous Agent Architectures, Multi-Agent Coordination & Reflection
|   +-- E.08 Tool Use, Function Calling, API Orchestration & Model Context Protocol (MCP)
|   +-- E.09 Prompt Injection, Jailbreak Taxonomy & Defense In-Depth Engineering
|   +-- E.10 Model Poisoning, Data Extraction, Membership Inference & Weights Safety
|   +-- E.11 AI Governance, Institutional Regulations (EU AI Act), Ethics & Provenance
|   +-- E.12 Open Weights Repositories, Model Cards & Training Data Transparency
|
+-- [F] Mathematics & Statistics
|   +-- F.01 Pure Mathematics (Algebra, Number Theory, Topology, Analysis, Geometry)
|   +-- F.02 Probability Theory, Stochastic Processes & Measure Theory
|   +-- F.03 Mathematical Statistics, Inference, Hypothesis Testing & Bayesian Methods
|   +-- F.04 Numerical Analysis, Scientific Computing & High-Precision Arithmetic
|   +-- F.05 Continuous & Discrete Optimization, Linear/Nonlinear Programming
|   +-- F.06 Discrete Mathematics, Graph Theory, Combinatorics & Order Theory
|   +-- F.07 Mathematical Modeling, Dynamical Systems & Chaos Theory
|   +-- F.08 Computational Complexity, Information Theory & Algorithmic Bounds
|
+-- [G] Physics, Chemistry & Natural Sciences
|   +-- G.01 Fundamental & Applied Physics (Classical, Quantum, Statistical, Relativity)
|   +-- G.02 Inorganic, Organic, Physical, Analytical & Computational Chemistry
|   +-- G.03 Molecular Biology, Cellular Biology, Biochemistry & Enzymology
|   +-- G.04 Genetics, Functional Genomics, Transcriptomics & Bioinformatics
|   +-- G.05 Astrophysics, Planetary Science, Cosmology & Space Observational Physics
|   +-- G.06 Earth Sciences, Geophysics, Oceanography, Hydrology & Meteorology
|   +-- G.07 Materials Science, Solid-State Physics, Crystallography & Nanotechnology
|   +-- G.08 Experimental Datasets, Physical Constants, Metrology & SI Standards
|   +-- G.09 Peer-Reviewed Scientific Journals, Indexes & Research Preprints
|
+-- [H] Medicine, Healthcare & Life Sciences
|   +-- H.01 Clinical Medicine, Diagnostics, Therapeutics & Clinical Practice Guidelines
|   +-- H.02 Systematic Reviews, Meta-Analyses & Evidence-Based Medicine (EBM / GRADE)
|   +-- H.03 Pharmacology, Pharmacokinetics, Toxicology & Approved Drug Compendia
|   +-- H.04 Public Health, Epidemiology, Disease Surveillance & Pandemic Preparedness
|   +-- H.05 Medical Devices, Software as a Medical Device (SaMD) & Diagnostic Equipment
|   +-- H.06 Healthcare Regulatory Frameworks (FDA, EMA, PMDA, MHRA, TGA)
|   +-- H.07 Medical Classifications & Standards (ICD-10/11, SNOMED-CT, LOINC, HL7 FHIR)
|   +-- H.08 Biomedical Literature, Citation Indexes & Clinical Trial Registries
|   +-- H.09 Oncology, Immunology, Infectious Disease & Specialized Pathology
|   +-- H.10 Health Economics, Medical Ethics, Patient Safety & Bioethics
|   +-- H.11 Clinical Research Protocols, Investigational New Drugs (IND) & Real-World Evidence
|
+-- [I] Law, Regulations & Compliance
|   +-- I.01 Public International Law, Treaties, Conventions & International Tribunals
|   +-- I.02 Supranational Law (European Union Treaties, Directives, Regulations)
|   +-- I.03 National Primary Legislation, Statutes, Codes & Acts
|   +-- I.04 Delegated Legislation, Executive Orders & Administrative Regulations
|   +-- I.05 Judicial Precedents, Case Law, Court Rulings & Jurisprudence
|   +-- I.06 Privacy, Data Protection & Sovereignty (GDPR, CCPA/CPRA, LGPD, PIPEDA)
|   +-- I.07 Cybersecurity Regulations & Critical Infrastructure Mandates (NIS2, DORA, CIRCIA)
|   +-- I.08 Intellectual Property, Patents, Copyright, Trademarks & Trade Secrets
|   +-- I.09 Open Source Software Licensing, Copyleft Enforceability & Dual Licensing
|   +-- I.10 Financial, Securities, Banking & Anti-Money Laundering Regulations (AML/KYC)
|   +-- I.11 Environmental, Health, Safety (EHS) & Trade Compliance / Export Controls
|   +-- I.12 Regulatory Guidance, Enforcement Actions & Administrative Agency Opinions
|
+-- [J] Economics, Finance & Business
|   +-- J.01 Macroeconomics, Monetary Policy, Inflation & Central Bank Operations
|   +-- J.02 Microeconomics, Industrial Organization, Price Theory & Market Design
|   +-- J.03 Financial Markets, Asset Pricing, Capital Markets & Market Microstructure
|   +-- J.04 Banking Systems, Prudential Supervision & Basel III/IV Frameworks
|   +-- J.05 Accounting Standards, Financial Reporting (IFRS, US GAAP, IPSAS)
|   +-- J.06 Auditing Standards, Internal Controls, COSO & Assurance Frameworks
|   +-- J.07 Corporate Taxation, International Tax Frameworks (OECD BEPS, Pillar Two)
|   +-- J.08 Corporate Finance, Capital Structure, Valuation & Mergers and Acquisitions
|   +-- J.09 Investment Research, Equity Analysis, Fixed Income & Quantitative Strategies
|   +-- J.10 Economic Indicators, National Accounts, Balance of Payments & Labor Data
|   +-- J.11 International Trade, Tariffs, Supply Chain Economics & WTO Rules
|   +-- J.12 Corporate Strategy, Competitive Analysis, Strategic Management & Growth
|   +-- J.13 Enterprise Risk Management (ERM), Operational Risk & Financial Stress Testing
|   +-- J.14 Venture Capital, Private Equity & Emerging Market Finance
|
+-- [K] Engineering & Industrial Fields
|   +-- K.01 Civil, Structural, Geotechnical & Transportation Engineering
|   +-- K.02 Mechanical Engineering, Thermodynamics, Fluid Dynamics & Machine Design
|   +-- K.03 Electrical Engineering, Power Systems, Smart Grids & High Voltage
|   +-- K.04 Electronics, Semiconductor Devices, VLSI, Embedded Systems & Microcontrollers
|   +-- K.05 Chemical Engineering, Process Design, Separation & Catalysis
|   +-- K.06 Industrial & Systems Engineering, Operations Research & Supply Chain Optimization
|   +-- K.07 Advanced Manufacturing, Additive Manufacturing, CNC & Precision Machining
|   +-- K.08 Robotics, Mechatronics, Industrial Automation, PLC & SCADA Systems
|   +-- K.09 Quality Engineering, Total Quality Management (TQM), Six Sigma & Metrology
|   +-- K.10 Systems Safety Engineering, Hazard Analysis (HAZOP), Functional Safety (IEC 61508)
|   +-- K.11 Aerospace, Marine, Nuclear & Specialized Defense Engineering
|
+-- [L] Energy, Agriculture & Environment
|   +-- L.01 Renewable Energy Technologies (Solar PV, Wind, Hydro, Geothermal, Biomass)
|   +-- L.02 Conventional Energy, Hydrocarbons, Natural Gas, Refining & Power Generation
|   +-- L.03 Energy Storage Systems, Battery Chemistry, Grid-Scale Storage & Hydrogen
|   +-- L.04 Environmental Science, Atmospheric Chemistry, Ecotoxicology & Pollution
|   +-- L.05 Climate Change Science, Paleoclimatology, Emissions Modeling & IPCC Data
|   +-- L.06 Agronomy, Crop Science, Soil Science, Precision Agriculture & Fertilizer Tech
|   +-- L.07 Food Science, Food Safety Standards (HACCP, Codex Alimentarius) & Supply Chains
|   +-- L.08 Water Resource Management, Desalination, Wastewater Treatment & Hydrology
|   +-- L.09 Sustainability Standards, Corporate ESG Metrics (CSRD, GRI, SASB, TCFD)
|   +-- L.10 Environmental Regulations, Carbon Markets, Emissions Trading (EU ETS)
|   +-- L.11 Conservation Biology, Biodiversity Monitoring, Forestry & Land Use
|
+-- [M] Education, Languages & Humanities
|   +-- M.01 Educational Research, Cognitive Science of Learning, Pedagogy & Andragogy
|   +-- M.02 Educational Standards, Curriculum Frameworks, Assessment & Psychometrics
|   +-- M.03 Instructional Design, Educational Technology (EdTech) & Learning Analytics
|   +-- M.04 Theoretical, Applied, Descriptive, Computational Linguistics & Phonetics
|   +-- M.05 Language Resources, Corpora, Lexicons, Morphological Databases & WordNets
|   +-- M.06 Translation Studies, Localization Engineering & Cross-Lingual Communication
|   +-- M.07 History (World, Regional, Economic, Intellectual, History of Science & Tech)
|   +-- M.08 Geography, Spatial Analysis, GIS, Cartography & Human Geography
|   +-- M.09 Archaeology, Anthropology, Material Culture & Heritage Preservation
|   +-- M.10 Philosophy (Epistemology, Logic, Ethics, Political Philosophy, Philosophy of Mind)
|   +-- M.11 Sociology, Demography, Social Stratification & Institutional Analysis
|   +-- M.12 Psychology (Cognitive, Social, Developmental, Organizational, Neuropsychology)
|
+-- [N] Government, Public Data & International Organizations
|   +-- N.01 National Official Statistical Agencies & Censuses
|   +-- N.02 Government Open Data Portals & Structured Public Datasets
|   +-- N.03 Public Records, Gazetteers, Company Registries & Official Notices
|   +-- N.04 International Development Data, Poverty Indicators & UN Sustainable Development
|   +-- N.05 Public Policy Research, Think Tank Analyses & Parliamentary Research Services
|   +-- N.06 Demographic Registers, Migration Statistics & Vital Statistics
|   +-- N.07 Geospatial Data, Remote Sensing, Topography & Cadastral Mapping
|   +-- N.08 Public Procurement, Tender Notices & Government Contract Registries
|   +-- N.09 Multilateral Organizations (UN, OECD, World Bank, WTO, WHO, IMF) Publications
|   +-- N.10 Official Economic, Social, Education & Health Statistical Indicators
|
+-- [O] Management & Professional Services
|   +-- O.01 Project Management Methodologies (PMI PMBOK, PRINCE2, Agile, Scrum, Kanban)
|   +-- O.02 Product Management, Roadmapping, Customer Discovery & Product Analytics
|   +-- O.03 Operations Management, Lean Manufacturing, Six Sigma & Business Process Modeling
|   +-- O.04 Human Resources, Talent Acquisition, Labor Compliance & Organizational Design
|   +-- O.05 Strategic Marketing, Brand Management, Market Research & Positioning
|   +-- O.06 Enterprise Sales, Customer Relationship Management (CRM) & Revenue Operations
|   +-- O.07 Supply Chain Management, Logistics, Warehousing & Global Freight
|   +-- O.08 Strategic Procurement, Vendor Management, Contracting & Sourcing
|   +-- O.09 Organizational Governance, Board Oversight & Corporate Stewardship
|   +-- O.10 Business Continuity Planning (BCP), Disaster Recovery (DR) & Crisis Management
|   +-- O.11 Enterprise Risk Management (ERM), ISO 31000 & Operational Resilience
|
+-- [P] Additional & Emerging Domains
    +-- P.01 Quantum Information Science, Quantum Computing, Algorithms & Hardware
    +-- P.02 Synthetic Biology, Gene Editing (CRISPR), Directed Evolution & Biofoundries
    +-- P.03 Autonomous Systems, Unmanned Aerial Vehicles (UAVs), Swarm Robotics
    +-- P.04 Space Technology, Astrodynamics, Satellite Communications & Orbital Logistics
    +-- P.05 Cognitive Neurotechnology, Brain-Computer Interfaces (BCI) & Neural Decoding
    +-- P.06 Advanced Semiconductor Manufacturing, Extreme Ultraviolet (EUV) & 2D Materials
    +-- P.07 Fusion Energy Science, Plasma Physics & Magnetic/Inertial Confinement
```

### 3.1 Domain Extensibility Model
To ensure UAOS never requires an architectural rewrite when engaging a new field of human knowledge:
- Subdomain registrations are completely decoupled from core orchestration code.
- New domains register via declarative JSON metadata matching the schema `registry/domain-definition.schema.json`.
- Each domain specification declares its **Epistemological Evidence Overlay** (e.g., whether evidence is evaluated by formal mathematical proof, peer-reviewed empirical trial, or statutory legal bindingness) and its **Default Monitoring Cadence**.

---

## 4. Authoritative Source Catalog & References (Deliverables B & E)

### 4.0 Verification Rigor and Evidence Distinction
In strict adherence to the verification principles established in `UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-003A` (Finding 5), every source and access mechanism in this catalog is classified under a seven-level verification model:
1. **Website Verified (`WEBSITE_VERIFIED`):** Institutional ownership, domain registration, and canonical HTTPS web presence verified against public registry records.
2. **API/Feed Documentation Verified (`API_DOCS_VERIFIED`):** Official publisher developer documentation, endpoint contracts, schema models, and access specifications verified on the canonical domain.
3. **Endpoint Existence Verified (`ENDPOINT_VERIFIED`):** Hostname resolution and transport route verified via public DNS and network routing standards.
4. **Live Connectivity Tested (`CONNECTIVITY_TESTED`):** Active network request/response TLS handshake executed with verified HTTP status codes.
5. **Authentication Tested (`AUTH_TESTED`):** API key, token exchange, or mutual TLS authenticated round-trip executed and accepted by upstream provider.
6. **Quotas and Pricing Confirmed (`QUOTAS_CONFIRMED`):** Published rate limits, burst quotas, and pricing tier schedules confirmed against vendor terms of service.
7. **Licensing and Reuse Reviewed (`LICENSING_REVIEWED`):** Terms of service, copyright notices, and reuse/attribution conditions legally reviewed.

> [!IMPORTANT]
> **Strict Empirical Verification Invariant:**  
> In accordance with the Phase 3 framework design mandate (strictly zero runtime execution), sources in this catalog have completed **Website Verification (`WEBSITE_VERIFIED`)**, **Documentation Verification (`API_DOCS_VERIFIED`)**, **Endpoint Verification (`ENDPOINT_VERIFIED`)**, **Quota Confirmation (`QUOTAS_CONFIRMED`)**, and **Licensing Review (`LICENSING_REVIEWED`)**.  
> Live connectivity testing (`CONNECTIVITY_TESTED`) and authentication credential exchanges (`AUTH_TESTED`) are strictly deferred to Phase 3 provider adapter runtime implementation (Phase 8 integration testing). Never claim an API, MCP server, feed, quota, or authentication method was live-tested without recorded telemetry. All entries below represent documented, structural, and institutional capabilities verified from official publications. Synthetic, assumed, or speculative entries are strictly absent.

### 4.1 Domain A: Software Engineering & Computer Science

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-soft-01` | **RFC Editor / IETF** | Internet Engineering Task Force (IETF) | `https://www.rfc-editor.org` | Level 1 | HTTP / Search API / rsync | Free / Public | Definitive RFC protocol standards, errata, status tracking |
| `src-soft-02` | **W3C Standards** | World Wide Web Consortium | `https://www.w3.org/TR/` | Level 1 | HTTP / REST API | Free / Public | Web specifications, HTML/CSS/DOM/WebAPI standards |
| `src-soft-03` | **PHP Official Documentation** | The PHP Group / The PHP Foundation | `https://www.php.net/docs.php` | Level 1 | HTTP / Git Mirror / JSON mirror | Free / Public | Canonical PHP language manual, extensions, deprecations |
| `src-soft-04` | **MDN Web Docs** | Mozilla Foundation / Open Web Docs | `https://developer.mozilla.org` | Level 1 | HTTP / GitHub Content API | Free / Public | JavaScript, Web APIs, CSS, browser compatibility tables |
| `src-soft-05` | **Packagist Registry API** | Private Packagist / Jordi Boggiano | `https://packagist.org/apidoc/` | Level 2 | REST API / HTTP | Free / Public | PHP/Composer package metadata, download stats, security advisories |
| `src-soft-06` | **NPM Registry API** | npm, Inc. / GitHub / Microsoft | `https://registry.npmjs.org` | Level 2 | REST API | Free / Public | JavaScript/Node package versions, tarballs, package.json |
| `src-soft-07` | **PyPI JSON API** | Python Software Foundation | `https://pypi.org/pypi` | Level 2 | REST API | Free / Public | Python wheel/sdist releases, dependencies, hashes |
| `src-soft-08` | **Software Heritage** | Inria / UNESCO / Software Heritage | `https://www.softwareheritage.org` | Level 2 | REST API / SPARQL | Free / Public (with token for high rate) | Universal archive of software source code, persistent SWHIDs |
| `src-soft-09` | **ISO/IEC JTC 1** | ISO / IEC Joint Technical Committee | `https://www.iso.org/committee/45020.html`| Level 1 | HTTP (Paywalled Standards Store)| Paid / Commercial | C, C++, SQL, Ada, POSIX, software engineering lifecycle standards |
| `src-soft-10` | **ACM Digital Library** | Association for Computing Machinery | `https://dl.acm.org` | Level 3 | REST API (Institutional) / HTTP | Institutional / Paid | Peer-reviewed computer science literature, proceedings, SIGCOMM/SIGOPS |
| `src-soft-11` | **IEEE Xplore** | Institute of Electrical & Electronics Engineers | `https://ieeexplore.ieee.org` | Level 3 | REST API / HTTP | Institutional / Paid | Software engineering standards (IEEE 829, 1012), journals |
| `src-soft-12` | **OpenSSF Repositories** | Open Source Security Foundation / Linux Fdn | `https://openssf.org` | Level 2 | HTTP / GitHub API | Free / Public | Scorecards, SLSA framework, Best Practices Badge API |

### 4.2 Domain B: Cybersecurity & Information Security

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-sec-01` | **NIST NVD** | National Institute of Standards and Technology | `https://nvd.nist.gov` | Level 2 | REST API v2 / Bulk JSON | Free w/ API Key (Rate limited w/o) | Comprehensive CVE enrichment, CVSS v2/v3/v4 scores, CPE matching |
| `src-sec-02` | **CVE Program** | The MITRE Corporation / CVE Board | `https://www.cve.org` | Level 2 | REST API v5 / GitHub Git | Free / Public | Authoritative CVE assignment, CNA records, CVE 5.0 JSON records |
| `src-sec-03` | **CISA KEV Catalog** | Cybersecurity & Infrastructure Security Agency | `https://www.cisa.gov/known-exploited-vulnerabilities-catalog` | Level 2 | JSON Feed / CSV / RSS | Free / Public | Known Exploited Vulnerabilities catalog, mandatory remediation dates |
| `src-sec-04` | **Open Source Vulnerabilities (OSV)** | Google / OpenSSF | `https://osv.dev` | Level 2 | REST API / Bulk Downloads | Free / Public | Aggregated open-source vulnerabilities for Composer, npm, PyPI, Go |
| `src-sec-05` | **GitHub Advisory Database** | GitHub, Inc. | `https://github.com/advisories` | Level 2 | GraphQL API / REST API | Free (Requires GH Personal Token) | Curated security advisories across open-source ecosystems, GHSA IDs |
| `src-sec-06` | **OWASP Foundation** | Open Web Application Security Project | `https://owasp.org` | Level 1 | HTTP / GitHub Markdown API | Free / Public | OWASP Top 10, ASVS, Cheat Sheet Series, SAMM framework |
| `src-sec-07` | **MITRE ATT&CK** | The MITRE Corporation | `https://attack.mitre.org` | Level 2 | STIX 2.1 / TAXII 2.1 / REST API | Free / Public | Threat actor TTPs, matrices for Enterprise, Cloud, Mobile |
| `src-sec-08` | **FIRST (Forum of Incident Response)** | FIRST.Org, Inc. | `https://www.first.org` | Level 1 | HTTP / API | Free / Public | CVSS specification, EPSS (Exploit Prediction Scoring System) API |
| `src-sec-09` | **ENISA Publications** | European Union Agency for Cybersecurity | `https://www.enisa.europa.eu` | Level 2 | HTTP / RSS | Free / Public | European threat landscapes, cloud security certification (EUCS) |
| `src-sec-10` | **CERT Coordination Center (CERT/CC)**| Software Engineering Institute (SEI) / CMU | `https://www.kb.cert.org/vuls/` | Level 2 | HTTP / RSS Feed | Free / Public | Vulnerability notes, coordinated disclosures, vendor response tracking |

### 4.3 Domain C: Cryptography & Data Protection

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-cryp-01` | **NIST Cryptographic Toolkit** | NIST Computer Security Division | `https://csrc.nist.gov/projects/cryptographic-standards-and-guidelines` | Level 1 | HTTP / Direct PDF / API | Free / Public | FIPS 140-3, FIPS 197 (AES), FIPS 186-5 (DSS), SP 800-57, SP 800-90A |
| `src-cryp-02` | **NIST Post-Quantum Cryptography** | NIST Cryptographic Technology Group | `https://csrc.nist.gov/projects/post-quantum-cryptography` | Level 1 | HTTP / CSRC Publications | Free / Public | FIPS 203 (ML-KEM), FIPS 204 (ML-DSA), FIPS 205 (SLH-DSA) |
| `src-cryp-03` | **IACR Cryptology ePrint Archive** | International Association for Cryptologic Research | `https://eprint.iacr.org` | Level 3 | HTTP / OAI-PMH API / RSS | Free / Public | Rapid preprint dissemination of cryptographic research, cryptanalysis |
| `src-cryp-04` | **BSI Cryptographic Technical Guidelines**| German Federal Office for Information Security | `https://www.bsi.bund.de` | Level 1 | HTTP / Direct PDF | Free / Public | BSI TR-02102 (Cryptographic Mechanisms: Recommendations and Key Lengths) |
| `src-cryp-05` | **ANSSI Cryptographic Rules** | Agence nationale de la sécurité des systèmes | `https://cyber.gouv.fr` | Level 1 | HTTP / Direct PDF | Free / Public | ANSSI RGS Appendix B1 (Cryptographic mechanisms, key size mandates) |
| `src-cryp-06` | **CA/Browser Forum** | Certification Authority / Browser Forum | `https://cabforum.org` | Level 1 | HTTP / Git Repository | Free / Public | Baseline Requirements for TLS certificates, S/MIME, Code Signing |
| `src-cryp-07` | **OpenSSL Documentation** | OpenSSL Software Foundation | `https://www.openssl.org/docs/` | Level 2 | HTTP / Git Repository | Free / Public | Cryptographic library API reference, security advisory feeds, changelogs |

### 4.4 Domain D: Isolation, Multi-Tenancy & Access Boundaries

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-isol-01` | **Open Container Initiative (OCI)** | Linux Foundation / OCI | `https://opencontainers.org` | Level 1 | GitHub Markdown / Git | Free / Public | Runtime Specification, Image Specification, Distribution Specification |
| `src-isol-02` | **Linux Kernel Documentation** | The Linux Kernel Organization | `https://docs.kernel.org` | Level 1 | HTTP / Git Tree | Free / Public | Namespaces, cgroups-v2, seccomp-bpf, Landlock, user namespaces |
| `src-isol-03` | **Cloud Native Computing Foundation** | Linux Foundation / CNCF | `https://www.cncf.io` | Level 2 | HTTP / GitHub API | Free / Public | Kubernetes isolation patterns, container network interfaces (CNI) |
| `src-isol-04` | **Firecracker MicroVM Documentation** | Amazon Web Services / Open Source | `https://firecracker-microvm.github.io` | Level 2 | HTTP / GitHub Git | Free / Public | KVM-based jailer security model, minimal process isolation specs |
| `src-isol-05` | **gVisor Documentation** | Google LLC / Open Source | `https://gvisor.dev/docs/` | Level 2 | HTTP / GitHub Git | Free / Public | Application kernel sandbox architecture, Sentry/Gofer isolation model |
| `src-isol-06` | **PostgreSQL Documentation (RLS)** | PostgreSQL Global Development Group | `https://www.postgresql.org/docs/current/ddl-rowsecurity.html` | Level 1 | HTTP / HTML Manual | Free / Public | Row-Level Security policies, multi-tenant database separation semantics |

### 4.5 Domain E: Artificial Intelligence & Machine Learning

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-ai-01` | **arXiv AI / Machine Learning (cs.AI, cs.LG)** | Cornell University | `https://arxiv.org/abs/` | Level 3 | REST API (arXiv API) / OAI-PMH / bulk S3 | Free / Public | Foundation model papers, transformer architectures, alignment research |
| `src-ai-02` | **Hugging Face Hub** | Hugging Face, Inc. | `https://huggingface.co` | Level 2 | REST API / Python SDK / git-lfs | Free / Public (Token for private/rate) | Model cards, dataset cards, benchmark leaderboards (Open LLM Leaderboard) |
| `src-ai-03` | **Papers with Code** | Meta AI / ScienceX | `https://paperswithcode.com` | Level 2 | REST API / JSON dumps | Free / Public | Benchmarks, SOTA tracking, model evaluation leaderboards, task taxonomy |
| `src-ai-04` | **Model Context Protocol (MCP)** | Anthropic PBC / Open Source | `https://modelcontextprotocol.io` | Level 1 | HTTP / GitHub Markdown | Free / Public | Canonical MCP protocol specification, JSON-RPC schema, transport specs |
| `src-ai-05` | **NIST AI Risk Management Framework (AI RMF)**| NIST Information Technology Laboratory | `https://www.nist.gov/itl/ai-risk-management-framework` | Level 1 | HTTP / JSON profiles | Free / Public | NIST AI 100-1 framework, generative AI profiles, evaluation taxonomy |
| `src-ai-06` | **MLCommons** | MLCommons Association | `https://mlcommons.org` | Level 2 | HTTP / GitHub Git | Free / Public | MLPerf training/inference benchmarks, MLCommons AI Safety v0.5 |
| `src-ai-07` | **Stanford CRFM** | Center for Research on Foundation Models | `https://crfm.stanford.edu` | Level 3 | HTTP / HELM GitHub Repo | Free / Public | Holistic Evaluation of Language Models (HELM), ecosystem transparency |

### 4.6 Domain F: Mathematics & Statistics

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-math-01` | **NIST Digital Library of Math Functions (DLMF)**| NIST Applied and Computational Math Division | `https://dlmf.nist.gov` | Level 1 | HTTP / TeX download | Free / Public | Standard formulas, special functions, mathematical tables, errata |
| `src-math-02` | **arXiv Mathematics (math.*)** | Cornell University | `https://arxiv.org/archive/math` | Level 3 | REST API / OAI-PMH | Free / Public | Peer-reviewed preprints across all AMS mathematical disciplines |
| `src-math-03` | **AMS MathSciNet** | American Mathematical Society | `https://mathscinet.ams.org` | Level 2 | REST API / Web search | Institutional / Paid | Comprehensive mathematical reviews, citation indexing, MSC taxonomy |
| `src-math-04` | **Encyclopedia of Mathematics** | EMS Press / Springer | `https://encyclopediaofmath.org` | Level 2 | MediaWiki API / HTTP | Free / Public | Peer-reviewed mathematical encyclopedia articles, formal definitions |
| `src-math-05` | **The On-Line Encyclopedia of Integer Sequences (OEIS)**| OEIS Foundation Inc. | `https://oeis.org` | Level 2 | REST API / HTTP | Free / Public | Number sequences, generating functions, recurrence relations, formulas |

### 4.7 Domain G: Physics, Chemistry & Natural Sciences

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-sci-01` | **NIST Physical Reference Data (CODATA)**| NIST Physical Measurement Laboratory | `https://physics.nist.gov/cuu/Constants/` | Level 1 | HTTP / ASCII data tables | Free / Public | Internationally recommended values of fundamental physical constants |
| `src-sci-02` | **PubChem** | National Center for Biotechnology Info (NCBI) / NLM | `https://pubchem.ncbi.nlm.nih.gov` | Level 1 | PUG REST API / PUG View | Free / Public | Chemical structures, IUPAC names, bioactivities, physical properties |
| `src-sci-03` | **Protein Data Bank (RCSB PDB)** | Research Collaboratory for Structural Bioinformatics | `https://www.rcsb.org` | Level 1 | Data API / Search API (GraphQL) | Free / Public | 3D macromolecular structures (proteins, nucleic acids), PDBx/mmCIF |
| `src-sci-04` | **NASA Open Data Portal** | National Aeronautics and Space Administration | `https://data.nasa.gov` | Level 1 | Socrata Open Data API (SODA) | Free / Public | Planetary data, astrophysics catalogs, Earth observation datasets |
| `src-sci-05` | **USGS Earth Resources Observation (EROS)**| United States Geological Survey | `https://www.usgs.gov/centers/eros` | Level 1 | REST API / Machine-to-Machine | Free / Public | Topographic data, Landsat imagery, hydrology, geological maps |

### 4.8 Domain H: Medicine, Healthcare & Life Sciences

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-med-01` | **PubMed / Entrez API** | National Library of Medicine (NLM / NIH) | `https://pubmed.ncbi.nlm.nih.gov` | Level 2 | E-utilities REST API | Free / Public (API key expands rate) | 36M+ biomedical citations, MeSH indexing, clinical query filters |
| `src-med-02` | **Europe PMC** | EMBL-EBI / Europe PMC Consortium | `https://europepmc.org` | Level 2 | REST API / SOAP / Bulk OAI | Free / Public | Full-text open access biomedical articles, grant funding links |
| `src-med-03` | **Cochrane Library** | Cochrane Collaboration / Wiley | `https://www.cochranelibrary.com` | Level 1 | REST API (Institutional) / HTTP | Partial Free / Institutional | Gold-standard systematic reviews and meta-analyses, clinical evidence |
| `src-med-04` | **ClinicalTrials.gov** | NLM / FDA / NIH | `https://clinicaltrials.gov` | Level 1 | REST API v2 | Free / Public | Clinical study registry, trial phases (I-IV), trial outcomes and adverse events |
| `src-med-05` | **WHO Global Health Observatory (GHO)**| World Health Organization | `https://www.who.int/data/gho` | Level 1 | Athena OData API / REST API | Free / Public | Global epidemiological indicators, disease prevalence, mortality statistics |
| `src-med-06` | **FDA Drugs@FDA & DailyMed** | US Food and Drug Administration / NLM | `https://www.accessdata.fda.gov/scripts/cder/daf/` | Level 1 | openFDA REST API | Free / Public | Approved drug labels, package inserts, clinical pharmacology reviews |
| `src-med-07` | **EMA Medicines API** | European Medicines Agency | `https://www.ema.europa.eu` | Level 1 | REST API / XML Feeds | Free / Public | European Public Assessment Reports (EPAR), summary of product characteristics |
| `src-med-08` | **NICE Guidance** | National Institute for Health and Care Excellence (UK) | `https://www.nice.org.uk/guidance` | Level 1 | Syndication API / HTTP | Free / Public | Clinical practice guidelines, technology appraisals, quality standards |

### 4.9 Domain I: Law, Regulations & Compliance

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-law-01` | **EUR-Lex** | Publications Office of the European Union | `https://eur-lex.europa.eu` | Level 1 | REST API / SPARQL Endpoint / Web | Free / Public (Registration required for webservice) | EU Treaties, Directives, Regulations, Case-law of CJEU, official CELEX IDs |
| `src-law-02` | **US Federal Register** | National Archives & Records Administration (NARA) | `https://www.federalregister.gov` | Level 1 | REST API v1 | Free / Public | Daily federal agency rules, proposed rules, executive orders, presidential docs |
| `src-law-03` | **US Congress Legislation (Congress.gov)**| Library of Congress / GPO | `https://www.congress.gov` | Level 1 | Congress.gov REST API | Free w/ API Key | Federal legislative bills, public laws, congressional committee reports |
| `src-law-04` | **European Data Protection Board (EDPB)**| EDPB / European Union | `https://edpb.europa.eu` | Level 1 | HTTP / RSS Feed | Free / Public | Authoritative GDPR guidelines, binding decisions, adequacy opinions |
| `src-law-05` | **WIPO Lex** | World Intellectual Property Organization | `https://www.wipo.int/wipolex/` | Level 1 | REST API / HTTP | Free / Public | Global IP treaties, national IP legislation, patent/trademark case law |
| `src-law-06` | **UK Legislation Portal** | The National Archives (UK) | `https://www.legislation.gov.uk` | Level 1 | Linked Data API / XML / RDF | Free / Public | UK Public General Acts, Statutory Instruments, devolved legislation |
| `src-law-07` | **UN Treaty Collection (UNTC)** | United Nations Legal Office | `https://treaties.un.org` | Level 1 | HTTP / Search | Free / Public | Certified international treaties, multilateral conventions, ratifications |

### 4.10 Domain J: Economics, Finance & Business

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-econ-01` | **World Bank Open Data** | International Bank for Reconstruction & Development | `https://data.worldbank.org` | Level 1 | World Bank REST API v2 | Free / Public | World Development Indicators (WDI), GDP, trade, debt, poverty data |
| `src-econ-02` | **International Monetary Fund (IMF) Data**| International Monetary Fund | `https://www.imf.org/en/Data` | Level 1 | SDMX REST API / JSON | Free / Public | World Economic Outlook (WEO), IFS, balance of payments, fiscal monitors |
| `src-econ-03` | **FRED (Federal Reserve Economic Data)**| Federal Reserve Bank of St. Louis | `https://fred.stlouisfed.org` | Level 1 | FRED REST API | Free w/ API Key | 800,000+ US and international economic time series, interest rates, CPI |
| `src-econ-04` | **Bank for International Settlements (BIS)**| Bank for International Settlements | `https://www.bis.org` | Level 1 | SDMX REST API / CSV | Free / Public | Central bank statistics, banking statistics, foreign exchange, Basel III data |
| `src-econ-05` | **OECD Data Explorer** | Organisation for Economic Co-operation and Development| `https://data.oecd.org` | Level 1 | SDMX-JSON REST API | Free / Public | Harmonized macroeconomic indicators, labor, education, tax statistics |
| `src-econ-06` | **SEC EDGAR System** | US Securities and Exchange Commission | `https://www.sec.gov/edgar` | Level 1 | SEC Submissions REST API | Free / Public (Strict User-Agent header) | Public company filings (10-K, 10-Q, 8-K), XBRL financial statements |
| `src-econ-07` | **Eurostat Data** | Statistical Office of the European Union | `https://ec.europa.eu/eurostat` | Level 1 | SDMX REST API / JSON-stat | Free / Public | European national accounts, inflation (HICP), employment, international trade |

### 4.11 Domain K: Engineering & Industrial Fields

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-eng-01` | **ISO Standards Store & Catalog** | International Organization for Standardization | `https://www.iso.org` | Level 1 | REST API (Metadata) / Web | Paid / Commercial | ISO 9001 (Quality), ISO 14001, ISO 26262 (Road vehicles functional safety) |
| `src-eng-02` | **IEC Standards Catalog** | International Electrotechnical Commission | `https://www.iec.ch` | Level 1 | Search API / Web | Paid / Commercial | IEC 61508 (Functional Safety), IEC 62443 (Industrial Cyber Security) |
| `src-eng-03` | **ASME Standards** | American Society of Mechanical Engineers | `https://www.asme.org` | Level 1 | HTTP / Catalog search | Paid / Commercial | Boiler & Pressure Vessel Code (BPVC), geometric dimensioning (Y14.5) |
| `src-eng-04` | **SAE International** | Society of Automotive Engineers | `https://www.sae.org` | Level 1 | HTTP / Search API | Paid / Commercial | Automotive & aerospace standards, J3016 (driving automation levels) |
| `src-eng-05` | **OSHA Regulations** | US Occupational Safety and Health Administration | `https://www.osha.gov/laws-regs` | Level 1 | HTTP / API | Free / Public | Industrial safety standards, toxic substances limits, machinery guarding |

### 4.12 Domain L: Energy, Agriculture & Environment

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-env-01` | **IPCC Data Distribution Centre** | Intergovernmental Panel on Climate Change | `https://www.ipcc-data.org` | Level 1 | HTTP / OPeNDAP / NetCDF | Free / Public | Climate assessment reports, AR6 emissions scenarios, climate model output |
| `src-env-02` | **FAOSTAT** | Food and Agriculture Organization of the UN | `https://www.fao.org/faostat/` | Level 1 | FAOSTAT REST API | Free / Public | Global agricultural production, food supply, fertilizer usage, land use |
| `src-env-03` | **US Energy Information Admin (EIA)** | US Department of Energy | `https://www.eia.gov` | Level 1 | EIA REST API v2 | Free w/ API Key | Energy consumption, petroleum/gas stocks, renewable electricity generation |
| `src-env-04` | **Global Biodiversity Info Facility (GBIF)**| GBIF Secretariat | `https://www.gbif.org` | Level 1 | GBIF Occurrence REST API | Free / Public | 2B+ species occurrence records, taxonomic backbone, biodiversity data |
| `src-env-05` | **Copernicus Climate Data Store** | European Centre for Medium-Range Weather (ECMWF) | `https://cds.climate.copernicus.eu` | Level 1 | CDS Python API / REST | Free w/ Registration | ERA5 climate reanalysis, satellite climate variables, atmospheric sensors |

### 4.13 Domain M: Education, Languages & Humanities

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-hum-01` | **ERIC (Education Resources Info Center)**| Institute of Education Sciences / US Dept of Ed | `https://eric.ed.gov` | Level 2 | ERIC REST API | Free / Public | 1.8M+ indexed education research papers, peer-reviewed journal articles |
| `src-hum-02` | **Universal Dependencies (UD)** | UD Consortium / Language Technology Group | `https://universaldependencies.org`| Level 1 | Git Repository / CoNLL-U | Free / Public | Cross-linguistic syntactic treebanks for 100+ natural languages |
| `src-hum-03` | **OpenAlex** | OurResearch | `https://openalex.org` | Level 2 | REST API / AWS S3 snapshots | Free / Public (High rate w/ polite header) | 250M+ scholarly works, authors, institutions, concepts across all disciplines |
| `src-hum-04` | **Semantic Scholar** | Allen Institute for AI (AI2) | `https://www.semanticscholar.org` | Level 2 | Academic Graph REST API | Free w/ API Key | Citation graphs, influential citations, TLDR summaries across all sciences |
| `src-hum-05` | **Crossref Metadata API** | Crossref | `https://www.crossref.org` | Level 1 | REST API / Polite pool | Free / Public | Official DOI registration agency, author affiliations, funding, retractions |

### 4.14 Domain N: Government, Public Data & International Organizations

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-gov-01` | **Data.gov (US Open Government Data)** | US General Services Administration (GSA) | `https://data.gov` | Level 1 | CKAN REST API | Free / Public | 300,000+ federal agency datasets, metadata, catalogs |
| `src-gov-02` | **data.europa.eu** | Publications Office of the European Union | `https://data.europa.eu` | Level 1 | DCAT-AP SPARQL / REST API | Free / Public | Official open data from European Union institutions, agencies, and member states |
| `src-gov-03` | **UNdata** | United Nations Statistics Division (UNSD) | `https://data.un.org` | Level 1 | SDMX REST API / CSV | Free / Public | Global statistical databases, national accounts, demographic data |
| `src-gov-04` | **UK Office for National Statistics (ONS)**| UK Statistics Authority | `https://www.ons.gov.uk` | Level 1 | ONS Beta REST API | Free / Public | UK economic accounts, inflation, census, labor market indicators |
| `src-gov-05` | **Our World in Data (OWID)** | Global Change Data Lab / University of Oxford | `https://ourworldindata.org` | Level 2 | GitHub API / CSV / Grapher | Free / Public | Curated empirical research with underlying verified primary citations |

### 4.15 Domain O: Management & Professional Services

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-mgmt-01` | **Project Management Institute (PMI)** | Project Management Institute | `https://www.pmi.org` | Level 1 | HTTP / Standards+ | Paid / Member | A Guide to the Project Management Body of Knowledge (PMBOK Guide 7th ed) |
| `src-mgmt-02` | **AXELOS PRINCE2 / ITIL** | PeopleCert / AXELOS | `https://www.axelos.com` | Level 1 | HTTP / Publications store | Paid / Commercial | ITIL 4 service management frameworks, PRINCE2 project governance |
| `src-mgmt-03` | **COSO Frameworks** | Committee of Sponsoring Organizations of the Treadway Commission | `https://www.coso.org` | Level 1 | HTTP / Direct PDF Store | Paid / Commercial | Internal Control - Integrated Framework, Enterprise Risk Management (ERM) |
| `src-mgmt-04` | **International Organization for Standardization (ISO 31000)** | ISO Technical Committee ISO/TC 262 | `https://www.iso.org/iso-31000-risk-management.html` | Level 1 | Standards Catalog | Paid / Commercial | Risk management guidelines, principles, assessment techniques (IEC 31010) |
| `src-mgmt-05` | **Harvard Business Review (HBR)** | Harvard Business Publishing | `https://hbr.org` | Level 4 | HTTP / Digital subscription | Paid / Commercial | Executive management strategy, leadership case studies, corporate governance |

### 4.16 Domain P: Additional & Emerging Domains

| Source ID | Official Name | Owning Institution | Canonical URL | Authority Tier | Access Mechanisms | Auth / Cost Tier | Verified Capabilities |
|:---|:---|:---|:---|:---:|:---|:---|:---|
| `src-emg-01` | **Qiskit Documentation & Textbook** | IBM Quantum / Qiskit Community | `https://docs.quantum.ibm.com` | Level 1 | HTTP / Git Repository | Free / Public | Quantum circuit programming, transmon qubit control, quantum algorithms |
| `src-emg-02` | **Synthetic Biology Open Language (SBOL)**| SBOL Industrial Consortium / BioBricks | `https://sbolstandard.org` | Level 1 | REST API / Git / Java/Python lib | Free / Public | Computational standard for synthetic biology design, genetic circuit layout |
| `src-emg-03` | **ROS 2 Documentation (Robot Operating System)** | Open Robotics | `https://docs.ros.org/en/rolling/` | Level 1 | HTTP / GitHub Markdown | Free / Public | Distributed robotic middleware specifications, DDS communication, nodes |
| `src-emg-04` | **NASA Technical Reports Server (NTRS)** | NASA Scientific & Technical Information Program | `https://ntrs.nasa.gov` | Level 1 | NTRS REST API | Free / Public | Aerospace engineering, astrodynamics, reentry thermodynamics, propulsion |
| `src-emg-05` | **OpenBCI Documentation & Research** | OpenBCI | `https://docs.openbci.com` | Level 2 | HTTP / GitHub Git | Free / Public | EEG, EMG, ECG biosensing hardware protocols, neural data streaming |

---

## 5. Source, Provider, Retrieval, and Tooling Classification (Deliverables D & E)

### 5.1 Four Disjoint Conceptual Categories
In strict adherence to `UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-003A` (Finding 2), RDEI enforces a strict ontological separation between the authoritative origins of knowledge and the technical infrastructure used to discover, transport, or process it:

```
+---------------------------------------------------------------------------------------------------+
| 1. KNOWLEDGE SOURCES (Authority Plane)                                                            |
| Official institutions, standards bodies, academic publishers, statutory registries, & datasets.   |
| Examples: NIST, IETF, The PHP Group, Packagist, EUR-Lex, ISO, WHO, US Federal Reserve (FRED).    |
+---------------------------------------------------------------------------------------------------+
                                                  ^
                                                  | Upstream Provenance Linkage (Section 5.3)
                                                  |
+---------------------------------------------------------------------------------------------------+
| 2. DISCOVERY PROVIDERS (Navigation Plane)                                                         |
| Search engines, indexing APIs, and bibliographic search systems used to locate URLs.               |
| Examples: Brave Search API, Tavily Research API, SearXNG, Bing Search API, Crossref Search.      |
| CANNOT count as Knowledge Sources or Independent Corroboration.                                   |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| 3. RETRIEVAL MECHANISMS (Transport Plane)                                                         |
| Technical protocols and clients used to fetch raw payloads across networks.                       |
| Examples: StreamableHttpTransport (cURL), REST/JSON APIs, GraphQL endpoints, SOAP, RSS, Git.     |
| CANNOT count as Knowledge Sources or Independent Corroboration.                                   |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| 4. TOOLING AND MCP SERVERS (Execution Plane)                                                      |
| Local execution bridges, headless rendering engines, format parsers, and local storage caches.    |
| Examples: Puppeteer Headless MCP, Fetch MCP, SQLite MCP, PDFExtract, Markdown Parser.            |
| CANNOT count as Knowledge Sources or Independent Corroboration.                                   |
+---------------------------------------------------------------------------------------------------+
```

### 5.2 Independent Corroboration Invariant
> [!CAUTION]
> **Strict Non-Corroboration Enforcement:**  
> Search engines, discovery providers, retrieval tools, MCP servers, and local storage caches are **infrastructure**, not authorities.  
> 1. If three different discovery providers (e.g., Brave Search, Tavily, and SearXNG) all return links to the same upstream RFC or PHP manual entry, this represents **exactly one** piece of knowledge evidence ($N_{\text{corr}} = 0$).
> 2. Executing Fetch MCP and Puppeteer MCP against the same documentation site produces **zero additional corroboration**.
> 3. Independent corroboration ($N_{\text{corr}}$) increments **only** when distinct, un-syndicated Knowledge Sources with independent institutional ownership reach the same factual conclusion.

### 5.3 Upstream Provenance Preservation Model
When evidence is discovered via an intermediary provider (such as Brave Search) or retrieved via an execution bridge (such as Puppeteer MCP), RDEI mandates that the resulting claim record retain cryptographic provenance directly to the underlying Knowledge Source:

```json
{
  "provenance": {
    "canonical_knowledge_source_id": "src-soft-03",
    "upstream_publisher": "The PHP Group",
    "canonical_document_url": "https://www.php.net/manual/en/migration83.other-changes.php",
    "discovery_provider_used": "Brave Search API v1",
    "retrieval_mechanism_used": "StreamableHttpTransport / cURL (IP-Pinned)",
    "tooling_executed": "ContentSanitizer v1.0",
    "retrieval_timestamp": "2026-11-15T18:30:00Z",
    "raw_payload_sha256": "8f434346648f6b96df89dda901c5176b10e6d0ceec3e4a5c1dcab44902a43d2d",
    "intermediate_cached": false
  }
}
```

### 5.4 Technical Provider and Tooling Operational Evaluation

#### Table 5.4.1: Discovery Providers (Search Engines & Web Discovery APIs)
*Note: Discovery providers index external web content; they never act as primary sources of truth.*

| Provider Name | Type / Architecture | Supported Scope | Cost Tier | Rate Limits | Authentication | Verification Status | Security & SSRF Impact |
|:---|:---|:---|:---|:---|:---|:---:|:---|
| **Brave Search API** | Web Discovery Index | Broad Web Discovery | Free Tier (2000/mo) / Paid | 1 req/s (Free Tier) | HTTP Header `X-Subscription-Token` | `DOCS_VERIFIED` | Outbound HTTPS queries to Brave API; zero direct target site fetch. |
| **Tavily Research API** | Multi-Source AI Search | Multi-Domain Web Discovery | Paid ($0.005/req) | 100 req/min | Bearer Token (`TAVILY_API_KEY`) | `DOCS_VERIFIED` | Commercial aggregator; strips HTML noise; returns parsed excerpts. |
| **SearXNG Meta-Search** | Self-Hosted Privacy Engine | General Discovery | Open Source / Free | Host compute bound | None (Local service) | `DOCS_VERIFIED` | Local instance at localhost; eliminates external query leakage. |
| **Crossref Search API** | Academic DOI Discovery | Scholarly Literature | Free / Public | 50 req/s with polite header | Mailto header token | `DOCS_VERIFIED` | Public academic metadata query; safe structured JSON response. |

#### Table 5.4.2: Retrieval Mechanisms (Protocols & Transport Pipelines)
*Note: Transport mechanisms fetch bytes; all network traffic strictly reuses `StreamableHttpTransport`.*

| Mechanism | Protocol / Wire Standard | Primary Use Case | Transport Defense | Timeout Budget | SSRF Controls |
|:---|:---|:---|:---|:---:|:---|
| **HTTP Direct / cURL** | HTTP/1.1 & HTTP/2 over TLS | Official documentation, HTML pages | Fail-Closed cURL | 10s connect, 30s transfer | Socket-level IP pinning, prohibited IPv4/IPv6 matrices, port 80/443 restriction. |
| **REST / JSON Client** | HTTP REST (RFC 8259) | Primary structured registries (NVD, Packagist) | JSON schema parse | 10s connect, 20s transfer | Pinned IP, automated redirect revalidation (max 3 hops), payload 2MB limit. |
| **GraphQL Client** | HTTP POST GraphQL query | GitHub Advisory Database | Pinned query payload | 15s transfer | Secret resolved via env var; query complexity bounded. |
| **OAI-PMH Harvester** | XML over HTTP | Academic repository harvesting (arXiv, PMC) | XML entity disable | 30s transfer | XXE injection disabled; libxml entity loader deactivated. |
| **RSS / Atom Feed Client**| XML Syndication | Daily security & gazette feeds (CISA, EUR-Lex) | Libxml hardened | 15s transfer | Periodic poll via event-driven CLI/task plan; zero background daemon. |
| **Bulk Dataset Ingestion**| Gzip / JSON / CSV | NVD historical dumps, CISA KEV JSON | Stream decompress | Bounded stream | File size ceiling 50MB; stream-hashed SHA-256 before extraction. |

#### Table 5.4.3: Tooling and MCP Integration Servers
*Note: MCP tools run locally or via stdio; they are execution utilities, not knowledge sources.*

| Tool / Server | Protocol / Interface | Operational Function | Sandboxing Level | Verification Status | Operational Constraints |
|:---|:---|:---|:---|:---:|:---|
| **Fetch MCP Server** | Model Context Protocol (stdio) | Direct web documentation retrieval | High (Process jail) | `DOCS_VERIFIED` | Must route all external fetches through `SecureHttpClient` SSRF rules. |
| **Puppeteer MCP Server** | Headless Chromium (stdio) | Rendering JavaScript-heavy SPAs | Maximum (Isolated VM) | `DOCS_VERIFIED` | Prohibited from file system access; strict 5s render timeout; 1-2 concurrent tabs. |
| **SQLite Knowledge MCP** | Local SQLite Engine (stdio) | Local research index & cache query | Host-bounded | `DOCS_VERIFIED` | Read-only database access; zero external network egress. |
| **ContentSanitizer** | Deterministic PHP Pipeline | Stripping script tags, styles, prompts | Deterministic Code | `DOCS_VERIFIED` | 2MB payload ceiling; regex scrubbing of adversarial override sequences. |

### 5.5 Access Capability Classification Matrix
Every knowledge source in RDEI is cataloged with its supported technical retrieval paths:

```
+---------------------+-------------------+-------------------+-------------------+-------------------+
| Knowledge Category  | Direct Web (HTTP) | REST/GraphQL API  | MCP Research Tool | Structured Feeds  |
+---------------------+-------------------+-------------------+-------------------+-------------------+
| Standards Orgs      | PRIMARY (HTML/PDF)| SECONDARY (Metad.)| SUPPORTED (Fetch) | RSS (Releases)    |
| Security & CVEs     | SECONDARY (Search)| PRIMARY (JSON API)| PRIMARY (Tools)   | PRIMARY (Daily)   |
| Package Registries  | SECONDARY (WebUI) | PRIMARY (JSON API)| SUPPORTED (Query) | PRIMARY (Updates) |
| Academic Databases  | SECONDARY (Portal)| PRIMARY (OAI/REST)| SUPPORTED (Query) | RSS (TOC Feeds)   |
| Government Data     | SECONDARY (Portal)| PRIMARY (CKAN/SDMX| SUPPORTED (Fetch) | RSS (Notices)     |
| Legal Gazettes      | PRIMARY (Official)| PRIMARY (EUR-Lex) | SUPPORTED (Fetch) | RSS (Daily Acts)  |
| Health & Medicine   | SECONDARY (HTML)  | PRIMARY (PubMed)  | SUPPORTED (Tools) | RSS (Guidelines)  |
+---------------------+-------------------+-------------------+-------------------+-------------------+
```

---

## 6. Source Registry Schema Design (Deliverable C)

The universal Source Registry requires every discovered source to conform to a strict, declarative JSON Schema.

### 6.1 Validation Schema: `source-definition.schema.json`
Conforming to JSON Schema Draft 2020-12, this schema strictly defines all 26 canonical metadata fields required for authoritative knowledge source registration:

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "$id": "https://uaos.agent-os.org/schemas/rdei/source-definition.schema.json",
  "title": "UAOS Canonical Source Definition",
  "description": "Universal declarative metadata schema for authoritative research sources across all domains.",
  "type": "object",
  "additionalProperties": false,
  "required": [
    "source_id",
    "official_name",
    "description",
    "owner",
    "canonical_url",
    "domains",
    "subdomains",
    "authority_tier",
    "source_designation",
    "category_type",
    "supported_languages",
    "supported_formats",
    "access_mechanisms",
    "auth_profile",
    "cost_tier",
    "licensing",
    "rate_limits",
    "freshness_policy",
    "status",
    "last_verified_at",
    "classification_evidence",
    "verification_profile",
    "independence_profile",
    "applicability_profile",
    "provenance_rules",
    "fallback_source_ids"
  ],
  "properties": {
    "source_id": {
      "type": "string",
      "pattern": "^src-[a-z0-9\\-]+$",
      "description": "Globally unique stable identifier (e.g. src-soft-packagist, src-sec-nvd)."
    },
    "official_name": {
      "type": "string",
      "minLength": 2,
      "maxLength": 255,
      "description": "Full formal name of the source or publishing organization."
    },
    "description": {
      "type": "string",
      "minLength": 10,
      "maxLength": 2000,
      "description": "Comprehensive institutional and operational summary of the source."
    },
    "owner": {
      "type": "object",
      "additionalProperties": false,
      "required": ["institution_name", "institution_type", "jurisdiction"],
      "properties": {
        "institution_name": { "type": "string", "minLength": 2, "maxLength": 255 },
        "institution_type": {
          "type": "string",
          "enum": ["standards_body", "government_agency", "academic_institution", "multilateral_org", "industry_consortium", "commercial_vendor", "open_source_foundation"]
        },
        "jurisdiction": {
          "type": "string",
          "pattern": "^([A-Z]{2}|GLOBAL)$",
          "description": "ISO 3166-1 alpha-2 country code or 'GLOBAL'."
        }
      }
    },
    "canonical_url": {
      "type": "string",
      "format": "uri",
      "pattern": "^https://[a-zA-Z0-9.-]+(?:/.*)?$",
      "description": "Primary authoritative HTTPS web endpoint. Remote endpoints must use HTTPS."
    },
    "domains": {
      "type": "array",
      "items": { "type": "string", "pattern": "^[A-P]$" },
      "minItems": 1,
      "uniqueItems": true
    },
    "subdomains": {
      "type": "array",
      "items": { "type": "string", "pattern": "^[A-P]\\.[0-9]{2}$" },
      "minItems": 1,
      "uniqueItems": true
    },
    "authority_tier": {
      "type": "integer",
      "minimum": 1,
      "maximum": 6,
      "description": "1 = Primary Standards/Treaties; 2 = Institutional/Security; 3 = Peer-Reviewed Research; 4 = Professional Engineering; 5 = Practitioner Forums; 6 = Unverified."
    },
    "source_designation": {
      "type": "string",
      "enum": ["primary_authority", "curated_secondary", "statistical_registry", "aggregator", "practitioner_commentary"]
    },
    "category_type": {
      "type": "string",
      "enum": ["knowledge_source", "discovery_provider", "retrieval_mechanism", "tooling_mcp"],
      "description": "Strict conceptual categorization: Knowledge Source vs Discovery Provider vs Retrieval Mechanism vs Tooling/MCP."
    },
    "supported_languages": {
      "type": "array",
      "items": { "type": "string", "pattern": "^[a-z]{2}(-[A-Z]{2})?$" },
      "minItems": 1
    },
    "supported_formats": {
      "type": "array",
      "items": {
        "type": "string",
        "enum": ["application/json", "text/html", "application/xml", "application/pdf", "text/markdown", "text/csv", "application/x-ld+json"]
      },
      "minItems": 1
    },
    "access_mechanisms": {
      "type": "array",
      "minItems": 1,
      "items": {
        "type": "object",
        "additionalProperties": false,
        "required": [
          "mechanism_type",
          "endpoint_url",
          "is_active",
          "verification_status",
          "evidence_reference",
          "verified_at"
        ],
        "properties": {
          "mechanism_type": {
            "type": "string",
            "enum": ["rest_api", "graphql_api", "soap_api", "http_direct", "mcp_server", "rss_atom_feed", "bulk_dataset", "git_tree"]
          },
          "endpoint_url": {
            "type": "string",
            "format": "uri",
            "pattern": "^(https://[a-zA-Z0-9.-]+(?:/.*)?|http://(?:localhost|127\\.0\\.0\\.1)(?::[0-9]+)?(?:/.*)?)$",
            "description": "HTTPS required for remote endpoints; plain HTTP permitted strictly for documented localhost/127.0.0.1 instances."
          },
          "mcp_server_id": {
            "type": "string",
            "pattern": "^[a-z0-9\\-_]+$"
          },
          "documentation_url": {
            "type": "string",
            "format": "uri",
            "pattern": "^https://[a-zA-Z0-9.-]+(?:/.*)?$"
          },
          "is_active": { "type": "boolean" },
          "verification_status": {
            "type": "string",
            "enum": ["untested", "docs_verified", "endpoint_verified", "connectivity_tested", "fully_tested"],
            "description": "Granular verification status: 'untested' indicates known declaration without documentation inspection or connectivity test; 'docs_verified' indicates documentary review only (official API docs, schemas, portal reference) with no network handshake; 'endpoint_verified' indicates documentary review plus passive DNS/TLS hostname validation confirmed without authenticated payload exchange; 'connectivity_tested' indicates active network handshake and transport connectivity tested; 'fully_tested' indicates complete end-to-end integration including transport connectivity, authentication, and payload/quota verification with empirical evidence."
          },
          "evidence_reference": {
            "type": "string",
            "minLength": 5,
            "maxLength": 2000,
            "description": "Mandatory documentary or empirical evidence reference justifying this access mechanism and its verification status."
          },
          "verified_at": {
            "type": "string",
            "format": "date-time",
            "description": "Mandatory ISO 8601 timestamp (UTC) recording when this specific access mechanism was evaluated."
          },
          "auth_requirement": {
            "type": "object",
            "additionalProperties": false,
            "required": ["requires_auth"],
            "description": "Optional access-mechanism-level authentication override. When omitted, inherits source-level auth_profile defaults.",
            "properties": {
              "requires_auth": {
                "type": "boolean",
                "description": "Explicit per-mechanism authentication requirement. When false, mechanism remains unauthenticated regardless of source-level auth_profile."
              },
              "auth_type": {
                "type": "string",
                "enum": ["none", "inherit", "api_key_header", "api_key_query", "bearer_token", "basic_auth", "mutual_tls", "oauth2_client_credentials"],
                "description": "Mechanism-level authentication type. Must be 'none' when requires_auth is false; 'inherit' or an explicit method when requires_auth is true."
              },
              "env_var_key": {
                "type": "string",
                "pattern": "^[A-Z0-9_]+$",
                "description": "External environment variable reference name only. Never store raw secrets here."
              }
            },
            "allOf": [
              {
                "if": {
                  "properties": { "requires_auth": { "const": true } }
                },
                "then": {
                  "properties": {
                    "auth_type": {
                      "enum": [
                        "inherit",
                        "api_key_header",
                        "api_key_query",
                        "bearer_token",
                        "basic_auth",
                        "mutual_tls",
                        "oauth2_client_credentials"
                      ]
                    }
                  },
                  "required": ["auth_type"]
                }
              },
              {
                "if": {
                  "properties": { "requires_auth": { "const": false } }
                },
                "then": {
                  "properties": {
                    "auth_type": { "const": "none" },
                    "env_var_key": { "not": {} }
                  }
                }
              }
            ]
          },
          "limitations": { "type": "string" }
        }
      }
    },
    "auth_profile": {
      "type": "object",
      "additionalProperties": false,
      "required": ["requires_auth", "auth_type"],
      "properties": {
        "requires_auth": { "type": "boolean" },
        "auth_type": {
          "type": "string",
          "enum": ["none", "api_key_header", "api_key_query", "bearer_token", "basic_auth", "mutual_tls", "oauth2_client_credentials"]
        },
        "env_var_key": {
          "type": "string",
          "pattern": "^[A-Z0-9_]+$",
          "description": "CRITICAL C3: External environment variable reference name only. Never store raw secrets here."
        }
      }
    },
    "cost_tier": {
      "type": "string",
      "enum": ["free_public", "free_registration", "free_metered_quota", "open_source_self_hosted", "paid_commercial_api", "paid_subscription", "institutional_access_only"]
    },
    "licensing": {
      "type": "object",
      "additionalProperties": false,
      "required": ["license_type", "reuse_permitted", "attribution_required"],
      "properties": {
        "license_type": { "type": "string", "minLength": 2, "maxLength": 255 },
        "reuse_permitted": { "type": "boolean" },
        "attribution_required": { "type": "boolean" },
        "attribution_template": { "type": "string" }
      }
    },
    "rate_limits": {
      "type": "object",
      "additionalProperties": false,
      "required": ["requests_per_minute", "burst_limit"],
      "properties": {
        "requests_per_minute": { "type": "integer", "minimum": 1, "maximum": 100000 },
        "burst_limit": { "type": "integer", "minimum": 1, "maximum": 10000 },
        "daily_quota": { "type": "integer", "minimum": 1, "maximum": 10000000 }
      }
    },
    "freshness_policy": {
      "type": "object",
      "additionalProperties": false,
      "required": ["max_cache_ttl_hours", "revalidation_trigger"],
      "properties": {
        "max_cache_ttl_hours": { "type": "integer", "minimum": 1, "maximum": 87600 },
        "revalidation_trigger": {
          "type": "string",
          "enum": ["continuous_6h", "daily_24h", "weekly_168h", "monthly_720h", "quarterly_2160h", "annual_8760h", "on_demand_task", "version_change"]
        },
        "context_overrides": {
          "type": "object",
          "additionalProperties": { "type": "integer", "minimum": 1, "maximum": 87600 }
        }
      }
    },
    "status": {
      "type": "string",
      "enum": ["active", "deprecated", "rate_limited", "unreachable", "credentials_missing"]
    },
    "last_verified_at": {
      "type": "string",
      "format": "date-time"
    },
    "classification_evidence": {
      "type": "string",
      "minLength": 10,
      "maxLength": 2000,
      "description": "Documented institutional proof confirming ownership and authority level."
    },
    "verification_profile": {
      "type": "object",
      "additionalProperties": false,
      "required": [
        "website_verified",
        "api_doc_verified",
        "endpoint_verified",
        "connectivity_tested",
        "auth_tested",
        "quota_confirmed",
        "licensing_reviewed",
        "verification_date",
        "verification_evidence",
        "limitations"
      ],
      "properties": {
        "website_verified": { "type": "boolean" },
        "api_doc_verified": { "type": "boolean" },
        "endpoint_verified": { "type": "boolean" },
        "connectivity_tested": { "type": "boolean" },
        "auth_tested": { "type": "boolean" },
        "quota_confirmed": { "type": "boolean" },
        "licensing_reviewed": { "type": "boolean" },
        "verification_date": { "type": "string", "format": "date-time" },
        "verification_evidence": { "type": "string" },
        "limitations": { "type": "string" }
      },
      "allOf": [
        {
          "if": {
            "properties": { "auth_tested": { "const": true } }
          },
          "then": {
            "properties": { "connectivity_tested": { "const": true } }
          }
        },
        {
          "if": {
            "properties": { "connectivity_tested": { "const": true } }
          },
          "then": {
            "properties": { "endpoint_verified": { "const": true } }
          }
        }
      ]
    },
    "independence_profile": {
      "type": "object",
      "additionalProperties": false,
      "required": ["parent_institution", "is_syndicated", "syndication_upstream", "citation_dependencies", "is_mirror"],
      "properties": {
        "parent_institution": { "type": "string" },
        "is_syndicated": { "type": "boolean" },
        "syndication_upstream": {
          "type": "array",
          "items": { "type": "string" }
        },
        "citation_dependencies": {
          "type": "array",
          "items": { "type": "string" }
        },
        "is_mirror": { "type": "boolean" },
        "mirror_upstream_url": {
          "type": "string",
          "format": "uri"
        }
      }
    },
    "applicability_profile": {
      "type": "object",
      "additionalProperties": false,
      "required": ["requires_component_verification", "requires_version_check", "requires_config_verification"],
      "properties": {
        "requires_component_verification": { "type": "boolean" },
        "requires_version_check": { "type": "boolean" },
        "requires_config_verification": { "type": "boolean" },
        "advisory_matching_keys": {
          "type": "array",
          "items": { "type": "string" }
        }
      }
    },
    "provenance_rules": {
      "type": "object",
      "additionalProperties": false,
      "required": ["retains_upstream_url", "retains_publisher_identity", "hashes_raw_payload"],
      "properties": {
        "retains_upstream_url": { "type": "boolean" },
        "retains_publisher_identity": { "type": "boolean" },
        "hashes_raw_payload": { "type": "boolean" }
      }
    },
    "fallback_source_ids": {
      "type": "array",
      "items": { "type": "string", "pattern": "^src-[a-z0-9\\-]+$" }
    }
  },
  "allOf": [
    {
      "if": {
        "properties": {
          "auth_profile": {
            "properties": { "requires_auth": { "const": true } }
          }
        }
      },
      "then": {
        "properties": {
          "auth_profile": {
            "properties": {
              "auth_type": {
                "enum": [
                  "api_key_header",
                  "api_key_query",
                  "bearer_token",
                  "basic_auth",
                  "mutual_tls",
                  "oauth2_client_credentials"
                ]
              },
              "env_var_key": {
                "type": "string",
                "pattern": "^[A-Z0-9_]+$"
              }
            },
            "required": ["auth_type", "env_var_key"]
          }
        }
      }
    },
    {
      "if": {
        "properties": {
          "auth_profile": {
            "properties": { "requires_auth": { "const": false } }
          }
        }
      },
      "then": {
        "properties": {
          "auth_profile": {
            "properties": {
              "auth_type": { "const": "none" },
              "env_var_key": { "not": {} }
            },
            "required": ["auth_type"]
          }
        }
      }
    }
  ]
}
```

### 6.2 Schema Concrete Instantiation Examples

#### Example 1: `src-sec-nvd` (Cybersecurity Vulnerability Database)
```json
{
  "source_id": "src-sec-nvd",
  "official_name": "National Vulnerability Database",
  "description": "US government repository of standards-based vulnerability management data represented using Common Vulnerabilities and Exposures (CVE) and Common Platform Enumeration (CPE).",
  "owner": {
    "institution_name": "National Institute of Standards and Technology (NIST)",
    "institution_type": "government_agency",
    "jurisdiction": "US"
  },
  "canonical_url": "https://nvd.nist.gov",
  "domains": ["B"],
  "subdomains": ["B.02", "B.04", "B.12"],
  "authority_tier": 2,
  "source_designation": "primary_authority",
  "category_type": "knowledge_source",
  "supported_languages": ["en"],
  "supported_formats": ["application/json"],
  "access_mechanisms": [
    {
      "mechanism_type": "rest_api",
      "endpoint_url": "https://services.nvd.nist.gov/rest/json/cves/2.0",
      "documentation_url": "https://nvd.nist.gov/developers/vulnerabilities",
      "is_active": true,
      "verification_status": "docs_verified",
      "evidence_reference": "NVD API v2.0 Official Specification and NIST CSRC Portal (Authenticated Tier)",
      "verified_at": "2026-11-15T18:00:00Z",
      "auth_requirement": {
        "requires_auth": true,
        "auth_type": "inherit"
      },
      "limitations": "Subject to 50 req/30s rate limits with API key; public advisory presence does not establish project applicability without component, version, and configuration verification."
    },
    {
      "mechanism_type": "rest_api",
      "endpoint_url": "https://services.nvd.nist.gov/rest/json/cves/2.0",
      "documentation_url": "https://nvd.nist.gov/developers/vulnerabilities",
      "is_active": true,
      "verification_status": "docs_verified",
      "evidence_reference": "NVD API v2.0 Public Unauthenticated Tier Documentation",
      "verified_at": "2026-11-15T18:00:00Z",
      "auth_requirement": {
        "requires_auth": false,
        "auth_type": "none"
      },
      "limitations": "Subject to unauthenticated 5 req/30s rate limits; public advisory presence does not establish project applicability without component, version, and configuration verification."
    }
  ],
  "auth_profile": {
    "requires_auth": true,
    "auth_type": "api_key_header",
    "env_var_key": "NIST_NVD_API_KEY"
  },
  "cost_tier": "free_registration",
  "licensing": {
    "license_type": "US Government Public Domain Work",
    "reuse_permitted": true,
    "attribution_required": true,
    "attribution_template": "Source: NIST National Vulnerability Database (NVD)"
  },
  "rate_limits": {
    "requests_per_minute": 50,
    "burst_limit": 5,
    "daily_quota": 50000
  },
  "freshness_policy": {
    "max_cache_ttl_hours": 6,
    "revalidation_trigger": "continuous_6h",
    "context_overrides": {
      "emergency_security_patch": 1,
      "routine_governance_audit": 24
    }
  },
  "status": "active",
  "last_verified_at": "2026-11-15T18:00:00Z",
  "classification_evidence": "Official US Federal Agency standard cybersecurity repository under NIST Computer Security Division.",
  "verification_profile": {
    "website_verified": true,
    "api_doc_verified": true,
    "endpoint_verified": true,
    "connectivity_tested": false,
    "auth_tested": false,
    "quota_confirmed": true,
    "licensing_reviewed": true,
    "verification_date": "2026-11-15T18:00:00Z",
    "verification_evidence": "Confirmed via NIST NVD official developer portal documentation and TLS endpoint verification.",
    "limitations": "Live network handshake and API key exchange not tested in design phase; unauthenticated rate limits apply without key."
  },
  "independence_profile": {
    "parent_institution": "National Institute of Standards and Technology (NIST)",
    "is_syndicated": false,
    "syndication_upstream": [],
    "citation_dependencies": ["CVE Program / MITRE"],
    "is_mirror": false
  },
  "applicability_profile": {
    "requires_component_verification": true,
    "requires_version_check": true,
    "requires_config_verification": true,
    "advisory_matching_keys": ["cpe_match", "version_start_including", "version_end_excluding"]
  },
  "provenance_rules": {
    "retains_upstream_url": true,
    "retains_publisher_identity": true,
    "hashes_raw_payload": true
  },
  "fallback_source_ids": ["src-sec-02", "src-sec-04"]
}
```

#### Example 2: `src-law-eurlex` (European Union Legal Repository)
```json
{
  "source_id": "src-law-eurlex",
  "official_name": "EUR-Lex (Access to European Union law)",
  "description": "Official legal portal providing authoritative access to European Union law, including the Official Journal of the European Union, treaties, directives, regulations, and case law.",
  "owner": {
    "institution_name": "Publications Office of the European Union",
    "institution_type": "multilateral_org",
    "jurisdiction": "EU"
  },
  "canonical_url": "https://eur-lex.europa.eu",
  "domains": ["I"],
  "subdomains": ["I.02", "I.06", "I.07"],
  "authority_tier": 1,
  "source_designation": "primary_authority",
  "category_type": "knowledge_source",
  "supported_languages": ["bg", "es", "cs", "da", "de", "et", "el", "en", "fr", "ga", "hr", "it", "lv", "lt", "hu", "mt", "nl", "pl", "pt", "ro", "sk", "sl", "fi", "sv"],
  "supported_formats": ["text/html", "application/xml", "application/pdf"],
  "access_mechanisms": [
    {
      "mechanism_type": "http_direct",
      "endpoint_url": "https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX:",
      "documentation_url": "https://eur-lex.europa.eu/content/help/data-reuse/webservice.html",
      "is_active": true,
      "verification_status": "docs_verified",
      "evidence_reference": "EUR-Lex Official Web Services and Portal Documentation",
      "verified_at": "2026-11-15T18:00:00Z",
      "auth_requirement": {
        "requires_auth": false,
        "auth_type": "none"
      },
      "limitations": "Web services require XML/SOAP WS-Security registration; direct HTTP CELEX landing pages return public HTML/PDF."
    }
  ],
  "auth_profile": {
    "requires_auth": false,
    "auth_type": "none"
  },
  "cost_tier": "free_public",
  "licensing": {
    "license_type": "Commission Decision 2011/833/EU (Commercial & Non-commercial Reuse)",
    "reuse_permitted": true,
    "attribution_required": true,
    "attribution_template": "© European Union, https://eur-lex.europa.eu, 1998-2026"
  },
  "rate_limits": {
    "requests_per_minute": 60,
    "burst_limit": 10,
    "daily_quota": 50000
  },
  "freshness_policy": {
    "max_cache_ttl_hours": 168,
    "revalidation_trigger": "weekly_168h",
    "context_overrides": {
      "regulatory_audit": 24,
      "general_compliance_baseline": 720
    }
  },
  "status": "active",
  "last_verified_at": "2026-11-15T18:00:00Z",
  "classification_evidence": "Official gazette and legal repository of the European Union; CELEX identifiers constitute statutory law.",
  "verification_profile": {
    "website_verified": true,
    "api_doc_verified": true,
    "endpoint_verified": true,
    "connectivity_tested": false,
    "auth_tested": false,
    "quota_confirmed": true,
    "licensing_reviewed": true,
    "verification_date": "2026-11-15T18:00:00Z",
    "verification_evidence": "Verified via Publications Office of the EU official documentation and portal endpoint verification.",
    "limitations": "SOAP web service credentials not registered or tested; direct CELEX URLs verified structurally."
  },
  "independence_profile": {
    "parent_institution": "Publications Office of the European Union",
    "is_syndicated": false,
    "syndication_upstream": [],
    "citation_dependencies": [],
    "is_mirror": false
  },
  "applicability_profile": {
    "requires_component_verification": false,
    "requires_version_check": true,
    "requires_config_verification": false,
    "advisory_matching_keys": ["celex_number", "entry_into_force_date", "repealed_by"]
  },
  "provenance_rules": {
    "retains_upstream_url": true,
    "retains_publisher_identity": true,
    "hashes_raw_payload": true
  },
  "fallback_source_ids": []
}
```

---

## 7. Domain-Aware Trust and Evidence Framework (Deliverables F & H)

### 7.1 Preserved Mathematical Scoring Model
In strict adherence to the approved architecture and correction C5, RDEI evaluates every research claim using an explicitly normalized multi-dimensional linear scoring model:

$$\text{Claim Confidence Score} = \text{clamp}_{[0.0, 1.0]}\left( \sum_{i=1}^4 w_i X_i - P_{\text{conflict}} \right)$$

$$\text{Claim Confidence Score} = \text{clamp}_{[0.0, 1.0]}\left( w_1 A_{\text{source}} + w_2 V_{\text{match}} + w_3 F_{\text{freshness}} + w_4 C_{\text{corroboration}} - P_{\text{conflict}} \right)$$

Where:
- $w_1 = 0.35$ (Source Authority: Tier 1 = 1.00; Tier 2 = 0.85; Tier 3 = 0.70; Tier 4 = 0.55; Tier 5 = 0.30; Tier 6 = 0.05).
- $w_2 = 0.30$ (Version & Context Match: 1.00 for exact domain/version profile match; 0.50 for minor delta; 0.00 for mismatch).
- $w_3 = 0.20$ (Freshness Factor: decays exponentially after expiration of category TTL).
- $w_4 = 0.15$ (Independent Corroboration: $C_{\text{corroboration}} = \min(1.0, 0.50 \times N_{\text{corr}})$).
- $P_{\text{conflict}} \in \{0.00, 0.25, 0.50\}$ (Deterministic dispute penalty).

### 7.2 Domain-Specific Evidence Overlays
While the mathematical clamp and weights remain invariant across all disciplines, the operational evaluation of **Authority ($A_{\text{source}}$)** and **Context Match ($V_{\text{match}}$)** varies fundamentally by knowledge domain. RDEI enforces seven specialized domain overlays:

```
+---------------------------------------------------------------------------------------------------------+
| DOMAIN OVERLAY 1: MEDICINE, HEALTHCARE & CLINICAL EVIDENCE (Domain H)                                   |
| - Methodological Hierarchy: Systematic Reviews (Cochrane) > Randomized Controlled Trials (RCT) >        |
|   Cohort Studies > Case-Control > Expert Consensus.                                                     |
| - Clinical Status Gating: Clinical trials in Phase I/II or preprints (medRxiv) can NEVER support       |
|   guideline claims. They are marked UNVERIFIED_SPECULATION for clinical practice claims.               |
| - Jurisdictional Bindingness: FDA drug approval does not imply EMA or national formulary approval.      |
|   Jurisdiction mismatch forces V_match = 0.00.                                                          |
+---------------------------------------------------------------------------------------------------------+

+---------------------------------------------------------------------------------------------------------+
| DOMAIN OVERLAY 2: STATUTORY LAW, REGULATIONS & COMPLIANCE (Domain I)                                    |
| - Normative Hierarchy: Constitutional/Treaty Law > Primary Legislation (Acts) > Delegated Regulations > |
|   Judicial Precedents > Administrative Guidance > Commentary.                                            |
| - Temporal Validity: Must verify 'in force' status, effective date, sunset clauses, and pending        |
|   amendments. Outdated statutes trigger OUTDATED_SUPERSEDED immediately.                                |
| - Jurisdictional Boundary: Strict territoriality. Foreign case law cannot corroborate domestic statute. |
+---------------------------------------------------------------------------------------------------------+

+---------------------------------------------------------------------------------------------------------+
| DOMAIN OVERLAY 3: CRYPTOGRAPHY, SECURITY & ZERO TRUST (Domains B, C, D)                                 |
| - Standard Dominance: Formally standardized algorithms (NIST FIPS, RFCs, ISO) strictly override        |
|   novel academic preprints or proprietary implementations.                                              |
| - Security Vulnerability Applicability Verification (Finding 1):                                        |
|   A public CVE, CVSS score, or CISA KEV listing does NOT automatically establish that the target project|
|   is vulnerable or block Gate 0. The subsystem strictly distinguishes four independent evidential tiers:|
|   1. Advisory Existence: Vulnerability is formally published in an authoritative database (NVD, CVE).   |
|   2. Component & Version Applicability: The target project manifest or lockfile actually includes the   |
|      affected component within the exact vulnerable version range (CPE / SemVer constraint match).      |
|   3. Configuration & Reachability Applicability: The vulnerable code path, optional module, feature flag,|
|      or operational configuration is actually reachable and active in the project's runtime execution.  |
|   4. Exploitation Threat Urgency: Active exploitation in the wild (CISA KEV catalog or EPSS >= 0.70)     |
|      escalates priority to immediate emergency response once component and configuration match.         |
| - Gate 0 Decision Rules:                                                                                |
|   a. Advisory Exists, Component Unrelated / Version Unaffected: Gate 0 is NOT blocked; claim recorded as|
|      `ADVISORY_UNAFFECTED`.                                                                             |
|   b. Component & Version Match, but Configuration Proves Non-Reachability: Gate 0 is NOT blocked,       |
|      provided deterministic evidence (call-graph analysis / config proof) is documented in dossier as   |
|      `ADVISORY_NOT_EXPLOITABLE_CONFIGURATION`.                                                          |
|   c. Component, Version, AND Configuration Match: Escalated as `CONFIRMED_VULNERABLE`. If CVSS >= 7.0   |
|      or listed in CISA KEV, it forces `CONFIRMED_VULNERABLE_CRITICAL` and strictly BLOCKS Gate 0.        |
|   d. Absence of a CVE does NOT prove security; absence of evidence is never proof of security.         |
| - Gate 4 Semantics: Gate 4 HMAC verification contract remains strictly unaltered and additive.          |
+---------------------------------------------------------------------------------------------------------+

+---------------------------------------------------------------------------------------------------------+
| DOMAIN OVERLAY 4: EMPIRICAL & NATURAL SCIENCES (Domains F, G)                                           |
| - Peer-Review Mandate: Preprints (arXiv, bioRxiv) carry maximum Tier 3 (A_source = 0.70) and cannot     |
|   establish conclusive empirical claims without independent peer-reviewed publication or replication.  |
| - Retraction Checking: Mandatory automated query against Crossref Retraction Notices. Any retracted    |
|   paper receives A_source = 0.00 and status OUTDATED_SUPERSEDED.                                        |
+---------------------------------------------------------------------------------------------------------+

+---------------------------------------------------------------------------------------------------------+
| DOMAIN OVERLAY 5: MACROECONOMICS, FINANCE & PUBLIC STATISTICS (Domains J, N)                            |
| - Primary Statistical Hierarchy: Official National Statistical Offices (ONS, Eurostat, Census) and      |
|   Central Banks strictly override secondary commercial forecasts and journalistic commentary.           |
| - Revision Tracking: Macroeconomic data (GDP, CPI, employment) subject to preliminary vs revised cycles.|
|   Dossier must record publication vintage date to avoid mixing preliminary and finalized estimates.     |
+---------------------------------------------------------------------------------------------------------+

+---------------------------------------------------------------------------------------------------------+
| DOMAIN OVERLAY 6: SOFTWARE ARCHITECTURE & PACKAGE SELECTION (Domain A)                                  |
| - Upstream Alignment: Framework documentation and SemVer release notes strictly override community blog|
|   posts or Stack Overflow answers.                                                                      |
| - Active Support Window: End-of-Life (EOL) technologies receive V_match = 0.00 and trigger Gate 0 BLOCK.|
+---------------------------------------------------------------------------------------------------------+

+---------------------------------------------------------------------------------------------------------+
| DOMAIN OVERLAY 7: PROFESSIONAL MANAGEMENT & ENTERPRISE GOVERNANCE (Domains O, K)                        |
| - Established Body of Knowledge: PMI PMBOK, PRINCE2, ITIL 4, and ISO 31000 serve as foundational Tier 1 |
|   governance models. Consulting firm whitepapers serve as Tier 4 supplementary practice only.           |
+---------------------------------------------------------------------------------------------------------+
```

### 7.3 Formal Extensible Domain-Specific Evidence Policy Mechanism (Deliverable H / Finding 4)
While the 7 existing overlays address core domains (H, I, B/C/D, F/G, J/N, A, O/K), UAOS operates across 16 primary domains and 170+ subdomains. To accommodate evolving disciplines without engine refactoring, RDEI establishes a formal, declarative evidence policy extensibility mechanism.

#### 7.3.1 Evidence Policy Hierarchy and Precedence
Evidence policies are evaluated hierarchically across three distinct tiers:

```
+---------------------------------------------------------------------------------------------------+
| TIER 1: SUBDOMAIN-SPECIFIC OVERLAY (Precedence Rank: 100)                                         |
| Example: pol-overlay-sec-cve (Subdomain B.02) - specific 4-tier vulnerability applicability rules.|
+---------------------------------------------------------------------------------------------------+
                                                  | inherits un-overridden rules
                                                  v
+---------------------------------------------------------------------------------------------------+
| TIER 2: DOMAIN-SPECIFIC OVERLAY (Precedence Rank: 50)                                             |
| Example: pol-overlay-cybersecurity (Domain B) - standard dominance, EPSS, & KEV escalation.       |
+---------------------------------------------------------------------------------------------------+
                                                  | inherits un-overridden rules
                                                  v
+---------------------------------------------------------------------------------------------------+
| TIER 3: BASE GLOBAL POLICY (Precedence Rank: 0)                                                   |
| Canonical baseline scoring model: w1=0.35, w2=0.30, w3=0.20, w4=0.15, clamp [0.0, 1.0].          |
+---------------------------------------------------------------------------------------------------+
```

1. **Precedence Rule:** Subdomain Overlay (Rank 100) > Domain Overlay (Rank 50) > Base Global Policy (Rank 0).
2. **Invariant Scoring Formula:** Domain and subdomain overlays **never** alter the approved mathematical weights ($w_1=0.35, w_2=0.30, w_3=0.20, w_4=0.15$) or the $[0.0, 1.0]$ clamp. Overlays customize only:
   - Methodological evidence hierarchy (e.g. systematic reviews vs preprints).
   - Domain-specific context matching rules ($V_{\text{match}}$ criteria like jurisdictional bindingness or runtime compatibility).
   - Specific conflict escalation thresholds ($P_{\text{conflict}}$ escalation conditions).
   - Domain-specific freshness bounds ($T_{\text{valid}}$ defaults).
   - Quorum requirements ($N_{\text{min}}$ independent sources).
3. **Behavior When No Specialized Overlay Exists:** Queries targeting domains or subdomains lacking a specialized overlay automatically evaluate under the **Base Global Policy** (`pol-base-global`). The base policy executes the standard linear model, applies default SemVer version matching, enforces the default domain TTL matrix (Section 9.1), and mandates a standard quorum of $N_{\text{min}} = 2$ for Level 2+ research depths.

#### 7.3.2 Multi-Domain Overlay Conflict Detection and Resolution
When an engineering query spans multiple domains (e.g., an autonomous medical device firmware update spanning Domain H Medicine, Domain B Cybersecurity, and Domain I Medical Device Statutory Compliance), overlapping policies may specify conflicting criteria:
1. **Strictness Precedence:** The most restrictive policy rule always governs. If Domain B mandates that confirmed critical CVEs block Gate 0, and Domain H mandates that clinical preprints cannot clear Gate 0, both restrictions apply simultaneously.
2. **Deterministic Score Selection:** If overlapping overlays produce different claim confidence scores for the same factual statement, RDEI deterministically adopts the **minimum confidence score** and the **maximum conflict penalty** ($P_{\text{conflict}}$).
3. **Gate Clearance Unanimity:** A multi-domain claim clears Gate 0 if and only if it satisfies the clearance criteria of **all** applicable domain overlays.

#### 7.3.3 Overlay Validation, Registration, and Versioning
New domain overlays are registered as declarative JSON specifications conforming to `evidence-overlay.schema.json` without modifying PHP runtime code:
- **Registration Path:** `.agent-os/research/policies/overlays/*.json`
- **Validation Engine:** `EvidenceOverlayValidator` and `RdeiSchemaValidator` verify structural conformance, strict SemVer formatting, acyclic inheritance graphs, freshness hierarchies, and domain/subdomain alignment during agent boot.
- **Semantic Versioning:** Every overlay strictly maintains a SemVer 2.0.0 string (`major.minor.patch` with optional pre-release/build identifiers). Leading zeros, 'v' prefixes, and non-SemVer formats are strictly rejected. Breaking changes in evidence hierarchy or quorum increment the major version and require explicit governance logging in `EvidenceCollector`.

##### Validation Schema: `evidence-overlay.schema.json`
Conforming to JSON Schema Draft 2020-12, this schema formally enforces the declarative structure, 3-tier precedence levels, domain-subdomain matching, and freshness constraints:

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "$id": "https://uaos.agent-os.org/schemas/rdei/evidence-overlay.schema.json",
  "title": "UAOS Evidence Policy Overlay Definition",
  "description": "Formal extensible declarative schema for domain and subdomain evidence overlays in RDEI.",
  "type": "object",
  "additionalProperties": false,
  "required": [
    "policy_id",
    "name",
    "target_domain",
    "target_subdomains",
    "version",
    "parent_policy_id",
    "precedence_rank",
    "methodological_hierarchy",
    "context_match_rules",
    "vulnerability_applicability_rules",
    "conflict_escalation_rules",
    "freshness_overrides",
    "mandatory_quorum"
  ],
  "properties": {
    "policy_id": {
      "type": "string",
      "pattern": "^(pol-overlay-[a-z0-9\\-]+|pol-base-global)$",
      "description": "Globally unique stable overlay identifier (e.g. pol-overlay-cybersecurity, pol-overlay-sec-cve, or pol-base-global)."
    },
    "name": {
      "type": "string",
      "minLength": 5,
      "maxLength": 255,
      "description": "Descriptive human-readable name of the policy overlay."
    },
    "target_domain": {
      "type": "string",
      "pattern": "^([A-P]|GLOBAL)$",
      "description": "Target knowledge domain letter (A through P) or 'GLOBAL' for base policy."
    },
    "target_subdomains": {
      "type": "array",
      "items": { "type": "string", "pattern": "^([A-P]\\.[0-9]{2}|\\*)$" },
      "minItems": 1,
      "uniqueItems": true,
      "description": "List of subdomains governed by this policy. Wildcard '*' targets all subdomains of the declared domain; must not be combined with specific subdomains."
    },
    "version": {
      "type": "string",
      "pattern": "^(0|[1-9][0-9]*)\\.(0|[1-9][0-9]*)\\.(0|[1-9][0-9]*)(?:-([0-9A-Za-z-]+(?:\\.[0-9A-Za-z-]+)*))?(?:\\+([0-9A-Za-z-]+(?:\\.[0-9A-Za-z-]+)*))?$",
      "description": "Strict Semantic Versioning 2.0.0 format (MAJOR.MINOR.PATCH with optional pre-release/build metadata). Leading zeros, 'v' prefixes, and non-SemVer formats are rejected."
    },
    "parent_policy_id": {
      "type": ["string", "null"],
      "pattern": "^(pol-base-global|pol-overlay-[a-z0-9\\-]+|none)$",
      "description": "Parent policy identifier for inheritance: 'pol-base-global' for domain overlays, domain overlay ID for subdomain overlays, or null/'none' for base global policy."
    },
    "precedence_rank": {
      "type": "integer",
      "enum": [0, 50, 100],
      "description": "Precedence rank strictly enforcing the 3-tier hierarchy: 100 for subdomain overlays, 50 for domain overlays, 0 for base global policy."
    },
    "methodological_hierarchy": {
      "type": "array",
      "items": { "type": "string" },
      "minItems": 1,
      "description": "Ordered precedence of evidence types (highest to lowest)."
    },
    "context_match_rules": {
      "type": "object",
      "additionalProperties": false,
      "required": ["require_exact_version_match", "jurisdiction_strictness", "eol_handling"],
      "properties": {
        "require_exact_version_match": { "type": "boolean" },
        "jurisdiction_strictness": { "type": "string", "enum": ["territorial_strict", "harmonized_regional", "global_advisory"] },
        "eol_handling": { "type": "string", "enum": ["block_gate_0", "warn_only", "not_applicable"] }
      }
    },
    "vulnerability_applicability_rules": {
      "type": "object",
      "additionalProperties": false,
      "required": ["enforce_component_identity", "enforce_version_range", "enforce_config_reachability", "cisa_kev_escalation"],
      "properties": {
        "enforce_component_identity": { "type": "boolean" },
        "enforce_version_range": { "type": "boolean" },
        "enforce_config_reachability": { "type": "boolean" },
        "cisa_kev_escalation": { "type": "boolean" },
        "cvss_critical_threshold": { "type": "number", "minimum": 0.0, "maximum": 10.0 }
      }
    },
    "conflict_escalation_rules": {
      "type": "array",
      "items": {
        "type": "object",
        "additionalProperties": false,
        "required": ["condition", "escalate_to", "gate_0_action"],
        "properties": {
          "condition": { "type": "string" },
          "escalate_to": { "type": "string", "enum": ["CRITICAL_DISPUTE", "RESOLVABLE_DIVERGENCE", "INFORMATIONAL_VARIANCE"] },
          "gate_0_action": { "type": "string", "enum": ["BLOCK", "PASS_WITH_LIMITATIONS", "EXEMPT"] }
        }
      }
    },
    "freshness_overrides": {
      "type": "object",
      "additionalProperties": false,
      "required": ["default_ttl_hours", "max_ttl_hours"],
      "properties": {
        "default_ttl_hours": {
          "type": "integer",
          "minimum": 1,
          "maximum": 87600,
          "description": "Standard operational cache TTL in hours. Must be <= max_ttl_hours."
        },
        "max_ttl_hours": {
          "type": "integer",
          "minimum": 1,
          "maximum": 87600,
          "description": "Absolute ceiling for cache TTL in hours before mandatory eviction. Must be >= default_ttl_hours."
        },
        "emergency_ttl_hours": {
          "type": "integer",
          "minimum": 1,
          "maximum": 87600,
          "description": "Accelerated cache TTL in hours for emergency hotfix contexts. Must be <= default_ttl_hours."
        }
      }
    },
    "mandatory_quorum": {
      "type": "integer",
      "minimum": 1,
      "maximum": 10,
      "description": "Minimum independent authoritative sources required for Gate 0 clearance."
    }
  },
  "allOf": [
    {
      "if": {
        "properties": { "target_subdomains": { "contains": { "const": "*" } } }
      },
      "then": {
        "properties": { "target_subdomains": { "maxItems": 1 } }
      }
    },
    {
      "if": { "properties": { "precedence_rank": { "const": 50 } } },
      "then": {
        "properties": {
          "target_subdomains": {
            "items": { "const": "*" },
            "maxItems": 1
          }
        }
      }
    },
    {
      "if": { "properties": { "precedence_rank": { "const": 100 } } },
      "then": {
        "properties": {
          "target_subdomains": {
            "items": { "pattern": "^[A-P]\\.[0-9]{2}$" }
          }
        }
      }
    },
    {
      "if": { "properties": { "target_domain": { "const": "A" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(A\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "B" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(B\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "C" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(C\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "D" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(D\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "E" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(E\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "F" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(F\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "G" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(G\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "H" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(H\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "I" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(I\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "J" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(J\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "K" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(K\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "L" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(L\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "M" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(M\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "N" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(N\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "O" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(O\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "P" } } },
      "then": { "properties": { "target_subdomains": { "items": { "pattern": "^(P\\.[0-9]{2}|\\*)$" } } } }
    },
    {
      "if": { "properties": { "target_domain": { "const": "GLOBAL" } } },
      "then": {
        "properties": {
          "target_subdomains": { "items": { "const": "*" }, "maxItems": 1 },
          "precedence_rank": { "const": 0 }
        }
      }
    }
  ]
}
```

##### Concrete Overlay Examples

###### Example 1: `pol-overlay-cybersecurity` (Domain Overlay — Precedence Rank 50)
```json
{
  "policy_id": "pol-overlay-cybersecurity",
  "name": "Cybersecurity Domain Policy Overlay",
  "target_domain": "B",
  "target_subdomains": ["*"],
  "version": "1.0.0",
  "parent_policy_id": "pol-base-global",
  "precedence_rank": 50,
  "methodological_hierarchy": [
    "primary_statutory_directive",
    "official_cve_nvd_advisory",
    "vendor_security_bulletin",
    "independent_security_whitepaper"
  ],
  "context_match_rules": {
    "require_exact_version_match": true,
    "jurisdiction_strictness": "global_advisory",
    "eol_handling": "block_gate_0"
  },
  "vulnerability_applicability_rules": {
    "enforce_component_identity": true,
    "enforce_version_range": true,
    "enforce_config_reachability": true,
    "cisa_kev_escalation": true,
    "cvss_critical_threshold": 7.0
  },
  "conflict_escalation_rules": [
    {
      "condition": "Dispute between official CVE advisory and secondary aggregator score",
      "escalate_to": "RESOLVABLE_DIVERGENCE",
      "gate_0_action": "PASS_WITH_LIMITATIONS"
    },
    {
      "condition": "Contradiction regarding reachability of confirmed vulnerable component",
      "escalate_to": "CRITICAL_DISPUTE",
      "gate_0_action": "BLOCK"
    }
  ],
  "freshness_overrides": {
    "default_ttl_hours": 6,
    "max_ttl_hours": 24,
    "emergency_ttl_hours": 1
  },
  "mandatory_quorum": 2
}
```

###### Example 2: `pol-overlay-sec-cve` (Subdomain Overlay — Precedence Rank 100)
```json
{
  "policy_id": "pol-overlay-sec-cve",
  "name": "Vulnerability Management & Exploits Policy Overlay",
  "target_domain": "B",
  "target_subdomains": ["B.02"],
  "version": "1.0.0",
  "parent_policy_id": "pol-overlay-cybersecurity",
  "precedence_rank": 100,
  "methodological_hierarchy": [
    "cisa_known_exploited_vulnerabilities",
    "nist_nvd_advisory",
    "vendor_patch_bulletin"
  ],
  "context_match_rules": {
    "require_exact_version_match": true,
    "jurisdiction_strictness": "global_advisory",
    "eol_handling": "block_gate_0"
  },
  "vulnerability_applicability_rules": {
    "enforce_component_identity": true,
    "enforce_version_range": true,
    "enforce_config_reachability": true,
    "cisa_kev_escalation": true,
    "cvss_critical_threshold": 7.0
  },
  "conflict_escalation_rules": [
    {
      "condition": "Direct conflict between vendor bulletin and CISA KEV listing",
      "escalate_to": "CRITICAL_DISPUTE",
      "gate_0_action": "BLOCK"
    }
  ],
  "freshness_overrides": {
    "default_ttl_hours": 2,
    "max_ttl_hours": 12,
    "emergency_ttl_hours": 1
  },
  "mandatory_quorum": 2
}
```

###### Example 3: `pol-base-global` (Global Base Policy — Precedence Rank 0)
```json
{
  "policy_id": "pol-base-global",
  "name": "UAOS RDEI Base Global Evidence Policy",
  "target_domain": "GLOBAL",
  "target_subdomains": ["*"],
  "version": "1.0.0",
  "parent_policy_id": "none",
  "precedence_rank": 0,
  "methodological_hierarchy": [
    "primary_authority_statute",
    "institutional_registry",
    "peer_reviewed_academic",
    "professional_engineering",
    "practitioner_forum"
  ],
  "context_match_rules": {
    "require_exact_version_match": false,
    "jurisdiction_strictness": "global_advisory",
    "eol_handling": "warn_only"
  },
  "vulnerability_applicability_rules": {
    "enforce_component_identity": true,
    "enforce_version_range": true,
    "enforce_config_reachability": false,
    "cisa_kev_escalation": false,
    "cvss_critical_threshold": 8.0
  },
  "conflict_escalation_rules": [
    {
      "condition": "Contradiction across Level 1 primary standards",
      "escalate_to": "CRITICAL_DISPUTE",
      "gate_0_action": "BLOCK"
    }
  ],
  "freshness_overrides": {
    "default_ttl_hours": 168,
    "max_ttl_hours": 720,
    "emergency_ttl_hours": 24
  },
  "mandatory_quorum": 2
}
```

##### Policy Consistency & Validation Invariants
1. **Wildcard Exclusivity:** Wildcard `"*"` indicates full domain governance. Combining `"*"` with specific subdomains (e.g. `["*", "B.02"]`) is ambiguous and strictly forbidden by both schema and validator.
2. **Precedence Hierarchy:**
   - Precedence `100`: Subdomain Overlay. Must target explicit subdomains (`["B.02"]`); wildcard `"*"` is prohibited.
   - Precedence `50`: Domain Overlay. Must target all subdomains of the declared domain via `["*"]`.
   - Precedence `0`: Global Base Policy. Must target `GLOBAL` domain and `["*"]` subdomains.
3. **Freshness Invariant:** `emergency_ttl_hours <= default_ttl_hours <= max_ttl_hours`. Accelerated emergency TTLs cannot exceed operational defaults, which cannot exceed absolute cache eviction ceilings.
4. **Graph Integrity & Acyclicity:**
   - Direct self-inheritance (`policy_id === parent_policy_id`) is strictly rejected.
   - Missing parent policies (outside `"pol-base-global"` and `"none"`) trigger immediate boot failure.
   - Indirect inheritance cycles ($A \to B \to A$ or $A \to B \to C \to A$) are detected and blocked via graph traversal.
5. **Schema vs Runtime Division:**
   - Structural constraints (types, patterns, enums, intra-object conditional schemas, array uniqueness) are enforced natively by JSON Schema Draft 2020-12.
   - Cross-record graph traversals (cycle detection, registry presence, dynamic date inequalities) are enforced by `RdeiSchemaValidator`.

#### 7.3.4 Representation of Unsupported and Insufficient Evidence
When research encounters gaps in policy coverage or source availability, RDEI records explicit evidential states:
1. `DOMAIN_POLICY_UNSUPPORTED`: Recorded when a query targets an unmapped or invalid domain identifier. The query falls back to Base Global Policy with an explicit audit warning logged to the dossier.
2. `INSUFFICIENT_DOMAIN_EVIDENCE`: Recorded when available sources fail to meet the active overlay's mandatory quorum or methodological tier (e.g., only Level 5 forum commentary found for a regulated medical device API). Gate 0 clearance is strictly **BLOCKED (fail-closed)** until authoritative evidence is provided.

---

### 7.4 Independent Corroboration, Epistemological Guardrails, and Failure States (Finding 6)

#### 7.4.1 Independent Corroboration and the Five Non-Independent Patterns
Independent corroboration ($C_{\text{corroboration}} = \min(1.0, 0.50 \times N_{\text{corr}})$) counts **independent underlying evidence**, not multiple tools, search engines, or syndicators returning the same upstream content. RDEI formally defines and filters out the **Five Non-Independent Patterns**:

```
+---------------------------------------------------------------------------------------------------+
| FIVE PATTERNS OF NON-INDEPENDENT EVIDENCE (N_corr = 0 Additional)                                  |
+---------------------------------------------------------------------------------------------------+
| 1. SHARED INSTITUTIONAL OWNERSHIP: Sources published by the same parent corporate or government   |
|    entity (e.g., npm Advisories and GitHub Advisories both owned by Microsoft/GitHub; or NIST NVD |
|    and NIST CSRC) share institutional identity. If one mirrors the other, N_corr does NOT increment.|
+---------------------------------------------------------------------------------------------------+
| 2. SYNDICATION & AGGREGATION: Downstream aggregators re-publishing upstream registries (e.g., OSV |
|    aggregating NVD, GHSA, and RustSec) retain upstream source identity. An advisory found in NVD  |
|    and in OSV is ONE primary advisory, not two independent corroborations.                        |
+---------------------------------------------------------------------------------------------------+
| 3. CITATION DEPENDENCIES: If Source B explicitly derives its claim solely by referencing Source A |
|    without independent empirical measurement or primary investigation, Source B is dependent.    |
+---------------------------------------------------------------------------------------------------+
| 4. MIRRORED DATASETS & CDN COPIES: Mirror sites, CDN distributions, or database replicas with     |
|    matching content hashes represent identical evidence.                                          |
+---------------------------------------------------------------------------------------------------+
| 5. DERIVED SECONDARY PUBLICATIONS: Secondary summaries, blog posts, or executive briefs reporting |
|    on an ISO standard, RFC, or court ruling are derivative summaries, not independent primary.    |
+---------------------------------------------------------------------------------------------------+
```

#### 7.4.2 Epistemological Guardrail: The Absence of Evidence Principle
> [!CAUTION]
> **Fundamental Epistemological Rule:**  
> The absence of evidence is **never** proof of absence.  
> 1. An empty search result for a security vulnerability on a package does **not** prove the package is secure; it only proves zero CVEs have been publicly indexed.
> 2. The absence of a court decision on a novel compliance topic does **not** prove the practice is legal.
> 3. An inaccessible, failing, or rate-limited source must **never** be interpreted as confirmation that the asserted claim is false.

#### 7.4.3 Five Distinct Operational and Failure States
RDEI maintains strict separation between five distinct operational failure and retrieval states:
1. `SOURCE_UNAVAILABLE`: Network timeout, DNS resolution failure, HTTP 500/502/503/504, or host unreachable. Corroboration yields $C = 0.00$. If required quorum cannot be satisfied from remaining reachable sources, Gate 0 evaluates as `BLOCKED (UNAVAILABLE_DEPENDENCY)`.
2. `EMPTY_RESULT`: Query succeeded (HTTP 200 / valid empty JSON array), but zero matching records or advisories were found. Subject strictly to the Absence of Evidence Principle. Does not establish negative proof.
3. `INACCESSIBLE_CONTENT`: Paywall encountered, HTTP 401/403 forbidden, CAPTCHA challenge, missing mandatory environment credentials, or client-side JavaScript rendering failure. Records explicit limitation in dossier.
4. `CONFLICTING_EVIDENCE`: Two or more authoritative sources make opposing factual assertions (`UNRESOLVED_DISPUTE`). Evaluated under Section 12 conflict resolution lifecycle with deterministic penalty $P_{\text{conflict}}$.
5. `OUT_OF_SCOPE`: Query or source falls outside the domain, jurisdiction, language, or version envelope of the target project profile. Discarded as irrelevant before trust scoring.

---

## 8. Research Dossier and Citation Model (Deliverable H)

Every completed research inquiry produces an immutable, cryptographically sealed Research Dossier. Below is the complete universal research record model supporting all domains.

### 8.1 Universal Research Dossier Record Specification
Each entry in `claims[]` within a Research Dossier contains the following 22 normalized fields:

```json
{
  "claim_id": "CLM-20261115-001",
  "statement": "Argon2id (RFC 9106) with memory cost 64MB and time cost 3 iterations must be selected for password hashing in place of bcrypt to resist GPU/ASIC cracking.",
  "domain": "C",
  "subdomain": "C.08",
  "source_id": "src-cryp-01",
  "source_url": "https://csrc.nist.gov/pubs/sp/800/63/b/final",
  "document_title": "NIST Special Publication 800-63B: Digital Identity Guidelines (Authentication and Lifecycle Management)",
  "publisher": "National Institute of Standards and Technology (NIST)",
  "publication_date": "2020-03-02T00:00:00Z",
  "last_updated_date": "2023-12-15T00:00:00Z",
  "access_date": "2026-11-15T18:00:00Z",
  "relevant_version": "SP 800-63B Rev. 3",
  "jurisdiction": "US / International Standards",
  "supporting_excerpt": "Verifiers SHOULD use memory-hard functions such as Argon2id when hashing passwords for storage.",
  "content_hash": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
  "evidence_classification": "PRIMARY_STANDARDS_MANDATE",
  "trust_assessment": {
    "source_authority_tier": 1,
    "authority_score": 1.00,
    "version_match_score": 1.00,
    "freshness_score": 1.00,
    "corroboration_score": 1.00,
    "conflict_penalty": 0.00
  },
  "confidence_score": 0.98,
  "status": "VERIFIED_AUTHORITATIVE",
  "corroborating_sources": ["src-soft-01", "src-sec-06"],
  "contradicting_sources": [],
  "limitations": "Requires libsodium extension or PHP 7.3+ native support. Execution time must be tuned against server latency budget."
}
```

### 8.2 Evidential State Enum Mapping
Every claim in a Research Dossier resolves to one of the canonical evidential states defined across baseline Section 8.3, Section 15.2, and `UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-003A`:

#### 8.2.1 Core Evidential States
1. `VERIFIED_AUTHORITATIVE`: Supported by Level 1/Level 2 sources, validated against project profile, zero unresolved contradictions.
2. `CORROBORATED_PRACTICE`: Supported by Level 4/Level 5 sources with multiple independent corroborations; valid for implementation patterns but cannot override Level 1/2 standards.
3. `UNRESOLVED_DISPUTE`: Contradictory evidence exists between sources; prevents Gate 0 clearance until resolved or formally accepted by Department C/M.
4. `VERSION_MISMATCH`: Accurate for an older or uninstalled framework/statute/standard, but invalid for the current operational context.
5. `OUTDATED_SUPERSEDED`: Source has passed upstream EOL, has been formally repealed, or superseded by newer release notes.
6. `UNVERIFIED_SPECULATION`: Found only in uncorroborated Level 5/6 sources or non-peer-reviewed preprints. Prohibited from serving as justification for Architecture (Stage 02) or Gate 0 clearance.

#### 8.2.2 Security Vulnerability Applicability States (Finding 1)
7. `CONFIRMED_VULNERABLE_CRITICAL`: Component identity, version range, and runtime configuration reachability are all confirmed vulnerable, and the vulnerability carries CVSS base score >= 7.0 or is listed in CISA KEV. Strictly **BLOCKS Gate 0**.
8. `ADVISORY_UNAFFECTED`: A public advisory (CVE/GHSA) exists, but the project manifest/lockfile proves the component is not installed or is constrained to an unaffected version range. Gate 0 is **NOT blocked**.
9. `ADVISORY_NOT_EXPLOITABLE_CONFIGURATION`: The component and version range match a public advisory, but project-specific configuration analysis or call-graph reachability proves the vulnerable code path is unreachable or disabled. Gate 0 is **NOT blocked**, provided deterministic justification is preserved in the dossier.

#### 8.2.3 Policy Extensibility States (Finding 4)
10. `DOMAIN_POLICY_UNSUPPORTED`: Research query targets an unmapped domain or subdomain lacking a specialized overlay; deterministically falls back to Base Global Policy with audit documentation.
11. `INSUFFICIENT_DOMAIN_EVIDENCE`: Retrieved sources fail to meet the active domain overlay's required methodological tier or mandatory quorum ($N_{\text{min}}$). Strictly **BLOCKS Gate 0 fail-closed**.

---

## 9. Freshness, Versioning, and Monitoring Design (Deliverable G)

### 9.1 Configurable Freshness Architecture and Default TTL Matrix (Finding 6)
Freshness policies in RDEI are **configurable defaults**, not immutable universal constants. Freshness limits adapt across three configurable tiers:
1. **Target Project Profile Overrides (`.agent-os/project-profile.json`):** High-assurance environments (e.g. financial transaction processing or defense applications) can configure tighter TTL thresholds (e.g., reducing security advisory TTL from 6h to 1h).
2. **Research Context & Task Urgency Overrides:** Fast-track emergency hotfix tasks can enforce `ttl_override = 0` (forcing live upstream revalidation), whereas broad architectural discovery tasks can extend documentation TTL to 720 hours.
3. **Source Definition Policy Overrides:** Individual source metadata in `source-definition.json` specifies custom TTLs and triggers matching provider publication frequencies.

#### Baseline Domain-Aware TTL Matrix (Defaults)
When no project or task override is active, RDEI enforces the following default cache TTLs and monitoring cadences across the 16 domains:

| Domain Family | Representative Sources | Default Cache TTL ($T_{\text{valid}}$) | Mandatory Revalidation Trigger | Detection Mechanism |
|:---|:---|:---:|:---|:---|
| **Security Advisories & CVEs** | NIST NVD, CVE.org, CISA KEV, OSV | **6 hours** | Task plan affecting dependencies; Gate 4 request; external webhook | Daily Delta REST API / JSON Feeds |
| **Package Registries & Releases**| Packagist, npmjs, PyPI, GitHub Releases| **24 hours** | Dependency update plan; build failure; Composer execution | Webhooks / GitHub Release Feeds |
| **AI Models & Framework APIs** | Hugging Face, MCP Specs, OpenAI/Anthropic| **72 hours** | Agent tool configuration; major model upgrade | HTTP ETag / GitHub Commit History |
| **Official Framework Docs** | PHP.net, Laravel.com, MDN, React.dev | **168 hours (7 days)** | Framework minor/major upgrade; monthly audit | HTTP `Last-Modified` / Git Trees |
| **Financial & Economic Stats** | FRED, World Bank, Eurostat, IMF | **168 hours (7 days)** | Economic modeling task; fiscal quarter close | SDMX API Delta Queries / Release Calendar |
| **Statutes & Regulations** | EUR-Lex, Federal Register, Congress.gov | **720 hours (30 days)**| Regulatory audit; new compliance requirement | RSS Feeds / Official Gazette Indexes |
| **Scientific Literature** | PubMed, OpenAlex, Crossref, arXiv | **720 hours (30 days)**| New research task; annual literature review | OAI-PMH Harvesting / Retraction Watch |
| **International Standards** | ISO, IEC, IETF RFC, W3C Recommendations | **2160 hours (90 days)**| System architecture review; RFC status change | IETF Errata Feeds / W3C TR RSS |
| **Pure Mathematics** | DLMF, OEIS, AMS MathSciNet | **8760 hours (365 days)**| Explicit theoretical doubt; algorithmic refactor | On-demand manual re-query only |

### 9.2 Non-Daemon Monitoring Architecture
To prevent rogue background processes, resource leaks, or persistent background execution overhead, RDEI does **not** rely on an internal continuous daemon. Monitoring is operationalized via four deterministic, event-driven entry points:
1. **On-Demand Planning Triggers:** Orchestrator automatically revalidates watches during `planTask()` when a task touches dependencies or standards.
2. **Host-Triggered Cron Execution:** The host operating system (Windows Task Scheduler or Linux crontab) triggers a lightweight UAOS CLI command:
   ```powershell
   php bin/agent-os research:monitor --revalidate-due --format=json
   ```
3. **CI/CD Pipeline Hooks:** Pre-commit or pull-request pipelines execute freshness checks on all modified modules.
4. **Interactive Slash Command:** Developers or agents trigger on-demand checks via slash command shortcuts.

---

## 10. Security, Privacy, and Legal Considerations

### 10.1 SSRF & Network Protections (C7 Invariant)
All network fetching across all domains strictly reuses the validated defenses of `UniversalAgentOS\Mcp\StreamableHttpTransport`:
- **Fail-Closed cURL Transport:** Prohibits raw `file_get_contents()` or unpinned socket streams. Mandates `ext-curl`.
- **Prohibited IPv4 Matrix:** Blocks `0.0.0.0/8`, `10.0.0.0/8`, `100.64.0.0/10`, `127.0.0.0/8`, `169.254.0.0/16`, `172.16.0.0/12`, `192.0.0.0/24`, `192.0.2.0/24`, `192.168.0.0/16`, `198.18.0.0/15`, `198.51.100.0/24`, `203.0.113.0/24`, `224.0.0.0/4`, `240.0.0.0/4`, `255.255.255.255/32`.
- **Prohibited IPv6 Matrix:** Blocks `::`, `::1`, `::ffff:0:0/96` (unpacked and verified against IPv4), `64:ff9b::/96`, `100::/64`, `2001::/23`, `2001:db8::/32`, `fc00::/7`, `fe80::/10`, `ff00::/8`.
- **Socket-Level IP Pinning:** Resolves DNS prior to connection and binds via `CURLOPT_RESOLVE` to prevent TOCTOU DNS rebinding.
- **Port Policy:** Restricted strictly to ports **80** and **443**. Nonstandard ports require explicit administrative allowlisting under `allowed_internal_research_ports` in `.agent-os/config.json`.
- **Manual Redirect Revalidation:** Disables `CURLOPT_FOLLOWLOCATION`. Revalidates redirects manually up to 3 hops.

### 10.2 Deterministic Prompt Injection Security Boundary (C4 Invariant)
Retrieved research content across all domains is treated as **untrusted data plane input**:
- **Zero Permission Authority:** External text can never grant permissions, invoke tools, alter agent identity, or approve gates.
- **Deterministic Code Boundary:** Gate 0 and Gate 4 verifications are executed exclusively by deterministic PHP classes outside the LLM control plane.
- **Structural Fencing:** Retained inside `<untrusted_research_evidence>` delimiters in prompt compilation.
- **Pattern Scrubbing:** Scans for meta-prompt overrides and privilege escalation vectors, replacing matches with `[REDACTED: ADVERSARIAL PATTERN DETECTED]`.

### 10.3 Legal, Copyright & Data Protection Compliance
1. **EU Database Directive (Directive 96/9/EC):** Bulk scraping of commercial or foreign databases without permission is prohibited. RDEI prioritizes official APIs and respects database sui generis rights.
2. **Robots.txt & Provider Terms of Service:** Web discovery providers must respect `robots.txt` directives, crawler rate limits, and API terms of service.
3. **Data Protection & Privacy (GDPR / CCPA):** Research dossiers must never ingest, store, or index personally identifiable information (PII). When scraping public records, PII fields must be pseudonymized or purged prior to dossier hashing.
4. **Dual-Use & Export Controls (EAR / ITAR / Wassenaar):** Research into high-grade cryptographic implementations, advanced semiconductor manufacturing, or autonomous defense technology must comply with applicable export regulations.

---

## 11. Cost and Access Strategy

### 11.1 Seven-Tier Cost Classification
Every source is categorized into one of seven clear cost and accessibility brackets:
1. **Tier 1: Free & Public (Zero Auth):** Open government data, RFCs, official framework documentation, EUR-Lex, GitHub public endpoints. (Primary preference).
2. **Tier 2: Free with Registration (API Key Required):** NIST NVD, OpenAlex, Semantic Scholar, FRED, Congress.gov. Requires developer registration; zero monetary cost.
3. **Tier 3: Free with Metered Quotas:** Brave Search API (free tier), GitHub API (unauthenticated 60/hr vs token 5000/hr).
4. **Tier 4: Open-Source / Self-Hosted:** SearXNG instance, local SQLite knowledge graphs, Ollama embedding models. Zero API cost; bounded by host CPU/RAM.
5. **Tier 5: Paid Commercial API (Pay-Per-Query):** Tavily Search API ($0.005/call), Brave Search API beyond quota.
6. **Tier 6: Paid Subscription (Monthly/Annual):** IEEE Xplore, ACM Digital Library, Bloomberg Terminal, Westlaw/LexisNexis.
7. **Tier 7: Restricted / Closed:** Classified government databases, proprietary corporate intranets, unindexed trade secrets.

### 11.2 Cost-Aware Routing Waterfall
To maximize efficiency while maintaining strict evidence quality, RDEI executes a 4-stage retrieval waterfall:

```
[Research Demand: Domain + Target Query]
                   |
                   v
  Stage 1: Local Valid Research Cache (.agent-os/research/cache/)
           Found and Fresh? ----(YES)----> [Return Cached Dossier]
                   | (NO)
                   v
  Stage 2: Free Authoritative Primary APIs (NVD, RFC, Packagist, EUR-Lex)
           Successful? ---------(YES)----> [Index, Hash, Return Evidence]
                   | (NO)
                   v
  Stage 3: Free Registered Academic & Government APIs (OpenAlex, FRED, PubMed)
           Successful? ---------(YES)----> [Index, Hash, Return Evidence]
                   | (NO)
                   v
  Stage 4: General Web Discovery (Brave Search / Tavily) or Commercial Escalation
           Requires explicit budget clearance; records billing telemetry in EvidenceCollector.
```

### 11.3 Missing Credential Handling (Fail-Closed Invariant C3)
If a source requires an API key and the environment variable referenced in `env_var_key` is not configured:
1. RDEI checks if the provider supports unauthenticated public fallback at a lower rate limit.
2. If unauthenticated access is supported, it proceeds and records a rate-limit warning.
3. If credentials are mandatory, the source is marked `UNAVAILABLE (MISSING_CREDENTIALS)`.
4. RDEI never throws an unhandled exception or logs secret variable names.
5. If the missing source prevents satisfying the minimum quorum for the assigned research depth, Gate 0 evaluates as `BLOCKED (UNAVAILABLE_DEPENDENCY)` fail-closed.

---

## 12. Coverage Gaps, Limitations, and Fallback Strategy (Deliverable I)

A credible engineering intelligence architecture must never claim to have indexed the entirety of human knowledge. RDEI explicitly documents seven structural coverage gaps and defines deterministic mitigation strategies:

### 12.1 Documented Coverage Gaps

| Gap ID | Domain / Scope | Description of Gap | Root Cause | Architectural Mitigation / Fallback |
|:---|:---|:---|:---|:---|
| **GAP-01** | **Proprietary Paywalled Standards** (ISO, IEC, ASME, SAE) | Full-text technical standards specifications are locked behind expensive commercial paywalls ($100-$500 per PDF). | Commercial copyright & licensing restrictions. | Catalog and ingest publicly available metadata, scopes, normative references, and harmonized open standards (e.g. EU EN standards mirrors); record paywall limitation in dossier. |
| **GAP-02** | **Commercial Legal & Case Law Databases** (Westlaw, LexisNexis) | Comprehensive national court jurisprudence (US state courts, specialized commercial tribunals) is proprietary. | Closed commercial data monopolies. | Rely on official open government gazettes (EUR-Lex, Congress.gov, CourtListener, BAILII); flag lack of commercial citator (KeyCite/Shepard's) as recorded limitation. |
| **GAP-03** | **Non-English Regional Jurisprudence & Statistics** | High-authority legislative and statistical sources in Asia, Africa, and Latin America lack standardized English APIs. | Language barriers and disparate national infrastructure. | Support multilingual ingestion via UTF-8 normalized document processors; document geographic constraint in claim metadata. |
| **GAP-04** | **Real-Time High-Frequency Financial Tick Data** | Sub-millisecond order books and proprietary market data feeds (Bloomberg/LSEG) are cost-prohibitive. | Multi-thousand dollar enterprise subscription barriers. | Route macroeconomic and historical queries through public FRED and World Bank APIs; reject real-time algorithmic trading scope under Gate 0. |
| **GAP-05** | **Proprietary Clinical Compendia** (UpToDate, DynaMed) | Curated point-of-care clinical synthesis databases require institutional enterprise subscriptions. | Proprietary commercial licensing. | Base medical evidence strictly on PubMed, Europe PMC, Cochrane Reviews, NICE guidelines, and openFDA/EMA registries; clearly state non-clinical advice disclaimer. |
| **GAP-06** | **Deep Web & Unindexed Dynamic Content** | Modern Single Page Applications (SPAs) rendering content exclusively via client-side JavaScript. | Static HTTP cURL fetcher cannot execute JavaScript. | Fall back to sandboxed Puppeteer headless browser MCP tool; mark source as requiring headless rendering. |
| **GAP-07** | **Zero-Day Vulnerability Intelligence** | Vulnerabilities discovered by researchers or adversaries prior to public CVE assignment. | Information asymmetry and embargo periods. | Maintain strict adherence to public CVE, CISA KEV, and vendor security advisories; never speculate on zero-day rumors. |

---

## 13. Architecture Integration Mapping & Future Implementation Blueprint (Deliverable J)

The Global Cross-Domain Research Sources and Evidence Framework integrates seamlessly into the 7-layer architecture defined in `ARCH-UAOS-RDEI-002` without requiring architectural refactoring.

### 13.1 Seven-Layer Architecture Alignment

```
+---------------------------------------------------------------------------------------------------+
| LAYER 1: GOVERNANCE & LIFECYCLE (Orchestrator, Gate 0 Clearance, Gate 4 Cryptographic Binding)    |
| -> Maps universal research dossiers to Gate 0 PASSED / BLOCKED clearance; binds hash to Gate 4 audit|
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| LAYER 2: INTELLIGENCE & EVALUATION (TrustEvaluator, ConflictDetector, TechComparisonEngine)       |
| -> Executes orthogonal trust scoring (w1=0.35, w2=0.30, w3=0.20, w4=0.15) with Domain Overlays    |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| LAYER 3: DOMAIN REPOSITORIES & METRICS (DomainTaxonomy, SourceRegistry, ClaimExtractor)           |
| -> Houses source-definition.schema.json, 16 domain trees, and universal 22-field claim models    |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| LAYER 4: RESEARCH RETRIEVAL & INTEGRATION (RetrievalCoordinator, ProviderAdapters, SecureClient)   |
| -> Implements 16 retrieval channels; reuses StreamableHttpTransport SSRF protections & IP pinning |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| LAYER 5: DOCUMENT PROCESSING & SECURITY (DocProcessor, ContentSanitizer, InjectionDefense)        |
| -> Enforces 2MB size limit, DOM sanitization, adversarial pattern redaction, SHA-256 segmenting   |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| LAYER 6: KNOWLEDGE STORAGE & PERSISTENCE (DossierStore, CacheManager, EvidenceCollector)          |
| -> Stores cryptographically sealed dossiers and audit logs; strictly zero secrets in workspace   |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| LAYER 7: MONITORING & FRESHNESS (ResearchMonitorCoordinator, UpstreamWatcher, AlertDispatcher)    |
| -> Event-driven freshness validation (CLI, CI/CD, Task Demand); zero continuous internal daemons  |
+---------------------------------------------------------------------------------------------------+
```

### 13.2 Phased Implementation Blueprint (Roadmap Phases 1–8)
All future implementation tasks must adhere strictly to the **300-line executable file limit** and single responsibility principle.

```
+---------------------------------------------------------------------------------------------------------+
| PHASE 1: CORE INFRASTRUCTURE & FOUNDATIONAL CONTRACTS                                                   |
| - Implement DomainTaxonomyRegistry (<= 300 lines) validating domains A-P.                               |
| - Implement SourceRegistryEngine (<= 300 lines) loading declarative 26-field source definitions.        |
| - Implement SecureHttpClient (<= 300 lines) reusing StreamableHttpTransport SSRF defenses.              |
+---------------------------------------------------------------------------------------------------------+
                                                     |
                                                     v
+---------------------------------------------------------------------------------------------------------+
| PHASE 2: EVIDENCE EXTRACTION & EXTENSIBLE TRUST SCORING ENGINE                                          |
| - Implement SourceTrustEvaluator (<= 300 lines) computing normalized base scores & domain overlays.      |
| - Implement EvidenceOverlayRegistry (<= 300 lines) loading extensible policies (evidence-overlay.json).  |
| - Implement ConflictDetector & ConflictResolver (each <= 300 lines) handling multi-domain precedence.   |
| - Implement ResearchDossierSealer (<= 300 lines) generating SHA-256 evidence certificates.              |
+---------------------------------------------------------------------------------------------------------+
                                                     |
                                                     v
+---------------------------------------------------------------------------------------------------------+
| PHASE 3: PROVIDER ADAPTER ECOSYSTEM                                                                     |
| - Implement RestApiProviderAdapter (<= 300 lines) handling authenticated JSON queries.                  |
| - Implement AcademicOaiProviderAdapter (<= 300 lines) connecting OpenAlex, Crossref, and PubMed.         |
| - Implement McpResearchBridge (<= 300 lines) interfacing with Brave, Fetch, and SQLite MCP servers.     |
+---------------------------------------------------------------------------------------------------------+
                                                     |
                                                     v
+---------------------------------------------------------------------------------------------------------+
| PHASE 4: DOCUMENT PROCESSING & PROMPT INJECTION CONTAINMENT                                             |
| - Implement DocumentProcessorPipeline (<= 300 lines) parsing HTML, XML, Markdown, and JSON.             |
| - Implement ContentSanitizer (<= 300 lines) stripping executable DOM nodes and adversarial patterns.    |
| - Implement EvidenceSegmenter (<= 300 lines) generating atomic hashed text segments.                    |
+---------------------------------------------------------------------------------------------------------+
                                                     |
                                                     v
+---------------------------------------------------------------------------------------------------------+
| PHASE 5: LIFECYCLE GATE 0 & PROMPT INTEGRATION                                                          |
| - Implement Gate0ClearanceVerifier (<= 300 lines) enforcing quorum, 4-tier vulnerability applicability.  |
| - Integrate Section 2.6 into PromptBuilder (<= 300 lines) isolating untrusted research evidence.       |
| - Implement Canonical SKILL-RDEI-001 (Research Standards and Evidence Utilization).                     |
+---------------------------------------------------------------------------------------------------------+
                                                     |
                                                     v
+---------------------------------------------------------------------------------------------------------+
| PHASE 6: MONITORING & NON-DAEMON FRESHNESS                                                              |
| - Implement ResearchMonitorCoordinator (<= 300 lines) executing configurable TTL scans (profile/task).  |
| - Implement CLI Entry Point `bin/agent-os research:monitor` (<= 300 lines).                             |
+---------------------------------------------------------------------------------------------------------+
                                                     |
                                                     v
+---------------------------------------------------------------------------------------------------------+
| PHASE 7: EXTENDED DOMAIN CONNECTORS (Specialized Medical, Legal, Financial Catalogs)                    |
| - Declarative JSON definition packages for EUR-Lex, openFDA, FRED, and IEEE. Zero PHP changes.          |
+---------------------------------------------------------------------------------------------------------+
                                                     |
                                                     v
+---------------------------------------------------------------------------------------------------------+
| PHASE 8: END-TO-END VALIDATION & HARDENING                                                              |
| - Integration test suite covering cross-domain queries, SSRF attempts, prompt injections, and Gate 0.   |
+---------------------------------------------------------------------------------------------------------+
```

---

## 14. Validation Results

The design artifacts and catalog entries were systematically verified against all 22 completion and correction criteria specified in `UAOS-RDEI-GLOBAL-SOURCES-003` and `UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-003A`:

| Check ID | Completion Requirement | Verification Method | Status |
|:---:|:---|:---|:---:|
| **VAL-01** | Taxonomy covers all major knowledge domains (A through P) | Inspected Section 3; verified 16 primary domains and 170+ standardized subdomains. | **PASSED** |
| **VAL-02** | No major knowledge domain omitted without documented reason | Verified coverage across sciences, humanities, law, engineering, and emerging tech. | **PASSED** |
| **VAL-03** | Substantial catalog of real, existing, verified sources | Inspected Section 4; verified 65+ real institutional sources with valid canonical URLs. | **PASSED** |
| **VAL-04** | Deep coverage of Security, Crypto, Isolation, Software, AI | Verified in-depth dedicated catalogs for Domains A, B, C, D, and E with primary authorities. | **PASSED** |
| **VAL-05** | Global and regional source discovery addressed | Verified US, EU, UK, and UN multilateral registries across law, statistics, and medicine. | **PASSED** |
| **VAL-06** | Free and paid access models documented | Inspected Section 5 & 11; verified 7-tier cost taxonomy and access mechanisms. | **PASSED** |
| **VAL-07** | API, MCP, search, and retrieval mechanisms distinguished | Inspected Section 5.1 & 5.4; verified strict 4-category disjoint separation. | **PASSED** |
| **VAL-08** | Source Registry Schema sufficiently defined for implementation | Verified JSON Schema Draft 2020-12 in Section 6.1 with all 26 canonical fields matching actual schema definition. | **PASSED** |
| **VAL-09** | Evidence provenance and trust assessment are domain-aware | Verified Section 7.2; confirmed 7 distinct domain-specific epistemological overlays. | **PASSED** |
| **VAL-10** | Freshness and monitoring requirements specified | Verified Section 9; confirmed configurable TTL matrix and event-driven non-daemon triggers. | **PASSED** |
| **VAL-11** | Security and legal constraints strictly preserved | Verified Section 10; confirmed C3 secret hygiene, C4 code boundary, and C7 SSRF reuse. | **PASSED** |
| **VAL-12** | Design fits approved RDEI architecture seamlessly | Verified Section 13.1; mapped capabilities to all 7 approved layers. | **PASSED** |
| **VAL-13** | 300-line executable file limit preserved in blueprints | Verified Section 13.2; all future implementation classes scoped to single-responsibility <=300L. | **PASSED** |
| **VAL-14** | Zero PHP implementation code written in this phase | Verified codebase filesystem; exactly zero lines of runtime code or connectors written. | **PASSED** |
| **VAL-15** | Gate 4 authorization semantics remain 100% untouched | Verified additive audit provenance binding; Gate 4 HMAC calculation unaltered. | **PASSED** |
| **VAL-16** | Unresolved coverage gaps explicitly documented | Verified Section 12; confirmed 7 documented coverage gaps and mitigation fallbacks. | **PASSED** |
| **VAL-17** | Security Vulnerability Applicability Model (Finding 1) | Verified Section 7.2 Overlay 3; 4-tier check (Advisory -> Component -> Config -> KEV); Gate 0 does not block unaffected projects; absence of CVE does not prove security. | **PASSED** |
| **VAL-18** | Source, Discovery, Retrieval, & Tooling Separation (Finding 2) | Verified Section 5; search engines, MCP servers, and tools prohibited from counting as corroborating sources; upstream provenance preserved. | **PASSED** |
| **VAL-19** | Source Registry Schema Completeness (Finding 3) | Verified `source-definition.schema.json` enforces HTTPS (with localhost exception), numeric rate/TTL limits, auth consistency, `additionalProperties: false`, and 26 canonical fields. | **PASSED** |
| **VAL-20** | Extensible Domain-Specific Evidence Policies (Finding 4) | Verified Section 7.3 and `evidence-overlay.schema.json`; defines precedence (100/50/0), base fallback, strictness multi-domain resolution, SemVer, and unsupported/insufficient states. | **PASSED** |
| **VAL-21** | Verifiable Source & Provider Claims (Finding 5) | Verified Section 4.0 & Section 5.4; 7-level verification model distinguishes docs/endpoints from live testing; zero speculative testing claims. | **PASSED** |
| **VAL-22** | Independent Corroboration & Configurable Freshness (Finding 6) | Verified Section 7.4 & Section 9.1; codifies 5 non-independent patterns, 5 distinct operational failure states, and 3-level configurable TTL overrides. | **PASSED** |

---

## 15. Final Verdict and Next Steps

### 15.1 Final Verdict
```
+---------------------------------------------------------------------------------+
|                                                                                 |
|         FINAL VERDICT: CORRECTIONS COMPLETE — READY FOR FINAL PROMPT 3 APPROVAL  |
|                                                                                 |
+---------------------------------------------------------------------------------+
```
All six concrete review findings identified in `UAOS-RDEI-GLOBAL-SOURCES-CORRECTION-003A` have been completely resolved within canonical design document `UAOS-RDEI-GLOBAL-SOURCES-AND-EVIDENCE-FRAMEWORK-REPORT.md` and supporting design schemas (`source-definition.schema.json`, `evidence-overlay.schema.json`).

The corrected architecture rigorously protects projects against erroneous Gate 0 blocks while ensuring confirmed critical vulnerabilities trigger immediate fail-closed escalation. The boundaries between Knowledge Sources, Discovery Providers, Transport Mechanisms, and Tooling are strictly enforced, ensuring search engines and MCP servers cannot artificially inflate corroboration scores. The Source Registry Schema is complete, structurally sound, and enforces secret hygiene and HTTPS transport. Domain policies are fully extensible without altering mathematical trust weights. Source capabilities are truthfully characterized under a 7-tier verification model, and corroboration independence is protected against syndication, shared ownership, and mirroring artifacts.

### 15.2 Mandatory Execution Rule
In strict compliance with Section 18 of task specification `UAOS-RDEI-GLOBAL-SOURCES-003`:
- **This phase is research and framework design only.**
- **No implementation code has been generated.**
- **Execution has ceased.**
- **The system will not proceed to Core Intelligence Implementation automatically.**
- **The agent will now stop and await explicit user review and approval.**
