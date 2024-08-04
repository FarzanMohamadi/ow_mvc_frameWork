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
    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'feed_add_item_label', 'یک خبر جدید ایجاد کرد.');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'news_manage_delete', 'حذف');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'confirm_delete_photos', 'آیا از حذف تصاویر همه کاربران اطمینان دارید؟');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'delete_content', 'حذف مطالب و حذف افزونه');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'delete_content_desc', 'قبل از حذف افزونه خبر ما باید تمام محتوای موجود کاربران را حذف کنیم . این ممکن است اندکی زمان‌بر باشد. در این زمان ما سایت را در حالت تعمیر و نگهداری قرار می دهیم و پس از پایان عملیات دوباره آن‌را فعال می‌کنیم.');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'page_title_uninstall', 'حذف افزونه خبر');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'plugin_set_for_uninstall', 'حذف افزونه اخبار آغاز شد');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'toolbar_delete', 'حذف');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'latest_description', 'اخباری که به تازگی در سامانه شبکه های اجتماعی به‌روزرسانی شده‌اند');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'admin_news_settings_heading', 'تنظیمات افزونه اخبار');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'admin_settings_results_per_page', 'نوشته‌های این صفحه');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'authorization_failed_view_news', 'با عرض پوزش، شما مجاز به مشاهده این خبر نیستید .');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'auth_action_label_delete_comment_by_content_owner', 'صاحب مطلب می تواند نظرات را پاک کند');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'news_archive_lbl_archives', 'بایگانی');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'news_entry_title', '{$entry_title} نوشته شده توسط : {$display_name} در {$site_name}');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'news_widget_preview_length_lbl', 'طول پیش نمایش');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'browse_by_tag_item_description', 'مرور برچسب‌های نوشته‌های خبر به عنوان {$tag}.');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'browse_by_tag_item_title', '{$tag}نوشته‌های مرتبط خبر {$site_name}');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'browse_by_tag_title', 'مرور نوشته‌های خبر به وسیله برچسب‌ها {$site_name}');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'by', 'توسط');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'cmp_widget_entry_count', 'تعداد نوشته‌ها برای نمایش');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'latest_entry', 'آخرین نوشته‌ها');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'manage_page_last_updated', 'آخرین به‌روزرسانی');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'manage_page_menu_drafts', 'پیش نویس ها');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'manage_page_menu_published', 'پست‌های منتشر شده');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'manage_page_status', 'وضعیت');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'menuItemMostDiscussed', 'بیشترین بحث شده‌ها');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'more', 'بیشتر');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'most_discussed_description', 'بیشترین نوشته‌های بحث شده خبر کاربر در {$site_name}.');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'most_discussed_title', 'بیشترین اخباری بحث شده  - {$site_name}');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'next_entry', 'نوشته بعدی');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'on', 'روی');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'sava_draft', 'ذخیره به عنوان پیش نویس');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'save_btn_label', 'ذخیره');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'settings', 'تنظیمات');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'top_rated_title', 'برترین خبرها - {$site_name}');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'most_discussed_title', 'بیشترین اخبار بحث شده  - {$site_name}');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'browse_by_tag_item_title', 'نوشته‌های مرتبط خبر با برچسب «{$tag}» - {$site_name}');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'save_form_lbl_date_enable', 'فعال‌سازی ویرایش تاریخ انتشار');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'save_form_lbl_date', 'تاریخ انتشار');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'news_notification_string', '<a href="{$actorUrl}">{$actor}</a> خبری منتشر کرده است: <a href="{$url}">«{$title}»</a> ');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'notification_form_lbl_published', 'اعلان انتشار خبر');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'email_notifications_setting_news', 'خبری منتشر شد');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'manage_page_menu_drafts', 'پیش‌نویس‌ها');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'manage_page_menu_published', 'اخبار منتشر شده');

    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'comment_notification_string', '<a href="{$actorUrl}">{$actor}</a> بر روی نوشته شما نظر گذاشته: <a href="{$url}">"{$title}"</a>');
    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'frmnews_mobile', 'اخبار');
    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'index_page_title', 'اخبار');
    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'index_page_heading', 'اخبار');
    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'auth_group_label', 'اخبار');
    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'results_by_tag','نتایج جستجو براساس برچسب: "<b>{$tag} </b>  "');
    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'news_entry_description', '{$entry_body} برچسب : {$tags}.');
    $languageService->addOrUpdateValue($langFaId, 'frmnews', 'news_entry_title', '{$entry_title} نوشته شده توسط: {$display_name} در {$site_name}');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmnews', 'save_form_lbl_date_enable', 'Enable publish date modification');

    $languageService->addOrUpdateValue($langEnId, 'frmnews', 'save_form_lbl_date', 'Publish date');

    $languageService->addOrUpdateValue($langEnId, 'frmnews', 'news_notification_string', '<a href="{$actorUrl}">{$actor}</a> published a news: <a href="{$url}">"{$title}"</a>');
    $languageService->addOrUpdateValue($langEnId, 'frmnews', 'frmnews_mobile', 'News');
    $languageService->addOrUpdateValue($langEnId, 'frmnews', 'index_page_title', 'News');
    $languageService->addOrUpdateValue($langEnId, 'frmnews', 'index_page_heading', 'News');
}