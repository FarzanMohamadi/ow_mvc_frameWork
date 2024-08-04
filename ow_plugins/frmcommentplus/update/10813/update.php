<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('en', 'frmcommentplus', 'comment_notification_string', '<a href="{$actorUrl}">{$actor}</a> also commented on <a href="{$contextUrl}"> this status </a> by <a href="{$ownerUrl}">{$ownerName}</a>\'s post');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmcommentplus', 'comment_news_string', '<a href="{$actorUrl}">{$actor}</a> also commented on <a href="{$contextUrl}"> this news </a>');