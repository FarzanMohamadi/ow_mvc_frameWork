<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 11/5/2017
 * Time: 11:26 AM
 */
$config = OW::getConfig();
if ( !$config->configExists('frmusersimport', 'notification_type') )
{
    $config->addConfig('frmusersimport', 'notification_type', 'all');
}

$languageService = Updater::getLanguageService();
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport','email_invalid', 'رایانامه کاربر {$email}، مشکل دارد. لذا اطلاعات این کاربر دیگر وارد نمی‌شود.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport','email_invalid', 'Email of User {$email} is not valid. So this user can not be add again.');