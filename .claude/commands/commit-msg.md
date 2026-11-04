---
description: Suggest a commit message (Conventional Commits, English)
---

Look at `git status`, `git diff --staged` (if empty, `git diff`) and `git log --oneline -10`.

Suggest **one** commit message in English following Conventional Commits:
- subject `type(scope): summary` — up to 72 characters, imperative mood, no trailing period
  (`feat`, `fix`, `chore`, `docs`, `refactor`, `style`, `build`, `test`);
- a blank line, then 2–5 bullet points: what changed and **why**;
- the last body line is the stage number, e.g. `Stage: 3`.

Print the message in a code block. Do not commit — the author does that.

$ARGUMENTS
