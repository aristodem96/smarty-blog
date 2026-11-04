# Architecture Decision Records

Format: **Date · Decision · Alternatives · Rationale**.
Decisions are made by the author; the AI assistant only proposes options.

---

## ADR-001 · Organizing AI-assisted work with CLAUDE.md and docs/
- **Date:** 2026-11-04
- **Decision:** keep the project memory in the repository: `CLAUDE.md` (rules), `docs/` (task,
  architecture, decisions, progress, AI usage log), `.claude/` (permissions, custom commands).
- **Alternatives:** no documentation (context is lost between sessions); notes outside the
  repository (reviewers cannot see the reasoning).
- **Rationale:** the task asks to state honestly how AI was used and to show the progress of the
  work — logs in the repository make this transparent; DECISIONS.md doubles as interview notes.

---

## ADR-002 · Project structure: layered by type (MVC-lite)
- **Date:** 2026-11-04
- **Decision:** group code by responsibility: `src/Core` (router, DB, view), `src/Controller`,
  `src/Repository`; Smarty templates separately in `templates/`; single entry point
  `public/index.php`. Namespace `App\` → `src/` (PSR-4).
- **Alternatives:** feature modules (`src/Article/`, `src/Category/`) — scale better, but are
  extra structure for two entities; the home page and "similar articles" have no natural module.
- **Rationale:** the project is small and simplicity is evaluated; the layout is familiar from
  Laravel and obvious to a reviewer; the request flow is linear and easy to explain.

---

## ADR-003 · Database schema: three tables, slugs in URLs, `description` as TEXT
- **Date:** 2026-11-04
- **Decision:**
  - `categories`, `articles` and the `article_category` pivot (many-to-many, composite PK).
  - URLs use `slug` (`/category/php`, `/article/my-first-post`) with a `UNIQUE` index on `slug`.
  - `articles.description` is `TEXT`, since a summary can be long; cards truncate it in the template.
  - Indexes for sorting: `articles(published_at)`, `articles(views)`; reverse pivot index
    `article_category(category_id, article_id)`.
  - InnoDB (foreign keys and transactions), `utf8mb4` (full Unicode, emoji).
  - *Updated in stage 2:* collation `utf8mb4_0900_ai_ci` instead of `utf8mb4_unicode_ci` —
    MySQL 8 default, Unicode 9.0 instead of 4.0, faster comparisons.
  - *Updated in stage 2:* removed `articles.updated_at ... ON UPDATE CURRENT_TIMESTAMP` — every
    `views` increment (i.e. every page view) would change it, and editing articles is out of scope.
    `created_at` remains.
- **Alternatives:**
  - `id` in URLs — simpler (no slug generation or uniqueness), but worse for SEO and readability.
  - `description VARCHAR(500)` — hard limit on summary length; rejected because summaries can be long.
  - Denormalize `published_at` into the pivot for a `(category_id, published_at)` index —
    premature optimization for this data size.
- **Rationale:** the normalized schema matches the task directly ("one or more categories");
  the composite PK prevents duplicate links and serves "categories of an article" (similar
  articles), the reverse index serves "articles of a category" (home page, category page).

---

## ADR-004 · Versions: PHP 8.1 minimum in code; PHP 8.3 / MySQL 8.4 / Smarty 5 at runtime
- **Date:** 2026-11-04
- **Decision:** `composer.json`: `"php": ">=8.1"` plus `config.platform.php = 8.1.0`; code uses
  PHP 8.1 features only. Docker: `php:8.3-fpm-alpine`, `mysql:8.4` (LTS), `nginx:1.30-alpine`.
  Template engine: Smarty 5.x (namespaced `Smarty\Smarty`).
- **Alternatives:** PHP 8.1 in Docker as well — literally as in the task, but 8.1 has been EOL
  since 2025-12-31 (no security fixes); MySQL 8.0 — EOL; Smarty 4 — legacy branch without namespaces.
- **Rationale:** "8.1+" in the task is a lower bound. `platform.php` guarantees Composer never pulls
  dependencies incompatible with 8.1, so the project formally meets the task while running on
  supported versions.

---

## ADR-005 · Docker: Nginx + PHP-FPM, Composer in the container, initialization in the entrypoint
- **Date:** 2026-11-04
- **Decision:**
  - Services: `nginx` (static files + FastCGI proxy), `php` (FPM), `mysql`.
  - Nginx mounts only `public/` and executes only `index.php`; any other `.php` → 404, hidden files → 403.
  - Composer is copied into the image (`COPY --from=composer:2`) and runs inside the container.
  - `entrypoint.sh` on every start: `composer install` (no-op when `vendor/` is up to date),
    creates `var/` writable by `www-data`, then hands over to `docker-php-entrypoint`.
  - PHP starts only after the MySQL healthcheck passes (`depends_on: service_healthy`); the
    healthcheck uses TCP because MySQL runs a temporary networkless server during initialization.
  - Credentials live in `docker-compose.yml` with `${VAR:-default}` defaults; `.env` is optional.
- **Alternatives:** Apache + mod_php (one container, but `.htaccess` and a dated stack); Composer
  on the host (`vendor/` built for a different PHP version); a Makefile (not available on Windows)
  or commands in the README only (not "one command").
- **Rationale:** production-like stack; `docker compose up` is the only command needed; variable
  defaults allow starting without copying `.env`.
- **Consequence:** everything added to the entrypoint (migrations in stage 2, seeding in stage 4)
  must be idempotent; automatic seeding runs on an empty database only.

---

## ADR-006 · Migrations: numbered SQL files + `migrations` table; DB connection moved to stage 2
- **Date:** 2026-11-04
- **Decision:**
  - `database/migrations/NNN_name.sql`, one table per file; `bin/migrate.php` applies files in name
    order and skips those recorded in `migrations`. Called from the entrypoint.
  - `--fresh` drops every table and re-applies all migrations. No down migrations.
  - `config/config.php` and `src/Core/Database.php` were created in stage 2 (not 3) because
    migrations need a connection. PDO: `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES = false`.
- **Alternatives:** a single `schema.sql` with `IF NOT EXISTS` (does not update existing tables);
  `/docker-entrypoint-initdb.d` in MySQL (runs on an empty volume only, tied to Docker); down
  migrations (unnecessary for a test task); a temporary DSN in the script (throwaway code).
- **Rationale:** idempotent by design (required by the entrypoint); schema changes are new files;
  familiar model (as in Laravel). DDL in MySQL commits implicitly, so transactions do not help —
  small files keep failures local, and a migration is recorded only after it succeeds.

---

## ADR-007 · HTTP layer: Router, Request, Response, manual dependency wiring
- **Date:** 2026-11-04
- **Decision:**
  - `Router` with `{slug}` placeholders → regex `[a-z0-9-]+`; parameters are passed to the controller
    as **named arguments** (`show(Request $request, string $slug)`). GET/HEAD only. The three routes
    are registered in `public/index.php` next to the controller construction.
  - Controllers always return a `Response` (status, headers, body).
  - "Not found" from anywhere is a `NotFoundException`; the front controller turns it into a 404
    `Response`, any other `Throwable` into a 500 plus `error_log`. Error details are shown only with
    `APP_DEBUG=1`. Warnings and notices are converted to `ErrorException`.
  - `Request` (readonly) is built from superglobals once; `query()` returns strings only.
  - Dependencies are wired manually with `new` in `index.php`, no DI container.
  - *Updated in stage 9:* each route is a closure that builds its controller only when the route
    matches, and PDO is opened lazily on first use — unknown URLs return 404 without connecting to
    MySQL (and still return 404 while the database is down).
  - Layout uses Smarty template inheritance (`{extends}` + `{block}`) with `escape_html` auto-escaping.
- **Alternatives:** `match(true)` in `index.php` (routes mixed with bootstrap); controllers returning
  strings (simpler, but no control over status/headers); a reflection-based DI container (magic);
  `$_GET` in controllers (global state); `{include}` header/footer.
- **Rationale:** the author chose `Response` with scaling in mind (redirects, headers, JSON, testing
  without output buffering); an exception for 404 removes duplicated 404 rendering — the same way
  Symfony and Laravel (`abort(404)`) work. Slug validation in the router rejects junk URLs before
  any database call.

---

## ADR-008 · Seeder: own deterministic generator, Ukrainian transliteration, SVG images
- **Date:** 2026-11-04
- **Decision:**
  - 7 fixed categories (one without articles, to check the home page filter); articles are generated
    from "title template × category topic" combinations plus paragraph pools
    (`database/seeds/data/blog.php`). Each article belongs to its own category plus 0–2 random others.
    `mt_srand(42)` → identical data on every run.
  - Content is Ukrainian; slugs come from the own `App\Support\Slugger` following the official
    Ukrainian transliteration standard (Cabinet of Ministers Resolution No. 55, 2010); duplicate slugs
    get `-2`, `-3` suffixes.
  - Images: 8 SVGs in `public/images/articles/` (committed); the DB stores the path from `public/`.
  - `bin/seed.php` always recreates the data (`--articles=N`, default 80); the entrypoint calls it
    with `--if-empty`, so only an empty database is seeded.
  - `TRUNCATE` (with `FOREIGN_KEY_CHECKS = 0`) runs before the transaction; all inserts run in one
    transaction with reused prepared statements.
  - Logic in the `Database\Seeders\BlogSeeder` class (PSR-4 → `database/seeds/`); `bin/seed.php` is a thin CLI.
- **Alternatives:** Faker (external library, lowers the "own implementation" score); hand-written
  articles (too few for pagination); `ext-intl` Transliterator (heavier image); English content;
  photos from picsum.photos (network dependency in the entrypoint); GD generation (extra extension);
  refusing to seed a non-empty database without `--force`; a procedural script.
- **Rationale:** reproducible data covering every scenario in the task (articles in several categories,
  an empty category, multiple pages, varied views and dates) without external dependencies.

---

## ADR-009 · Home page: one ROW_NUMBER() query, readonly entities, categories by freshness
- **Date:** 2026-11-04
- **Decision:**
  - `CategoryRepository::findWithLatestArticles(3)` — **one** SQL query: the subquery computes
    `ROW_NUMBER() OVER (PARTITION BY category_id ORDER BY published_at DESC, id DESC)` and
    `MAX(published_at) OVER (PARTITION BY category_id)` over ids only; full article columns are
    joined outside for the selected rows (`position <= 3`). Empty categories drop out naturally
    (no rows in `article_category`).
  - Categories are ordered by their most recent article, then by `id`.
  - Repositories return readonly entities `App\Entity\Category` / `Article` (`fromRow()`); dates are
    `DateTimeImmutable`, formatted in templates with `->format()`.
  - Article card: `templates/partials/article-card.tpl` (reused in stages 6–7).
- **Alternatives:** one query per category (N+1); `JOIN LATERAL … LIMIT 3` (fastest on large data —
  reads exactly 3 index rows per category — but less familiar syntax); associative arrays in templates
  (no structural guarantees); Smarty `date_format` (relies on deprecated `strftime`); categories
  ordered alphabetically or by `id`.
- **Rationale:** a fixed number of queries regardless of the number of categories; window functions
  are standard SQL, available since MySQL 8.0. Cost: ROW_NUMBER is computed for every link (EXPLAIN:
  full scan of the `article_category` index + filesort) — cheap for thousands of articles; for
  millions, switch to LATERAL.

---

## ADR-010 · Category page: enum sorting, LIMIT/OFFSET, separate COUNT, 404 for missing pages
- **Date:** 2026-11-04
- **Decision:**
  - `?sort=date|views` with a fixed direction (newest / most viewed). The backed enum
    `App\Repository\ArticleSort` is the `ORDER BY` whitelist (`tryFrom()`; unknown value → `Date`).
    Changing the sort order resets to page 1.
  - 9 articles per page; `LIMIT :limit OFFSET :offset` with integer parameters.
  - The total comes from a separate `COUNT(*)` on `article_category` (index only).
  - `?page` is validated with `filter_var(FILTER_VALIDATE_INT, min_range 1)`; invalid or out of range
    → 404. Missing `page` = 1; an empty category has a page 1 with a message.
  - `App\Support\Pagination` — readonly value object (offset, totalPages, links) with a window
    `1 … 4 5 [6] 7 8 … 20` (radius 2; a gap of one page is shown as the number, not "…").
  - Page 1 links have no `&page`; `sort` is kept in every pagination link.
- **Alternatives:** `?sort=…&dir=…` (double validation with no benefit to readers); 12 or 10 per page;
  redirect to the last page (friendlier, but hides non-existent URLs); `SQL_CALC_FOUND_ROWS`
  (deprecated since MySQL 8.0.17, often slower); `COUNT(*) OVER ()` (an out-of-range page returns no
  rows, hence no total); listing every page number (does not scale).
- **Rationale:** the task requires two sort orders — the enum makes them type-safe and closes SQL
  injection through `ORDER BY`; 404 is the honest answer for a non-existent page and avoids duplicate
  content. The pagination window is the author's choice, anticipating a growing number of articles.
- **Known limitations:** OFFSET pagination slows down on deep pages (MySQL reads and discards OFFSET
  rows) — at scale, keyset pagination (`WHERE (published_at, id) < (…)`). `?page[]=2` is treated as a
  missing `page` (page 1) because `Request::query()` returns strings only.

---

## ADR-011 · Article page: view counter with cookie deduplication, similar articles by shared categories
- **Date:** 2026-11-04
- **Decision:**
  - A view is counted once per browser: cookie `viewed_articles` (ids joined with `.` because RFC 6265
    forbids commas; last 50 ids; `Max-Age` 24 h; `HttpOnly`; `SameSite=Lax`). Logic lives in
    `App\Support\ViewedArticles`; the cookie value is user input — only positive integers are
    accepted. HEAD requests are not counted.
  - Atomic increment: `UPDATE articles SET views = views + 1 WHERE id = ?`. The page shows
    `views + 1` from memory (`Article::withViews()`), without a second SELECT.
  - Similar articles: one query — `article_category` self-join, `COUNT(*)` of shared categories,
    `ORDER BY shared DESC, published_at DESC, id DESC LIMIT 3`, the current article excluded.
    Fewer than 3 found → show what was found; none → the block is hidden.
  - Content: `Article::paragraphs()` (split on blank lines) → one auto-escaped `<p>` each.
  - Article categories: `CategoryRepository::findByArticle()`, linked to category pages.
  - `Response` accepts extra headers on top of the defaults (`Set-Cookie`); `Request::cookie()`.
- **Alternatives:** count every GET (refreshes inflate the counter); PHP sessions (server state just for
  a counter); an `article_views` table with IPs (more accurate, but a new table and GDPR concerns);
  views as the tie-breaker (the popular get more popular) or `RAND()` (sorts all candidates, not
  reproducible); filling similar articles with the latest posts; `|nl2br` (escape_html runs after
  modifiers and would escape the `<br>`).
- **Rationale:** protection against accidental refreshes without server state; an atomic UPDATE with no
  race conditions; "similarity" through shared categories as the task says, with a deterministic order.
- **Known limitations:** `Max-Age` is refreshed on every new view, so the 24-hour window counts from the
  last cookie write, not per article; clearing cookies bypasses the protection (acceptable for a blog).
  `Response` stores headers as `name => value`, so two `Set-Cookie` headers in one response are not supported.

---

## ADR-012 · Styles: SCSS in an `assets` service, one file per BEM block, mobile-first, light + dark theme
- **Date:** 2026-11-04
- **Decision:**
  - `assets` service (`node:22-alpine`) in compose: `npm ci && npm run build` →
    `public/assets/css/app.css` (compressed, no source map), then exits; `nginx` starts after
    `service_completed_successfully`. Watch mode: `docker compose run --rm assets npm run watch` (`--poll`).
  - Compiler: `sass` (Dart Sass, the only npm dependency); modules via `@use`.
  - Structure: `abstracts/` (variables, `respond-to()`, `visually-hidden`, `focus-ring`), `base/`
    (theme, reset, typography), `layout/`, `components/` — one BEM block per file.
  - Colors are CSS custom properties; the dark theme is `@media (prefers-color-scheme: dark)`
    overriding the same properties; `color-scheme: light dark`. SCSS variables hold compile-time values.
  - Mobile-first `min-width` breakpoints; card grid `repeat(auto-fill, minmax(min(100%, 260px), 1fr))`
    without media queries. System fonts. The whole card is clickable via the title link's `::after`.
  - Cache busting: `app.css?v=<filemtime>` (a global `View` variable).
  - Accessibility: `:focus-visible`, `prefers-reduced-motion`, "Previous/Next" labels on phones are
    hidden visually only.
- **Alternatives:** multi-stage Dockerfile (image rebuild on every style change with a bind mount);
  npm on the host (reviewers need Node); committing the CSS (generated file in git); full 7-1 pattern
  (empty folders) or a single file; light theme only.
- **Rationale:** `docker compose up` stays a single command with no Node on the host; one file per BEM
  block — a style is found by the class name in the template; the dark theme is the author's choice
  and costs ~15 lines thanks to custom properties.

---

## ADR-013 · Hardening and polish after the final review
- **Date:** 2026-11-04
- **Decision** (all findings of the final review, applied at the author's request):
  - PHP: `php.ini-production` is activated in the image (errors hidden from users, logged to stderr,
    deprecations excluded from `error_reporting`); `docker/php/app.ini` adds `expose_php = Off`.
  - Nginx: `server_tokens off`; `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` on every
    response (`always`); `expires 30d` for `/assets/` and `/images/` (CSS is cache-busted with `?v=`).
  - Lazy controller construction and DB connection (see ADR-007 update).
  - `APP_ENV` (`development` by default): `production` disables Smarty's per-request template checks.
  - Accessibility/SEO: a visually hidden `h2` keeps the heading outline on the category page;
    `<meta name="description">` per page (category / article description, site default otherwise).
  - `bin/migrate.php` tolerates `glob()` returning `false`.
  - License: MIT (`LICENSE`, `composer.json`).
- **Alternatives:** leaving the PHP image defaults (`display_errors=On`, version in `X-Powered-By`);
  a Content-Security-Policy header (more value, but needs careful tuning — not required here);
  keeping the eager wiring and documenting the 500-instead-of-404 behavior while MySQL is down.
- **Rationale:** cheap fixes that remove information disclosure and make the deliverable closer to
  production defaults without adding dependencies.
