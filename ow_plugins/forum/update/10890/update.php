<?php
/**
 * Created by Hamed Salimian.
 * Date: 10/9/2019
 */

try
{
    Updater::getDbo()->query("ALTER TABLE " . OW_DB_PREFIX . "forum_topic ADD COLUMN `closeTime` INT(11) NULL DEFAULT NULL");
    Updater::getDbo()->query("ALTER TABLE " . OW_DB_PREFIX . "forum_topic ADD COLUMN `conclusionPostId` INT(11) NULL DEFAULT NULL");
} catch (Exception $ex) {
}
try {
    Updater::getDbo()->query("ALTER TABLE `frmbckp_" . OW_DB_PREFIX . "forum_topic` ADD COLUMN `closeTime` INT(11) AFTER `status` NULL DEFAULT NULL");
    Updater::getDbo()->query("ALTER TABLE `frmbckp_" . OW_DB_PREFIX . "forum_topic` ADD COLUMN `conclusionPostId` INT(11) AFTER `closeTime` NULL DEFAULT NULL");
} catch (Exception $ex) {
}

Updater::getDbo()->query("UPDATE " .
        OW_DB_PREFIX . "forum_topic t
    SET
      t.closeTime =(
        SELECT
          fp.createStamp
        FROM
          " . OW_DB_PREFIX . "forum_post AS fp
        WHERE
          t.lastPostId = fp.id
          AND t.locked = 1
      )
");