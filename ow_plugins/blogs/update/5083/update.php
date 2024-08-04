<?php
try
{
    Updater::getWidgetService()->deleteWidgetPlace('dashboard-BLOGS_CMP_MyBlogWidget');
}
catch( Exception $e ) {}


$exArr = array();

try
{

    $dbPrefix = OW_DB_PREFIX;

    $sql =

    "UPDATE `".$dbPrefix."base_flag` SET `langKey` = 'blogs+flags' WHERE `type`='blog_post' ";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    $exArr[] = $e;
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'blogs');
