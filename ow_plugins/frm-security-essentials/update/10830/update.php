<?php
$config = OW::getConfig();
if ( !$config->configExists('frmsecurityessentials', 'privacyUpdateNotification') )
{
    $config->addConfig('frmsecurityessentials', 'privacyUpdateNotification', false);
}

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
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'privacyUpdateNotification', 'فعال بودن اعلان به‌روزرسانی تنظیمات حریم خصوصی');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'privacyUpdateNotification', 'Privacy settings update notification');
}
