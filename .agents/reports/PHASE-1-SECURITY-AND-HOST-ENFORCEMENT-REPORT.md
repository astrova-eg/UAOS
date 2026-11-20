# Universal Core Autonomous Agent Operating System: Security & Host Enforcement Report

**Document ID:** SEC-REP-001  
**Architecture Authority:** ARCH-GOV-DES-001 (1.1.0-DESIGN-REVIEWED)  
**Security Standard:** SEC-POL-001 (Security Governance Policy) & APP-GATE-001 (Approval Gates Policy)  
**Lifecycle Stage:** Stage 13 — Acceptance Verification  
**Execution Timestamp:** 2026-11-15T12:35:00+02:00  

---

## 1. Executive Summary

This report delivers a rigorous assessment of governance, permissions, cryptographic authorization, and execution boundary enforcement within the Universal Core Autonomous Agent Operating System.

In strict adherence to human authority and security validation principles, this report explicitly distinguishes **Application-Level Enforcement** (enforced by the PHP Tool Execution Gateway and Gate 4 Verifier) from **Host-Level Enforcement** (the native execution environment provided by the Antigravity AI host).

**Core Findings:**
- **Application-Level Enforcement:** **100% VERIFIED & RIGIDLY ENFORCED**. All unauthorized file writes, governance modifications, destructive shell commands, unapproved Stage 10 code writes, tampered artifacts, and replayed tokens are blocked and audited.
- **Host-Level Enforcement:** **ARCHITECTURAL BOUNDARY IDENTIFIED**. Antigravity lacks native pre-tool execution hooks. An agent possessing native host tools can directly invoke file writes or shell execution without passing through application-level gateways. Genuine host enforcement relies on subagent tool scoping (`EnableWriteTools: false`) and CLI pipeline orchestration.

---

## 2. Security Architecture & Invariant Enforcement

The system implements a defense-in-depth model across the agent lifecycle:

```
+-------------------------------------------------------------+
|                      HUMAN AUTHORITY                        |
|  Exclusive authority over Gates, Architecture & Governance  |
+------------------------------+------------------------------+
                               |
                   [Gate 4 Human HMAC Token]
                               |
+------------------------------v------------------------------+
|             APPLICATION-LEVEL TOOL GATEWAY                  |
|  - Mandatory Invariant Checks                               |
|  - Agent Identity & Department Authorization                |
|  - Prerequisite Hash Recalculation (SRS, DAG, Plan)         |
|  - Destructive Command Pattern Filtering                    |
|  - Tamper Invalidation & Nonce Replay Prevention            |
|  - Full Audit Trail Logging (JSON & Markdown)               |
+------------------------------+------------------------------+
                               | (Authorized Path)
+------------------------------v------------------------------+
|                    PHYSICAL PROJECT ROOT                    |
|  - Scoped File I/O & Verified Process Execution             |
+-------------------------------------------------------------+
```

### 2.1 The 5 Mandatory Gateway Invariants

1. **Invariant 1 — No Agent Self-Approval (Human Authority Supremacy):**
   - Agents are constitutionally forbidden from approving Gates or issuing Gate 4 tokens.
   - Operations `approve_gate4` and `sign_gate4` are intercepted and rejected with `INVARIANT-1`.
   - Empirically verified in `Gate4Test` and `ToolGatewayTest`.
2. **Invariant 2 — Governance File Protection:**
   - Files within `.agents/governance/` cannot be modified or deleted by non-Department A agents.
   - Attempted modification by agent `A11Y-001` (Department G) was blocked with `INVARIANT-5`.
3. **Invariant 3 — Mandatory Gate 4 Human Implementation Authorization:**
   - Stage 10 code-writing operations (`write_file`, `modify_code`) strictly require a cryptographically valid Gate 4 token.
   - Invocations lacking a token are blocked with `GATE-4-ENFORCEMENT`.
4. **Invariant 4 — Destructive Shell Command Prohibition:**
   - Shell commands matching dangerous patterns (`rm -rf /`, `drop database`, `format`, `del /f /s /q c:`) are halted immediately with `SECURITY-DESTRUCTIVE-BLOCK`.
5. **Invariant 5 — Immutable Audit Trail:**
   - Every evaluated operation (whether permitted, executed, or blocked) is appended to the audit trail and persisted in local execution reports.

---

## 3. Cryptographic Gate 4 Token Lifecycle & Verification

### 3.1 Token Specification
- **Algorithm:** HMAC-SHA256
- **Signing Authority:** Human Project Sponsor / Architecture Authority (Out-of-band PowerShell execution)
- **Manifest Canonicalization:** Keys sorted alphabetically (`ksort`), formatted as key-value pairs (`k=v`), and concatenated with `&`.
- **Prerequisite Artifact Hashes:** SHA-256 hashes of physical files (`srs_hash`, `task_dag_hash`, `plan_hash`).

### 3.2 Empirical Verification Results

| Test Scenario | Test Description | Expected Behavior | Actual Behavior | Result |
|---|---|---|---|---|
| **Valid Authorization** | Token signed with correct secret and matching artifact hashes | Verification succeeds (`valid: true`) | Verified successfully | **PASS** |
| **Wrong Secret Key** | Token evaluated with incorrect HMAC secret | Verification fails (`HMAC signature failed`) | Blocked; signature rejected | **PASS** |
| **Tampered Prerequisite** | `plan.md` modified after token issuance | Hash mismatch detected | Blocked (`GATE-4-INVALID`); write halted | **PASS** |
| **Replay Attack** | Identical token re-submitted with previously consumed nonce | Nonce reuse detected | Blocked (`Replay attack detected`) | **PASS** |
| **Expired Token** | Token evaluated after `expires_at` timestamp | Token expiration detected | Blocked (`Gate 4 token expired`) | **PASS** |
| **Cross-Project Reuse** | Project B token submitted to Project A | Missing project-local artifact | Blocked (`File does not exist: srs.md`) | **PASS** |

### 3.3 Secret Handling & Isolation
- Human secret keys are never committed to git, embedded in source files, or persisted in configuration.
- The PowerShell signing script prompts for secret input via `Read-Host -AsSecureString` or retrieves it from a protected session environment variable (`$env:CORE_GATE4_HUMAN_SECRET`).
- The secret is strictly isolated from ambient agent server environments.

---

## 4. Critical Host Integration Verification: Bypass Analysis

### 4.1 Host Tool Capabilities
The Antigravity AI host exposes native model-level tools:
- `read_file`
- `write_file`
- `edit_file`
- `run_command`
- `invoke_subagent`
- `define_subagent`

### 4.2 The Direct Bypass Path
- **Mechanism:** If an LLM agent is provided with native tools (`write_file` or `run_command`), the model can construct and emit a native `<tool_call>` directly to the host runtime.
- **Interception Reality:** The Antigravity host runtime **does not provide native pre-tool execution hooks** or hypervisor/kernel-level interception. As a consequence, a native `<tool_call: write_file>` executes directly against the workspace with host process permissions, completely bypassing the PHP `ToolGateway`.
- **Security Impact Classification:** **HIGH / ARCHITECTURAL BOUNDARY LIMITATION**. Prompt instructions directing an agent to use `cli.php` or `ToolGateway` represent soft prompt-level guidance, not an operating-system cryptographic sandbox.

### 4.3 Genuine Host-Enforced Restrictions Available
To mitigate this limitation, two genuine host-enforced mechanisms must be applied:
1. **Subagent Tool Scoping:** When spawning autonomous subagents via `define_subagent`, the host permits setting `EnableWriteTools: false`. This provides a true host-level guarantee that the subagent cannot write files or execute commands.
2. **Orchestrator Pipeline Mediation:** In standalone production deployments, agents execute strictly within the PHP CLI runtime (`cli.php`), where all tool evaluation is channeled through `ToolGateway.php` before any disk I/O or process spawning occurs.

---

## 5. Security Closure Gate Assessment

| Criterion | Evaluation | Justification |
|---|---|---|
| **P1-AC-06 (Governance & Permissions)** | **CONDITIONALLY PASSED** | Application-level Tool Gateway enforcement is 100% verified. Host-level tool-call interception is constrained by Antigravity host architecture. |
| **P1-AC-07 (Gate 4 Security)** | **PASS** | Cryptographic token generation, prerequisite artifact hashing, expiration checking, tamper invalidation, and replay protection are fully operational. |
| **P1-AC-13 (Zero Unresolved Blockers)** | **CONDITIONALLY PASSED** | All defects corrected. Host-level interception limitation is documented transparently and submitted for human acceptance. |

---

## 6. Recommendations for Human Authority

1. **Acknowledge Host Limitation:** Formally record that Antigravity's current version lacks pre-tool execution hooks, making application-level ToolGateway mediation the primary enforcement mechanism.
2. **Mandate Scoped Subagents:** For any autonomous subagent tasks not requiring file modification, enforce `EnableWriteTools: false`.
3. **Approve Conditional Closure:** With zero code defects and 100% verified application governance, approve Phase 1 conditional closure.
