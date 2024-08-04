<?php
/**
 * Created by PhpStorm.
 * User: seied
 * Date: 4/19/2017
 * Time: 3:25 PM
 */

$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langFaId = null;
foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
}
if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'request_to_users_desc', 'لیست زیر شامل افرادی است که قبلا عضو شده‌اند. شما می‌توانید درخواست افزودن به مخاطبان را برای آن‌ها بفرستید.');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'request_to_users', 'فرستادن درخواست افزودن به مخاطبان');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'find_list_successfully', 'تمامی مخاطبان حساب کاربری شما با موفقیت دریافت شدند.');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'find_list_empty', 'مخاطبی یافت نشد. لطفا دوباره تلاش کنید.');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'yahoo_import_button', 'پیدا کردن مخاطبان از طریق حساب کاربری Yahoo');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'google_import_button', 'پیدا کردن مخاطبان از طریق حساب کاربری Google');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'import_users_desc', 'شما می‌توانید با داشتن هر کدام از حساب کاربری‌های زیر، مخاطبان خود را پیدا کرده و درخواست افزودن مخاطب یا ثبت‌نام برای آن‌ها ارسال کنید.');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'import_users_header', 'پیدا کردن مخاطبان از طریق حساب کاربری‌های دیگر');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'bottom_menu_item', 'پیدا کردن مخاطبان');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'no_account_exist', 'امکان استفاده از حساب کاربری‌های دیگر برای پیدا کردن مخاطب وجود ندارد.');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'admin_page_heading', 'تنظیمات افزونه پیدا کردن مخاطبان از حسابهای دیگر');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'admin_page_title', 'تنظیمات افزونه پیدا کردن مخاطبان از حسابهای دیگر');
}