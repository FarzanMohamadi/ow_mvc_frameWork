<?php
$authorization = OW::getAuthorization();
$groupName = 'frmtechnology';
$authorization->addGroup($groupName);
//$authorization->addAction($groupName, 'add_technology');
//$authorization->addAction($groupName, 'view_technology', true);
//$authorization->addAction($groupName, 'view_order');
$authorization->addAction($groupName, 'manage-technology');

$config = OW::getConfig();
if($config->configExists('frmtechnology', 'positions_list_setting'))
{
    $config->deleteConfig('frmtechnology', 'positions_list_setting');
}
if($config->configExists('frmtechnology', 'grades_list_setting'))
{
    $config->deleteConfig('frmtechnology', 'grades_list_setting');
}
if($config->configExists('frmtechnology', 'orgs_list_setting'))
{
    $config->deleteConfig('frmtechnology', 'orgs_list_setting');
}
if ( !$config->configExists('frmtechnology', 'positions_list_setting'))
{
    $positions = array(
        'دانشجو', 'هیئت علمی', 'محقق پسا دکتری'
    );
    $config->addConfig('frmtechnology', 'positions_list_setting',json_encode($positions));
}
if ( !$config->configExists('frmtechnology', 'grades_list_setting'))
{
    $grades = array(
        'کارشناسی', 'کارشناسی ارشد', 'دکتری'
    );
    $config->addConfig('frmtechnology', 'grades_list_setting',json_encode($grades));
}
if ( !$config->configExists('frmtechnology', 'orgs_list_setting'))
{
    $orgs = array(
        'دانشکده ریاضی', 'دانشکده مهندسی کامپیوتر', 'پژوهشکده فیزیک'
    );
    $config->addConfig('frmtechnology', 'orgs_list_setting',json_encode($orgs));
}

OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmtechnology_technology`;");
OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmtechnology_order`;");

OW::getDbo()->query("
  CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmtechnology_technology` (
  `id` int(11) NOT NULL auto_increment,
  `timeStamp` int(11) NOT NULL,
  `title` VARCHAR(512) NOT NULL,
  `userFullName` VARCHAR(256) NOT NULL,
  `position` VARCHAR(256) NOT NULL,
  `grade` VARCHAR(256) default NULL,
  `studentNumber` VARCHAR(128) default NULL,
  `organization` VARCHAR(256) NOT NULL,
  `email` VARCHAR(128) NOT NULL,
  `description` text NOT NULL,
  `status` varchar(20) NOT NULL default 'active',
  `phoneNumber` VARCHAR(32) NOT NULL,
  `area` VARCHAR(32) NOT NULL,
  `image1` VARCHAR(32) default NULL,
  `image2` VARCHAR(32) default NULL,
  `image3` VARCHAR(32) default NULL,
  `image4` VARCHAR(32) default NULL,
  `image5` VARCHAR(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

OW::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmtechnology_order` (
  `id` int(11) NOT NULL auto_increment,
  `timeStamp` int(11) NOT NULL,
  `technologyId` int(11) NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(256) NOT NULL,
  `companyName` VARCHAR(128) NOT NULL,
  `companyWebsite` VARCHAR(128) NOT NULL,
  `jobTitle` VARCHAR(128) NOT NULL,
  `companyAddress` text NOT NULL,
  `companyActivityField` VARCHAR(256) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
