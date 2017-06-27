---
layout: page
title: Guides
excerpt: "These guides firstly present and explain the process for setting up your own website. You can then progress to find out about new sharing and publishing features for your website developed within the IndieWeb community."
search_omit: true
---

<ul class="post-list">
{% for post in site.categories.guides %}
  <li><article><a href="{{ site.url }}{{ post.url }}">{{ post.title }} {% if post.excerpt %} <span class="excerpt">{{ post.excerpt | remove: '\[ ... \]' | remove: '\( ... \)' | markdownify | strip_html | strip_newlines | escape_once }}</span>{% endif %} </a></article></li>
{% endfor %}
</ul>
