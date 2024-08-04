<?php

try {
    $frmgroupplus_channel_table = OW_DB_PREFIX . 'frmgroupsplus_channel';
    $frmgroup_group_table = OW_DB_PREFIX . 'groups_group';

    Updater::getDbo()->query("ALTER TABLE `" . $frmgroup_group_table ."` ADD COLUMN `isChannel` BOOLEAN DEFAULT FALSE");

    $sql = "UPDATE `" . $frmgroup_group_table."` AS group_table 
            SET isChannel = IF(EXISTS(SELECT * FROM `". $frmgroupplus_channel_table ."` AS channel_table
                                      WHERE channel_table.groupId = group_table.id),TRUE,FALSE)";
    Updater::getDbo()->query($sql);

    Updater::getDbo()->query("DROP TABLE IF EXISTS `" . $frmgroupplus_channel_table . "`;");

}catch (Exception $ex){
    OW::getLogger()->writeLog(OW_Log::ERROR, 'update_10920_frmgroupsplus_failed',
        ['actionType'=>OW_Log::UPDATE, 'enType'=>'plugin', 'enId'=> 'frmgroupsplus', 'error'=>'error in mysql', 'exception'=>$ex]);
}
