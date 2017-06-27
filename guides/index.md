---
layout: page
title: Guides
excerpt: "Donec viverra tellus sed tellus maximus, vel sagittis justo accumsan. Nullam blandit iaculis laoreet. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse tincidunt, massa quis porta egestas, est dolor ullamcorper arcu, non fringilla nisl massa vel arcu. Nullam ut dui non justo congue aliquet."
search_omit: true
---

<ul class="post-list">
{% for post in site.categories.guides %}
  <li><article><a href="{{ site.url }}{{ post.url }}">{{ post.title }} {% if post.excerpt %} <span class="excerpt">{{ post.excerpt | remove: '\[ ... \]' | remove: '\( ... \)' | markdownify | strip_html | strip_newlines | escape_once }}</span>{% endif %} </a></article></li>
{% endfor %}
</ul>
