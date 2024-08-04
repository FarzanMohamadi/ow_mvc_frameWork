<?php
$authorization = OW::getAuthorization();
$groupName = 'frmgrant';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'manage-grant');


$config = OW::getConfig();
if($config->configExists('frmgrant', 'collegeAndFields_list_setting'))
{
    $config->deleteConfig('frmgrant', 'collegeAndFields_list_setting');
}
if ( !$config->configExists('frmgrant', 'collegeAndFields_list_setting'))
{
    $collegeAndFields = array(
        'مهندسی کامپیوتر - نرم افزار', 'مهندسی کامپیوتر - سخت افزار', 'مهندسی عمران - سازه', 'مهندسی برق - قدرت'
    );
    $config->addConfig('frmgrant', 'collegeAndFields_list_setting',json_encode($collegeAndFields));
}

OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmgrant_grant`;");

OW::getDbo()->query("
  CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmgrant_grant` (
  `id` int(11) NOT NULL auto_increment,
  `timeStamp` int(11) NOT NULL,
  `title` VARCHAR(512) default NULL,
  `professor` VARCHAR(256) default NULL,
  `collegeAndField` VARCHAR(256) default NULL,
  `laboratory` VARCHAR(256) default NULL,
  `startedYear` int(11) default NULL,
  `description` TEXT default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
