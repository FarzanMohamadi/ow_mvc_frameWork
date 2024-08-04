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
    $languageService->addOrUpdateValue($langFaId, 'frmterms', 'js_agree_with_terms', ' من با <a href="{$value}">قوانین</a> موافقم');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmterms', 'js_agree_with_terms', 'I agree with <a href="{$value}">terms</a>');
}