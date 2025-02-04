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
    $languageService->addOrUpdateValue($langFaId, 'frmblockingip', 'admin_page_heading', 'تنظیمات افزونه مسدودکردن کاربران ناهنجار');
    $languageService->addOrUpdateValue($langFaId, 'frmblockingip', 'admin_page_title', 'تنظیمات افزونه مسدودکردن کاربران ناهنجار');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmblockingip', 'admin_page_heading', 'blocking-ip plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmblockingip', 'admin_page_title', 'blocking-ip plugin settings');
}