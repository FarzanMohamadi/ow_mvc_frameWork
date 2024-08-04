<?php
//BOL_LanguageService::getInstance()->addPrefix('contactus', 'Contact Us');

$config = OW::getConfig();
if (!$config->configExists('frmcontactus', 'adminComment')) {
    $config->addConfig('frmcontactus', 'adminComment', '');
}
OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmcontactus_department`;");
$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmcontactus_department` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(200) NOT NULL,
	`label` VARCHAR(200) NOT NULL,
	 UNIQUE KEY `label` (`label`),
	PRIMARY KEY (`id`)
)
CHARSET=utf8 AUTO_INCREMENT=1;";
//installing database
OW::getDbo()->query($sql);


OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmcontactus_user_information`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmcontactus_user_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` VARCHAR(1024) NOT NULL,
  `useremail` VARCHAR(256) NOT NULL,
  `label` VARCHAR(128) NOT NULL,
  `message` VARCHAR(2000) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
