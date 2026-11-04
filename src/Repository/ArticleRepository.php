<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use PDO;

final class ArticleRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<Article>
     */
    public function findByCategory(int $categoryId, ArticleSort $sort, int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.id, a.title, a.slug, a.description, a.content, a.image, a.views, a.published_at
             FROM articles a
             JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id = :category_id
             ORDER BY ' . $sort->orderBy() . '
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue('category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(Article::fromRow(...), $stmt->fetchAll());
    }

    public function countByCategory(int $categoryId): int
    {
        // Answered from the (category_id, article_id) index alone; articles is not read.
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM article_category WHERE category_id = :category_id');
        $stmt->bindValue('category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
