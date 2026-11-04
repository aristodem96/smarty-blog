{* Article preview card. Expects: $article (App\Entity\Article). *}
<article class="article-card">
    <a class="article-card__image-link" href="/article/{$article->slug}" tabindex="-1" aria-hidden="true">
        {if $article->image}
            <img class="article-card__image" src="{$article->image}" alt="" width="800" height="450" loading="lazy">
        {else}
            <span class="article-card__image article-card__image--placeholder"></span>
        {/if}
    </a>
    <div class="article-card__body">
        <h3 class="article-card__title">
            <a href="/article/{$article->slug}">{$article->title}</a>
        </h3>
        <p class="article-card__description">{$article->description|truncate:160:'…'}</p>
        <p class="article-card__meta">
            <time datetime="{$article->publishedAt->format('Y-m-d')}">{$article->publishedAt->format('d.m.Y')}</time>
            <span>Переглядів: {$article->views}</span>
        </p>
    </div>
</article>
