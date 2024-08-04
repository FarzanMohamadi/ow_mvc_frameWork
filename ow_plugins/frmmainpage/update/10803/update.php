<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmmainpage', 'frmmainpage.admin');
$config = OW::getConfig();
if(!$config->configExists('frmmainpage', 'orders'))
{
    $config->addConfig('frmmainpage', 'orders', '');
}

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
    $languageService->addOrUpdateValue($langFaId, 'frmmainpage', 'orders', 'ترتیب منوها');
    $languageService->addOrUpdateValue($langFaId, 'frmmainpage', 'empty_row_label', 'خالی');
}

if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmmainpage', 'orders', 'Menu orders');
    $languageService->addOrUpdateValue($langEnId, 'frmmainpage', 'empty_row_label', 'Empty');
}
