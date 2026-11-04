<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repository\CategoryRepository;

final class HomeController
{
    private const ARTICLES_PER_CATEGORY = 3;

    public function __construct(
        private readonly View $view,
        private readonly CategoryRepository $categories,
    ) {
    }

    public function index(Request $request): Response
    {
        return new Response($this->view->render('home.tpl', [
            'sections' => $this->categories->findWithLatestArticles(self::ARTICLES_PER_CATEGORY),
        ]));
    }
}
