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
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'plugins_sample', 'افزونه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'themes_sample', 'پوسته‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'view_versions', 'مشاهده تمامی نسخه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'download_plugins_description', 'برای بارگیری افزونه‌ها می‌توانید وارد صفحه <a href="{$url}" target="_blank">بارگیری افزونه‌ها</a> شده و افزونه را پیدا کرده و نسخه مورد نظر را بارگیری کنید. همچنین می‌توانید افزونه مورد نظر خود را در لیست زیر پیدا کنید.');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'download_themes_description', 'برای بارگیری پوسته‌ها می‌توانید وارد صفحه <a href="{$url}" target="_blank">بارگیری پوسته‌ها</a> شده و پوسته را پیدا کرده و نسخه مورد نظر را بارگیری کنید. همچنین می‌توانید پوسته مورد نظر خود را در لیست زیر پیدا کنید.');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'download_core_update_description','با استفاده از نسخه به‌روزرسانی، شما می‌توانید به صورت دستی، شبکه اجتماعی خود را از هر نسخه‌ای به نسخه {$version} به‌روزرسانی کنید.');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'view_guideline', 'راهنمای کاربری');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'guidelineurl_label', 'نشانی اینترنتی راهنمای کاربری');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'check_update_version', 'بررسی به‌روزرسانی');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'sha256_label', 'صحت اطلاعات (Sha256)');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'admin_page_heading', 'تنظیمات افزونه سرور بروزرسانی افزونه ها و پوسته ها');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'admin_page_title', 'تنظیمات افزونه سرور بروزرسانی افزونه ها و پوسته ها');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'return', 'بازگشت');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'update_guideline', 'نحوه به‌روزرسانی');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'download_core_main_description', 'دریافت مجموعه فایل‌های لازم راه‌اندازی کامل یک شبکه اجتماعی (در داخل این مجموعه، افزونه‌های مورد نیاز نیز قرار دارند.)');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'delete_item', 'حذف کردن مورد');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'buildNumber', 'نسخه');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'item_deleted_successfully', 'نسخه مورد نظر با موفقیت حذف شد.');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'admin_delete_item_title', 'تنظیمات افزونه سرور به‌روزرسانی افزونه‌ها و پوسته‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'last_version_buildNumber', 'شماره آخرین نسخه:');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'admin_check_item_title', 'بررسی به روز رسانی مورد');
    $languageService->addOrUpdateValue($langFaId, 'frmupdateserver', 'check_item', 'به روز رسانی مورد');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'plugins_sample', 'Plugins');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'themes_sample', 'Themes');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'download_plugins_description', 'You can find plugins in the page of <a href="{$url}" target="_blank"> download plugins</a> for downloading in any versions. Also you can find all plugins in the list.');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'download_themes_description', 'You can find themes in the page of <a href="{$url}" target="_blank">download themes</a> for downloading in any versions. Also you can find all themes in the list.');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'download_core_update_description', 'You can update manually your social network using with any version to version of {$version}.');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'view_guideline', 'User manual');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'guidelineurl_label', 'Guideline url');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'sha256_label', 'Information verification (Sha256)');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'admin_page_heading', 'Update server plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'admin_page_title', 'Update server plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'return', 'Return');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'update_guideline', 'Update guideline');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'delete_item', 'Delete Item');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'buildNumber', 'Build Number');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'item_deleted_successfully', 'Item Deleted Successfully');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'admin_delete_item_title', 'Update Server Plugin setting');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'last_version_buildNumber', 'Last Version Build Number');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'admin_check_item_title', 'Check item for update');
    $languageService->addOrUpdateValue($langEnId, 'frmupdateserver', 'check_item', 'Update item');
}