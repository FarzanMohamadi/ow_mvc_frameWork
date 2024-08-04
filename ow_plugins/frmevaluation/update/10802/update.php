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
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'degree_header', 'رده‌بندی فعلی: ');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'results_header', 'نتایج ارزیابی');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'requirement_suggest', 'ارزیابی موارد پیشنهادی به تفکیک حوزه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'requirement_normal', 'ارزیابی الزامات عادی به تفکیک حوزه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'requirement_important', 'ارزیابی الزامات مهم به تفکیک حوزه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'requirement_fundamental', 'ارزیابی الزامات اساسی به تفکیک حوزه‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'user_value', 'امتیاز کسب شده');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'remaining_value', 'امتیاز باقی‌مانده');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'total_value', 'حداکثر امتیاز');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'questions_without_values', 'برای این سوال، پاسخی ایجاد نشده است.');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'category_questions_header', 'فهرست سوالات');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'admin_evaluation_settings_heading', 'تنظیمات افزونه ارزیابی');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'delete_item_warning', 'آیا از حذف این مورد اطمینان دارید؟');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'active_item_warning', 'آیا از فعال‌سازی این مورد اطمینان دارید؟');
    $languageService->addOrUpdateValue($langFaId, 'frmevaluation', 'lock_item_warning', 'آیا از قفل‌گذاری روی این مورد اطمینان دارید؟');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'degree_header', 'Current level:');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'results_header', 'Results of evaluation:');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'requirement_suggest', 'Assessment of suggested requirements in separate categories');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'requirement_normal', 'Assessment of normal requirements in separate categories');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'requirement_important', 'Assessment of important requirements in separate categories');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'requirement_fundamental', 'Assessment of fundamental requirements in separate categories');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'user_value', 'Earned point');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'remaining_value', 'Remaining point');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'total_value', 'Maximum point');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'questions_without_values', 'This question has not any values');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'category_questions_header', 'Questions');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'admin_evaluation_settings_heading', 'Evaluation plugin setting');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'delete_item_warning', 'Are you sure to delete this item?');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'active_item_warning', 'Are you sure to active this item?');
    $languageService->addOrUpdateValue($langEnId, 'frmevaluation', 'lock_item_warning', 'Are you sure to lock this item?');
}