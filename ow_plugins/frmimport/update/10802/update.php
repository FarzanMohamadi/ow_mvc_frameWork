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

if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmimport', 'admin_page_heading', 'Import plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmimport', 'admin_page_title', 'Import plugin settings');
}