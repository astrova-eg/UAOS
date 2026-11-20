# Core System Integration Guide

**Document ID:** SYS-INT-001  
**Version:** 2.0.0  
**Classification:** Integration Standard  
**Status:** [Active Specification]  

---

## 1. Cross-System Communication Patterns
1. **Synchronous Contract Invocation:** For queries within the same request lifecycle, systems interact via PHP interfaces bound in the Laravel service container.
2. **Asynchronous Event-Driven Messaging:** For cross-aggregate side effects (e.g., Billing payment trigger sending an invoice email), systems publish domain events dispatched onto Redis queues.
3. **Direct Database Coupling Prohibited:** A core system MUST NOT query another core system's private tables directly.