<?php
try {
    BOL_LanguageService::getInstance()->addPrefix('frmadvanceeditor','FRMAdvanceEditor');
    Updater::getLanguageService()->updatePrefixForPlugin('frmadvanceeditor');

    OW::getPluginManager()->addPluginSettingsRouteName('frmadvanceeditor', 'frmadvanceeditor.admin_config');
}catch(Exception $e){}

$langEnId = null;
$langFaId = null;

$languageService = Updater::getLanguageService();
$languages = $languageService->getLanguages();

foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
    if ($lang->tag == 'en') {
        $langEnId = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId, 'frmadvanceeditor', 'config_page_title', 'تنظیمات ویرایشگر پیشرفته');
    $languageService->addOrUpdateValue($langFaId, 'frmadvanceeditor', 'max_symbols_count_title', 'حداکثر تعداد کاراکترها');
    $languageService->addOrUpdateValue($langFaId, 'frmadvanceeditor', 'max_symbols_count_error', 'یک عدد بزرگتر از صفر وارد کنید');
    $languageService->addOrUpdateValue($langFaId, 'frmadvanceeditor', 'set_max_symbols_count', 'ثبت تعداد کاراکتر مجاز');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmadvanceeditor', 'config_page_title', 'Wysiwyg Settings');
    $languageService->addOrUpdateValue($langEnId, 'frmadvanceeditor', 'max_symbols_count_title', 'Max symbols count');
    $languageService->addOrUpdateValue($langEnId, 'frmadvanceeditor', 'max_symbols_count_error', 'enter a number greater than 0');
    $languageService->addOrUpdateValue($langEnId, 'frmadvanceeditor', 'set_max_symbols_count', 'Set max symbols count');
}

