<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmforumplus','email_notifications_group_topic', 'کسی موضوع جدید در گروه‌های من ایجاد کرد.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmforumplus','email_notifications_group_topic', 'someone creates topic in my groups');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmforumplus','notify_add_group_topic', '<a href="{$userUrl}">{$userName}</a>  موضوع «<a href="{$topicUrl}">{$topicTitle}</a>»  را در انجمن گروه «<a href="{$groupUrl}">{$groupTitle}</a>» ایجاد کرد');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmforumplus','notify_add_group_topic', '<a href="{$userUrl}">{$userName}</a>  add topic «<a href="{$topicUrl}">{$topicTitle}</a>»  to  «<a href="{$groupUrl}">{$groupTitle}</a>»');