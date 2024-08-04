<?php
class FRMAJAXLOADER_MCLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function genericInit()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));
    }

    public function onBeforeDocumentRender( OW_Event $event )
    {
        $attr = OW::getRequestHandler()->getHandlerAttributes();
        if(empty($GLOBALS['has_newsfeed_list'])){
            return;
        }
        if($attr[OW_RequestHandler::ATTRS_KEY_CTRL]=="NEWSFEED_MCTRL_Feed" && $attr[OW_RequestHandler::ATTRS_KEY_ACTION]=="viewItem")
        {
            return;
        }
        OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('frmajaxloader')->getStaticCssUrl() . 'frmajaxloader.css' );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmajaxloader')->getStaticJsUrl() . 'frmajaxloader.js' );

        $user = null;
        if(strpos($_SERVER['REQUEST_URI'],'/user/')!==false){
            $username = substr($_SERVER['REQUEST_URI'],strpos($_SERVER['REQUEST_URI'],'/user/')+6);
            if(strpos($username,'/')!==false){
                $username = substr($username,0,strpos($username,'/'));
            }
            $user = BOL_UserService::getInstance()->findByUsername($username);
        }
        if($user) {
            $btn_label = OW::getLanguage()->text("frmajaxloader","new_posts");
            $load_url = OW::getRouter()->urlForRoute('frmajaxloader.userfeed.newly', array('userId' => $user->getId(), 'lastTS' => ''));
            $js = "ajax_loadNewly('$load_url', " . time() . ", '#content > #feed1 .owm_newsfeed_list', 'userfeed', '$btn_label');";
        }else{
            $btn_label = OW::getLanguage()->text("frmajaxloader","new_activities");

            $load_url = OW::getRouter()->urlForRoute('frmajaxloader.myfeed.newly',array('lastTS'=>''));
            $js = "ajax_loadNewly('$load_url', ".time().", '.mobile\\\\.dashboard-NEWSFEED_MCMP_MyFeedWidget #feed1 .owm_newsfeed_list', 'myfeed', '$btn_label');";

            $load_url = OW::getRouter()->urlForRoute('frmajaxloader.sitefeed.newly',array('lastTS'=>''));
            $js.= "ajax_loadNewly('$load_url', ".time().", '#content > #feed1 .owm_newsfeed_list', 'sitefeed', '$btn_label');";

            $groupId = FRMAJAXLOADER_BOL_Service::getInstance()->findIdFromUrl('/groups/');
            if(!is_null($groupId)){
                $load_url = OW::getRouter()->urlForRoute('frmajaxloader.groupsfeed.newly',array('groupId' => $groupId, 'lastTS'=>''));
                $js.= "ajax_loadNewly('$load_url', ".time().", '#content #feed1 .owm_newsfeed_list', 'groupsfeed', '$btn_label');";
            }
        }

        OW::getDocument()->addOnloadScript($js);
    }

}
