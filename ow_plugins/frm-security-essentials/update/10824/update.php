<?php
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
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'set_valid_ips', 'تنظیمات IPهای مجاز');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'input_settings_valid_ip_list_label', 'آدرس IPهای مجاز');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'input_settings_valid_ip_list_desc', 'در این قسمت آدرس IPهای مجاز برای دسترسی به بخش مدیریت را وارد نمایید. در هر خط یک IP وارد کنید.');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'user_ip_address', 'آدرس IP شما: ');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'set_valid_ips', 'set valid ips');
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'input_settings_valid_ip_list_label', 'valid IP addresses');
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'input_settings_valid_ip_list_desc', 'Insert valid IP addresses that has access to admin panel. Enter one IP for each row');
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'user_ip_address', 'Your IP address: ');
}
