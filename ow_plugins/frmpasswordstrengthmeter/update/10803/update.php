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
    $languageService->addOrUpdateValue($langFaId, 'frmpasswordstrengthmeter', 'strength_password_validate_error', 'گذرواژه وارد شده، حداقل استحکام مورد نیاز را ندارد. حداقل استحکام قابل قبول، سطح {$value} است.');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordstrengthmeter', 'minimum_requirement_password_strength_label', 'انتخاب حداقل معیار قبولی برای گذرواژه ');

    $languageService->addOrUpdateValue($langFaId, 'frmpasswordstrengthmeter', 'admin_page_heading', 'تنظیمات افزونه نمایشگر میزان قدرت گذرواژه');
    $languageService->addOrUpdateValue($langFaId, 'frmpasswordstrengthmeter', 'admin_page_title', 'تنظیمات افزونه نمایشگر میزان قدرت گذرواژه');
    $languageService->addOrUpdateValue($langFaId, 'frmpasswordstrengthmeter', 'secure_password_information_minimum_strength_type', 'گذرواژه باید دارای حداقل سطح امنیتی {$value} باشد.');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmpasswordstrengthmeter', 'admin_page_heading', 'Password strength meter plugin settings');
    $languageService->addOrUpdateValue($langEnId, 'frmpasswordstrengthmeter', 'admin_page_title', 'Password strength meter plugin settings');
}