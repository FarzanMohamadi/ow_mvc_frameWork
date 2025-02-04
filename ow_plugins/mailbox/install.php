<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.mailbox
 * @since 1.0
 */

OW::getConfig()->saveConfig('mailbox', 'results_per_page', 10, 'Conversations number per page');
OW::getConfig()->saveConfig('mailbox', 'enable_attachments', true, 'Enable file attachments');
require_once OW_DIR_PLUGIN . 'mailbox' . DS . 'alloc.php';

$preference = BOL_PreferenceService::getInstance()->findPreference('mailbox_create_conversation_stamp');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'mailbox_create_conversation_stamp';
$preference->sectionName = 'general';
$preference->defaultValue = 0;
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

$preference = BOL_PreferenceService::getInstance()->findPreference('mailbox_create_conversation_display_capcha');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'mailbox_create_conversation_display_capcha';
$preference->sectionName = 'general';
$preference->defaultValue = false;
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

$preference = BOL_PreferenceService::getInstance()->findPreference('mailbox_user_settings_enable_sound');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'mailbox_user_settings_enable_sound';
$preference->defaultValue = true;
$preference->sectionName = 'general';
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

$preference = BOL_PreferenceService::getInstance()->findPreference('mailbox_user_settings_show_online_only');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'mailbox_user_settings_show_online_only';
$preference->defaultValue = true;
$preference->sectionName = 'general';
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

$modes = array('chat');
$config = OW::getConfig();
if ( !$config->configExists('mailbox', 'active_modes') )
{
    $config->addConfig('mailbox', 'active_modes', json_encode($modes));
}
if ( !$config->configExists('mailbox', 'show_all_members') )
{
    $config->addConfig('mailbox', 'show_all_members', false);
}
if ( !$config->configExists('mailbox', 'updated_to_messages') )
{
    $config->addConfig('mailbox', 'updated_to_messages', 1);
}
if ( !$config->configExists('mailbox', 'install_complete') )
{
    $config->addConfig('mailbox', 'install_complete', 0);
}
if ( !$config->configExists('mailbox', 'last_attachment_id') )
{
    $config->addConfig('mailbox', 'last_attachment_id', 0);
}
if ( !$config->configExists('mailbox', 'plugin_update_timestamp') )
{
    $config->addConfig('mailbox', 'plugin_update_timestamp', 0);
}
if ( !$config->configExists('mailbox', 'send_message_interval') )
{
    $config->addConfig('mailbox', 'send_message_interval', 60);
}

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "mailbox_conversation`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "mailbox_conversation` (
  `id` int(10) NOT NULL auto_increment,
  `initiatorId` int(10) NOT NULL default '0',
  `interlocutorId` int(10) NOT NULL default '0',
  `subject` varchar(100) NOT NULL default '',
  `read` tinyint(3) NOT NULL default '1' COMMENT 'bitmap, values: 0 - none, 1 - read by initiator, 2 - read by interlocutor, 3 - read all',
  `deleted` tinyint(3) NOT NULL default '0' COMMENT 'bitmap, values: 0 - none, 1 - deleted by initiator, 2 - deleted by interlocutor.',
  `viewed` tinyint(3) NOT NULL default '1' COMMENT 'bitmap, is user viewed conversation in console, values: 0 - none, 1 - viewed by initiator, 2 - viewed by interlocutor, 3 - viewed all',
  `muted` tinyint(3) NOT NULL default '0' COMMENT 'bitmap, is user muted conversation, values: 0 - none, 1 - muted by initiator, 2 - muted by interlocutor, 3 - muted all',
  `notificationSent` tinyint(3) NOT NULL default '0' COMMENT 'int flag, was notification about this letter sent to user',
  `createStamp` int(10) default '0',
  `initiatorDeletedTimestamp` INT( 10 ) NOT NULL DEFAULT  '0',
  `interlocutorDeletedTimestamp` INT( 10 ) NOT NULL DEFAULT  '0',
  `lastMessageId` int(11) NOT NULL,
  `lastMessageTimestamp` int(11) NOT NULL,
  `costumeFeatures` JSON DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `initiatorId` (`initiatorId`),
  KEY `interlocutorId` (`interlocutorId`),
  KEY `lastMessageTimestamp` (`lastMessageTimestamp`),
  KEY `subject` (`subject`)
) DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "mailbox_last_message`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "mailbox_last_message` (
  `id` int(10) NOT NULL auto_increment,
  `conversationId` int(10) NOT NULL default '0',
  `initiatorMessageId` int(10) NOT NULL default '0',
  `interlocutorMessageId` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `conversationId` (`conversationId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

OW::getDbo()->query($sql);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "mailbox_message`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "mailbox_message` (
  `id` int(10) NOT NULL auto_increment,
  `replyId` int(10),
  `changed` TINYINT NOT NULL DEFAULT '0',
  `conversationId` int(10) NOT NULL default '0',
  `timeStamp` bigint(10) NOT NULL default '0',
  `senderId` int(10) NOT NULL default '0',
  `recipientId` int(10) NOT NULL default '0',
  `isForwarded` int(10) NOT NULL default '0',
  `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipientRead` TINYINT NOT NULL DEFAULT '0',
  `isSystem` TINYINT NOT NULL DEFAULT  '0',
  `wasAuthorized` TINYINT NOT NULL DEFAULT  '0',
  `costumeFeatures` JSON DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `senderId` (`senderId`),
  KEY `recipientId` (`recipientId`),
  KEY `conversationId` (`conversationId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

OW::getDbo()->query($sql);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "mailbox_deleted_message`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "mailbox_deleted_message` (
  `id` int(10) NOT NULL auto_increment,
  `deletedId` int(10) NOT NULL,
  `conversationId` int(10) NOT NULL default '0',
  `time` bigint(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

OW::getDbo()->query($sql);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "mailbox_attachment`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "mailbox_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `messageId` int(11) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `fileName` varchar(255) NOT NULL,
  `fileSize` int(10) NOT NULL DEFAULT '0',
  `thumbName` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messageId` (`messageId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

OW::getDbo()->query($sql);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "mailbox_user_last_data`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "mailbox_user_last_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `data` longtext,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);
