<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frminvite', 'reach_daily_limit_error', 'شما به محدودیت تعداد ارسال دعوت روزانه خود رسیدید.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frminvite', 'reach_daily_limit_error', 'You have reach the invitation limit.');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frminvite', 'general_setting', 'عمومی');
$languageService->addOrUpdateValueByLanguageTag('en', 'frminvite', 'general_setting', 'General');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frminvite', 'limit_field', 'محدودیت تعداد دعوت روزانه برای هر کاربر');
$languageService->addOrUpdateValueByLanguageTag('en', 'frminvite', 'limit_field', 'Invitation daily limit for each user');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frminvite', 'left_budget_message', 'بودجه باقیمانده امروز شما برای دعوت {$budget} است.');
$languageService->addOrUpdateValueByLanguageTag('en', 'frminvite', 'left_budget_message', 'Your left invite budget is {$budget} for today.');


OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frminvite_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `date` varchar(10) NOT NULL,
  `number` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

$config = OW::getConfig();
if ( !$config->configExists('frminvite', 'invite_daily_limit') )
{
    $config->addConfig('frminvite', 'invite_daily_limit',100);
}
