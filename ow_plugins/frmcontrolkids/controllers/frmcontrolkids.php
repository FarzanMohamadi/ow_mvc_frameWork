<?php
class FRMCONTROLKIDS_CTRL_Iiscontrolkids extends OW_ActionController
{

    public function index($params)
    {
        if(!OW::getUser()->isAuthenticated()){
            OW::getApplication()->redirect(OW_URL_HOME);
        }
        $service = FRMCONTROLKIDS_BOL_Service::getInstance();
        $kids = $service->getKids(OW::getUser()->getId());
        $items = array();
        foreach ($kids as $kid) {
            $user = BOL_UserService::getInstance()->findUserById($kid->kidUserId);
            $items[] = array(
                'username' => $user->username,
                'email' => $user->email,
                'shadowLoginUrl' => OW::getRouter()->urlForRoute('frmcontrolkids.shadow_login_by_parent',array('kidUserId' => $user->getId()))
            );
        }
        $this->assign("items", $items);
    }

    public function shadowLoginByParent($params){
        if(!OW::getUser()->isAuthenticated()){
            OW::getApplication()->redirect(OW_URL_HOME);
        }
        $kid_user_id = $params['kidUserId'];
        $service = FRMCONTROLKIDS_BOL_Service::getInstance();
        if($service->isParentExist($kid_user_id, OW::getUser()->getId())){
            $parentId = OW::getUser()->getId();
            $service->logout();
            OW_User::getInstance()->login($kid_user_id);
            OW_Session::getInstance()->set('sl_'.$kid_user_id, $parentId);
        }
        OW::getApplication()->redirect(OW_URL_HOME);
    }

    public function logoutFromShadowLogin(){
        $user = OW::getUser();
        $service = FRMCONTROLKIDS_BOL_Service::getInstance();
        if(OW_Session::getInstance()->get('sl_'.$user->getId())){
            $parentId = OW_Session::getInstance()->get('sl_'.$user->getId());
            OW_Session::getInstance()->delete('sl_'.$user->getId());
            $service->logout();
            OW_User::getInstance()->login($parentId);
            OW::getApplication()->redirect(OW_URL_HOME);
        }
    }
    public function enterParentEmail(){
        if(!OW::getUser()->isAuthenticated()){
            throw new Redirect404Exception();
        }
        $userId = OW::getUser()->getId();
        if(FRMCONTROLKIDS_BOL_Service::getInstance()->getParentInfo($userId) !== null){
            throw new Redirect404Exception();
        }
        $this->setPageTitle(OW::getLanguage()->text('frmcontrolkids', 'enter_parent_email_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmcontrolkids', 'enter_parent_email_heading'));

        $form = new Form('add_parent_email');
        $this->addForm($form);

        $fieldParentEmail = new TextField("parentEmail");
        $fieldParentEmail->addValidator(new EmailValidator());
        $fieldParentEmail->addValidator(new RequiredParentEmailValidator());
        $fieldParentEmail->setRequired();
        $fieldParentEmail->setLabel(OW_Language::getInstance()->text('frmcontrolkids', "join_parent_email_header"));
        $form->addElement($fieldParentEmail);

        $submit = new Submit('add');
        $submit->setValue(OW_Language::getInstance()->text('frmcontrolkids', 'form_add_parent_email_submit'));
        $form->addElement($submit);
        $this->assign('kidsAge',OW::getConfig()->getValue('frmcontrolkids', 'kidsAge'));
        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $parentEmail = $data['parentEmail'];
                FRMCONTROLKIDS_BOL_Service::getInstance()->addRelationship($userId,$parentEmail);
                $this->redirect(OW_URL_HOME);

            }
        }
    }
}