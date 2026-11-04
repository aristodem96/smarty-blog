{extends file="layout.tpl"}

{block name="title"}Server error — Smarty Blog{/block}

{block name="content"}
    <h1 class="page-title">500 — something went wrong</h1>
    <p>Please try again later.</p>
    {if $error}
        <pre>{$error}</pre>
    {/if}
{/block}
