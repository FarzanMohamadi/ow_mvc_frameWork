<?php
/**
 * @param $lastId
 * @return bool
 */
function convertHtmlTags(&$lastId)
{
    $messageDao = MAILBOX_BOL_MessageDao::getInstance();
    $messageList = $messageDao->findNotUpdatedMessages($lastId, 2000);

    if ( empty($messageList) )
    {
        return false;
    }

    foreach ( $messageList as $message )
    {
        $message->text = preg_replace("/\n/", "", $message->text);
        $message->text = preg_replace("/<br \/>/", "\n", $message->text);
        $message->text = strip_tags($message->text);
        $messageDao->save($message);

        $lastId = $message->id;
    }

    return true;
}

$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();
$logger = Updater::getLogger();

$queryList = array(
    "ALTER TABLE  `{$tblPrefix}mailbox_message` ADD  `isSystem` TINYINT NOT NULL DEFAULT  '0'",
    "ALTER TABLE  `{$tblPrefix}mailbox_message` ADD  `wasAuthorized` TINYINT NOT NULL DEFAULT  '0'",
    "ALTER TABLE  `{$tblPrefix}mailbox_conversation` ADD  `initiatorDeletedTimestamp` INT( 10 ) NOT NULL DEFAULT  '0'",
    "ALTER TABLE  `{$tblPrefix}mailbox_conversation` ADD  `interlocutorDeletedTimestamp` INT( 10 ) NOT NULL DEFAULT  '0'",
    "UPDATE `{$tblPrefix}mailbox_conversation` SET `viewed`=3"
);

foreach ( $queryList as $query )
{
    try
    {
        $db->query($query);
    }
    catch ( Exception $e )
    {
        $logger->addEntry(json_encode($e));
    }
}

try
{
    $authorization = OW::getAuthorization();
    $groupName = 'mailbox';
    $authorization->addAction($groupName, 'reply_to_message');

    $authorization->addAction($groupName, 'read_chat_message');
    $authorization->addAction($groupName, 'send_chat_message');
    $authorization->addAction($groupName, 'reply_to_chat_message');
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

try
{
    $preference = new BOL_Preference();

    $preference->key = 'mailbox_user_settings_enable_sound';
    $preference->defaultValue = true;
    $preference->sectionName = 'general';
    $preference->sortOrder = 1;

    BOL_PreferenceService::getInstance()->savePreference($preference);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

try
{
    $preference = new BOL_Preference();

    $preference->key = 'mailbox_user_settings_show_online_only';
    $preference->defaultValue = false;
    $preference->sectionName = 'general';
    $preference->sortOrder = 1;

    BOL_PreferenceService::getInstance()->savePreference($preference);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}


$modes = array('mail', 'chat');
Updater::getConfigService()->addConfig('mailbox', 'active_modes', json_encode($modes));
Updater::getConfigService()->addConfig('mailbox', 'show_all_members', true);
$lastId = 0;
while (convertHtmlTags($lastId)) {
    ;
}
Updater::getConfigService()->addConfig('mailbox', 'updated_to_messages', 0, '');
Updater::getConfigService()->addConfig('mailbox', 'install_complete', 1, '');

Updater::getConfigService()->deleteConfig('mailbox', 'upload_max_file_size');
