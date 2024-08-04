<?php
// register sitemap entities
Updater::getSeoService()->addSitemapEntity('forum', 'forum_sitemap', 'forum', array(
    'forum_list',
    'forum_section',
    'forum_group',
    'forum_topic'
));

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'forum');
