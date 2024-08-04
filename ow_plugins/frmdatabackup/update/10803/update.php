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
    $languageService->addOrUpdateValue($langFaId, 'frmdatabackup', 'newsfeed_status', 'وضعیت‌های کاربران (به جر آخرین وضعیت)');
    $languageService->addOrUpdateValue($langFaId, 'frmdatabackup', 'newsfeed_action', 'اعمال انجام شده کاربران در تازه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmdatabackup', 'admin_page_heading', 'تنظیمات افزونه پشتیبانی از داده ها');
    $languageService->addOrUpdateValue($langFaId, 'frmdatabackup', 'admin_page_title', 'تنظیمات افزونه پشتیبانی از داده ها');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmdatabackup', 'newsfeed_status', 'Newsfeed status(Except the last)');
    $languageService->addOrUpdateValue($langEnId, 'frmdatabackup', 'admin_page_heading', 'Data backup plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmdatabackup', 'admin_page_title', 'Data backup plugin settings');
}