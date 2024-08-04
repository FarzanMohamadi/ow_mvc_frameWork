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
    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'admin_page_heading', 'تنظیمات افزونه تغییر رمز دوره ای');
    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'admin_page_title', 'تنظیمات افزونه تغییر رمز دوره ای');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'description_change_password', 'شما باید گذرواژه خود را به دلیل چالش امنیتی تغییر دهید');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'deal_with_expired_password_normal_with_notif', 'کاربران با آگاه شدن از منقضی بودن گذرواژه خود، قادر هستند هر کاری انجام دهند');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'set_all_password_valid_description', 'شما می‏توانید گذرواژه‌ها تمامی کاربران را معتبر کنید تا آن‏ها نیاز به تغییر گذرواژه نداشته باشند.');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'change_password_per_day_label', 'تغییر گذرواژه بصورت بازه ای (بر حسب روز)');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'deal_with_expired_password', 'نحوه مقابله با گذرواژه‌های منقضی شده');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'set_all_password_invalid', 'نامعتبر سازی تمامی گذرواژه‌ها');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'set_all_password_valid', 'معتبرسازی تمامی گذرواژه‌ها');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'set_all_password_invalid_description', ' شما می‏توانید گذرواژه‌های تمامی کاربران را نامعتبر کنید.');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'password_is_invalid_description', 'گذرواژه قبلی شما به دلیل چالش امنیتی نامعتبر است. نیاز است تا شما از پیوندی که به رایانشانی شما فرستاده شده وارد شوید و گذرواژه خود را تغییر دهید.');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'admin_page_heading', 'تنظیمات افزونه تغییر دوره ای رمز عبور');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordchangeinterval', 'admin_page_title', 'تنظیمات افزونه تغییر دوره ای رمز عبور');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmpasswordchangeinterval', 'admin_page_heading', 'Password interval change settings');
    $languageService->addOrUpdateValue($langEnId, 'frmpasswordchangeinterval', 'admin_page_title', 'Password interval change settings');
}