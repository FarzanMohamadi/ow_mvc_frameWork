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
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport','saved_successfully', 'با موفقیت ذخیره شد.');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport','notification_type', 'نوع اطلاع‌رسانی به کاربران');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport','email_type', 'رایانامه');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport','mobile_type', 'پیامک');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport','all_type', 'همه');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport','saved_successfully', 'Saved successfully.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport','notification_type', 'Type of notification to users');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport','email_type', 'Email');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport','mobile_type', 'Sms');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport','all_type', 'all');