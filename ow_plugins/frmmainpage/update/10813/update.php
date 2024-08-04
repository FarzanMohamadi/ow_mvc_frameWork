<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

$languageService = Updater::getLanguageService();
$languageService->addOrUpdateValueByLanguageTag('en', 'frmmainpage', 'admin_settings_heading', 'Mainpage Plugin Settings');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmmainpage', 'admin_settings_heading', 'تنظیمات افزونه صفحه اصلی موبایل');

try{
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmmainpage.settings', 'frmmainpage', 'settings', OW_Navigation::VISIBLE_FOR_MEMBER);
}catch(Exception $e){

}