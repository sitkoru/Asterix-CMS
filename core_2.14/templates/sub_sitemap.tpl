<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {foreach from=$recs item=rec}
        <url>
            <loc>{$rec.url}</loc>
            <lastmod>{$rec.date_public}</lastmod>
            <changefreq>{if $rec.seo_changefreq}{$rec.seo_changefreq}{else}always{/if}</changefreq>
            <priority>{if $rec.seo_priority}{$rec.seo_priority}{else}0.5{/if}</priority>
        </url>
    {/foreach}
</urlset>