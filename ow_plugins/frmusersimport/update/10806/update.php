<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/25/2017
 * Time: 10:29 AM
 */

$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmusersimport', 'email_description', 'شما عضو <a href="{$site_url}">{$site_name}</a> شده‌اید. حساب کاربری برای شما با اطلاعات نام کاربری {$username} و گذرواژه {$password} ایجاد شده است. <br/> شما می‌توانید از طریق اطلاعات مذکور، وارد سامانه شوید. <br/> پیوند سامانه: <a href="{$site_url}">{$site_name}</a> <br/> با سپاس، مدیریت {$site_name}');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmusersimport', 'email_description', 'You can login into <a href="{$site_url}">{$site_name}</a> using username of {$username} and password of {$password}. <br/> Link: <a href="{$site_url}">{$site_name}</a>');