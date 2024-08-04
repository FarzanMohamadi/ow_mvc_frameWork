<?php
$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langFaId = null;
$langEn = null;
foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
    if ($lang->tag == 'en') {
        $langEn = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId,'frmphotoplus','photo_gallery','آلبوم تصاویر');
}
if ($langEn != null) {
    $languageService->addOrUpdateValue($langEn,'frmphotoplus','photo_gallery','Gallery');
}