<?php
OW::getConfig()->saveConfig('frmmobilesupport', 'fcm_api_key', '');
OW::getConfig()->saveConfig('frmmobilesupport', 'fcm_api_url', 'https://fcm.googleapis.com/fcm/send');
OW::getConfig()->saveConfig('frmmobilesupport', 'constraint_user_device', '10');
OW::getConfig()->saveConfig('frmmobilesupport', 'disable_notification_content', false);
OW::getConfig()->saveConfig('frmmobilesupport', 'custom_download_link_code', '<a class="app_download_link android" href="/mobile-app/latest/native" target="_blank"></a>');
OW::getConfig()->saveConfig('frmmobilesupport', 'custom_download_link_activation', false);
OW::getConfig()->saveConfig('frmmobilesupport', 'last_firebase_send_notifications_time', '0');

try
{
    $authorization = OW::getAuthorization();
    $groupName = 'frmmobilesupport';
    $authorization->addGroup($groupName);
    $authorization->addAction($groupName, 'show-desktop-version');
}
catch ( LogicException $e ) {}

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmmobilesupport_device`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmmobilesupport_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `token` longtext NOT NULL,
  `time` int(1),
  `type` varchar(30) NOT NULL,
  `cookie` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmmobilesupport_app_version`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmmobilesupport_app_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL,
  `versionName` varchar(100) NOT NULL,
  `versionCode` int(100),
  `url` varchar(400) NOT NULL,
  `timestamp` int(11),
  `deprecated` BOOL NOT NULL DEFAULT \'0\',
  `message` varchar(400),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmmobilesupport_notifications`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmmobilesupport_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
