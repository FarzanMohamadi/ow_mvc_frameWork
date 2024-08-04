<?php
try
{
    Updater::getDbo()->query("ALTER TABLE  `". OW_DB_PREFIX ."frmcontactus_user_information` ADD  `timeStamp` int(11) NOT NULL");
} catch (Exception $ex) {
    // Pass
}
$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'frmcontactus');
