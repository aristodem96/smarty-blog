# Архітектура

Рішення: ADR-002 (структура), ADR-003 (схема БД) — див. [DECISIONS.md](DECISIONS.md).

## 1. Структура проєкту
```
smarty-blog/
├── public/                 # document root веб-сервера — тільки це доступно ззовні
│   ├── index.php           # front controller: єдина точка входу всіх HTTP-запитів
│   └── assets/             # скомпільований CSS, зображення статей
├── src/                    # PHP-код, namespace App\ (PSR-4)
│   ├── Core/               # інфраструктура: Router, Database (PDO), View (Smarty)
│   ├── Controller/         # HomeController, CategoryController, ArticleController
│   └── Repository/         # CategoryRepository, ArticleRepository — увесь SQL тут
├── templates/              # Smarty-шаблони (.tpl): layout, сторінки, partials/
├── config/                 # конфіг застосунку (значення з env-змінних)
├── database/
│   ├── migrations/         # SQL-файли схеми
│   └── seeds/              # логіка сидера
├── bin/                    # CLI-скрипти: migrate.php, seed.php
├── scss/                   # вихідні стилі → компілюються в public/assets/css
├── docker/                 # Dockerfile PHP, конфіг Nginx
├── var/                    # скомпільовані шаблони Smarty, кеш (у .gitignore)
└── docs/                   # ТЗ, архітектура, рішення, прогрес, журнал ІІ
```

Чому `public/` окремо: веб-сервер віддає лише цю папку, тож `src/`, `config/`, `vendor/`
фізично недоступні з браузера (не можна відкрити `/config/config.php`).

## 2. Схема БД
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
  description   TEXT NOT NULL                  -- анонс, може бути великим
  content       MEDIUMTEXT NOT NULL            -- повний текст
  image         VARCHAR(255) NULL              -- шлях до файлу, не BLOB
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

migrations                                     -- службова, веде bin/migrate.php
  name          VARCHAR(255) PK                -- ім'я застосованого SQL-файлу
  applied_at    DATETIME
```
Engine InnoDB, charset `utf8mb4`, collation `utf8mb4_0900_ai_ci`.
Міграції: `database/migrations/NNN_*.sql`, одна таблиця на файл (ADR-006).

| Індекс | Який запит обслуговує |
|---|---|
| `categories.slug`, `articles.slug` UNIQUE | пошук сторінки за URL + гарантія унікальності |
| `article_category` PK `(article_id, category_id)` | категорії статті → «схожі статті»; захист від дублів |
| `idx_article_category_category (category_id, article_id)` | статті категорії → головна, сторінка категорії |
| `idx_published_at`, `idx_views` | сортування за датою / переглядами |

## 3. Потік запиту
```
HTTP-запит
  → Nginx (усе, що не є файлом у public/, віддає на public/index.php)
  → public/index.php        — bootstrap: autoload, конфіг, PDO, Smarty
  → Router                  — зіставляє метод + шлях із маршрутом
  → Controller              — читає параметри запиту, валідує їх
  → Repository              — SQL через PDO (prepared statements)
  → Smarty-шаблон           — рендер HTML з екрануванням
  → HTTP-відповідь (200 / 404)
```

## 4. Маршрути
| Метод | Шлях                | Сторінка                                         |
|-------|---------------------|--------------------------------------------------|
| GET   | `/`                 | Головна                                          |
| GET   | `/category/{slug}`  | Категорія (`?sort=date\|views`, `?page=N`)       |
| GET   | `/article/{slug}`   | Стаття                                           |
| —     | будь-що інше        | 404                                              |
