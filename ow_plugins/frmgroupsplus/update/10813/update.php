<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 5/31/2017
 * Time: 3:43 PM
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
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'widget_files_settings_count', 'تعداد فایل');
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'files_count', 'تعداد کل فایل‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'widget_files_title', 'فایل‌ها');
}

if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmgroupsplus', 'widget_files_settings_count', 'Files number');
    $languageService->addOrUpdateValue($langEnId, 'frmgroupsplus', 'files_count', 'Files count');
    $languageService->addOrUpdateValue($langEnId, 'frmgroupsplus', 'widget_files_title', 'Files');
}