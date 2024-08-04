<?php
/**
 * User: Issa Annamoradnejad
 */

$languageService = Updater::getLanguageService();
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmhashtag', 'author_label', 'نویسنده: <span class="ow_txt_value">{$name}</span>');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmhashtag', 'author_label', 'Author: <span class="ow_txt_value">{$name}</span>');
