<?php

declare(strict_types=1);

namespace App\Core;

use Smarty\Smarty;

final class View
{
    private Smarty $smarty;

    public function __construct(string $templateDir, string $compileDir)
    {
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir($templateDir);
        $this->smarty->setCompileDir($compileDir);
        // Every {$var} is HTML-escaped by default; raw output needs an explicit |nofilter.
        $this->smarty->setEscapeHtml(true);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        // A separate template object keeps variables from leaking between renders.
        $tpl = $this->smarty->createTemplate($template);
        $tpl->assign($data);

        return $tpl->fetch();
    }
}
