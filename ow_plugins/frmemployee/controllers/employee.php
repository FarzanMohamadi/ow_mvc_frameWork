<?php
class FRMEMPLOYEE_CTRL_Employee extends OW_ActionController
{
    /**
     * FRMEMPLOYEE_CTRL_Employee constructor.
     * @throws Redirect404Exception if the user has no access to view the FRMEmployee pages
     */
    public function __construct()
    {
        if ( ! FRMEMPLOYEE_BOL_Service::getInstance()->isUserACompany()){
            throw new Redirect404Exception();
        }
        $this->setDocumentKey('frmemployee');
    }

    public function employees()
    {
        $q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
        $uId = OW::getUser()->getId();
        $db = OW_DB_PREFIX;

        $q = "SELECT userId,dateValue FROM {$db}base_question_data
                WHERE questionName='{$q_ee}' AND intValue={$uId};";
        $users = OW::getDbo()->queryForList($q);

        foreach($users as $k=>$v){
            $username = BOL_UserService::getInstance()->findUserById($v['userId'])->getUsername();
            $users[$k]['name']=BOL_UserService::getInstance()->getDisplayName($v['userId']);
            $users[$k]['href']=OW::getRouter()->urlForRoute('base_user_profile', array('username' => $username));
            $users[$k]['action']=OW::getRouter()->urlForRoute('frmemployee.toggle.state', array('id' => $v['userId']));
        }

        $this->assign('users', $users);
    }

    public function toggleState($params)
    {
        $ee_id = intval($params['id']);
        $er_id = OW::getUser()->getId();
        $q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
        $db = OW_DB_PREFIX;

        $q = "SELECT dateValue FROM {$db}base_question_data
                WHERE questionName='{$q_ee}' AND intValue={$er_id} AND userId={$ee_id};";
        $c_value = OW::getDbo()->queryForColumnList($q);
        if(!empty($c_value)){
            $date = (empty($c_value[0]))?'CURRENT_TIME()':'NULL';
            $q = "UPDATE {$db}base_question_data
                SET dateValue={$date}
                WHERE questionName='{$q_ee}' AND intValue={$er_id} AND userId={$ee_id};";
            OW::getDbo()->query($q);

            OW::getFeedback()->info(OW::getLanguage()->text('frmemployee', 'saved_successfully'));
        }
        $this->redirect(OW::getRouter()->urlForRoute('frmemployee.employees'));
    }
}
