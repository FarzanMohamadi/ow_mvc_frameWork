<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnewsfeedplus', 'group_select_empty_list_message', 'برای ادامه دادن لازم است یک یا چند گروه را انتخاب کنید');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnewsfeedplus', 'source_user_information', '(بازارسال محتوای <a href="{$userUrl}">{$userName}</a>)');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmnewsfeedplus', 'source_user_information', '(Forwarded from <a href="{$userUrl}">{$userName}</a>)');

