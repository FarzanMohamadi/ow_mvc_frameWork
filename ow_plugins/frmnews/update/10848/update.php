<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnews', 'news_entry_title', '{$entry_title} | {$site_name}');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmnews', 'news_entry_title', '{$site_name} | {$entry_title}');


