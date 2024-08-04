<?php
try {
    OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmupdateserver_category` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(200) NOT NULL,
    PRIMARY KEY (`id`)
    )DEFAULT CHARSET=utf8');
} catch (Exception $e) {}

try {
    OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmupdateserver_plugin_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itemId` int(11) NOT NULL,
  `categories` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8 ');
} catch (Exception $e) {}


$languageService = Updater::getLanguageService();
$languages = $languageService->getLanguages();
$langFaId = null;
$langEn = null;
foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
    if ($lang->tag == 'en') {
        $langEn = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','categories','دسته‌ها');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','label_category_label','عنوان دسته');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','label_error_already_exist','دسته وارد شده وجود دارد');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','form_add_category_submit','افزودن دسته');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','admin_settings_categories','دسته افزونه‌ها');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','edit_item','ویرایش');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','database_record_removed','دسته مورد نظر حذف شد');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','database_record_edit','دسته مورد نظر ویرایش شد');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','database_record_edit_fail','دسته مورد نظر ویرایش نشد');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','name_category','دسته‌های موجود');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','edit_item_page_title','ویرایش برچسب دسته');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','category_label','دسته');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','all_categories','تمامی دسته‌ها');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','category_description','دسته‌بندی افزونه‌ها بر اساس:');
    $languageService->addOrUpdateValue($langFaId,'frmupdateserver','are_you_sure','آیا از حذف این دسته مطمئن هستید؟');
}

if ($langEn != null) {
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','categories','categories');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','label_category_label','Category label');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','label_error_already_exist','label already exist');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','form_add_category_submit','Add‌ Category');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','admin_settings_categories','Plugin Categories');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','edit_item','Edit');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','database_record_removed','category removed');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','database_record_edit','category edited');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','database_record_edit_fail','category edit failed');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','name_category','Existing Categories');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','edit_item_page_title','Category label edit');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','category_label','Category');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','all_categories','All categories');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','category_description','Plugins are categorized in: ');
    $languageService->addOrUpdateValue($langEn,'frmupdateserver','are_you_sure','are you sure to delete this category');
}
