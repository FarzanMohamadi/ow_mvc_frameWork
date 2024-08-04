<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('en', 'frmchallenge', 'win_num', 'Count winners');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmchallenge', 'win_num', 'تعداد نمایش برندگان');

$sql = "ALTER TABLE  `" . OW_DB_PREFIX . "frmchallenge_challenge_universal` ADD  `winNum` int(11) DEFAULT NULL";

try {
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }
