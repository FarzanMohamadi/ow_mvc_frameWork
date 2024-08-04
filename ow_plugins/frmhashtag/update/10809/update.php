<?php
/**
 * User: Issa Annamoradnejad
 * Date: 8/19/2017
 */
$authorization = OW::getAuthorization();
$groupName = 'frmhashtag';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view_newsfeed', true);

$languageService = Updater::getLanguageService();
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmhashtag', 'auth_action_label_view_newsfeed', 'مشاهده نتایج تازه‌ها');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmhashtag', 'auth_action_label_view_newsfeed', 'View Newsfeed Results');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmhashtag', 'auth_group_label', 'هشتگ');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmhashtag', 'auth_group_label', 'Hashtag');
