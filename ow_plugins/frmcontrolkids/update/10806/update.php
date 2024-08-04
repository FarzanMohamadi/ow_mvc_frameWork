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
    $languageService->addOrUpdateValue($langFaId, 'frmcontrolkids', 'admin_page_heading', 'تنظیمات افزونه پایش فرزندان');
    $languageService->addOrUpdateValue($langFaId, 'frmcontrolkids', 'admin_page_title', 'تنظیمات افزونه پایش فرزندان');
    $languageService->addOrUpdateValue($langFaId, 'frmcontrolkids', 'marginTimeLabel', 'مدت زمان باقی‌مانده برای خروج از سن کودکی (بر حسب هفته)');
    $languageService->addOrUpdateValue($langFaId, 'frmcontrolkids', 'minimumKidsAgeLabel','حداکثر سن برای شناسایی کاربر کودک');
    $languageService->addOrUpdateValue($langFaId, 'frmcontrolkids', 'parents_kids_message', 'بر اساس قوانین این شبکه اگر شما زیر {$kidsAge} سال سن دارید، موظف هستید رایانامه والد خود را وارد نمایید.');

}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmcontrolkids', 'admin_page_heading', 'Control kids plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmcontrolkids', 'admin_page_title', ' Control kids plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmcontrolkids', 'minimumKidsAgeLabel', ' Maximum of kids age');
}