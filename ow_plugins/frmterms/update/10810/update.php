<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/25/2017
 * Time: 10:29 AM
 */

$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmterms', 'terms_show_in_join_form_set_enable', '<a href="{$value}"> نمایش در فرم ثبت‌نام.</a>');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmterms', 'terms_show_in_join_form_set_disable', '<a href="{$value}"> پنهان‌ شده در فرم ثبت‌نام.</a>');