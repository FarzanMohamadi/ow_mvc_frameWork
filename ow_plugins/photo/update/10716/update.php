<?php
// register sitemap entities
Updater::getSeoService()->addSitemapEntity('photo', 'photo_sitemap', 'photos', array(
    'photo_list',
    'photos',
    'photos_latest',
    'photos_toprated',
    'photos_most_discussed',
    'photo_albums',
    'photo_tags',
    'photo_user_albums',
    'photo_users'
));

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'photo');
