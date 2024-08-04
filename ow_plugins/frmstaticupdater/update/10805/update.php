<?php
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
    $languageService->addOrUpdateValue($langFaId, 'frmstaticupdater', 'admin_settings_title', 'تنظیمات افزونه به‌روزرسان فایل‌های استاتیک هسته، افزونه‌ها، پوسته‌ها و به‌روزرسانی ترجمه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmstaticupdater', 'update_language_description', 'همچنین با استفاده از دکمه زیر، می‌توان نسخ اصلاح شده تمامی ترجمه‌ها را اعمال کرد.');
    $languageService->addOrUpdateValue($langFaId, 'frmstaticupdater', 'update_language_button', 'به‌روزرسانی ترجمه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmstaticupdater', 'update_language_successfully', 'به‌روزرسانی ترجمه‌ها با موفقیت انجام شد.');
}

if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmstaticupdater', 'admin_settings_title', 'Settings of static updater plugin');
    $languageService->addOrUpdateValue($langEnId, 'frmstaticupdater', 'update_language_description', 'Also all languages values can be updated using button.');
    $languageService->addOrUpdateValue($langEnId, 'frmstaticupdater', 'update_language_button', 'Update languages');
    $languageService->addOrUpdateValue($langEnId, 'frmstaticupdater', 'update_language_successfully', 'Languages values updated.');
}