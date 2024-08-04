<?php
/**
 * 
 * All rights reserved.
 */

$config = OW::getConfig();

if ( !$config->configExists('frmimport', 'use_import_yahoo') )
{
    $config->addConfig('frmimport', 'use_import_yahoo', false);
}

if ( !$config->configExists('frmimport', 'yahoo_id') )
{
    $config->addConfig('frmimport', 'yahoo_id', '');
}

if ( !$config->configExists('frmimport', 'yahoo_secret') )
{
    $config->addConfig('frmimport', 'yahoo_secret', '');
};

if ( !$config->configExists('frmimport', 'use_import_google') )
{
    $config->addConfig('frmimport', 'use_import_google', false);
}

if ( !$config->configExists('frmimport', 'google_id') )
{
    $config->addConfig('frmimport', 'google_id', '');
}

if ( !$config->configExists('frmimport', 'google_secret') )
{
    $config->addConfig('frmimport', 'google_secret', '');
};

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmimport_users`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmimport_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(40) NOT NULL,
  `email` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmimport_users_try`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmimport_users_try` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(40) NOT NULL,
  `time` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
