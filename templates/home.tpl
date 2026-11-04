{extends file="layout.tpl"}

{block name="content"}
    <h1 class="page-title">Latest articles by category</h1>

    {foreach $sections as $section}
        <section class="category-section">
            <h2 class="category-section__title">
                <a href="/category/{$section.category->slug}">{$section.category->name}</a>
            </h2>

            <div class="article-grid">
                {foreach $section.articles as $article}
                    {include file="partials/article-card.tpl" article=$article}
                {/foreach}
            </div>

            <a class="button" href="/category/{$section.category->slug}">All articles</a>
        </section>
    {foreachelse}
        <p>No articles yet.</p>
    {/foreach}
{/block}
