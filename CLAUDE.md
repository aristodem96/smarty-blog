# CLAUDE.md — smarty-blog

AbeloHost test assignment (Full-stack PHP/JS): a blog with categories and articles on plain PHP,
Smarty and MySQL. Deadline: **Monday, 2026-11-09**. Full task: [docs/TASK.md](docs/TASK.md).

## Read at the start of every session
1. [docs/PROGRESS.md](docs/PROGRESS.md) — where we stopped, current stage.
2. [docs/DECISIONS.md](docs/DECISIONS.md) — decisions already made (do not revisit unless asked).
3. [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) — structure, DB schema, request flow.
4. [docs/AI_USAGE.md](docs/AI_USAGE.md) — AI usage log (append after every stage).

## Stack and hard constraints
- PHP 8.1+, no frameworks (no Laravel, Symfony, Slim or their components).
  Code uses **PHP 8.1 features only** (Composer `platform.php = 8.1.0`); Docker runs 8.3 (ADR-004).
- Smarty is the only template engine. MySQL 8 through **PDO** (no ORM, no query builder).
- Composer only for Smarty and PSR-4 autoloading. SCSS → CSS with `sass` (Dart Sass, npm).
- Docker: PHP-FPM 8.3 + Nginx + MySQL 8.4 + one-off `assets` (Node 22, SCSS build). The PHP
  entrypoint runs on every start — everything in it (migrations, seeding) must be idempotent (ADR-005).
- **No new libraries (Composer/npm) without the author's explicit permission.**

## Project structure
Layered by type (ADR-002). Details in docs/ARCHITECTURE.md.
- `public/` — document root: `index.php` (single entry point, routes and wiring — ADR-007), `assets/`,
  `images/articles/` (article SVGs).
- `src/Core/` — Router, Request, Response, View (Smarty), Database (PDO), NotFoundException.
- `src/Controller/` — methods return `Response`; "not found" → `throw new NotFoundException()`.
- `src/Repository/` — **all SQL lives here**. Dynamic `ORDER BY` only via enum `ArticleSort::orderBy()`.
- `src/Entity/` — readonly entities with `fromRow()`; repositories return entities, not arrays.
  Dates are `DateTimeImmutable`, formatted with `->format()` in templates (not `date_format`: it uses `strftime`).
- `src/Support/` — helpers (`Slugger`, `Pagination`, `ViewedArticles`). Namespace `App\` → `src/`.
- `templates/` — Smarty `.tpl`. `config/` — config from env. `bin/` — CLI (migrate, seed).
- `database/migrations/NNN_*.sql` — one table/change per file; never edit applied files (schema
  change = new file). `database/seeds/` — `BlogSeeder` (`Database\Seeders\`) and its data.
- `scss/` → `public/assets/css` (ADR-012): `abstracts/`, `base/`, `layout/`, `components/` — one BEM
  block per file; `@use`, not `@import`. Colors only via CSS custom properties in `base/_theme.scss`.
- `docker/` — environment. `var/` — Smarty cache (gitignored). URLs use `slug`, not `id` (ADR-003).

## Code rules
- `declare(strict_types=1);` in every PHP file. PSR-12, PSR-4, full parameter/return/property types.
- SQL: prepared statements with placeholders only; never concatenate user input into SQL.
- Templates: `escape_html` is on globally — `{$var}` is escaped automatically; raw HTML (`nofilter`)
  only deliberately, with a comment explaining why. Pages use `{extends file="layout.tpl"}`.
- Query params and cookies only via `Request::query()` / `Request::cookie()`, never `$_GET` / `$_COOKIE`;
  response headers (`Set-Cookie`) via `Response`, never `header()`.
- Unknown route or missing record → HTTP 404 with the 404 template.
- No magic: no `__get`/`__call`, no globals, no service locator. Constructor injection, wired explicitly.
- Simplicity over architecture for its own sake: add a layer/class only if it removes real duplication
  or mixed responsibilities. Comments only where the "why" is not obvious from the code.
- Everything in the repository is in professional English: docs, README, comments, commits, the site
  UI and the seeded content.

## Commands
Site: http://localhost:8080 · MySQL from the host: `127.0.0.1:3307` (blog / secret).
```bash
docker compose up -d --build                         # start everything (deps, migrations, seed, CSS)
docker compose logs -f php                           # PHP / entrypoint / error_log output
APP_DEBUG=1 docker compose up -d php                 # show error details on the 500 page
APP_ENV=production docker compose up -d php          # no per-request Smarty template checks
docker compose exec php composer validate --strict
docker compose down                                  # stop (add -v to drop the database)
docker compose exec php php bin/migrate.php          # apply new migrations (also runs on start)
docker compose exec php php bin/migrate.php --fresh  # drop all tables and re-migrate
docker compose exec php php bin/seed.php             # recreate demo data (80 articles)
docker compose exec php php bin/seed.php --articles=300
docker compose up assets                             # rebuild CSS
docker compose run --rm assets npm run watch         # SCSS watch mode
```

## Working with the author
- **The author makes architectural decisions.** Propose 2–3 options with pros, cons and a
  recommendation; once decided, record it in DECISIONS.md.
- Do routine work (boilerplate, styles, Docker, seeding) after the approach is agreed.
- **One stage — one commit.** Do not start the next stage without the author's "ok".
- Do not make sweeping changes to files unrelated to the current stage.
- The author commits. `git push` is forbidden. Commit only when explicitly asked.
- Explain every step so the author can defend it in an interview (the author is a Laravel
  developer: draw parallels with Laravel where helpful).
- Talk to the author in Ukrainian; write everything in the repository in English.

## After every stage
1. Update the status in docs/PROGRESS.md.
2. Add ADRs to docs/DECISIONS.md (if decisions were made).
3. Add an entry to docs/AI_USAGE.md: what the AI did, what the author decided.
4. Update docs/ARCHITECTURE.md if the structure or schema changed.
5. Suggest a commit message (Conventional Commits, English) — see `/commit-msg`.

## Custom commands (.claude/commands/)
- `/stage` — current stage, step plan, files to touch.
- `/review` — review of the stage changes: readability, security, compliance with the task and CLAUDE.md.
- `/commit-msg` — Conventional Commits message.
- `/explain` — explain the latest changes as interview questions and answers.
