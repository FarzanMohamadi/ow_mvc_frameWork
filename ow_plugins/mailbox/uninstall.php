<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.mailbox
 * @since 1.0
 */
OW::getConfig()->deleteConfig('mailbox', 'results_per_page');

$sql = "DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "mailbox_conversation`";

OW::getDbo()->query($sql);

$sql = "DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "mailbox_last_message`";

OW::getDbo()->query($sql);

$sql = "DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "mailbox_message`";

OW::getDbo()->query($sql);

BOL_PreferenceService::getInstance()->deletePreference('mailbox_create_conversation_display_capcha');
BOL_PreferenceService::getInstance()->deletePreference('mailbox_create_conversation_stamp');