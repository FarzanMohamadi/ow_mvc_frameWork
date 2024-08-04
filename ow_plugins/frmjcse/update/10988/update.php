<?php

try {
    Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmjcse_article` ADD COLUMN `citation` TEXT");
    Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmjcse_article` ADD COLUMN `institution` VARCHAR(200)");
} catch (Exception $ex) {
}
