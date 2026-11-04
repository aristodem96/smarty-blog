{extends file="layout.tpl"}

{block name="title"}{$category->name} — Smarty Blog{/block}

{block name="content"}
    <header class="page-header">
        <h1 class="page-title">{$category->name}</h1>
        {if $category->description}
            <p class="page-header__description">{$category->description}</p>
        {/if}
    </header>

    {if $articles}
        {* Switching the sort order starts again from page 1. *}
        <nav class="sort" aria-label="Сортування статей">
            <span class="sort__label">Сортувати:</span>
            {foreach $sorts as $option}
                {if $option === $sort}
                    <span class="sort__option sort__option--active" aria-current="true">{$option->label()}</span>
                {else}
                    <a class="sort__option" href="/category/{$category->slug}?sort={$option->value}">{$option->label()}</a>
                {/if}
            {/foreach}
        </nav>

        <div class="article-grid">
            {foreach $articles as $article}
                {include file="partials/article-card.tpl" article=$article}
            {/foreach}
        </div>

        {include file="partials/pagination.tpl"}
    {else}
        <p>У цій категорії поки немає статей.</p>
    {/if}
{/block}
