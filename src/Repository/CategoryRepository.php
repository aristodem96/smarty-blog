<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Category;
use PDO;

final class CategoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Categories that have articles, each with its latest $perCategory articles.
     * Categories are ordered by their most recent article. One query, no N+1.
     *
     * @return list<array{category: Category, articles: list<Article>}>
     */
    public function findWithLatestArticles(int $perCategory): array
    {
        // The inner query ranks only ids; full article columns are joined for the selected rows.
        // Categories without articles produce no rows in article_category, so they drop out.
        $stmt = $this->pdo->prepare(
            'SELECT c.id AS category_id, c.name AS category_name,
                    c.slug AS category_slug, c.description AS category_description,
                    a.id, a.title, a.slug, a.description, a.content, a.image, a.views, a.published_at
             FROM (
                 SELECT ac.category_id,
                        ac.article_id,
                        ROW_NUMBER() OVER (
                            PARTITION BY ac.category_id
                            ORDER BY a.published_at DESC, a.id DESC
                        ) AS position,
                        MAX(a.published_at) OVER (PARTITION BY ac.category_id) AS latest_published_at
                 FROM article_category ac
                 JOIN articles a ON a.id = ac.article_id
             ) ranked
             JOIN categories c ON c.id = ranked.category_id
             JOIN articles a ON a.id = ranked.article_id
             WHERE ranked.position <= :per_category
             ORDER BY ranked.latest_published_at DESC, c.id, ranked.position'
        );
        $stmt->bindValue('per_category', $perCategory, PDO::PARAM_INT);
        $stmt->execute();

        // PHP arrays keep insertion order, so the SQL ordering survives the grouping.
        $sections = [];
        foreach ($stmt as $row) {
            $categoryId = (int) $row['category_id'];
            $sections[$categoryId] ??= ['category' => Category::fromRow($row, 'category_'), 'articles' => []];
            $sections[$categoryId]['articles'][] = Article::fromRow($row);
        }

        return array_values($sections);
    }
}
