<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/25/2017
 * Time: 10:29 AM
 */

$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langEnId = null;
$langFaId = null;
foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
    if ($lang->tag == 'en') {
        $langEnId = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId, 'frmmainpage', 'user_groups', 'گروه‌های من');
    $languageService->addOrUpdateValue($langFaId, 'frmmainpage', 'find_friends', 'یافتن مخاطبان جدید');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmmainpage', 'user_groups', 'My groups');
    $languageService->addOrUpdateValue($langEnId, 'frmmainpage', 'find_friends', 'Find New Friends');
}