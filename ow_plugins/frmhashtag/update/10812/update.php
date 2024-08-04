<?php
/**
 * User: Issa Annamoradnejad
 * Date: 10/10/2017
 */

$languageService = Updater::getLanguageService();
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmhashtag', 'list_page_title', 'نتایج برای #{$tag}');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmhashtag', 'list_page_title', 'Results for #{$tag}');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'forum', 'toolbar_post_number', 'پست شماره {$num}');
$languageService->addOrUpdateValueByLanguageTag('en', 'forum', 'toolbar_post_number', 'Post {$num}');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmhashtag', 'able_to_see_text', 'شما قادر به مشاهده {$num} مورد از {$all} مورد هستید.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmhashtag', 'able_to_see_text', 'You are able to view {$num} of {$all} item(s).');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmhashtag', 'search_placeholder', 'هشتگ');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmhashtag', 'search_placeholder', 'Enter Hashtag');
