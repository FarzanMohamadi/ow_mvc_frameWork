<?php
try {
    $sql = "ALTER TABLE  `" . OW_DB_PREFIX . "frmchallenge_challenge_universal` ADD  `questionsNumber` int(11) DEFAULT NULL";
    Updater::getDbo()->query($sql);

    $sql = "ALTER TABLE  `" . OW_DB_PREFIX . "frmchallenge_challenge_universal` ADD  `startTime` int(11) DEFAULT NULL";
    Updater::getDbo()->query($sql);

}
catch ( Exception $e ) { }
