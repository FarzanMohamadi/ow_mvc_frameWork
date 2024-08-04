<?php
$question = BOL_QuestionService::getInstance()->findQuestionByName('field_national_code');
BOL_QuestionService::getInstance()->deleteQuestion(array($question->id));
BOL_QuestionService::getInstance()->deleteQuestionToAccountType('field_national_code', array('290365aadde35a97f11207ca7e4279cc'));
