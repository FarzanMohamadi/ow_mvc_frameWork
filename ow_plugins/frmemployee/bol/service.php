<?php
/**
 * FRM Employee
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmemployee
 * @since 1.0
 */
final class FRMEMPLOYEE_BOL_Service
{

    private static $classInstance;
    /**
     * Class constructor
     *
     */
    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === self::$classInstance) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * @return bool
     */
    public function isUserACompany()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return false;
        }
        $q_er = OW::getConfig()->getValue('frmemployee', 'employer_question');
        $q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
        if (empty($q_ee) || empty($q_er)) {
            return false;
        }

        $question_ee = BOL_QuestionService::getInstance()->findQuestionByName($q_ee);
        $question_er = BOL_QuestionService::getInstance()->findQuestionByName($q_er);
        if (empty($question_ee) || empty($question_er)) {
            return false;
        }

        $type = trim(OW::getUser()->getUserObject()->accountType);
        $a_er = OW::getConfig()->getValue('frmemployee', 'employer_account_type');
        return $type == $a_er;
    }

    public function getCompanies()
    {
        $q_er = OW::getConfig()->getValue('frmemployee', 'employer_question');
        if (empty($q_er)) {
            return [];
        }

        $question_er = BOL_QuestionService::getInstance()->findQuestionByName($q_er);
        if (empty($question_er)) {
            return [];
        }

        $a_er = OW::getConfig()->getValue('frmemployee', 'employer_account_type');
        return BOL_UserService::getInstance()->findQuestionValuesByAccountType($a_er, $question_er->name);
    }

    public function getEmployerId($userId)
    {
        $q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
        $val = BOL_QuestionDataDao::getInstance()->findQuestionValuesData($q_ee, [$userId]);
        if (empty($val) || $val[0]->intValue == $userId || empty($val[0]->dateValue)) {
            return;
        }
        return $val[0]->intValue;
    }

    public function getEmployer($userId)
    {
        $q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
        $val = BOL_QuestionDataDao::getInstance()->findQuestionValuesData($q_ee, [$userId]);
        if (empty($val) || $val[0]->intValue == $userId || empty($val[0]->dateValue)) {
            return;
        }

        return OW::getLanguage()->text('base', "questions_question_{$q_ee}_value_{$val[0]->intValue}");
    }

    public function toggleStateForUserId($ee_id){
        $q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
        $db = OW_DB_PREFIX;

        $q = "SELECT dateValue FROM {$db}base_question_data
                WHERE questionName='{$q_ee}' AND userId={$ee_id};";
        $c_value = OW::getDbo()->queryForColumnList($q);
        if(!empty($c_value)){
            $date = (empty($c_value[0]))?'CURRENT_TIME()':'NULL';
            $q = "UPDATE {$db}base_question_data
                SET dateValue={$date}
                WHERE questionName='{$q_ee}' AND userId={$ee_id};";
            OW::getDbo()->query($q);
        }
    }
}