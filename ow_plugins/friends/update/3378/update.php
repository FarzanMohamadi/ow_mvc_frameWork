<?php
 $tblPrefix = OW_DB_PREFIX;

$db = Updater::getDbo();

$queryList = array(
    "ALTER IGNORE TABLE `{$tblPrefix}friends_friendship` ADD INDEX `userId` (  `userId` )",
    "ALTER IGNORE TABLE `{$tblPrefix}friends_friendship` ADD INDEX `friendId` (  `friendId` )",
    "ALTER IGNORE TABLE `{$tblPrefix}friends_friendship` ADD UNIQUE  `userId_friendId` (  `userId` ,  `friendId` )"
);

$sqlErrors = array();

foreach ( $queryList as $query )
{
    try
    {
        $db->query($query);
    }
    catch ( Exception $e )
    {
        $sqlErrors[] = $e;
    }
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'friends');

if ( !empty($sqlErrors) )
{
    //printVar($sqlErrors);
}