{extends file="layout.tpl"}

{block name="title"}Помилка сервера — Smarty Blog{/block}

{block name="content"}
    <h1>500 — щось пішло не так</h1>
    <p>Спробуйте оновити сторінку пізніше.</p>
    {if $error}
        <pre>{$error}</pre>
    {/if}
{/block}
