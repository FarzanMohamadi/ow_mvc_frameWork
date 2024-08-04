<?php
/**
 * frmslideshow
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */
$config = OW::getConfig();

if ( !$config->configExists('frmslideshow', 'news_count') )
{
    $config->addConfig('frmslideshow', 'news_count', 10, 'news_count');
}

if ( !$config->configExists('frmslideshow', 'max_text_char') )
{
    $config->addConfig('frmslideshow', 'max_text_char', 128, 'max_text_char');
}

OW::getDbo()->query('
DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'frmslideshow_album`;
CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmslideshow_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(500) NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'frmslideshow_slide`;
CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmslideshow_slide` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumId` int(11) NOT NULL,
  `description` VARCHAR(5000) NULL,
  `order` int(2) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 ;');
