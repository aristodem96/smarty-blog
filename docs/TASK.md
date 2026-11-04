# Task

## Original (Russian, verbatim as received from AbeloHost — do not edit)

Необходимо разработать простой, но полностью рабочий веб-сайт на чистом PHP (без фреймворков) с использованием MySQL и шаблонизатора Smarty. Сайт должен реализовывать функционал блога с категориями и постами.

Технологический стек:
- PHP 8.1+
- Шаблонизатор Smarty
- База данных MySQL
- Не использовать фреймворки

Структура данных:
- Категория: Название, Описание
- Статья: Изображение, Название, Описание, Текст, Категория (одна или несколько), Кол-во просмотров

Обязательные страницы:
- Главная страница:
  - Вывести каждую категорию, в которой есть статьи, и отобразить 3 последних поста (по дате публикации).
  - Вывести кнопку «Все статьи» для каждой категории.
- Страница категории:
  - Вывести название, описание, список статей
  - Реализовать сортировку статей (по кол-ву просмотров, по дате публикации)
  - Реализовать пагинацию
- Страница статьи:
  - Вывести всю информацию о статье
  - Вывести блок из 3 похожих статей

Дополнительный функционал:
- Реализовать функционал для сидинга категорий и статей

Что будет оцениваться:
- Простота, читаемость и структура кода
- Структура проекта
- Работа с MySQL
- Уровень самостоятельной реализации
- Глубина понимания решения

Будет плюсом:
- Использование SCSS для стилей
- Docker-окружение

Сдача: ссылка на открытый репозиторий GitHub. Коммиты — поэтапно, чтобы был виден ход работы и логика реализации. Нужно указать, использовался ли ИИ и для чего конкретно.

---

## English translation

Build a simple but fully working website in plain PHP (no frameworks) using MySQL and the Smarty
template engine. The site implements a blog with categories and posts.

**Tech stack:** PHP 8.1+, Smarty, MySQL, no frameworks.

**Data structure:**
- Category: name, description.
- Article: image, title, description, text, category (one or more), view count.

**Required pages:**
- Home page:
  - Show every category that has articles, with its 3 latest posts (by publication date).
  - Show an "All articles" button for each category.
- Category page:
  - Show the name, description and list of articles.
  - Implement sorting (by view count, by publication date).
  - Implement pagination.
- Article page:
  - Show all information about the article.
  - Show a block of 3 similar articles.

**Additional functionality:** seeding of categories and articles.

**Evaluation criteria:** simplicity, readability and structure of the code; project structure; work
with MySQL; level of own implementation; depth of understanding of the solution.

**Nice to have:** SCSS for styles; a Docker environment.

**Submission:** a link to a public GitHub repository. Commits should be incremental, showing the
progress and logic of the implementation. State whether AI was used and for what exactly.

---

## Additional quality criteria (set by the author, not by the company)

- Simple, readable code without architecture for its own sake, but with a clear structure
  (`public/index.php` as the entry point, a simple router, repositories for DB access, Smarty templates separately).
- PDO with prepared statements, no SQL injection; a proper schema with an `article_category` pivot
  table (many-to-many) and indexes for sorting.
- View counter, LIMIT/OFFSET pagination, "similar articles" based on shared categories.
- Output escaping in templates (XSS), 404 handling.
- A seeder that runs with a single command.
- Docker (PHP + MySQL + web server) started with a single command; SCSS compiled to CSS.
- README: how to run, project structure, key decisions.
