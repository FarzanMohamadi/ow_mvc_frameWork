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
    $languageService->addOrUpdateValue($langFaId, 'frmcontactus', 'admin_dept_title', 'تنظیمات افزونه ارتباط با ما');
    $languageService->addOrUpdateValue($langFaId, 'frmcontactus', 'admin_dept_heading', 'تنظیمات افزونه ارتباط با ما');
    $languageService->addOrUpdateValue($langFaId, 'frmcontactus', 'after_install_notification', 'لطفا برای ارسال رایانامه یک گروه <a href=\'{$url}\'>ایجاد</a> کنید');
    $languageService->addOrUpdateValue($langFaId, 'frmcontactus', 'form_label_from', 'رایانشانی');
    $languageService->addOrUpdateValue($langFaId, 'frmcontactus', 'message_sent', 'پیام شما به {$dept} ارسال شد. پاسخ در اسرع وقت ارسال خواهد شد.');
    $languageService->addOrUpdateValue($langFaId, 'frmcontactus', 'modified_successfully', 'تغییرات با موفقیت ذخیره شد');
    $languageService->addOrUpdateValue($langFaId, 'frmcontactus', 'mobile_bottom_menu_item', 'ارتباط با ما');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmcontactus', 'admin_contactus_settings_heading', 'Contact us settings');
    $languageService->addOrUpdateValue($langEnId, 'frmcontactus', 'modified_successfully', 'Changes saved successfully');
    $languageService->addOrUpdateValue($langEnId, 'frmcontactus', 'mobile_bottom_menu_item', 'Contact us');
}