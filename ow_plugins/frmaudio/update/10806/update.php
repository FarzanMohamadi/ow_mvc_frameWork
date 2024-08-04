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
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','main_menu_item', 'پیام صوتی');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','database_record_deleted', 'پیام صوتی حذف شد.');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','delete_item_warning', 'آیا از حذف این پیام صوتی اطمینان دارید؟');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','Audio_inserterd', ' پیام صوتی افزوده شد.');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','feed_item_line', 'یک پیام صوتی اضافه کرد.');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','Audio_not_inserterd', 'افزودن پیام صوتی با خطا مواجه شده است.');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','Audio_inserterd', 'پیام صوتی افزوده شد.');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','description_audio_page', ' لیست پیام‌های صوتی شما');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','admin_settings_title', 'تنظیمات افزونه پیام صوتی');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','audio_in_dashbord', 'اجازه درج پیام صوتی در داشبورد');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','audio_in_profile', 'اجازه درج پیام صوتی نمایه من');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','audio_in_forum', 'اجازه درج پیام صوتی در انجمن');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','audio_feed_removed', 'پیام صوتی افزوده شده به این نوشته حذف شده است.');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','mobile_main_menu_item', 'پیام صوتی');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','no_audio_data_list', 'هیچ پیام صوتی یافت نشد.');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','index_page_heading','پیام صوتی');
    $languageService->addOrUpdateValue($langFaId, 'frmaudio','index_page_title','پیام صوتی');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','index_page_title', 'Audio Massages');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','database_record_deleted', 'Audio Massage Removed');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','delete_item_warning', 'Are You Sure to Delete This Audio Massage?');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','Audio_inserterd', 'Audio Massage Inserterd Successfully');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','feed_item_line', 'Inserted New Audio Massage');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','Audio_not_inserterd', 'Audio Massage Has Not Inserterd');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','description_audio_page', 'List of Your Audio Massages');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','audionamefield', 'Audio Massage Name:');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','admin_settings_title', 'Audio Massage Plugin Settings.');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','audio_in_dashbord', 'Users Are Able to Add Aduio Massage in Dashboard');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','audio_in_profile', 'Users Are Able to Add Aduio in Massage Profile');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','audio_in_forum', 'Users Are Able to Add Aduio Massage in Forum');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','audio_feed_removed', 'An Audio Massage Has Been Removed From This Post');
    $languageService->addOrUpdateValue($langEnId, 'frmaudio','no_audio_data_list', 'No Audio Massages');
}