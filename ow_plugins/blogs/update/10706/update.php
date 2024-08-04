<?php
// register sitemap entities
Updater::getSeoService()->addSitemapEntity('blogs', 'blogs_sitemap', 'blogs', array(
    'blogs_list',
    'blogs_post_list',
    'blogs_post_authors',
    'blogs_tags',
), 'blogs_sitemap_desc');

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'blogs');
