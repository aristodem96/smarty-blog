<?php

declare(strict_types=1);

// Seeds categories and articles. Replaces existing blog data.
// Usage: php bin/seed.php [--articles=N] [--if-empty]
//   --articles=N  number of articles to generate (1..1000, default 80)
//   --if-empty    do nothing when the database already has data (used by the Docker entrypoint)

use App\Core\Database;
use Database\Seeders\BlogSeeder;

require __DIR__ . '/../vendor/autoload.php';

$options = getopt('', ['articles:', 'if-empty']);

$articleCount = filter_var($options['articles'] ?? 80, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 1000],
]);
if ($articleCount === false) {
    fwrite(STDERR, "--articles must be an integer from 1 to 1000.\n");
    exit(1);
}

$config = require __DIR__ . '/../config/config.php';
$seeder = new BlogSeeder(Database::connect($config['db']));

if (isset($options['if-empty']) && !$seeder->isEmpty()) {
    echo "Skipped seeding: the database already has data.\n";
    exit(0);
}

$stats = $seeder->run($articleCount);

printf(
    "Seeded categories: %d, articles: %d, article-category links: %d.\n",
    $stats['categories'],
    $stats['articles'],
    $stats['links'],
);
