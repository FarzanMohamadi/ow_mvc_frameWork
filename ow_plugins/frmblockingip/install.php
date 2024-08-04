<?php
/**
 * 
 * All rights reserved.
 */

$config = OW::getConfig();

if ( !$config->configExists('frmblockingip', 'loginCaptcha') )
{
    $config->addConfig('frmblockingip', 'loginCaptcha', true);
}

if ( !$config->configExists('frmblockingip', 'try_count_captcha') )
{
    $config->addConfig('frmblockingip', 'try_count_captcha', 1);
};

if ( !$config->configExists('frmblockingip', 'block') )
{
    $config->addConfig('frmblockingip', 'block', true);
}

if ( !$config->configExists('frmblockingip', 'try_count_block') )
{
    $config->addConfig('frmblockingip', 'try_count_block', 5);
};

if ( !$config->configExists('frmblockingip', 'expire_time') )
{
    $config->addConfig('frmblockingip', 'expire_time', 15);
}
OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmblockingip_block_ip`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmblockingip_block_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL,
  `time` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
