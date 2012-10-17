<?xml version="1.0"?>
<rss version="2.0">
  <channel>
    <title>{$content.title}</title>
    <link>{$content.url}</link>
    <description></description>
    <language>ru-ru</language>
    <pubDate>{$content.date}</pubDate>
{foreach from=$content.recs item=rec}
    <item>
      <title>{$rec.title}</title>
      <link>http://yamobi.ru{$rec.url}</link>
      <description>{$rec.text|cut:300|escape:html}</description>
      <pubDate>{$rec.date_public.r}</pubDate>
    </item>
{/foreach}
  </channel>
</rss>
