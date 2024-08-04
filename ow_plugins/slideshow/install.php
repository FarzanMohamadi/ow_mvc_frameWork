<?php 



$config = OW::getConfig();

if ( !$config->configExists('slideshow', 'uninstall_inprogress') )
{
    $config->addConfig('slideshow', 'uninstall_inprogress', 0, 'Plugin is being uninstalled');
}

if ( !$config->configExists('slideshow', 'uninstall_cron_busy') )
{
    $config->addConfig('slideshow', 'uninstall_cron_busy', 0, 'Uninstall queue is busy');
}

OW::getPluginManager()->addUninstallRouteName('slideshow', 'slideshow.uninstall');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "slideshow_slide`;");

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."slideshow_slide` (
  `id` int(11) NOT NULL auto_increment,
  `widgetId` varchar(255) NOT NULL,
  `label` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `order` int(11) default '0',
  `width` int(11) NOT NULL default '0',
  `height` int(11) NOT NULL default '0',
  `ext` varchar(5) default NULL,
  `addStamp` int(11) default '0',
  `status` ENUM( 'active', 'delete' ) NULL DEFAULT 'active',
  PRIMARY KEY  (`id`),
  KEY `order` (`order`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);
