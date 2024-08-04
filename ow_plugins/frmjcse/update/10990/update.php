<?php

try {
    Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmjcse_article` ADD COLUMN `startPage` INT(11)");
    Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmjcse_article` ADD COLUMN `endPage` INT(11)");
} catch (Exception $ex) {
}
