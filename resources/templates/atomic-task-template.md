# Atomic Engineering Task: [Task Title]
**Task ID:** TASK-[000]  
**Specification Ref:** SRS-[000]  
**Assigned Agent ID:** [Agent ID]  
**Reviewer Agent ID:** [Reviewer ID]  
**Status:** [Pending | In Progress | Ready for Audit | Completed]  

## 1. Task Objective
[Single, bounded, unambiguous objective.]

## 2. Preconditions
- [Precondition 1, e.g., Migration X applied]
- [Precondition 2, e.g., Interface Y defined]

## 3. Strict Boundary & File Scope
- Target Files to Modify/Create:
  - `path/to/FileA.php`
  - `tests/Feature/FileATest.php`
- Prohibited Modifications:
  - No changes outside specified bounded context.

## 4. Step-by-Step Execution Instructions
1. [Step 1]
2. [Step 2]
3. [Step 3]

## 5. Verification & Acceptance Criteria
- [ ] Strictly typed with `declare(strict_types=1);`
- [ ] Code file length capped at 300 lines by default (any exception requires prior explicit human approval).
- [ ] 0 PHPStan errors at level 8+.
- [ ] Feature test covering happy path and failure edge cases.
- [ ] Independent audit pass.