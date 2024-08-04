<?php
/***
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

FRMSecurityProvider::createBackupTables(new OW_Event('update_tables',
    ['update_tables' => [OW_DB_PREFIX . 'frmsms_mobile_verify', OW_DB_PREFIX . 'frmsms_token']]));