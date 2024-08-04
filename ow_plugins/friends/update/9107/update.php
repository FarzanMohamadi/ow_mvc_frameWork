<?php
$languages = BOL_LanguageService::getInstance()->getLanguages();
foreach ($languages as $lang)
{
    if ($lang->tag == 'en')
    {
        break;
    }
}

Updater::getLanguageService()->addOrUpdateValue($lang->id, 'friends', 'friend_request_was_sent', 'Friend request was sent');
Updater::getLanguageService()->addOrUpdateValue($lang->id, 'friends', 'cancel_request', 'Cancel');
