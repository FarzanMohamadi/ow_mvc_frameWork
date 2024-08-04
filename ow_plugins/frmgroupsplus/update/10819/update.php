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

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'select_category', 'هر دسته');
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'choose_category', 'انتخاب دسته');
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'view_category_label', 'دسته: <a href="{$categoryUrl}">{$categoryLabel}</a>');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmgroupsplus', 'select_category', 'Any category');
    $languageService->addOrUpdateValue($langEnId, 'frmgroupsplus', 'choose_category', 'Select category');
    $languageService->addOrUpdateValue($langEnId, 'frmgroupsplus', 'choose_category', 'Category: <a href="{$$categoryUrl}">{$categoryLabel}</a>');
}