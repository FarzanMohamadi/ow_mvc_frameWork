<?php
try
{
    Updater::getDbo()->query("ALTER TABLE  `". OW_DB_PREFIX ."groups_group` ADD  `status` VARCHAR( 100 ) NOT NULL DEFAULT  'active'");
} catch (Exception $ex) {
    // Pass
}

$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'groups');