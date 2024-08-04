<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/25/2017
 * Time: 10:29 AM
 */

$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport', 'file_description', 'قبل از بارگذاری فایل، مطمئن شوید که فایل مورد نظر دارای فرمت متنی (txt) است و مقدار encoding آن برابر با «utf-8» باشد. در غیر اینصورت، متون فارسی به درستی بارگذاری نخواهند شد. فایل مورد نظر باید از نرم‌افزار excel استخراخ شود. ستون‌ داده‌ها باید به ترتیب ایمیل، نام و موبایل باشند. ردیف اول نیز باید شامل سرستون (داده‌ای در ردیف اول قرار نگرفته باشد) باشد.');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport', 'notification_description', 'لطفا پیش از بارگذاری فایل، نوع اطلاع‌رسانی را انتخاب نموده و ثبت را بفشارید.');

$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport', 'notification_description', 'Before uploading the file, please select the type of notification to users and press Save button');