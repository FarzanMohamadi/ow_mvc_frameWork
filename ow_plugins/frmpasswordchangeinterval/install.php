<?php
/**
 * 
 * All rights reserved.
 */

$config = OW::getConfig();

if ( !$config->configExists('frmpasswordchangeinterval', 'expire_time') )
{
    $config->addConfig('frmpasswordchangeinterval', 'expire_time', 90);
}
if ( !$config->configExists('frmpasswordchangeinterval', 'dealWithExpiredPassword') )
{
    $config->addConfig('frmpasswordchangeinterval', 'dealWithExpiredPassword', 'normal');
}

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmpasswordchangeinterval_password_validation`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmpasswordchangeinterval_password_validation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `valid` int(1) NOT NULL,
  `token` VARCHAR(128),
  `tokenTime` int(11) NOT NULL,
  `passwordTime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
