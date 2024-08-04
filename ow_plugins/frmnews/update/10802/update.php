<?php
Updater::getSeoService()->addSitemapEntity('frmnews', 'frmnews_sitemap', 'frmnews', array(
    'news'
));
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'frmnews');
