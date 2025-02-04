<?php
/**
 * frmphotoplus
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmphotoplus
 * @since 1.0
 */

try {
    OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmphotoplus_status_photo`;");

    OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmphotoplus_status_photo` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `photoId`int(11) NOT NULL,
              `userId` int(11) NOT NULL,
              PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

} catch (Exception $e) {
    Updater::getLogger()->addEntry(json_encode($e));
}
