<?php
// register sitemap entities
Updater::getSeoService()->addSitemapEntity('event', 'event_sitemap', 'event', array(
    'event_list',
    'event',
    'event_participants'
));

Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'event');
