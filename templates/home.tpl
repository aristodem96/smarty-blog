{extends file="layout.tpl"}

{block name="content"}
    <h1 class="page-title">Свіжі статті за категоріями</h1>

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

            <a class="button" href="/category/{$section.category->slug}">Усі статті</a>
        </section>
    {foreachelse}
        <p>Статей поки немає.</p>
    {/foreach}
{/block}
