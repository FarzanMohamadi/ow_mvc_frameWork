<?php
$langService = Updater::getLanguageService();

$keys = array('reply_to_chat_message_promoted', 'reply_to_message_promoted', 'send_chat_message_promoted', 'send_message_promoted');

foreach ($keys as $key)
{
    $langService->deleteLangKey('mailbox', $key);
}

$langService->importPrefixFromZip( dirname(__FILE__) . DS . 'langs.zip', 'mailbox' );
