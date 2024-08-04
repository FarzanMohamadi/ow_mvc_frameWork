<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/25/2017
 * Time: 10:29 AM
 */

$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport', 'frmsms_plugin_not_active', 'افزونه پیامکی نصب نیست. امکان ارسال پیامک در هنگام واردسازی کاربران و ذخیره شماره تلفن همراه آن‌ها مقدور نخواهد بود.');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport', 'resend_to_user_sleepy_label', 'ایجاد گذرواژه مجدد و ارسال اطلاعات به کاربرانی که تا کنون وارد سامانه نشده‌اند');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport', 'resend_to_user_sleepy_description', 'با استفاده از دکمه زیر می‌توانید گذرواژه کاربرانی که تا کنون وارد سامانه نشده را مجدد ایجاد کرده و اطلاعات حساب کاربری آن‌ها را مجدد برایشان ارسال نمایید.');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport', 'resend_successfully', 'اطلاعات حساب کاربری ارسال شدند.');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport', 'empty_sleepy_users', 'تمامی کاربران برای حداقل یکبار وارد سامانه شدند.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport', 'frmsms_plugin_not_active', 'Plugin of frmsms is not activated or installed. You can not use mobile data and sms not sent.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport', 'resend_to_user_sleepy_label', 'Create new password and resend account information to users that not login yet.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport', 'resend_to_user_sleepy_description', 'Using this bottom, you can resend account information to users that not login yet.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport', 'resend_successfully', 'All account sent successfully.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport', 'empty_sleepy_users', 'All users login once.');