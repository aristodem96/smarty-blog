# Architecture

Key decisions: ADR-002 (structure), ADR-003 (database schema) — see [DECISIONS.md](DECISIONS.md).

## 1. Project structure
```
smarty-blog/
├── public/                 # web server document root — the only publicly reachable folder
│   ├── index.php           # front controller: single entry point for every HTTP request
│   ├── assets/             # compiled CSS (build output, gitignored)
│   └── images/articles/    # article SVG illustrations (path stored in articles.image)
├── src/                    # PHP code, namespace App\ (PSR-4)
│   ├── Core/               # infrastructure: Router, Request, Response, View (Smarty),
│   │                       #   Database (PDO), NotFoundException
│   ├── Controller/         # HomeController, CategoryController, ArticleController
│   ├── Entity/             # readonly entities Category, Article (fromRow from a DB row)
│   ├── Repository/         # CategoryRepository, ArticleRepository, ArticleSort (enum) — all SQL
│   └── Support/            # Slugger (transliteration → slug), Pagination (value object),
│                           #   ViewedArticles (viewed-articles cookie)
├── templates/              # Smarty templates (.tpl): layout, pages, partials/, errors/
├── config/                 # application config (values from environment variables)
├── database/
│   ├── migrations/         # SQL schema files
│   └── seeds/              # BlogSeeder (namespace Database\Seeders), data/blog.php — source data
├── bin/                    # CLI scripts: migrate.php, seed.php
├── scss/                   # source styles → public/assets/css/app.css (assets service)
│   ├── app.scss            #   entry point: @use only
│   ├── abstracts/          #   variables, mixins (respond-to, visually-hidden, focus-ring)
│   ├── base/               #   theme (custom properties, light/dark), reset, typography
│   ├── layout/             #   container, header, footer, grid
│   └── components/         #   one BEM block per file (article-card, pagination, …)
├── package.json            # sass + build / watch scripts
├── docker/                 # PHP Dockerfile, entrypoint and app.ini; Nginx config
├── var/                    # compiled Smarty templates (gitignored)
└── docs/                   # task, architecture, decisions, progress, AI usage log
```

Why `public/` is separate: the web server serves only this folder, so `src/`, `config/` and `vendor/`
are physically unreachable from a browser (there is no way to open `/config/config.php`).

## 2. Database schema
```sql
categories
  id            INT UNSIGNED PK AUTO_INCREMENT
  name          VARCHAR(255) NOT NULL
  slug          VARCHAR(255) NOT NULL          UNIQUE
  description   TEXT NULL
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP

articles
  id            INT UNSIGNED PK AUTO_INCREMENT
  title         VARCHAR(255) NOT NULL
  slug          VARCHAR(255) NOT NULL          UNIQUE
  description   TEXT NOT NULL                  -- summary, can be long
  content       MEDIUMTEXT NOT NULL            -- full text
  image         VARCHAR(255) NULL              -- file path, not a BLOB
  views         INT UNSIGNED NOT NULL DEFAULT 0
  published_at  DATETIME NOT NULL
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  INDEX idx_articles_published_at (published_at)
  INDEX idx_articles_views (views)

article_category
  article_id    INT UNSIGNED  FK → articles.id   ON DELETE CASCADE
  category_id   INT UNSIGNED  FK → categories.id ON DELETE CASCADE
  PRIMARY KEY (article_id, category_id)
  INDEX idx_article_category_category (category_id, article_id)

migrations                                     -- bookkeeping, managed by bin/migrate.php
  name          VARCHAR(255) PK                -- applied SQL file name
  applied_at    DATETIME
```
Engine InnoDB, charset `utf8mb4`, collation `utf8mb4_0900_ai_ci`.
Migrations: `database/migrations/NNN_*.sql`, one table per file (ADR-006).

| Index | Query it serves |
|---|---|
| `categories.slug`, `articles.slug` UNIQUE | page lookup by URL + uniqueness guarantee |
| `article_category` PK `(article_id, category_id)` | categories of an article → similar articles; no duplicate links |
| `idx_article_category_category (category_id, article_id)` | articles of a category → home page, category page |
| `idx_articles_published_at`, `idx_articles_views` | sorting by date / views |

## 3. Request flow
```
HTTP request
  → Nginx (anything that is not a file in public/ goes to public/index.php)
  → public/index.php        — bootstrap: autoload, config, error handler, View, lazy PDO,
                              routes as closures that build their controller on match
  → Request::fromGlobals()  — method, path, query, cookies (the only place reading superglobals)
  → Router::dispatch()      — GET/HEAD + path regex; {slug} → named argument; trailing slash → 301
  → Controller              — validates query input, calls repositories, returns a Response
  → Repository              — SQL through PDO (prepared statements)
  → View::render()          — Smarty template with auto-escaping
  → Response::send()        — status, headers, body

Errors (caught in index.php):
  NotFoundException  → 404 Response (errors/404.tpl)
  any other Throwable → error_log + 500 Response (errors/500.tpl; details only with APP_DEBUG=1)
```

## 4. Key queries
| Page | Repository methods | Queries | How |
|---|---|---|---|
| Home | `CategoryRepository::findWithLatestArticles(3)` | 1 | `ROW_NUMBER()` + `MAX() OVER` in a subquery over ids; full columns only for `position <= 3` |
| Category | `CategoryRepository::findBySlug`, `ArticleRepository::countByCategory`, `ArticleRepository::findByCategory` | 3 | slug → UNIQUE index; COUNT from the pivot index only; list with `ORDER BY` from enum `ArticleSort`, `LIMIT/OFFSET` |
| Article | `ArticleRepository::findBySlug`, `incrementViews`*, `findSimilar`; `CategoryRepository::findByArticle` | 3–4 | *UPDATE only for a new view (cookie); similar = `article_category` self-join + `COUNT(*)` of shared categories |

## 5. Routes
| Method | Path                | Page                                              |
|--------|---------------------|---------------------------------------------------|
| GET    | `/`                 | Home                                              |
| GET    | `/category/{slug}`  | Category (`?sort=date\|views`, `?page=N`)         |
| GET    | `/article/{slug}`   | Article                                           |
| —      | anything else       | 404                                               |

A matching path with a trailing slash (`/category/php/`) gets a 301 to the canonical URL without it.
