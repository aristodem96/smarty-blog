# Task

## Original (Russian, verbatim as received from AbeloHost — do not edit)

```text
Отлично, распишу тестовое задание, подскажите сколько времени вам потребуется?

Задание является неоплачиваемым. Так же мы хотели бы заверить вас, что решения, разработанные в ходе выполнения тестового задания, не будут использованы в коммерческих целях.

Необходимо разработать простой, но полностью рабочий веб-сайт на чистом PHP (без фреймворков) с использованием MySQL и шаблонизатора Smarty. Сайт должен реализовывать функционал блога с категориями и постами.

Технологический стек:
● PHP 8.1+
● Шаблонизатор Smarty
● База данных MySQL
● Не использовать фреймворки

Структура данных:
● Категория:
○ Название
○ Описание
● Статья:
○ Изображение
○ Название
○ Описание
○ Текст
○ Категория (одна или несколько)
○ Кол-во просмотров

Обязательные страницы:
● Главная страница:
○ Вывести каждую категорию, в которой есть статьи и отобразить 3 последних поста (по дате публикации).
○ Вывести кнопку “Все статьи” для каждой категории.
● Страница категории:
○ Вывести название, описание, список статей
○ Реализовать сортировку статей (по кол-во просмотров, по дате публикации)
○ Реализовать пагинацию
● Страница статьи:
○ Вывести всю информацию о статье
○ Вывести блок из 3 похожих статей

Дополнительный функционал:
● Реализовать функционал для сидинга категорий и статей

Что будет оцениваться:
● Простота, читаемость и структура кода
● Структура проекта
● Работа с MySQL
● Уровень самостоятельной реализации
● Глубина понимания решения
Будет плюсом:
● Использование SCSS для стилей
● Docker-окружение

Выполненное задание можно прислать в одном из следующих форматов: ссылка на открытый репозиторий GitLab/GitHub/Bitbucket по вашему выбору
Вместе с выполненным заданием, нужно указать использовали ли вы ИИ при выполнении и если использовали, то для чего конкретно. Это важно для корректной оценки результата, поскольку при рассмотрении мы отдельно обращаем внимание на уровень самостоятельной реализации, глубину понимания решения и ваш личный технический подход.
Также будем признательны, если коммиты будут вноситься поэтапно в процессе разработки, чтобы можно было увидеть ход работы и логику реализации.
```

---

## English translation

Great, here is the test assignment — please let us know how much time you will need.

The assignment is unpaid. We also want to assure you that solutions developed during the test
assignment will not be used for commercial purposes.

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

**Submission:** a link to a public GitLab/GitHub/Bitbucket repository of your choice. Together with
the result, state whether you used AI and, if so, for what exactly — this matters for a fair
assessment, since the review pays particular attention to the level of own implementation, the depth
of understanding and your personal technical approach. Incremental commits during development are
appreciated, so the progress and logic of the implementation can be seen.

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
