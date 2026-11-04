<?php

declare(strict_types=1);

// Source data for BlogSeeder. Article titles are built as title template × category topic.
// A category without topics gets no articles (checks that the home page hides empty categories).
return [
    'categories' => [
        [
            'name' => 'PHP',
            'description' => 'The language, its ecosystem and modern practices: from the type system to structuring a project without a framework.',
            'topics' => ['PDO', 'Composer', 'The PHP 8 type system', 'PHP enums', 'Readonly properties', 'PSR standards'],
        ],
        [
            'name' => 'JavaScript',
            'description' => 'Modern JavaScript in the browser: asynchronous code, modules, the DOM and the network.',
            'topics' => ['The Fetch API', 'Async/await', 'ES modules', 'Closures', 'The event loop', 'The DOM API'],
        ],
        [
            'name' => 'Databases',
            'description' => 'MySQL in practice: schema design, indexes, transactions and query optimization.',
            'topics' => ['MySQL indexes', 'Transactions', 'JOIN queries', 'Window functions', 'EXPLAIN', 'Schema normalization'],
        ],
        [
            'name' => 'DevOps',
            'description' => 'Environments, deployment and operations: Docker, web servers, CI/CD and observability.',
            'topics' => ['Docker Compose', 'Nginx', 'CI/CD', 'Environment variables', 'Logging', 'Monitoring'],
        ],
        [
            'name' => 'HTML & CSS',
            'description' => 'The building blocks of every interface: preprocessors, layout systems, responsiveness and accessibility.',
            'topics' => ['SCSS', 'Flexbox', 'CSS Grid', 'Responsive layouts', 'BEM', 'Accessibility (a11y)'],
        ],
        [
            'name' => 'Security',
            'description' => 'Web application vulnerabilities and how to defend against them — calmly and with examples.',
            'topics' => ['SQL injection', 'XSS', 'CSRF', 'Password hashing', 'HTTPS', 'Security headers'],
        ],
        [
            'name' => 'Career',
            'description' => 'Interviews, growth and teamwork. Articles are coming soon.',
            'topics' => [],
        ],
    ],

    'title_templates' => [
        '%s: a practical guide',
        '%s in practice',
        '%s: common mistakes and how to avoid them',
        '%s for beginners',
        '%s: lessons from a real project',
        'Deep dive: %s',
        '%s in a nutshell',
        'Checklist: %s',
    ],

    'description_templates' => [
        'We break down %s: the key ideas, practical examples and the pitfalls developers run into.',
        'A short, practical overview of %s — what you need to know to use it at work today.',
        '%s by example: from the basic concepts to solutions that hold up in production.',
        '%s without the theory overload: why it matters and how to avoid the usual mistakes.',
    ],

    'intro_templates' => [
        '%s often looks simple until it meets a real project. This article collects what helps you not only understand the basics but also apply them with confidence in everyday work.',
        'A lot has been written about %s, but most of it is either too shallow or too academic. Let us find the middle ground: minimum theory, maximum practice.',
        'If you have already worked with %s, you know the devil is in the details. Let us walk through the key points step by step.',
    ],

    'paragraphs' => [
        'Start with the main principle: code is read far more often than it is written. Clear names, small functions and explicit dependencies pay off within a week, as soon as you have to come back to the code.',
        'A good habit is to describe the problem in words before writing any code. It often turns out that no complex solution is needed and a few lines of simple code do the job.',
        'Do not optimize blindly. Measure first: profiling and logs quickly reveal the real bottleneck, and it is rarely where you expect it to be.',
        'Pay special attention to error handling. An error that was silently ignored costs far more than one that stopped execution right away with a clear message.',
        'Automating routine steps — starting the environment, running migrations, checks — saves the whole team time. If you have to do something twice, it deserves a script.',
        'Security cannot be "added later". Input validation, parameterized queries and output escaping have to be part of the solution from the very first line of code.',
        'Document the non-obvious, not the obvious. A comment saying "increment the counter" adds nothing, while explaining why a particular approach was chosen saves your colleagues hours.',
        'Tests are not only a correctness check but also the best documentation of how the system behaves. Start with the critical scenarios and extend the coverage gradually.',
        'Remember the user: load speed, clear messages and predictable behavior matter more than fashionable technology under the hood.',
        'Code review is not about finding someone to blame but about sharing knowledge within the team. Specific, friendly comments that explain the "why" make every next pull request better.',
    ],

    'outro_templates' => [
        'To sum up: %s is a tool, not a goal in itself. Use it where it genuinely makes life easier, and do not hesitate to drop it when a simpler solution works better.',
        'We hope this article helps you work with %s more confidently. The best way to make the knowledge stick is to try it on your own project right away.',
        '%s is a much broader topic than a single article can cover, but these principles are enough for a confident start. Practice and careful reading of the documentation will do the rest.',
    ],

    // Paths relative to public/.
    'images' => [
        '/images/articles/01.svg',
        '/images/articles/02.svg',
        '/images/articles/03.svg',
        '/images/articles/04.svg',
        '/images/articles/05.svg',
        '/images/articles/06.svg',
        '/images/articles/07.svg',
        '/images/articles/08.svg',
    ],
];
