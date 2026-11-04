# Progress

Status: ✅ done · 🔄 in progress · ⏳ next

| #  | Stage                                                       | Status |
|----|-------------------------------------------------------------|--------|
| 0  | Workflow setup: CLAUDE.md, docs/, .claude/                  | ✅ |
| 1  | Initialization: Composer, Smarty, Docker                    | ✅ |
| 2  | DB schema and migrations (SQL) + config and DB connection   | ✅ |
| 3  | Router, controllers, base layout, 404                       | ✅ |
| 4  | Category and article seeder                                 | ✅ |
| 5  | Home page                                                   | ✅ |
| 6  | Category page: sorting + pagination                         | ✅ |
| 7  | Article page: view counter + similar articles               | ✅ |
| 8  | SCSS styles, responsive layout                              | ✅ |
| 9  | English docs, final review, README                          | 🔄 |

## Current state
- Stage 9 split into three commits: 9a — docs translated into English (done); 9b — fixes from the
  final code review (done, ADR-013); 9c — README, screenshots, clean-clone check.
- Next: 9c — README.md, screenshots in docs/screenshots/, AI usage statement for the company,
  clean `git clone` + `docker compose up` check on separate ports.
