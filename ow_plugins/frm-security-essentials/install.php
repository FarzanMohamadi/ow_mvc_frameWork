<?php
/**
 * User: Hamed Tahmooresi
 * Date: 12/23/2015
 * Time: 11:00 AM
 */

$config = OW::getConfig();

$config->saveConfig('frmsecurityessentials', 'idleTime', 20);
$config->saveConfig('frmsecurityessentials', 'viewUserCommentWidget', false);
$config->saveConfig('frmsecurityessentials', 'approveUserAfterEditProfile', false);
$config->saveConfig('frmsecurityessentials', 'disabled_home_page_action_types', '');
$config->saveConfig('frmsecurityessentials', 'newsFeedShowDefault', '');
$config->saveConfig('frmsecurityessentials', 'passwordRequiredProfile', '');
$config->saveConfig('frmsecurityessentials', 'remember_me_default_value', false);
$config->saveConfig('frmsecurityessentials', 'privacySet', false);
$config->saveConfig('frmsecurityessentials', 'privacyUpdateNotification', false);
$config->saveConfig('frmsecurityessentials', 'update_all_plugins_activated', true);
$config->saveConfig('frmsecurityessentials', 'ie_message_enabled', true);
$config->saveConfig('frmsecurityessentials', 'disable_verify_peer', false);
$config->saveConfig('frmsecurityessentials', 'disable_user_get_other_sites_content', false);
$config->saveConfig('frmsecurityessentials', 'user_can_change_account_type', true);

try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmsecurityessentials';
    $authorization->addGroup($groupName);
    $authorization->addAction($groupName, 'security-privacy_alert');
    $authorization->addAction($groupName, 'view-users-list', true);
    $authorization->addAction($groupName, 'customize_user_profile');
    $authorization->addAction($groupName, 'user-can-view-comments', true);
} catch (Exception $e) {
}

OW::getDbo()->query("
DROP TABLE IF EXISTS  `' . OW_DB_PREFIX . 'frmsecurityessentials_question_privacy`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmsecurityessentials_question_privacy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `questionId` int(11) NOT NULL,
  `privacy` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmsecurityessentials_request_manager`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmsecurityessentials_request_manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `senderId` int(11) NOT NULL,
  `receiverId` int(11) NOT NULL,
  `code` varchar(150) NOT NULL,
  `activityType` varchar(100) NOT NULL,
  `expirationTimeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `senderId` (`senderId`),
  KEY `receiverId` (`receiverId`)
) DEFAULT CHARSET=utf8 ;');
