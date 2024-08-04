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
    $languageService->addOrUpdateValue($langFaId, 'frmterms', 'notification_content', 'یک نسخه جدید از {$value1} منتشر شد. {$value2} بند مهم افزوده، ویرایش یا حذف شدند.');
    $languageService->addOrUpdateValue($langFaId, 'frmterms', 'send_notification_description', 'ارسال پیام جهت اطلاع از تغییرات در شرایط استفاده از خدمات و سیاست حریم خصوصی');
    $languageService->addOrUpdateValue($langFaId, 'frmterms', 'delete_item', 'حذف');
    $languageService->addOrUpdateValue($langFaId, 'frmterms', 'delete_section', 'حذف');
    $languageService->addOrUpdateValue($langFaId, 'frmterms', 'delete_section_warning', 'آیا از حذف این نسخه اطمینان دارید؟');
    $languageService->addOrUpdateValue($langFaId, 'frmterms', 'admin_page_heading', 'تنظیمات افزونه شرایط');
    $languageService->addOrUpdateValue($langFaId, 'frmterms', 'admin_page_title', 'تنظیمات افزونه شرایط');
    $languageService->addOrUpdateValue($langFaId, 'frmterms', 'mobile_notification_content', '<a href="{$url}">یک نسخه جدید از {$value1} منتشر شد. {$value2} بند مهم افزوده، ویرایش یا حذف شدند.</a>');
    $languageService->addOrUpdateValue($langFaId, 'frmterms', 'mobile_bottom_menu_item', 'شرایط');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','database_record_add', 'مورد با موفقیت اضافه شد');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','database_record_edit', 'مورد با موفقیت ویرایش شد');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','database_record_deleted', 'مورد با موفقیت برداشته شد');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','database_record_deactivate_item', 'مورد با موفقیت غیرفعال شد');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','database_record_activate_item', 'مورد با موفقیت فعال شد.');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','items', 'موارد');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','active_items', 'موارد فعال');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','inactive_items', 'موارد غیرفعال');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','delete_item_warning', 'آیا از حذف این مورد اطمینان دارید؟');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','edit_item_page_title', 'ویرایش مورد');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','add_version_description', 'شما با استفاده از پیوند زیرمی‌توانید نسخه جدید را منتشر کنید. نسخه جدید شامل همه مورد‌های فعال است.');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','add_version_without_items', 'شما نمی‌توانید یک نسخه جدید را بدون هیچ موردی منتشر کنید.');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','add_new_item_header', 'اضافه کردن یک مورد جدید به این بخش.');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','terms_description','افزونه به شما اجازه می‌دهد تا به عنوان مدیر، مورد اضافه کنید یا از موارد پیش‌فرض مانند شرایط برخورداری از خدمت، سیاست حریم خصوصی و صفحه سئوالات متداول استفاده کنید. به‌علاوه، شما می‌توانید دو صفحه پیش‌فرض را برای موارد خود شخصی‌سازی کنید. کاربر می‌تواند صفحه‌ای را ببیند که حداقل دارای یک مورد باشد. شما می‌توانید از گزینه‌های کشیدن و انداختن برای مدیریت چینش یا تغییر وضعیت موارد استفاده کنید.');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','add_version_warning', 'آیا از انتشار نسخه جدید موارد فعال مطمئن هستید؟ کاربران در ارتباط با موارد ویرایش شده در مقایسه با نسخه‌های سابق آن‌ها  زمانی که گزینه‌های اطلاع رسانی یا رایانشانی برای آن موارد فعال باشد، آگاه خواهند شد.');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','email_html_content', 'شما می‌توانید همه موارد را در {$value} مشاهده کنید. موارد مهمی که تغییر کرده‌اند یا در یک نسخه منتشر شده اضافه شده‌اند در زیر قابل مشاهده‌اند.');
    $languageService->addOrUpdateValue($langFaId, 'frmterms','section_empty_description', 'نسخه‌ای منتشر نشده است. شرایط در اسرع وقت منتشر خواهد شد.');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmterms', 'admin_page_heading', 'Terms plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmterms', 'admin_page_title', 'Terms plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmterms', 'mobile_notification_content', '<a href="{$url}">A new version of {$value1} released. {$value2} important items changed, added or removed.</a>');
    $languageService->addOrUpdateValue($langEnId, 'frmterms', 'mobile_bottom_menu_item', 'Terms');
}