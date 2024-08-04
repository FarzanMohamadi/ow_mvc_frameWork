<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frminvite', 'sms_template_invite_user_text', 'کاربر گرامی، شما به «{$site_name}» دعوت شده‌اید. جهت ثبت‌نام از پیوند روبرو استفاده کنید: {$url}');
$languageService->addOrUpdateValueByLanguageTag('en', 'frminvite', 'sms_template_invite_user_text', 'Join {$site_name} at {$url}');
