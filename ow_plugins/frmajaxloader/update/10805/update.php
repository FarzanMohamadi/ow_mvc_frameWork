<?php
/**
 * frmajaxloader
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmajaxloader
 * @since 1.0
 */

$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langFaId = null;
$langEnId = null;

foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
    if ($lang->tag == 'en') {
        $langEnId = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId, 'frmajaxloader', 'new_activities',
        'فعالیت جدید در تازه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmajaxloader', 'new_posts',
        'نوشته جدید در تازه‌ها');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmajaxloader', 'new_activities',
        'New Activities in Newsfeed');
    $languageService->addOrUpdateValue($langEnId, 'frmajaxloader', 'new_posts',
        'New Posts in Newsfeed');
}