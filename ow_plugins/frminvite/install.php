<?php
/**
 * FRM Invite
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminvite
 * @since 1.0
 */

$config = OW::getConfig();
if ( !$config->configExists('frminvite', 'invitation_view_count') )
{
    $config->addConfig('frminvite', 'invitation_view_count',15);
}

if ( !$config->configExists('frminvite', 'invite_daily_limit') )
{
    $config->addConfig('frminvite', 'invite_daily_limit',100);
}

$authorization = OW::getAuthorization();
$groupName = 'frminvite';
$authorization->addGroup($groupName);

$authorization->addAction($groupName, 'invite');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frminvite_details`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frminvite_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `senderId` int(11) NOT NULL,
  `invitedEmail` varchar(512) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frminvite_limit`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frminvite_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `date` varchar(10) NOT NULL,
  `number` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
