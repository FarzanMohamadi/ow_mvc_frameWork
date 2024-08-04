<?php
try {

    $sql = "DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmgroupsplus_forced_groups`;";
    OW::getDbo()->query($sql);

    $sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmgroupsplus_forced_groups` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `groupId` INT(11) NOT NULL,
        `canLeave` TINYINT(1) NOT NULL DEFAULT 1,
        `condition` TEXT NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    )
    CHARSET=utf8 AUTO_INCREMENT=1;";
    OW::getDbo()->query($sql);

    $config = OW::getConfig();
    $list = $config->getValue('frmgroupsplus', 'forced_groups');
    if (!empty($list)) {
        $list = json_decode($list, true);
        $forcedGroups = [];
        foreach ($list as $gId => $item) {
            $forcedGroup = new FRMGROUPSPLUS_BOL_ForcedGroups();
            $forcedGroup->groupId = $gId;
            $forcedGroup->canLeave = (int)($item['canLeave'] == 'false');
            $forcedGroup->condition = json_encode($item["conditions"]);
            $forcedGroups[] = $forcedGroup;
        }

        FRMGROUPSPLUS_BOL_ForcedGroupsDao::getInstance()->batchReplace($forcedGroups);
    }

    $config->deleteConfig('frmgroupsplus', 'forced_groups');
}catch (Exception $e){}
