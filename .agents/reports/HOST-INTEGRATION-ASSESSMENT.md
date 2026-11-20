# Antigravity Host Integration Assessment Report

**Document ID:** ASSESS-HOST-001  
**Target Environment:** Antigravity Agent Runtime (Windows x64 / PowerShell 5.1 / PHP 8.3.30)  
**Target Path:** `C:\laragon\www\core`  
**Assessment Date:** 2026-11-15  
**Compliance Standard:** ARCH-GOV-DES-001 (Version 1.1.0-DESIGN-REVIEWED)  

---

## 1. Executive Summary

This assessment establishes the primary technical reality of the Antigravity host environment. It verifies what capabilities the host directly supports, what can be achieved through verified adapter/gateway integrations, what is partially supported, and what is not supported.

Crucially: Prompt instructions do not constitute a cryptographic or operating-system security boundary. The host executes tool calls issued by the model without a host-native interception hook. Therefore, security boundaries must be enforced at the Tool Gateway / CLI boundary, and human authorization must be isolated cryptographically outside agent context.

---

## 2. Technical Capabilities Classification Matrix

| # | Capability | Classification | Technical Evidence & Verification Path |
|---|---|---|---|
| 1 | Agent invocation and execution | Directly Supported | Antigravity host exposes `invoke_subagent` and `define_subagent` native tools, allowing dynamic agent spawning. |
| 2 | Agent identity and role selection | Supported Through a Verified Integration | Canonical `agent-registry.json` indexes 112 specialized agent personas; Orchestrator selects and binds them into execution context. |
| 3 | Agent prompt construction and context injection | Supported Through a Verified Integration | Runtime `PromptBuilder` compiles immutable Shared Governance Core, department charters, skills, and constraints into agent context. |
| 4 | Tool discovery and invocation | Directly Supported | Host statically exposes runtime tools (`read_file`, `write_file`, `run_command`, etc.). Subagents can receive scoped permissions (`EnableWriteTools`). |
| 5 | File-system operations | Directly Supported | Host provides `read_file`, `write_file`, `edit_file` tools executing directly against workspace `C:\laragon\www\core`. |
| 6 | Shell and PowerShell execution | Directly Supported | Host provides `run_command` executing Windows PowerShell 5.1 and PHP 8.3 CLI synchronously or as background tasks. |
| 7 | Command interception / execution mediation | Partially Supported | Host lacks native tool-call interception hooks; mediation is enforced when execution is routed through the PHP Tool Execution Gateway. |
| 8 | Runtime permission enforcement | Supported Through a Verified Integration | The PHP Tool Execution Gateway inspects agent identity, target paths, and Gate 4 tokens, blocking unauthorized file writes and commands. |
| 9 | Human interaction and approval | Directly Supported | Host provides `ask_question` tool and interactive chat; Gate 4 human HMAC-SHA256 signing is executed out-of-band via PowerShell. |
| 10 | Agent-to-agent delegation and coordination | Directly Supported | Host provides `invoke_subagent`, `send_message`, and `manage_subagents` for hierarchical delegation between specialized agents. |
| 11 | Execution state and session management | Supported Through a Verified Integration | Background tasks, conversation transcripts, and runtime JSON session stores (`.agents/runtime/state/`) track lifecycle states. |
| 12 | Execution logs and evidence collection | Directly Supported | Antigravity conversation transcripts (`transcript.jsonl`), task logs, and `.agents/reports/` persist verifiable empirical evidence. |

---

## 3. Host Boundaries and Limitations

1. **Host-Level Interception:** Antigravity does not provide a pre-tool hook to intercept raw host-level `write_file` or `run_command` invocations. Security enforcement operates via the Tool Execution Gateway architecture: agents are instructed and verified to route operations through the Gateway CLI/API, which enforces permissions and halts unverified actions.
2. **Key Isolation:** The Gate 4 human signing key must not be placed in environment variables or repository files accessible to agents. The PowerShell script prompts for key input interactively or retrieves it from a secure human-only store, preventing automated agent self-signing.
3. **No Fake Simulators:** All components must use real PHP 8.3 CLI execution, real JSON registries, and real PowerShell cryptographic operations. Simulated agent responses are strictly forbidden.
