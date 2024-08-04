<?php

try {
    OW::getDbo()->query("UPDATE `" . OW_DB_PREFIX . "base_question`
                            SET `editable` = 0
                            WHERE `name` = 'field_mobile';");
} catch(Exception $e) {
    OW::getLogger()->writeLog(OW_Log::ERROR, 'frmsms_update_error_10862');
}
