<?php
/**
 * Created by PhpStorm.
 * User: seied
 * Date: 4/19/2017
 * Time: 2:05 PM
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
    $languageService->addOrUpdateValue($langFaId, 'frmmutual', 'main_menu_item', 'مخاطبان مشترک');
    $languageService->addOrUpdateValue($langFaId, 'frmmutual', 'empty_list', 'شما مخاطب مشترکی ندارید.');
    $languageService->addOrUpdateValue($langFaId, 'frmmutual', 'numberOfMutualFriends', 'حداکثر تعداد مخاطب مشترک به کاربر');
    $languageService->addOrUpdateValue($langFaId, 'frmmutual', 'admin_page_heading', 'تنظیمات افزونه مخاطبان مشترک');
    $languageService->addOrUpdateValue($langFaId, 'frmmutual', 'admin_page_title', 'تنظیمات افزونه مخاطبان مشترک');
}