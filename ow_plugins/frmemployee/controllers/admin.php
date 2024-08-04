<?php
/**
 * FRM Employee
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmemployee
 * @since 1.0
 */
class FRMEMPLOYEE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmemployee', 'admin_settings_title'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmemployee', 'admin_settings_title'));

        $form = new Form('form');
        $this->addForm($form);

        $Type = new Selectbox('employer_account_type');
        $accounts = $this->getAccountTypes();
        foreach ($accounts as $key => $account) {
            $Type->addOption($key, $account);
        }
        $Type->setRequired(true);
        $Type->setValue(OW::getConfig()->getValue('frmemployee', 'employer_account_type'));
        $Type->setLabel(OW::getLanguage()->text('frmemployee', 'employer_account_type'));
        $form->addElement($Type);

        $Type = new Selectbox('employee_account_type');
        $accounts = $this->getAccountTypes();
        foreach ($accounts as $key => $account) {
            $Type->addOption($key, $account);
        }
        $Type->setRequired(true);
        $Type->setValue(OW::getConfig()->getValue('frmemployee', 'employee_account_type'));
        $Type->setLabel(OW::getLanguage()->text('frmemployee', 'employee_account_type'));
        $form->addElement($Type);

        $q = new Selectbox('employer_question');
        $accounts = BOL_QuestionService::getInstance()->getQuestions(['text']);
        $q->addOptions($accounts);
        $q->setRequired(true);
        $q->setValue(OW::getConfig()->getValue('frmemployee', 'employer_question'));
        $q->setLabel(OW::getLanguage()->text('frmemployee', 'employer_question'));
        $form->addElement($q);

        $q = new Selectbox('employee_question');
        $accounts = BOL_QuestionService::getInstance()->getQuestions(['fselect']);
        $q->addOptions($accounts);
        $q->setRequired(true);
        $q->setValue(OW::getConfig()->getValue('frmemployee', 'employee_question'));
        $q->setLabel(OW::getLanguage()->text('frmemployee', 'employee_question'));
        $form->addElement($q);

        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('frmemployee', 'form_submit'));
        $form->addElement($submit);

        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                OW::getConfig()->saveConfig('frmemployee', 'employer_account_type', $_POST['employer_account_type']);
                OW::getConfig()->saveConfig('frmemployee', 'employee_account_type', $_POST['employee_account_type']);
                OW::getConfig()->saveConfig('frmemployee', 'employer_question', $_POST['employer_question']);
                OW::getConfig()->saveConfig('frmemployee', 'employee_question', $_POST['employee_question']);

                OW::getFeedback()->info(OW::getLanguage()->text('frmemployee', 'saved_successfully'));
            }
        }
    }

    protected function getAccountTypes()
    {
        // get available account types from DB
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

        $accounts = array();

        /* @var $value BOL_QuestionAccountType */
        foreach ( $accountTypes as $key => $value )
        {
            $accounts[$value->name] = OW::getLanguage()->text('base', 'questions_account_type_' . $value->name);
        }

        return $accounts;
    }
}
