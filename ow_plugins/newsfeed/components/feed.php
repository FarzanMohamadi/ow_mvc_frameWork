<?php
/**
 * Feed component
 *
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
class NEWSFEED_CMP_Feed extends OW_Component
{
    private static $feedCounter = 0;

    /**
     *
     * @var NEWSFEED_CLASS_Driver
     */
    protected $driver;

    protected $data = array();
    protected $displayType = 'action';
    protected $autoId;
    protected $focused = false;

    protected $actionList = null;
    
    /**
     *
     * @var NEWSFEED_CMP_UpdateStatus
     */
    protected $statusCmp;

    const DISPLAY_TYPE_ACTION = 'action';
    const DISPLAY_TYPE_ACTIVITY = 'activity';
    const DISPLAY_TYPE_PAGE = 'page';

    public function __construct( NEWSFEED_CLASS_Driver $driver, $feedType, $feedId )
    {
        parent::__construct();
        self::$feedCounter++;

        $this->autoId = 'feed' . self::$feedCounter;
        $this->driver = $driver;

        $this->data['feedType'] = $feedType;
        $this->data['feedId'] = $feedId;
        $this->data['feedAutoId'] = $this->autoId;
        $this->data['startTime'] = time();
        $this->data['displayType'] = $this->displayType;

        OW::getEventManager()->trigger(new OW_Event('newsfeed.feed.render', array('feedType' => $feedType, 'feedId' => $feedId)));
    }

    public function addAction( NEWSFEED_CLASS_Action $action )
    {
        if ( $this->actionList === null )
        {
            $this->actionList = array();
        }

        $this->actionList[$action->getId()] = $action;
    }

    public function focusOnInput( $focused = true )
    {
        $this->focused = $focused;
    }
    
    public function setDisplayType( $type )
    {
        $this->displayType = $type;
    }

    public function addStatusForm( $type, $id, $visibility = null )
    {
        $event = new OW_Event('feed.get_status_update_cmp', array(
            'entityType' => $type,
            'entityId' => $id,
            'feedAutoId' => $this->autoId,
            'visibility' => $visibility
        ));
        
        OW::getEventManager()->trigger($event);
        
        $status = $event->getData();

        if ( $status === null )
        {
            $cmp = $this->createNativeStatusForm($this->autoId, $type, $id, $visibility);
        }
        else
        {
            $cmp = $status;
        }
        
        
        
        if ( !empty($cmp) )
        {
            $this->statusCmp = $cmp;
        }
    }
    
    /**
     * 
     * @param string $autoId
     * @param string $type
     * @param int $id
     * @param int $visibility
     * @return NEWSFEED_CMP_UpdateStatus
     */
    protected function createNativeStatusForm($autoId, $type, $id, $visibility)
    {
        return OW::getClassInstance("NEWSFEED_CMP_UpdateStatus", $autoId, $type, $id, $visibility);
    }
    
    public function addStatusMessage( $message )
    {
        $this->assign('statusMessage', $message);
    }

    public function setup( $data )
    {
        $this->data = array_merge($this->data, $data);
        $driverOptions = $this->data;

        $driverOptions['offset'] = 0;
        $this->data['otpForm']=false;
        $otpEvent=OW_EventManager::getInstance()->trigger(new OW_Event('newsfeed.check.chat.form',['feedType'=> $this->data['feedType']]));
        if( isset($otpEvent->getData()['showOtpForm']) && $otpEvent->getData()['showOtpForm']){
            $this->data['otpForm']=true;
        }

        $this->driver->setup($driverOptions);
    }

    protected function initJsConstants( $rsp = 'NEWSFEED_CTRL_Ajax' )
    {
        $codeId = rand(1,10000);
        $likeCode='';
        $unlikeCode='';
        $removeCode='';
        $usersCode='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$codeId,'isPermanent'=>true,'activityType'=>'like_feed')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $likeCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$codeId,'isPermanent'=>true,'activityType'=>'unlike_feed')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $unlikeCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$codeId,'isPermanent'=>true,'activityType'=>'users_feed')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $usersCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$codeId,'isPermanent'=>true,'activityType'=>'remove_feed')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $removeCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        $js = UTIL_JsGenerator::composeJsString('
            window.ow_newsfeed_const.LIKE_RSP = {$like};
            window.ow_newsfeed_const.UNLIKE_RSP = {$unlike};
            window.ow_newsfeed_const.USERS_RSP = {$users};
            window.ow_newsfeed_const.DELETE_RSP = {$delete};
            window.ow_newsfeed_const.LOAD_ITEM_RSP = {$loadItem};
            window.ow_newsfeed_const.LOAD_ITEM_LIST_RSP = {$loadItemList};
            window.ow_newsfeed_const.REMOVE_ATTACHMENT = {$removeAttachment};
        ', array(
            'like' => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor("NEWSFEED_CTRL_Ajax", 'action'),array()),
            'unlike' => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor("NEWSFEED_CTRL_Ajax", 'action'),array()),
            'users' => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor($rsp, 'users'),array('code'=>$usersCode)),
            'delete' => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor($rsp, 'remove'),array('code'=>$removeCode)),
            'loadItem' => OW::getRouter()->urlFor($rsp, 'loadItem'),
            'loadItemList' => OW::getRouter()->urlFor($rsp, 'loadItemList'),
            'removeAttachment' => OW::getRouter()->urlFor($rsp, 'removeAttachment')
        ));

        OW::getDocument()->addOnloadScript($js, 50);
    }
    
    protected function initializeJs( $jsConstructor = "NEWSFEED_Feed", $ajaxRsp = 'NEWSFEED_CTRL_Ajax', $scriptFile = null )
    {
        if ( $scriptFile === null )
        {
            OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('newsfeed')->getStaticJsUrl() . 'newsfeed.js' );
        }
        else
        {
            OW::getDocument()->addScript($scriptFile);
        }
        
        $this->initJsConstants($ajaxRsp);

        $total = $this->getActionsCount();
        
        $js = UTIL_JsGenerator::composeJsString('
            window.ow_newsfeed_feed_list[{$autoId}] = new ' . $jsConstructor . '({$autoId}, {$data});
            window.ow_newsfeed_feed_list[{$autoId}].totalItems = {$total};
        ', array(
            'total' => $total,
            'autoId' => $this->autoId,
            'data' => array( 'data' => $this->data, 'driver' => $this->driver->getState() )
        ));

        OW::getDocument()->addOnloadScript($js, 50);
    }

    protected function getActionsList()
    {
        if ( $this->actionList === null )
        {
            $this->actionList = $this->driver->getActionList();
        }

        return $this->actionList;
    }

    protected function getActionsCount()
    {
        return $this->driver->getActionCount();
    }

    /**
     * 
     * @param array $actionList
     * @param array $data
     * @return NEWSFEED_CMP_FeedList
     */
    protected function createFeedList( $actionList, $data )
    {
        return OW::getClassInstance("NEWSFEED_CMP_FeedList", $actionList, $data);
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        if ( $this->statusCmp !== null )
        {
            if ( method_exists($this->statusCmp, "focusOnInput") )
            {
                $this->statusCmp->focusOnInput($this->focused);
            }
            
            $this->addComponent('status', $this->statusCmp);
        }
    }
    
    public function render()
    {
        $this->data['displayType'] = $this->displayType;
        
        $this->actionList = $this->getActionsList();
        $this->initializeJs();

        $list = $this->createFeedList($this->actionList, $this->data);
        $list->setDisplayType($this->displayType);

        $this->assign('list', $list->render());
        $this->assign('autoId', $this->autoId);
        $this->assign('data', $this->data);

        if ( $this->displayType == self::DISPLAY_TYPE_PAGE || !$this->data['viewMore'] )
        {
            $viewMore = 0;
        }
        else
        {
            $viewMore = $this->getActionsCount() - $this->data['displayCount'];
            $viewMore = $viewMore < 0 ? 0 : $viewMore;
        }
        
        $this->assign('viewMore', $viewMore);

        return parent::render();
    }
}