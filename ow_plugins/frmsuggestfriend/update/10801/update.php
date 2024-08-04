<?php
/**
 * Created by PhpStorm.
 * User: seied
 * Date: 4/19/2017
 * Time: 2:25 PM
 */

$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langFaId = null;
foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId, 'frmsuggestfriend', 'main_menu_item', 'پیشنهاد مخاطب');
    $languageService->addOrUpdateValue($langFaId, 'frmsuggestfriend', 'empty_list', 'شخصی برای افزودن به مخاطبان وجود ندارد.');
}