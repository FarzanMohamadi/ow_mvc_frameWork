<?php
/**
 * Created by PhpStorm.
 * User: seied
 * Date: 4/19/2017
 * Time: 2:05 PM
 */

$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langFaId = null;
foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId, 'base', 'mail_template_admin_invite_user_content_html', '<p> سلام <br/> <p>  <br/> <a href="{$avatar}">{$name}</a> شما را دعوت کرده است تا به {$site_name}  بپیوندید. شما می توانید با استفاده از پیوند زیر وارد بخش ثبت‌نام شوید: <br /> <a href="{$url}">ثبت‌نام</a>');
    $languageService->addOrUpdateValue($langFaId, 'base', 'mail_template_admin_invite_user_content_text', '    سلام ،



     شما را دعوت کرده است تا به <a href="{$avatar}">{$name}</a> {$site_name}  بپیوندید.

    شما می توانید با استفاده از پیوند زیر وارد بخش ثبت‌نام شوید:

    {$url}



    گروه توسعه

    {$site_url}');

    $languageService->addOrUpdateValue($langFaId, 'admin', 'mail_template_admin_invite_user_content_html', '<p> سلام <br/> <p>  <br/> <a href="{$avatar}">{$name}</a> شما را دعوت کرده است تا به {$site_name}  بپیوندید. شما می توانید با استفاده از پیوند زیر وارد بخش ثبت‌نام شوید: <br /> <a href="{$url}">ثبت‌نام</a>');
    $languageService->addOrUpdateValue($langFaId, 'admin', 'mail_template_admin_invite_user_content_text', '    سلام ،



     شما را دعوت کرده است تا به <a href="{$avatar}">{$name}</a> {$site_name}  بپیوندید.

    شما می توانید با استفاده از پیوند زیر وارد بخش ثبت‌نام شوید:

    {$url}



    گروه توسعه

    {$site_url}');
}