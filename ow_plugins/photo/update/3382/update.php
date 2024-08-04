<?php
$config = OW::getConfig();

if ( !$config->configExists('photo', 'advanced_upload_enabled') )
{
    $config->addConfig('photo', 'advanced_upload_enabled', 1, 'Enables advanced multiple file flash uploader');
}

if ( !$config->configExists('photo', 'fullsize_resolution') )
{
    $config->addConfig('photo', 'fullsize_resolution', 1024, 'Full-size photo resolution');
}

try
{
    $sql = "ALTER TABLE `".OW_DB_PREFIX."photo` ADD `privacy` varchar(50) NOT NULL default 'everybody';";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }

try {
    $sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."photo_temporary` (
      `id` int(11) NOT NULL auto_increment,
      `userId` int(11) NOT NULL,
      `addDatetime` int(11) NOT NULL,
      `hasFullsize` tinyint(1) NOT NULL default '0',
      `order` int(11) NOT NULL default '0',
      PRIMARY KEY  (`id`),
      KEY `userId` (`userId`)
    ) DEFAULT CHARSET=utf8;";
    
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }


$plugin = OW::getPluginManager()->getPlugin('photo');

$staticDir = OW_DIR_STATIC_PLUGIN . $plugin->getModuleName() . DS;
$staticJsDir = $staticDir  . 'js' . DS;
$staticSwfDir = $staticDir  . 'swf' . DS;

if ( !OW::getStorage()->fileExists($staticDir) )
{
    OW::getStorage()->mkdir($staticDir);
}

if ( !OW::getStorage()->fileExists($staticJsDir) )
{
    OW::getStorage()->mkdir($staticJsDir);
}

OW::getStorage()->copyFile($plugin->getStaticJsDir() . 'swfobject.js', $staticJsDir . 'swfobject.js', true);
OW::getStorage()->copyFile($plugin->getStaticJsDir() . 'upload_photo.js', $staticJsDir . 'upload_photo.js', true);
OW::getStorage()->copyFile($plugin->getStaticJsDir() . 'photo.js', $staticJsDir . 'photo.js', true);

if ( !OW::getStorage()->fileExists($staticSwfDir) )
{
    OW::getStorage()->mkdir($staticSwfDir);
}

OW::getStorage()->copyFile($plugin->getStaticDir() . 'swf' . DS . 'playerProductInstall.swf', $staticSwfDir . 'playerProductInstall.swf', true);
OW::getStorage()->copyFile($plugin->getStaticDir() . 'swf' . DS . 'main.swf', $staticSwfDir . 'main.swf', true);

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'photo');
