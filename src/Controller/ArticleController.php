<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Support\ViewedArticles;

final class ArticleController
{
    private const SIMILAR_LIMIT = 3;

    public function __construct(
        private readonly View $view,
        private readonly ArticleRepository $articles,
        private readonly CategoryRepository $categories,
    ) {
    }

    public function show(Request $request, string $slug): Response
    {
        $article = $this->articles->findBySlug($slug) ?? throw new NotFoundException();

        // One view per browser: a refresh or a HEAD request does not count again.
        $headers = [];
        $viewed = ViewedArticles::fromCookie($request->cookie(ViewedArticles::COOKIE_NAME));
        if ($request->method === 'GET' && !$viewed->has($article->id)) {
            $this->articles->incrementViews($article->id);
            $article = $article->withViews($article->views + 1);
            $headers['Set-Cookie'] = $viewed->with($article->id)->toCookieHeader();
        }

        return new Response($this->view->render('article.tpl', [
            'article' => $article,
            'categories' => $this->categories->findByArticle($article->id),
            'similar' => $this->articles->findSimilar($article->id, self::SIMILAR_LIMIT),
        ]), 200, $headers);
    }
}
