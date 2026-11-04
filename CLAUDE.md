# CLAUDE.md — smarty-blog

Тестове завдання AbeloHost (Full-stack PHP/JS): блог з категоріями й статтями
на чистому PHP + Smarty + MySQL. Дедлайн: **понеділок, 09.11.2026**.
Повне ТЗ: [docs/TASK.md](docs/TASK.md).

## На початку кожної сесії прочитай
1. [docs/PROGRESS.md](docs/PROGRESS.md) — де зупинились, поточний етап.
2. [docs/DECISIONS.md](docs/DECISIONS.md) — вже прийняті рішення (не переглядай їх без запиту).
3. [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) — структура, схема БД, потік запиту.
4. [docs/AI_USAGE.md](docs/AI_USAGE.md) — журнал використання ІІ (дописуй після кожного етапу).

## Стек і жорсткі обмеження
- PHP 8.1+ (без фреймворків: ні Laravel, ні Symfony, ні Slim, ні їхніх компонентів).
  Код — **лише з можливостями 8.1** (Composer `platform.php = 8.1.0`), Docker запускає 8.3 (ADR-004).
- Smarty — єдиний шаблонізатор.
- MySQL 8 через **PDO** (без ORM, без query builder-ів).
- Composer — лише для Smarty та автозавантаження PSR-4.
- SCSS → CSS через `sass` (npm), JS — мінімальний, без фреймворків.
- Docker: PHP-FPM 8.3 + Nginx + MySQL 8.4. Entrypoint PHP виконується на кожному
  старті — усе, що в нього додаємо (міграції, сидер), має бути ідемпотентним (ADR-005).
- **Жодних нових бібліотек (composer/npm) без явного дозволу автора.**

## Структура папок
Шари за типом (ADR-002). Деталі — docs/ARCHITECTURE.md.
- `public/` — document root: `index.php` (єдина точка входу), `assets/`.
- `src/Core/` — Router, Request, Response, View (Smarty), Database (PDO), NotFoundException.
- `src/Controller/` — метод повертає `Response`; «не знайдено» → `throw new NotFoundException()`.
  Маршрути й збирання залежностей — у `public/index.php` (ADR-007).
  `src/Repository/` — **увесь SQL тільки тут**. Namespace `App\` → `src/`.
- `templates/` — Smarty `.tpl`. `config/` — конфіг з env. `bin/` — CLI (migrate, seed).
- `database/migrations/NNN_*.sql` — одна таблиця/зміна на файл, існуючі файли не редагуємо
  (зміна схеми = новий файл); `database/seeds/` — `BlogSeeder` (`Database\Seeders\`) і дані.
- `src/Entity/` — readonly-сутності з `fromRow()`; репозиторії повертають сутності, не масиви.
  Дати — `DateTimeImmutable`, у шаблоні `->format()` (не `date_format`: він на `strftime`).
- `src/Support/` — утиліти (`Slugger`, `Pagination`, `ViewedArticles`).
- Динамічний `ORDER BY` — лише через enum `ArticleSort::orderBy()`, ніколи з вводу користувача. `public/images/articles/` — SVG статей.
- `scss/` → `public/assets/css`. `docker/` — оточення. `var/` — кеш Smarty (gitignored).
- В URL — `slug`, не `id` (ADR-003).

## Правила коду
- `declare(strict_types=1);` у кожному PHP-файлі.
- PSR-12, PSR-4 автозавантаження, повна типізація параметрів, повернень і властивостей.
- SQL — тільки prepared statements з плейсхолдерами; ніякої конкатенації
  користувацьких даних у SQL. Для `ORDER BY` — whitelist дозволених значень.
- Шаблони: `escape_html` увімкнено глобально — `{$var}` екранується автоматично;
  сирий HTML (`nofilter`) — лише свідомо і з коментарем чому. Сторінки — `{extends file="layout.tpl"}`.
- Query-параметри і cookies — лише через `Request::query()` / `Request::cookie()`, не через
  `$_GET` / `$_COOKIE`; заголовки відповіді (`Set-Cookie`) — через `Response`, не `header()`.
- Невідомий маршрут або відсутній запис → HTTP 404 + шаблон 404.
- Без «магії»: без `__get`/`__call`, без глобальних змінних, без service locator.
  Залежності — через конструктор, збираються явно в одному місці.
- Простота понад «архітектуру заради архітектури»: новий шар/клас — лише якщо
  він прибирає реальне дублювання чи змішування відповідальностей.
- Коментарі — тільки там, де «чому» неочевидне з коду.

## Команди
Сайт: http://localhost:8080 · MySQL з хоста: `127.0.0.1:3307` (blog / secret).
```bash
docker compose up -d --build          # підняти все; entrypoint сам робить composer install
docker compose logs -f php            # логи PHP / entrypoint / error_log
APP_DEBUG=1 docker compose up -d php  # деталі помилок на сторінці 500
docker compose exec php sh            # shell у контейнері PHP
docker compose exec php composer validate --strict
docker compose down                   # зупинити (додати -v — стерти БД)
docker compose exec php php bin/migrate.php           # нові міграції (також на старті)
docker compose exec php php bin/migrate.php --fresh   # видалити всі таблиці й перестворити
docker compose exec php php bin/seed.php              # перестворити демо-дані (80 статей)
docker compose exec php php bin/seed.php --articles=300
# далі по етапах:
npm run build:css                     # SCSS → CSS (етап 8)
```

## Правила роботи з автором
- **Архітектурні рішення приймає автор.** Ти пропонуєш 2–3 варіанти з плюсами
  й мінусами та рекомендацією; після рішення — записуєш його в DECISIONS.md.
- Рутину (шаблонний код, стилі, Docker, сидинг) робиш після погодження підходу.
- **Один етап — один коміт.** Не переходь до наступного етапу без «ок» автора.
- Не роби масових змін у файлах, яких не стосується поточний етап.
- Коміти робить автор. `git push` — заборонено. Сам комітиш лише на прямий запит.
- Пояснюй кожен крок так, щоб автор міг захистити його на інтерв'ю
  (автор — Laravel-розробник: проводь паралелі з Laravel, де це допомагає).
- Мова спілкування та docs — українська; код, ідентифікатори і коміти — англійською.

## Після кожного етапу
1. Онови статус у docs/PROGRESS.md.
2. Допиши ADR у docs/DECISIONS.md (якщо були рішення).
3. Допиши запис у docs/AI_USAGE.md: що зробив ІІ, що вирішив автор.
4. Онови docs/ARCHITECTURE.md, якщо змінилась структура/схема.
5. Запропонуй повідомлення коміту (Conventional Commits, англійською) — див. `/commit-msg`.

## Власні команди (.claude/commands/)
- `/stage` — поточний етап, план кроків, файли, які зачепимо.
- `/review` — рев'ю змін етапу: читабельність, безпека, відповідність ТЗ і CLAUDE.md.
- `/commit-msg` — повідомлення коміту в стилі Conventional Commits.
- `/explain` — пояснення останніх змін у форматі «питання на інтерв'ю».
