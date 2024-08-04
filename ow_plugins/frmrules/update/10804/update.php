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
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'security', 'توصیه‌های امنیتی');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'privacy', 'توصیه‌های حریم خصوصی');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'security_header', 'فهرست توصیه‌های امنیت نرم‌افزار در شبکه‌های اجتماعی');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'privacy_header', 'فهرست توصیه‌های حریم خصوصی کاربران در شبکه‌های اجتماعی');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'numberingLabel', 'توصیه شماره {$value}:');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'bottom_menu_item', 'توصیه به متولیان');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'country_header', 'فهرست قوانین و ضوابط اجرایی جمهوری اسلامی ایران در حوزه فضای مجازی');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'guideline', 'راهنمای مطالعه توصیه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'delete_item_warning', 'آیا از حذف این آیتم اطمینان دارید؟');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'admin_page_heading', 'تنظیمات افزونه توصیه ها');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'admin_page_title', 'تنظیمات افزونه توصیه ها');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'guidelineFieldLabel', 'متن راهنمای توصیه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmrules', 'filer_by_category', 'دسته‌ها');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmrules', 'admin_page_heading', 'Rules plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmrules', 'admin_page_title', 'Rules plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmrules', 'guidelineFieldLabel', 'Guideline text');
    $languageService->addOrUpdateValue($langEnId, 'frmrules', 'filer_by_category', 'Categories');
    $languageService->addOrUpdateValue($langEnId, 'frmrules', 'guideline', 'Guideline');
}