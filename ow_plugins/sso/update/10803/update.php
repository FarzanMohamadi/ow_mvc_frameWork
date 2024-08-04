<?php

try {
    OW::getDbo()->query("UPDATE `" . OW_DB_PREFIX . "base_question`
                            SET `required` = 0
                            WHERE `name` = 'mobile_number';");
} catch(Exception $e) {
    OW::getLogger()->writeLog(OW_Log::ERROR, 'sso_update_error_10803');
}
