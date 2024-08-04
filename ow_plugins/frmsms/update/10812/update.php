<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsms', 'forgot_password_email_and_mobile_invitation_message', 'آدرس رایانامه یا تلفن همراه شما');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsms', 'form_validator_email_or_mobile_error_message', 'خطای اعتبارسنجی آدرس رایانامه یا تلفن همراه!');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsms', 'reset_password_sms_template_content_txt', 'کاربر گرامی «{$username}»،
            شما درخواست بازتنظیم گذرواژه خود را کرده‌اید. پیوند زیر را برای تغییر گذرواژه خود دنبال کنید: ({$resetUrl}) .
            اگر شما در خواست کد تنظیم مجدد نکرده‌اید لطفا این پیامک را نادیده بگیرید');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsms', 'forgot_password_sms_send_success', 'اطلاعات در ارتباط با تغییر و تایید گذرواژه جدید شما به تلفن همراه شما ارسال شد.');

$languageService->addOrUpdateValueByLanguageTag('en', 'frmsms', 'forgot_password_email_and_mobile_invitation_message', 'Your email address or phone number');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmsms', 'form_validator_email_or_mobile_error_message', 'Email or Mobile validator error!');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmsms', 'reset_password_sms_template_content_txt', 'Dear user ({$username}),\r\n\r\nYou requested to reset your password. Follow this link ({$resetUrl}) to change your password.\r\n\r\nIf you didn\'t request password reset, please ignore this sms.\r\n\r\nThank you,\r\n{$site_name}');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmsms', 'forgot_password_sms_send_success', 'The information on changing and confirming your new password sent to your mobile');


