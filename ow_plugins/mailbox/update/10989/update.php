<?php
$dbo = Updater::getDbo();

try{
    $dbo->query("ALTER TABLE `". FRMSecurityProvider::getTableBackupName(OW_DB_PREFIX."mailbox_message")."` MODIFY `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
}catch (Exception $ex){}

$backupTable = 'frmbckp_'.OW_DB_PREFIX.'mailbox_message';
$table = OW_DB_PREFIX.'mailbox_message';

try
{
    $query = "SELECT 1 FROM `".$backupTable."` LIMIT 1";
    $dbo->query($query);
}catch (Exception $e)
{
    return;
}
$query = 'SELECT * FROM `' . $table . '` WHERE  `text` IS NULL';
$all_null_messages = $dbo->queryForObjectList($query,MAILBOX_BOL_MessageDao::getInstance()->getDtoClassName());

foreach ($all_null_messages as $message) {
    $query = 'SELECT text FROM `' . $backupTable . '` WHERE `id`=' . $message->id . ' AND `text` IS NOT NULL AND `backup_action` ="u" order by `backup_pk_id` DESC LIMIT 1';
    $text = $dbo->queryForColumn($query);

    if (strpos($text, '<json>') === 0) {
        $text = substr($text, 6);
        $text = trim($text, '"'); // check it

        $decodedString = null;
        while ($decodedString == null && strlen($text) > 0){
            $decodedString = json_decode('"'.$text.'"');
            $text = substr($text, 0, -1);
        }

        if ($decodedString != null || $decodedString != '') {
            $message->text = $decodedString;
            MAILBOX_BOL_MessageDao::getInstance()->save($message);
        }

    }
}
