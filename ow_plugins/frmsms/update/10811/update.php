<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsms', 'smsActivation_user_btn', 'تایید');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsms', 'users_need_sms_code_activation', 'کاربران تایید نشده(کد تلفن همراه)');

$languageService->addOrUpdateValueByLanguageTag('en', 'frmsms', 'smsActivation_user_btn', 'approve');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmsms', 'users_need_sms_code_activation', 'unapproved users(mobile sms code)');


