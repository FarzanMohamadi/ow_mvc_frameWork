<?php
$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langId = null;

foreach ($languages as $lang)
{
    if ($lang->tag == 'en')
    {
        $langId = $lang->id;
        break;
    }
}

if ($langId !== null)
{
    $languageService->addOrUpdateValue($langId, 'mailbox', 'reply_to_chat_message_promoted', 'Please subscribe or buy credits to reply to chat message');
    $languageService->addOrUpdateValue($langId, 'mailbox', 'reply_to_message_promoted', 'Please subscribe or buy credits to reply to conversation');
    $languageService->addOrUpdateValue($langId, 'mailbox', 'send_chat_message_promoted', 'Please subscribe or buy credits to send chat message');
    $languageService->addOrUpdateValue($langId, 'mailbox', 'send_message_promoted', 'Please subscribe or buy credits to send messages');
}
