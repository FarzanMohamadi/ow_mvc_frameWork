<?php
/**
 * User: Issa Annamoradnejad <i.moradnejad@gmail.com>
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmadvancesearch', 'frmadvancesearch.admin');

$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValue( 'fa-IR', 'frmadvancesearch', 'admin_settings_heading', 'تنظیمات افزونه جستجوی پیشرفته');
$languageService->addOrUpdateValue( 'en', 'frmadvancesearch', 'admin_settings_heading', 'Advance Search Settings');

$languageService->addOrUpdateValue( 'fa-IR', 'frmadvancesearch', 'settings', 'تنظیمات');
$languageService->addOrUpdateValue( 'en', 'frmadvancesearch', 'settings', 'Settings');

$languageService->addOrUpdateValue( 'fa-IR', 'frmadvancesearch', 'save_btn_label', 'ذخیره');
$languageService->addOrUpdateValue( 'en', 'frmadvancesearch', 'save_btn_label', 'Save');

$languageService->addOrUpdateValue( 'fa-IR', 'frmadvancesearch', 'admin_changed_success', ' تغییرات با موفقیت ذخیره شد');
$languageService->addOrUpdateValue( 'en', 'frmadvancesearch', 'admin_changed_success', 'Settings successfully saved');
