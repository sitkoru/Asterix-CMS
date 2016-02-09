<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {foreach from=$urls item=url}
        <url>
            <loc>{$url}</loc>
            <lastmod>{$date}</lastmod>
        </url>
    {/foreach}
</urlset>