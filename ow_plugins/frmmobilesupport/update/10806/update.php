<?php
try
{
    $languageService = Updater::getLanguageService();

    $languages = $languageService->getLanguages();
    $langFaId = null;
    $langEnId = null;
    foreach ($languages as $lang) {
        if ($lang->tag == 'fa-IR') {
            $langFaId = $lang->id;
        }
        if ($lang->tag == 'en') {
            $langEnId = $lang->id;
        }
    }

    if ($langFaId != null) {
        $languageService->addOrUpdateValue($langFaId, 'frmmobilesupport', 'auth_action_label_show_desktop_version', 'مشاهده نسخه رومیزی');
        $languageService->addOrUpdateValue($langFaId, 'frmmobilesupport', 'use_with_mobile_version', 'شما تنها قادر به استفاده از برنامه تلفن همراه این شبکه هستید.');
        $languageService->addOrUpdateValue($langFaId, 'frmmobilesupport', 'android_app_download', 'دریافت نسخه اندروید');
        $languageService->addOrUpdateValue($langFaId, 'frmmobilesupport', 'ios_app_download', 'دریافت نسخه iOS');
        $languageService->addOrUpdateValue($langFaId, 'frmmobilesupport', 'sign_out_description', 'شما هم‌اکنون وارد حساب کاربری خود شده‌اید. می‌توانید با استفاده از لینک زیر، از حساب کاربری خود خارج شوید.');
    }
    if ($langEnId != null) {
        $languageService->addOrUpdateValue($langEnId, 'frmmobilesupport', 'auth_action_label_show_desktop_version', 'Show desktop version');
        $languageService->addOrUpdateValue($langEnId, 'frmmobilesupport', 'use_with_mobile_version', 'You can only use mobile app.');
        $languageService->addOrUpdateValue($langEnId, 'frmmobilesupport', 'android_app_download', 'Download android version');
        $languageService->addOrUpdateValue($langEnId, 'frmmobilesupport', 'ios_app_download', 'Download iOS version');
        $languageService->addOrUpdateValue($langEnId, 'frmmobilesupport', 'sign_out_description', 'You are currently signed in to your account. You can use the link below, sign out of your account.');
    }

    $authService = BOL_AuthorizationService::getInstance();
    $authorization = OW::getAuthorization();
    $groupName = 'frmmobilesupport';
    if ( $authService->findGroupByName($groupName) === null ) {
        $authorization->addGroup($groupName);
    }
    $actionName = 'show-desktop-version';
    if($authService->findAction($groupName, $actionName) === null) {
        $authorization->addAction($groupName, $actionName);
    }
}
catch ( LogicException $e ) {}