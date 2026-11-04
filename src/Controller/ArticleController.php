<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class ArticleController
{
    public function __construct(private readonly View $view)
    {
    }

    public function show(Request $request, string $slug): Response
    {
        return new Response($this->view->render('article.tpl', ['slug' => $slug]));
    }
}
