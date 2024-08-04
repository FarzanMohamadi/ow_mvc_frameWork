<?php
// register sitemap entities
Updater::getSeoService()->addSitemapEntity('groups', 'groups_sitemap', 'groups', array(
    'groups_list',
    'groups',
    'groups_user_list',
    'groups_authors'
), 'groups_sitemap_desc');

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'groups');
