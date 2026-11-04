<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repository\ArticleRepository;
use App\Repository\ArticleSort;
use App\Repository\CategoryRepository;
use App\Support\Pagination;

final class CategoryController
{
    private const PER_PAGE = 9;

    public function __construct(
        private readonly View $view,
        private readonly CategoryRepository $categories,
        private readonly ArticleRepository $articles,
    ) {
    }

    public function show(Request $request, string $slug): Response
    {
        $category = $this->categories->findBySlug($slug) ?? throw new NotFoundException();

        // Unknown sort values fall back to the default instead of failing.
        $sort = ArticleSort::tryFrom($request->query('sort') ?? '') ?? ArticleSort::Date;

        $pagination = new Pagination(
            $this->pageFrom($request),
            self::PER_PAGE,
            $this->articles->countByCategory($category->id),
        );
        if ($pagination->isOutOfRange()) {
            throw new NotFoundException();
        }

        return new Response($this->view->render('category.tpl', [
            'category' => $category,
            'articles' => $this->articles->findByCategory(
                $category->id,
                $sort,
                $pagination->perPage,
                $pagination->offset(),
            ),
            'sort' => $sort,
            'sorts' => ArticleSort::cases(),
            'pagination' => $pagination,
            'baseUrl' => '/category/' . $category->slug . '?sort=' . $sort->value,
        ]));
    }

    /**
     * Missing ?page means page 1; anything but a positive integer is a page that does not exist.
     */
    private function pageFrom(Request $request): int
    {
        $page = $request->query('page');
        if ($page === null) {
            return 1;
        }

        $page = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $page === false ? throw new NotFoundException() : $page;
    }
}
