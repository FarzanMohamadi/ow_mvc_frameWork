<?php
try
{
	$sql = " ALTER TABLE `" . OW_DB_PREFIX . "mailbox_conversation` ADD `viewed` TINYINT NOT NULL DEFAULT '0' ";

	Updater::getDbo()->query($sql);
}
catch ( Exception $ex )
{
	//printVar($ex);
}

try
{
	$sql = " ALTER TABLE `" . OW_DB_PREFIX . "mailbox_conversation` ADD `notificationSent` TINYINT NOT NULL DEFAULT '0' ";

	Updater::getDbo()->query($sql);
}
catch ( Exception $ex )
{
	//printVar($ex);
}

try
{
	$sql = " UPDATE `" . OW_DB_PREFIX . "mailbox_conversation` SET `viewed` = 3 WHERE 1 ";

	Updater::getDbo()->query($sql);
}
catch ( Exception $ex )
{
	//printVar($ex);
}

try
{
	$sql = " UPDATE `" . OW_DB_PREFIX . "mailbox_conversation` SET `notificationSent` = 1 WHERE 1 ";

	Updater::getDbo()->query($sql);
}
catch ( Exception $ex )
{
	//printVar($ex);
}

Updater::getWidgetService()->deleteWidget('MAILBOX_CMP_NewMessageNoteWidget');

OW::getStorage()->removeFile( OW_DIR_STATIC_PLUGIN . $moduleName . DS . 'js' . DS . 'mailbox.js', true );
OW::getStorage()->copyFile( OW_DIR_PLUGIN . $moduleName . DS . 'static' .DS . 'js' . DS . 'mailbox.js' , OW_DIR_STATIC_PLUGIN . $moduleName . DS . 'js' . DS . 'mailbox.js', true);
OW::getStorage()->copyFile( OW_DIR_PLUGIN . $moduleName . DS . 'static' .DS . 'js' . DS . 'mailbox_console.js' , OW_DIR_STATIC_PLUGIN . $moduleName . DS . 'js' . DS . 'mailbox_console.js', true);

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'mailbox');


