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
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'verify_using_code', 'فعال‌سازی حساب کاربری از طریق کد ارسال شده به شما');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'admin_page_heading', 'تنظیمات موارد امنیتی ضروری');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'admin_page_title', 'تنظیمات موارد امنیتی ضروری');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'view_user_comment_widget', 'نمایش ابزارک نظر در نمایه (به دلیل مسائل حریم خصوصی توصیه می‌شود آن را فعال نکنید.)');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'delete_feed_item_label','حذف نوشته از نمایه');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'delete_feed_item_confirmation','آیا از حذف این نوشته از نمایه اطمینان دارید؟');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'last_post_of_others_newsfeed_description','با تغییر این گزینه، حق دسترسی تمامی نوشته‌های گذشته ایجاد شده توسط دیگران در نمایه شما، تغییر خواهد کرد.');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'questions_section_password','گذرواژه');
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'password','گذرواژه');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'verify_using_code', 'Account activation via the verification code that has been sent');
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'admin_page_heading', 'Security essentials plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'admin_page_title', 'Security essentials plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'delete_feed_item_label', 'Delete post form profile');
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'delete_feed_item_confirmation', 'Are you sure to delete this post from your profile?');
}
