{* Expects: $pagination (App\Support\Pagination), $baseUrl (URL with the current ?sort=…). Page 1 has no &page. *}
{if $pagination->totalPages() > 1}
    <nav class="pagination" aria-label="Сторінки">
        {if $pagination->hasPrevious()}
            <a class="pagination__link" rel="prev"
               href="{$baseUrl}{if $pagination->page > 2}&amp;page={$pagination->page - 1}{/if}">← Назад</a>
        {/if}

        {foreach $pagination->links() as $page}
            {if $page === null}
                <span class="pagination__gap">…</span>
            {elseif $page === $pagination->page}
                <span class="pagination__link pagination__link--current" aria-current="page">{$page}</span>
            {else}
                <a class="pagination__link" href="{$baseUrl}{if $page > 1}&amp;page={$page}{/if}">{$page}</a>
            {/if}
        {/foreach}

        {if $pagination->hasNext()}
            <a class="pagination__link" rel="next" href="{$baseUrl}&amp;page={$pagination->page + 1}">Далі →</a>
        {/if}
    </nav>
{/if}
