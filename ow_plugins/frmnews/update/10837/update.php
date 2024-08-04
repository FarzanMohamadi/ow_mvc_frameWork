<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnews', 'news_notification_string', ' خبر جدیدی منتشر شده است: <a href="{$url}">«{$title}»</a> ');

$languageService->addOrUpdateValueByLanguageTag('en', 'frmnews', 'news_notification_string','a news has been published: <a href="{$url}">"{$title}"</a>');