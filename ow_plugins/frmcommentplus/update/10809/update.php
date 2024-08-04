<?php
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
    $languageService->addOrUpdateValue($langFaId, 'frmcommentplus', 'comment_news_string', '<a href="{$actorUrl}">{$actor}</a>  نیز روی <a href="{$contextUrl}">این خبر</a> نظر داد');
}

if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmcommentplus', 'comment_news_string', '<a href="{$actorUrl}">{$actor}</a> also commented on  <a href="{$contextUrl}">this news</a>');

}