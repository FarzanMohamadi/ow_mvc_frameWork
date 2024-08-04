<?php
/**
 * frmoghat
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmoghat
 * @since 1.0
 */

$config = OW::getConfig();
if ( !$config->configExists('frmoghat', 'importDefaultItem') )
{
    $config->addConfig('frmoghat', 'importDefaultItem', false);
}

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmoghat_city`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmoghat_city` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `longitude` varchar(40),
  `latitude` varchar(40),
  `default` int(1),
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");
