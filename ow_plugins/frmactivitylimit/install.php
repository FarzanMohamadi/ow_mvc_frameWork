<?php
/**
 * frmactivitylimit
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmactivitylimit
 * @since 1.0
 */

$config = OW::getConfig();

if ( !$config->configExists('frmactivitylimit', 'max_db_requests') )
{
    $config->addConfig('frmactivitylimit', 'max_db_requests', 1000);
}
if ( !$config->configExists('frmactivitylimit', 'minutes_to_reset') )
{
    $config->addConfig('frmactivitylimit', 'minutes_to_reset', 30);
}
if ( !$config->configExists('frmactivitylimit', 'blocking_minutes') )
{
    $config->addConfig('frmactivitylimit', 'blocking_minutes', 60);
}

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmactivitylimit_user_requests`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmactivitylimit_user_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `last_reset_timestamp` int(11)NOT NULL,
  `db_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
