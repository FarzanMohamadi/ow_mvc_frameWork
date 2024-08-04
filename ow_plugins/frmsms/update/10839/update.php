<?php
Updater::getDbo()->query("
             DELETE FROM `" . OW_DB_PREFIX . "frmsms_mobile_verify` WHERE userId IS NULL;");



Updater::getDbo()->query("
             DELETE FROM `" . OW_DB_PREFIX . "frmsms_mobile_verify`
             WHERE `id` IN (
                 SELECT id FROM (SELECT mv1.id as id FROM `".OW_DB_PREFIX."frmsms_mobile_verify` AS mv1
                 INNER JOIN `".OW_DB_PREFIX."frmsms_mobile_verify` AS mv2 ON  mv1.userId = mv2.userId AND mv1.id <> mv2.id
                 ) AS mv3   
             );"
        );

Updater::getDbo()->query("
             DELETE FROM `" . OW_DB_PREFIX . "frmsms_mobile_verify`
             WHERE `id` IN (
                 SELECT id FROM (SELECT mv1.id as id FROM `".OW_DB_PREFIX."frmsms_mobile_verify` AS mv1
                 INNER JOIN `".OW_DB_PREFIX."frmsms_mobile_verify` AS mv2 ON  mv1.mobile = mv2.mobile AND mv1.id <> mv2.id
                 ) AS mv3   
             );"
);