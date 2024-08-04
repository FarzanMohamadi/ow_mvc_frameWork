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
    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'reposition_label', 'تغییر موقعیت سرصفحه');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'main_menu_item', 'تصویر سرصفحه');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'list_is_empty', 'شما تصویر سرصفحه‏ای ندارید.');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'edit_for_select_cover', 'ویرایش تصاویر سرصفحه');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'empty_image', 'شما باید یک تصویر سرصفحه بارگذاری نمایید.');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'not_valid_image', 'این تصویر پشتیبانی نمی‏شود');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'description_coverphoto_float_page', 'شما می‌توانید از تصاویر سرصفحه قدیمی خود به عنوان تصویر سرصفحه فعلی استفاده کنید و یا آن‏ها را حذف کنید.');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'description_coverphoto_page', 'شما می‌توانید تصویر سرصفحه خود را بارگذاری کرده و آخرین تصویر بارگذاری شده به عنوان تصویر سرصفحه انتخاب خواهد شد. اگر می‌خواهید تصویر سرصفحه خود را تغییر دهید، می‌توانید از تصاویر سرصفحه قدیمی خود استفاده کنید و یا یک تصویر جدید بارگذاری نمایید.');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'errors_image_invalid', 'این نوع تصویر پشتیبانی نمی‏شود');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'forms_page_heading', 'تصاویر سرصفحه');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'are_you_sure_to_remove', 'آیا شما از حذف این تصویر سرصفحه اطمینان دارید؟');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'are_you_sure_to_use_this', 'آیا شما از استفاده از این تصویر به عنوان تصویر سرصفحه اطمینان دارید؟');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'covers', 'لیست تصاویر سرصفحه');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'forms_title_field_description', 'شما باید عنوان تصویر سرصفحه را وارد نمایید.');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'forms_title_field_label', 'عنوان تصویر سرصفحه');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'index_page_heading', 'تصویر سرصفحه');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'index_page_title', 'تصویر سرصفحه');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'database_record_saved_info', 'تصویر سرصفحه با موفقیت ذخیره شد.');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'database_record_used', 'تصویر سرصفحه با موفقیت تغییر کرد.');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'database_record_deleted', 'تصویر سرصفحه با موفقیت حذف شد.');

    $languageService->addOrUpdateValue($langFaId, 'coverphoto', 'upload_image', 'بارگذاری تصویر');
}