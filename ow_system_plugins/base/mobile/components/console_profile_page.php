<?php
/**
 * Mobile console profile page
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ConsoleProfilePage extends OW_MobileComponent
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ( !OW::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $userId = OW::getUser()->getId();
        $userService = BOL_UserService::getInstance();

        $this->assign('username', $userService->getDisplayName($userId));
        $this->assign('url', $userService->getUserUrl($userId));
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $this->assign('avatarUrl', $avatars[$userId]['src']);


        $code='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'isPermanent'=>true,'activityType'=>'logout')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $code = $frmSecuritymanagerEvent->getData()['code'];
        }
        $signoutUrl=OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_sign_out'), array('code' => $code));


        $script = '$(".owm_sidebar_profile_logout").click(function(){
            document.location.href = '.$signoutUrl.';
        });';
        OW::getDocument()->addOnloadScript($script);
        $this->assign('signoutUrl',$signoutUrl);
        $event = new BASE_CLASS_EventCollector('frm.on.mobile.add.item');

        $resultsEvent =  OW::getEventManager()->trigger($event);
        //$resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_MOBILE_ADD_ITEM, array('user'=>OW::getUser())));
        if(sizeof($resultsEvent->getData())>0) {
            $this->assign('isParent',true);
            $addItems = $resultsEvent->getData();
            $parentalLinks = array();
            foreach ( $addItems as $addItem )
            {
                if ( !empty($addItem['label']) && !empty($addItem['url']) )
                {
                    $parentalLinks[]=array(
                        'label' => $addItem['label'],
                        'url' => $addItem['url']);
                }
            }
            $this->assign('parentalLinks',$parentalLinks);
        }
    }
}