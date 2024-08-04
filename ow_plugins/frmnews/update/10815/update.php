<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('en', 'frmnews', 'search_entries', 'Search');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnews', 'search_entries', 'جستجو میان اخبار');

$languageService->addOrUpdateValueByLanguageTag('en', 'frmnews', 'search_results_for', 'Search results for: "<b>{$q}</b>"');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnews', 'search_results_for', 'نتایج جستجو برای: "<b>{$q}</b>"');

$languageService->addOrUpdateValueByLanguageTag('en', 'frmnews', 'search_entries', 'Search entries');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnews', 'search_entries', 'جستجوی محتوا');