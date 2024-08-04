<?php
Updater::getLanguageService()->updatePrefixForPlugin('frmsso');
$question = BOL_QuestionService::getInstance()->findQuestionByName('sso-phoneNumber');
if ($question != null){
    $label = OW::getLanguage()->text('frmsso', 'field_mobile_label');
    $description = OW::getLanguage()->text('frmsso', 'field_mobile_description');
    BOL_QuestionService::getInstance()->setQuestionLabel($question->name, $label, false);
    BOL_QuestionService::getInstance()->setQuestionDescription($question->name, $description, false);
}