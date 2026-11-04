---
description: Review the current stage changes (readability, security, task and CLAUDE.md compliance)
---

Look at the uncommitted changes: `git status` and `git diff` (including new files).
Compare them with `CLAUDE.md`, `docs/TASK.md` and the current stage in `docs/PROGRESS.md`.

Check and report to the author in Ukrainian, grouped by priority (🔴 critical · 🟡 should fix · 🟢 minor):
- **Security:** SQL only through prepared statements; `ORDER BY`/`LIMIT` through a whitelist or int
  casting; XSS — every template output is escaped; request input is validated; 404 for missing records.
- **MySQL:** query correctness, N+1, index usage, transactions where needed.
- **Readability:** `strict_types`, types, PSR-12, clear names, no magic or needless abstractions.
- **Task and stage compliance:** nothing missing; no changes outside the stage.

For each finding: file:line, what is wrong, why it matters, how to fix it.
Do not fix anything yourself — list only. The author decides what to change.

$ARGUMENTS
