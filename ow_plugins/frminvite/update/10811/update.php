<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frminvite', 'invite_members_textarea_sms_invitation_text', 'فهرست شماره‌های تلفن همراه را وارد کنید، بیشینه 50 شماره و در هر خط یک شماره وارد کنید');
$languageService->addOrUpdateValueByLanguageTag('en', 'frminvite', 'invite_members_textarea_sms_invitation_text', 'Enter list of phone numbers (max 50, one phone number per line)');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frminvite', 'sms_text_prefix', 'به {$site_name} بپیوندید');
$languageService->addOrUpdateValueByLanguageTag('en', 'frminvite', 'sms_text_prefix', 'Join {$site_name} at');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frminvite', 'wrong_mobile_format_error', 'شماره تلفن در فرمت مناسب نیست: {$phone}');
$languageService->addOrUpdateValueByLanguageTag('en', 'frminvite', 'wrong_mobile_format_error', 'Phone number is not in the correct format: {$phone}');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frminvite', 'wrong_email_format_error', 'آدرس ایمیل در فرمت مناسب نیست: {$email}');
$languageService->addOrUpdateValueByLanguageTag('en', 'frminvite', 'wrong_email_format_error', 'Email address is not in the correct format: {$email}');
