<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Slugger;
use PDO;
use Throwable;

/**
 * Fills categories, articles and article_category with generated demo data.
 * A fixed random seed makes every run produce the same titles, categories and views.
 */
final class BlogSeeder
{
    private const RANDOM_SEED = 42;
    private const MAX_EXTRA_CATEGORIES = 2;
    private const MAX_VIEWS = 5000;
    private const PUBLISHED_WITHIN_DAYS = 365;

    /** @var array<string, array<string, true>> slugs already used, per table */
    private array $usedSlugs = [];

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function isEmpty(): bool
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() === 0;
    }

    /**
     * Replaces all blog data.
     *
     * @return array{categories: int, articles: int, links: int}
     */
    public function run(int $articleCount): array
    {
        mt_srand(self::RANDOM_SEED);
        $this->usedSlugs = [];
        $data = require __DIR__ . '/data/blog.php';

        // TRUNCATE is DDL (implicit commit), so it runs before the transaction.
        $this->truncate();

        $this->pdo->beginTransaction();
        try {
            $categoryIds = $this->insertCategories($data['categories']);
            $stats = $this->insertArticles($data, $categoryIds, $articleCount);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return ['categories' => count($categoryIds)] + $stats;
    }

    private function truncate(): void
    {
        // MySQL refuses to TRUNCATE a table referenced by a foreign key, even an empty one.
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['article_category', 'articles', 'categories'] as $table) {
            $this->pdo->exec("TRUNCATE TABLE {$table}");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * @param list<array{name: string, description: string, topics: list<string>}> $categories
     * @return list<int> category ids in the same order as $categories
     */
    private function insertCategories(array $categories): array
    {
        $insert = $this->pdo->prepare(
            'INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)'
        );

        $ids = [];
        foreach ($categories as $category) {
            $insert->execute([
                'name' => $category['name'],
                'slug' => $this->uniqueSlug('categories', $category['name']),
                'description' => $category['description'],
            ]);
            $ids[] = (int) $this->pdo->lastInsertId();
        }

        return $ids;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<int> $categoryIds
     * @return array{articles: int, links: int}
     */
    private function insertArticles(array $data, array $categoryIds, int $articleCount): array
    {
        $insertArticle = $this->pdo->prepare(
            'INSERT INTO articles (title, slug, description, content, image, views, published_at)
             VALUES (:title, :slug, :description, :content, :image, :views, :published_at)'
        );
        $insertLink = $this->pdo->prepare(
            'INSERT INTO article_category (article_id, category_id) VALUES (:article_id, :category_id)'
        );

        // Every unique (category, topic, title template) combination, in random order.
        $combinations = [];
        $categoriesWithTopics = [];
        foreach ($data['categories'] as $index => $category) {
            if ($category['topics'] !== []) {
                $categoriesWithTopics[] = $index;
            }
            foreach ($category['topics'] as $topic) {
                foreach ($data['title_templates'] as $template) {
                    $combinations[] = [$index, $topic, $template];
                }
            }
        }
        shuffle($combinations);

        $links = 0;
        for ($i = 0; $i < $articleCount; $i++) {
            [$primary, $topic, $template] = $combinations[$i % count($combinations)];

            $title = sprintf($template, $topic);
            $round = intdiv($i, count($combinations));
            if ($round > 0) {
                $title .= ' (частина ' . ($round + 1) . ')';
            }

            $insertArticle->execute([
                'title' => $title,
                'slug' => $this->uniqueSlug('articles', $title),
                'description' => sprintf($this->pick($data['description_templates']), $topic),
                'content' => $this->buildContent($data, $topic),
                'image' => $this->pick($data['images']),
                'views' => mt_rand(0, self::MAX_VIEWS),
                'published_at' => date('Y-m-d H:i:s', time() - mt_rand(0, self::PUBLISHED_WITHIN_DAYS * 86400)),
            ]);
            $articleId = (int) $this->pdo->lastInsertId();

            foreach ($this->pickCategories($primary, $categoriesWithTopics) as $index) {
                $insertLink->execute(['article_id' => $articleId, 'category_id' => $categoryIds[$index]]);
                $links++;
            }
        }

        return ['articles' => $articleCount, 'links' => $links];
    }

    /**
     * The topic's own category plus up to two random others, so some articles belong to several categories.
     *
     * @param list<int> $candidates
     * @return list<int>
     */
    private function pickCategories(int $primary, array $candidates): array
    {
        $others = array_values(array_diff($candidates, [$primary]));
        shuffle($others);

        return [$primary, ...array_slice($others, 0, mt_rand(0, self::MAX_EXTRA_CATEGORIES))];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildContent(array $data, string $topic): string
    {
        $paragraphs = $data['paragraphs'];
        shuffle($paragraphs);

        return implode("\n\n", [
            sprintf($this->pick($data['intro_templates']), $topic),
            ...array_slice($paragraphs, 0, mt_rand(3, 5)),
            sprintf($this->pick($data['outro_templates']), $topic),
        ]);
    }

    private function uniqueSlug(string $table, string $text): string
    {
        $base = Slugger::slugify($text);
        $slug = $base;
        for ($n = 2; isset($this->usedSlugs[$table][$slug]); $n++) {
            $slug = $base . '-' . $n;
        }
        $this->usedSlugs[$table][$slug] = true;

        return $slug;
    }

    /**
     * @param list<string> $items
     */
    private function pick(array $items): string
    {
        return $items[mt_rand(0, count($items) - 1)];
    }
}
