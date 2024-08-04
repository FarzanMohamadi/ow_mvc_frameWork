<?php
/**
 * Restricted Usernames
 *
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_RestrictedUsernames extends ADMIN_CTRL_Abstract
{
    private $userService;
    private $ajaxResponderUrl;

    public function __construct()
    {
        $this->userService = BOL_UserService::getInstance();

        $this->ajaxResponderUrl = OW::getRouter()->urlFor("ADMIN_CTRL_RestrictedUsernames", "ajaxResponder");

        parent::__construct();
    }

    public function index( $params = array() )
    {
        $userService = BOL_UserService::getInstance();

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('admin', 'restrictedusernames'));

        $this->setPageHeadingIconClass('ow_ic_script');

        $restrictedUsernamesForm = new Form('restrictedUsernamesForm');
        $restrictedUsernamesForm->setId('restrictedUsernamesForm');

        $username = new TextField('restrictedUsername');
        $username->addAttribute('class', 'ow_text');
        $username->addAttribute('style', 'width: auto;');
        $username->setRequired();
        $username->setLabel($language->text('admin', 'restrictedusernames_username_label'));

        $restrictedUsernamesForm->addElement($username);

        $submit = new Submit('addUsername');
        $submit->addAttribute('class', 'ow_button');
        $submit->setValue($language->text('admin', 'restrictedusernames_add_username_button'));

        $restrictedUsernamesForm->addElement($submit);

        $this->addForm($restrictedUsernamesForm);

        $userNames = $this->userService->getRestrictedUsernameList();
        $codes = array();
        foreach ($userNames as $restUserName){
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$restUserName->id,'isPermanent'=>true,'activityType'=>'delete_restrictedUserName')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
            }
            $codes[$restUserName->id]=$code;
        }
        $this->assign('codes',$codes);
        $this->assign('restricted_list', $this->userService->getRestrictedUsernameList());

        if ( OW::getRequest()->isPost() )
        {
            if ( $restrictedUsernamesForm->isValid($_POST) )
            {
                $data = $restrictedUsernamesForm->getValues();

                $username = $this->userService->getRestrictedUsername($data['restrictedUsername']);

                if ( empty($username) )
                {
                    $username = new BOL_RestrictedUsernames();

                    $username->setRestrictedUsername($data['restrictedUsername']);

                    $this->userService->addRestrictedUsername($username);

                    OW::getFeedback()->info($language->text('admin', 'restrictedusernames_username_added'));
                    $this->redirect();
                }
                else
                {
                    OW::getFeedback()->warning($language->text('admin', 'restrictedusernames_username_already_exists'));
                }
            }
        }
    }

    public function delete()
    {

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_restrictedUserName')));
        }

        $restrictedUsernamesService = BOL_RestrictedUsernamesDao::getInstance();
        $restrictedUsernamesService->deleteRestrictedUsername($_GET['username']);

        $language = OW::getLanguage();
        OW::getFeedback()->info($language->text('admin', 'restrictedusernames_username_deleted'));

        $this->redirect(OW::getRouter()->urlForRoute('admin_restrictedusernames'));
    }
}
