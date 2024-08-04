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
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'invite_users_desc', 'لیست زیر شامل افرادی است که هنوز عضو نیستند. شما می‌توانید درخواست ثبت‌نام را برای آن‌ها بفرستید.');
    $languageService->addOrUpdateValue($langFaId, 'frmimport', 'import_users_desc', 'شما می‌توانید با داشتن هر کدام از حساب کاربری‌های زیر، مخاطبان خود را پیدا کرده و درخواست افزودن مخاطب یا ثبت‌نام برای آن‌ها ارسال کنید.');
}