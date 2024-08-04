<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 7/13/2017
 */

try
{
    $sql = "ALTER TABLE `".OW_DB_PREFIX."frmmobilesupport_device` ADD `type` varchar(30) NOT NULL default '1';";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }