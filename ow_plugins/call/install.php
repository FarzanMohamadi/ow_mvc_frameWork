<?php
//BOL_LanguageService::getInstance()->addPrefix('contactus', 'Contact Us');

$config = OW::getConfig();
if (!$config->configExists('call', 'allowVoiceCall')) {
    $config->addConfig('call', 'allowVoiceCall', '1');
}
if (!$config->configExists('call', 'allowVideoCall')) {
    $config->addConfig('call', 'allowVideoCall', '1');
}
if (!$config->configExists('call', 'allowGroupCall')) {
    $config->addConfig('call', 'allowGroupCall', '0');
}

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "call_call`;");
$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "call_call` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`senderId` INT(11) NOT NULL,
	`mode` text NOT NULL,
	`candidate` text,
	`offer` text,
	`establishTimestamp` INT(11),
	`dismissTimestamp` INT(11),
	PRIMARY KEY (`id`)
)
CHARSET=utf8 AUTO_INCREMENT=1;";
OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "call_call_user`;");
$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "call_call_user` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`userId` INT(11) NOT NULL,
	`callId` INT(11) NOT NULL,
	`role` TINYINT(1),
	`joinTimestamp` INT(11),
	`leaveTimestamp` INT(11),
PRIMARY KEY (`id`)
)
CHARSET=utf8 AUTO_INCREMENT=1;";
OW::getDbo()->query($sql);