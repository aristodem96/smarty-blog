{extends file="layout.tpl"}

{block name="title"}{$article->title} — Smarty Blog{/block}

{block name="description"}{$article->description|truncate:160:'…'}{/block}

{block name="content"}
    <article class="article">
        <header class="article__header">
            {if $categories}
                <ul class="article__categories" aria-label="Categories">
                    {foreach $categories as $category}
                        <li><a class="tag" href="/category/{$category->slug}">{$category->name}</a></li>
                    {/foreach}
                </ul>
            {/if}

            <h1 class="article__title">{$article->title}</h1>

            <p class="article__meta">
                <time datetime="{$article->publishedAt->format('Y-m-d')}">{$article->publishedAt->format('M j, Y')}</time>
                <span>Views: {$article->views}</span>
            </p>

            <p class="article__lead">{$article->description}</p>
        </header>

        {if $article->image}
            <img class="article__image" src="{$article->image}" alt="" width="800" height="450">
        {/if}

        <div class="article__content">
            {foreach $article->paragraphs() as $paragraph}
                <p>{$paragraph}</p>
            {/foreach}
        </div>
    </article>

    {if $similar}
        <section class="similar" aria-labelledby="similar-title">
            <h2 class="similar__title" id="similar-title">Similar articles</h2>
            <div class="article-grid">
                {foreach $similar as $item}
                    {include file="partials/article-card.tpl" article=$item}
                {/foreach}
            </div>
        </section>
    {/if}
{/block}
