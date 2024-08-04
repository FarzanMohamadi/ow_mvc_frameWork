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
    $languageService->addOrUpdateValue($langFaId, 'birthdays', 'my_widget_title', 'تاریخ تولد من');
    $languageService->addOrUpdateValue($langFaId, 'birthdays', 'feed_item_line', 'تولدت مبارک');
    $languageService->addOrUpdateValue($langFaId, 'birthdays', 'birthday', 'روز تولد');
}