<?php
// register sitemap entities
Updater::getSeoService()->addSitemapEntity('video', 'video_sitemap', 'video', array(
    'video_list',
    'video_tags',
    'video',
    'video_authors'
));

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'video');
