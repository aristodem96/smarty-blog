<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use PDO;

final class ArticleRepository
{
    private const COLUMNS = 'a.id, a.title, a.slug, a.description, a.content, a.image, a.views, a.published_at';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findBySlug(string $slug): ?Article
    {
        $stmt = $this->pdo->prepare('SELECT ' . self::COLUMNS . ' FROM articles a WHERE a.slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row === false ? null : Article::fromRow($row);
    }

    /**
     * @return list<Article>
     */
    public function findByCategory(int $categoryId, ArticleSort $sort, int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::COLUMNS . '
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

    /**
     * Articles sharing the most categories with the given one; newer first among equals.
     *
     * @return list<Article>
     */
    public function findSimilar(int $articleId, int $limit): array
    {
        // `mine` uses the (article_id, category_id) primary key, `other` the (category_id, article_id) index.
        // Selecting a.* with GROUP BY a.id is valid under ONLY_FULL_GROUP_BY: columns depend on the primary key.
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::COLUMNS . ', COUNT(*) AS shared_categories
             FROM article_category mine
             JOIN article_category other
               ON other.category_id = mine.category_id
              AND other.article_id <> mine.article_id
             JOIN articles a ON a.id = other.article_id
             WHERE mine.article_id = :article_id
             GROUP BY a.id
             ORDER BY shared_categories DESC, a.published_at DESC, a.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue('article_id', $articleId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(Article::fromRow(...), $stmt->fetchAll());
    }

    /**
     * Atomic increment: no read-modify-write, so concurrent views are never lost.
     */
    public function incrementViews(int $articleId): void
    {
        $stmt = $this->pdo->prepare('UPDATE articles SET views = views + 1 WHERE id = :id');
        $stmt->bindValue('id', $articleId, PDO::PARAM_INT);
        $stmt->execute();
    }
}
