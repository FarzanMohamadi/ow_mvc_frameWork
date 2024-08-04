<?php
/**
 * FRM Cert
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcert
 * @since 1.0
 */

$config = OW::getConfig();
if ( !$config->configExists('frmcertedu', 'importDefaultItem') )
{
    $config->addConfig('frmcertedu', 'importDefaultItem', false);
}
if ( !$config->configExists('frmcertedu', 'showOnRegistrationForm') )
{
    $config->addConfig('frmcertedu', 'showOnRegistrationForm', false);
}
if ( !$config->configExists('frmcertedu', 'terms1') )
{
    $config->addConfig('frmcertedu', 'terms1', true);
}
if ( !$config->configExists('frmcertedu', 'terms2') )
{
    $config->addConfig('frmcertedu', 'terms2', true);
}
if ( !$config->configExists('frmcertedu', 'terms3') )
{
    $config->addConfig('frmcertedu', 'terms3', false);
}
if ( !$config->configExists('frmcertedu', 'terms4') )
{
    $config->addConfig('frmcertedu', 'terms4', false);
}
if ( !$config->configExists('frmcertedu', 'terms5') )
{
    $config->addConfig('frmcertedu', 'terms5', false);
}

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmcertedu_items`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmcertedu_items` (
  `id` int(11) NOT NULL auto_increment,
  `langId` int(11) NOT NULL,
  `use` int(1),
  `notification` int(1),
  `email` int(1),
  `order` int(11) NOT NULL,
  `sectionId` int (11) NOT NULL,
  `description` text NOT NULL,
  `header` varchar(250),
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmcertedu_item_version`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmcertedu_item_version` (
  `id` int(11) NOT NULL auto_increment,
  `langId` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `sectionId` int (11) NOT NULL,
  `description` text NOT NULL,
  `header` varchar(250),
  `time` int (11) NOT NULL,
  `version` int (11) NOT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");
