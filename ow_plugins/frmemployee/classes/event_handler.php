<?php
/**
 * FRM Employee
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmemployee
 * @since 1.0
 */
class FRMEMPLOYEE_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    private function __construct()
    {
    }

    public function addEmployeeConsoleItem(BASE_CLASS_EventCollector $event)
    {
        if (FRMEMPLOYEE_BOL_Service::getInstance()->isUserACompany()) {
            $event->add(array(
                'label' => OW::getLanguage()->text('frmemployee', 'manage_employees'),
                'url' => OW_Router::getInstance()->urlForRoute('frmemployee.employees')));
        }
    }

    public function beforeGetQuestionValues(OW_Event $event)
    {
        // this step should be replace by cron
        $q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
        if (empty($q_ee)) {
            return;
        }

        $question_ee = BOL_QuestionService::getInstance()->findQuestionByName($q_ee);
        if (empty($question_ee)) {
            return;
        }

        $params = $event->getParams();
        if (isset($params['name']) && $params['name'] == $question_ee->name) {
            // find user ids with account type er
            $values = FRMEMPLOYEE_BOL_Service::getInstance()->getCompanies();
            $result = [];
            foreach ($values as $key => $val) {
                $item = new BOL_QuestionValue();
                $item->setId($val['userId']);
                $item->value = $val['userId'];
                $item->questionName = $question_ee->name;
                $item->questionText = $val['textValue'];
                $result[] = $item;
            }
            $event->setData(['value' => ['values' => $result, 'count' => count($result)]]);
        }
    }

    public function beforeGetQuestionLanguageValues(OW_Event $event)
    {
        // should replace all translations by cron
        $key = $event->getParams()['key'];
        $q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
        if (empty($q_ee) || !UTIL_String::startsWith($key, "questions_question_{$q_ee}_value_")) {
            return;
        }

        $parts = explode('_', $key);
        $er_id = array_pop($parts);

        $values = FRMEMPLOYEE_BOL_Service::getInstance()->getCompanies();
        foreach ($values as $key => $val) {
            if ($val['userId'] == $er_id) {
                $event->setData(['value' => $val['textValue']]);
                return;
            }
        }
    }

    public function onSaveQuestionData(OW_Event $event)
    {
        $userId = $event->getParams()['userId'];
        $data = $event->getData();
        $q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
        if(!isset($data[$q_ee])){
            return;
        }

        $currentEmployer = FRMEMPLOYEE_BOL_Service::getInstance()->getEmployerId($userId);
        if (isset($currentEmployer) && $data[$q_ee] != $currentEmployer){
            // remove date
            FRMEMPLOYEE_BOL_Service::getInstance()->toggleStateForUserId($userId);
        }
    }

    public function genericInit()
    {
        OW::getEventManager()->bind('before.get.question.values', array($this, 'beforeGetQuestionValues'));
        OW::getEventManager()->bind('core.get_text', array($this, 'beforeGetQuestionLanguageValues'));
        OW::getEventManager()->bind('base.questions_save_data', array($this, 'onSaveQuestionData'));
    }

    public function init()
    {
        $this->genericInit();

        OW::getEventManager()->bind('base.add_main_console_item', array($this, 'addEmployeeConsoleItem'));
    }

}