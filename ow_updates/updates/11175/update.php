<?php

$entities = json_decode(OW::getConfig()->getValue('base', 'seo_sitemap_entities'), true);

# write for the first time
BOL_SeoService::getInstance()->setSitemapEntities($entities);

# remove old config
OW::getConfig()->deleteConfig('base', 'seo_sitemap_entities');
