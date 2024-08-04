<?php
$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();
$logger = Updater::getLogger();

$sql = "SELECT * FROM `{$tblPrefix}mailbox_attachment` ORDER BY `id` DESC LIMIT 1";
$last_attachment = $db->queryForRow($sql);

if ($last_attachment)
{
    $last_attachment_id = (int)$last_attachment['id'];
}
else
{
    $last_attachment_id = 0;
}

Updater::getConfigService()->addConfig('mailbox', 'last_attachment_id', $last_attachment_id, '');