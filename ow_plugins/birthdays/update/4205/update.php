<?php
$errors = array();

try
{
    Updater::getDbo()->query("
        CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "birthdays_privacy` (
          `id` int(11) NOT NULL auto_increment,
          `userId` int(11) NOT NULL,
          `privacy` varchar(32) NOT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `userId` (`userId`)
        ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ");
}
catch( Exception $e )
{
    $errors[] = $e;
}