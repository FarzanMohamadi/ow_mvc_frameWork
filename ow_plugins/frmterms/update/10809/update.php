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
    $languageService->addOrUpdateValue($langFaId, 'frmterms','terms_show_on_join_form_enable', 'این ویژگی فعال است. با فعال کردن این ویژگی، کاربر باید شرایط قرارداد را مورد بررسی قرار دهد اما با غیر فعال کردن آن، شرایط قرارداد در فرم ثبت‌نام به کاربران نمایش داده نمی‌شود.');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','terms_show_on_join_form_disable', 'این ویژگی غیرفعال است. با فعال کردن این ویژگی، کاربر باید شرایط قرارداد را مورد بررسی قرار دهد اما با غیر فعال کردن آن، شرایط قرارداد در فرم ثبت‌نام به کاربران نمایش داده نمی‌شود.');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','terms_show_in_join_form_set_enable', '<a href="{$value}"> نمایش در فرم ثبت‌نام.</a>');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','terms_show_in_join_form_set_disable', '<a href="{$value}"> پنهان‌ شده در فرم ثبت‌نام.</a>');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','terms_show_in_join_form', 'قابل مشاهده در فرم ثبت‌نام');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','terms_hide_in_join_form', 'پنهان شده در فرم ثبت‌نام');
}