<?php
/**
 * FRM Terms
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmterms
 * @since 1.0
 */

$config = OW::getConfig();
if ( !$config->configExists('frmterms', 'importDefaultItem') )
{
    $config->addConfig('frmterms', 'importDefaultItem', false);
}
if ( !$config->configExists('frmterms', 'showOnRegistrationForm') )
{
    $config->addConfig('frmterms', 'showOnRegistrationForm', false);
}
if ( !$config->configExists('frmterms', 'terms1') )
{
    $config->addConfig('frmterms', 'terms1', true);
}
if ( !$config->configExists('frmterms', 'terms2') )
{
    $config->addConfig('frmterms', 'terms2', true);
}
if ( !$config->configExists('frmterms', 'terms3') )
{
    $config->addConfig('frmterms', 'terms3', false);
}
if ( !$config->configExists('frmterms', 'terms4') )
{
    $config->addConfig('frmterms', 'terms4', false);
}
if ( !$config->configExists('frmterms', 'terms5') )
{
    $config->addConfig('frmterms', 'terms5', false);
}

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmterms_items`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmterms_items` (
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
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmterms_item_version`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmterms_item_version` (
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
