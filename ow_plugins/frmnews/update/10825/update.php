<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 11/25/2017
 * Time: 5:46 AM
 */

$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnews', 'news_entry_title', '{$entry_title} در {$site_name}');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmnews', 'news_entry_title', '{$entry_title} at {$site_name}');
