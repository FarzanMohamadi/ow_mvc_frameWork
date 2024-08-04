<?php
/**
 * 
 * All rights reserved.
 */

$config = OW::getConfig();
if ( !$config->configExists('frmmassmailing', 'mail_view_count') )
{
    $config->addConfig('frmmassmailing', 'mail_view_count',15);
}

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmmassmailing_details`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmmassmailing_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roles` longtext NOT NULL,
  `title` varchar(512) NOT NULL,
  `body` longtext NOT NULL,
  `createTimeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
