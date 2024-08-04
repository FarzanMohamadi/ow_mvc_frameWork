<?php
/**
 * 
 * All rights reserved.
 */

$question = BOL_QuestionService::getInstance()->findQuestionByName("mobile_number");
BOL_QuestionService::getInstance()->deleteQuestion(array($question->id));
BOL_QuestionService::getInstance()->deleteQuestionToAccountType("mobile_number", array('290365aadde35a97f11207ca7e4279cc'));