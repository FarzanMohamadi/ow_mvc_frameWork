<?php
try {
    OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmupdateserver_download_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` longtext,
  `version` longtext,
  `time` int(11) NOT NULL,
  `ip` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

    Updater::getSeoService()->addSitemapEntity('frmupdateserver', 'frmupdateserver_sitemap', 'frmupdateserver', array(
        'frmupdateserver_download'
    ));
    Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'frmupdateserver');
} catch (Exception $e) {
    Updater::getLogger()->addEntry(json_encode($e));
}