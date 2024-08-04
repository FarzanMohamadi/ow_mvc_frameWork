<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

try {
    $languageService = Updater::getLanguageService();

    $languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsecurityessentials', 'change_user_password_by_code', 'تغییر رمز عبور');
    $languageService->addOrUpdateValueByLanguageTag('en', 'frmsecurityessentials', 'change_user_password_by_code', 'change user password');

    $languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsecurityessentials', 'email_is_not_registered', 'کاربری با ایمیل وارد شده یافت نشد');
    $languageService->addOrUpdateValueByLanguageTag('en', 'frmsecurityessentials', 'email_is_not_registered', 'No user found with this email');

    $languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsecurityessentials', 'password_changed_successfully', 'رمز عبور با موفقیت تغییر کرد');
    $languageService->addOrUpdateValueByLanguageTag('en', 'frmsecurityessentials', 'password_changed_successfully', 'user password changed successfully');
}catch(Exception $e){

}