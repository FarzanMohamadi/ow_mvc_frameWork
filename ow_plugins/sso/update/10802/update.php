<?php

try {
    $question = new BOL_Question();
    $question->name = 'mobile_number';
    $question->required = false;
    $question->onJoin = true;
    $question->onEdit = false;
    $question->onSearch = false;
    $question->onView = false;
    $question->editable = false;
    $question->presentation = 'text';
    $question->type = 'text';
    $question->columnCount = 0;
    $question->sectionName = 'f90cde5913235d172603cc4e7b9726e3';
    $question->sortOrder = ( (int) BOL_QuestionService::getInstance()->findLastQuestionOrder($question->sectionName) ) + 1;
    $question->custom = json_encode(array());
    $question->removable = false;
    $questionValues = false;
    $name = OW::getLanguage()->text('sso', 'field_mobile_label');
    $description = OW::getLanguage()->text('sso', 'field_mobile_description');
    BOL_QuestionService::getInstance()->createQuestion($question, $name, $description, $questionValues, true);
    BOL_QuestionService::getInstance()->addQuestionToAccountType('mobile_number', array('290365aadde35a97f11207ca7e4279cc'));

} catch(Exception $e) {
    OW::getLogger()->writeLog(OW_Log::ERROR, 'sso_update_error_10802');
}
