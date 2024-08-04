<?php
$hasTableKey = OW::getDbo()->queryForRow('SHOW INDEXES FROM '.OW_DB_PREFIX.'mailbox_message WHERE Key_name = \'changed2\'');
if (empty($hasTableKey)) {
    $query = 'ALTER TABLE `'.OW_DB_PREFIX.'mailbox_message` ADD INDEX changed2 (changed)';
    OW::getDbo()->query($query);
}
