<?php
/**
 * 
 * All rights reserved.
 */

$config = OW::getConfig();

if ( !$config->configExists('frmcontrolkids', 'kidsAge') )
{
    $config->addConfig('frmcontrolkids', 'kidsAge', 13);
}
if ( !$config->configExists('frmcontrolkids', 'marginTime') )
{
    $config->addConfig('frmcontrolkids', 'marginTime', 1);
}

OW::getDbo()->query('
DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'frmcontrolkids_kids_relationship`;
CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmcontrolkids_kids_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kidUserId` int(11) NOT NULL,
  `parentUserId` int(11),
  `parentEmail` varchar(100) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
