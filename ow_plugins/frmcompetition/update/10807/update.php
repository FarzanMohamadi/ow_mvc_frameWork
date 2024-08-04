<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmcompetition', 'deleted_successfully', 'حذف با موفقیت انجام شد');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmcompetition', 'deleted_successfully', 'Deleted successfully');

$languageService->addOrUpdateValueByLanguageTag('en', 'frmcompetition', 'saved_successfully', 'Saved successfully');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmcompetition', 'saved_successfully', 'ذخیره با موفقیت انجام شد');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmcompetition', 'user_not_found', 'کاربری یافت نشد');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmcompetition', 'user_not_found', 'User not found');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmcompetition', 'not_active', 'این مسابقه به پایان رسیده است');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmcompetition', 'not_active', 'The competition is over');


$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmcompetition', 'no_participant', 'شرکت‌کننده‌ای در این مسابقه شرکت نکرده است');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmcompetition', 'no_participant', 'There is no participant');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmcompetition', 'empty_competition', 'مسابقه‌ای وجود ندارد');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmcompetition', 'empty_competition', 'There is no competition');

$languageService->addOrUpdateValueByLanguageTag('en', 'frmcompetition', 'competition_notification_string', 'New competition with title: &lt;a href="{$competitionUrl}"&gt;{$competitionTitle}&lt;/a&gt; has been published');