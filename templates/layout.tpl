<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>{block name="title"}Smarty Blog{/block}</title>
    <meta name="description" content="{block name="description"}A blog about web development: PHP, JavaScript, databases, DevOps, HTML &amp; CSS and security.{/block}">
    {* ?v= changes whenever the CSS is rebuilt, so browsers never keep a stale stylesheet. *}
    <link rel="stylesheet" href="/assets/css/app.css?v={$assetVersion}">
</head>
<body>
    <header class="site-header">
        <div class="container site-header__inner">
            <a href="/" class="site-header__logo">Smarty Blog</a>
        </div>
    </header>

    <main class="container">
        {block name="content"}{/block}
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>Smarty Blog — AbeloHost test assignment</p>
        </div>
    </footer>
</body>
</html>
