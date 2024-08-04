<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.privacy.controllers
 * @since 1.0
 */
class PRIVACY_MCTRL_Privacy extends OW_MobileActionController
{
    private $actionService;
    private $userService;

    public function __construct()
    {
        parent::__construct();

        $this->actionService = PRIVACY_BOL_ActionService::getInstance();
        $this->userService = BOL_UserService::getInstance();
    }

    public function index( $params )
    {
        $userId = OW::getUser()->getId();

        if ( OW::getRequest()->isAjax() )
        {
            exit;
        }

        if ( !OW::getUser()->isAuthenticated() || $userId === null )
        {
            throw new AuthenticateException();
        }

        if(!FRMSecurityProvider::checkPluginActive('frmprofilemanagement', true)) {
            throw new Redirect404Exception();
        }

        if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=null)
        {
            $this->assign('backUrl',$_SERVER['HTTP_REFERER']);
        }

        $contentMenu = new FRMPROFILEMANAGEMENT_MCMP_PreferenceContentMenu();
        $contentMenu->setItemActive('privacy');

        $this->addComponent('contentMenu', $contentMenu);

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('privacy', 'privacy_index'));
        $this->setPageHeadingIconClass('ow_ic_lock');

        // -- Action form --

        $privacyForm = new Form('privacyForm');
        $privacyForm->setId('privacyForm');

        $actionSubmit = new Submit('privacySubmit');
        $actionSubmit->addAttribute('class', 'ow_button ow_ic_save');

        $actionSubmit->setValue($language->text('privacy', 'privacy_submit_button'));

        $privacyForm->addElement($actionSubmit);

        // --

        $actionList = PRIVACY_BOL_ActionService::getInstance()->findAllAction();

        $actionNameList = array();
        foreach( $actionList as $action )
        {
            $actionNameList[$action->key] = $action->key;
        }

        $actionValueList = PRIVACY_BOL_ActionService::getInstance()->getActionValueList($actionNameList, $userId);

        $actionValuesEvent= new BASE_CLASS_EventCollector( PRIVACY_BOL_ActionService::EVENT_GET_PRIVACY_LIST );
        OW::getEventManager()->trigger($actionValuesEvent);
        $data = $actionValuesEvent->getData();

        $actionValuesInfo = empty($data) ? array() : $data;
        usort($actionValuesInfo, array($this, "sortPrivacyOptions"));

        $optionsList = array();
        // -- sort action values
        foreach( $actionValuesInfo as $value )
        {
            $optionsList[$value['key']] = $value['label'];
        }
        // --

        $resultList = array();

        foreach( $actionList as $action )
        {

            /* @var $action PRIVACY_CLASS_Action */
            if ( !empty( $action->label ) )
            {
                $formElement = new Selectbox($action->key);
                $formElement->setLabel($action->label);

                $formElement->setDescription('');

                if ( !empty($action->description) )
                {
                    $formElement->setDescription($action->description);
                }

                $formElement->setOptions($optionsList);
                $formElement->setHasInvitation(false);

                if ( !empty($actionValueList[$action->key]) )
                {
                    $formElement->setValue($actionValueList[$action->key]);

                    if( array_key_exists($actionValueList[$action->key], $optionsList) )
                    {
                        $formElement->setValue($actionValueList[$action->key]);
                    }
                    else if ( $actionValueList[$action->key] != 'everybody' )
                    {
                        $formElement->setValue('only_for_me');
                    }
                }

                $privacyForm->addElement($formElement);

                $resultList[$action->key] = $action->key;
            }
        }

        if ( OW::getRequest()->isPost() )
        {
            if( $privacyForm->isValid($_POST) )
            {
                $values = $privacyForm->getValues();
                $restul = PRIVACY_BOL_ActionService::getInstance()->saveActionValues($values, $userId);

                if ( $restul )
                {
                    OW::getFeedback()->info($language->text('privacy', 'action_action_data_was_saved'));
                }
                else
                {
                    OW::getFeedback()->warning($language->text('privacy', 'action_action_data_not_changed'));
                }

                $this->redirect();
            }
        }


        $this->addForm($privacyForm);
        $this->assign('actionList', $resultList);
        OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.render.user.privacy',
            array('controller' => $this,'userId'=>$userId)));

        OW::getEventManager()->trigger(new OW_Event('frm.on.before.profile.pages.view.render', array('pageType' => "preferences")));
    }

    private function sortPrivacyOptions( $a, $b )
    {
        if ( $a["sortOrder"] == $b["sortOrder"]  )
        {
            return 0;
        }

        return $a["sortOrder"] < $b["sortOrder"] ? -1 : 1;
    }
    public function noPermission( $params )
    {
        $username = $params['username'];

        $user = BOL_UserService::getInstance()->findByUsername($username);

        if ( $user === null )
        {
            throw new Redirect404Exception();
        }

        $this->setPageHeading(OW::getLanguage()->text('privacy', 'privacy_no_permission_heading'));
        $this->setPageHeadingIconClass('ow_ic_lock');

        if( OW::getSession()->isKeySet('privacyRedirectExceptionMessage') )
        {
            $this->assign('message', OW::getSession()->get('privacyRedirectExceptionMessage'));
        }

        $avatarService = BOL_AvatarService::getInstance();

        $viewerId = OW::getUser()->getId();

        $userId = $user->id;

        $this->assign('owner', false);

        $avatar = $avatarService->getAvatarUrl($userId, 2);
        $this->assign('avatar', $avatar);
        $roles = BOL_AuthorizationService::getInstance()->getLastDisplayLabelRoleOfIdList(array($userId));
        $this->assign('role', !empty($roles[$userId]) ? $roles[$userId] : null);

        $userService = BOL_UserService::getInstance();

        $this->assign('username', $username);

        $this->assign('avatarSize', OW::getConfig()->getValue('base', 'avatar_big_size'));
    }
}