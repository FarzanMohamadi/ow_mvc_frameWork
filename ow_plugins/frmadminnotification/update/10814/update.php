<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','registration_notice','کاربری با نام کاربری «<a href="{$profile_url}">{$username}</a>» و اسم واقعی «{$realname}»، در تاریخ «{$join_date}»، عضو «{$site_name}» شد');
$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','registration_notice','User registered in {$site_name} (username: <a href="{$profile_url}">{$username}</a> and real name: {$realname}) at {$join_date}');