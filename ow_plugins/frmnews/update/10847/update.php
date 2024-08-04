<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnews', 'index_page_title', '{$title} | {$site_name}');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmnews', 'index_page_title', '{$site_name} | {$title}');


