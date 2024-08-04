<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */


$config = OW::getConfig();
if (!$config->configExists('multimedia', 'allowVoiceCall')) {
    $config->addConfig('multimedia', 'allowVoiceCall', '1');
}
if (!$config->configExists('multimedia', 'allowVideoCall')) {
    $config->addConfig('multimedia', 'allowVideoCall', '1');
}
if (!$config->configExists('multimedia', 'allowGroupCall')) {
    $config->addConfig('multimedia', 'allowGroupCall', '0');
}

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "multimedia_call`;");
$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "multimedia_call` (
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
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "multimedia_call_user`;");
$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "multimedia_call_user` (
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