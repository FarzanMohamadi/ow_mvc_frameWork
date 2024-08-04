<?php
/**
 * frmajaxloader
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmajaxloader
 * @since 1.0
 */

class FRMAJAXLOADER_CLASS_EventHandler
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
        OW::getEventManager()->bind('base.on_socket_message_received', array($this, 'checkReceivedMessage'));

    }

    public function onBeforeDocumentRender( OW_Event $event )
    {
        $attr = OW::getRequestHandler()->getHandlerAttributes();
        if(empty($GLOBALS['has_newsfeed_list'])){
            return;
        }
        if($attr[OW_RequestHandler::ATTRS_KEY_CTRL]=="NEWSFEED_CTRL_Feed" && $attr[OW_RequestHandler::ATTRS_KEY_ACTION]=="viewItem")
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
        if(isset($user)) {
            $btn_label = OW::getLanguage()->text("frmajaxloader","new_posts");
            $load_url = OW::getRouter()->urlForRoute('frmajaxloader.userfeed.newly', array('userId' => $user->getId(), 'lastTS' => ''));
            $js = "ajax_loadNewly('$load_url', " . time() . ", '.profile-NEWSFEED_CMP_UserFeedWidget #feed1 .ow_newsfeed', 'userfeed', '$btn_label', " . $user->getId() . ");";
        }else{
            $btn_label = OW::getLanguage()->text("frmajaxloader","new_activities");

            $load_url = OW::getRouter()->urlForRoute('frmajaxloader.myfeed.newly',array('lastTS'=>''));
            $js = "ajax_loadNewly('$load_url', ".time().", '.dashboard-NEWSFEED_CMP_MyFeedWidget #feed1 .ow_newsfeed', 'myfeed', '$btn_label');";

            $load_url = OW::getRouter()->urlForRoute('frmajaxloader.sitefeed.newly',array('lastTS'=>''));
            $js.= "ajax_loadNewly('$load_url', ".time().", '.index-NEWSFEED_CMP_SiteFeedWidget #feed1 .ow_newsfeed', 'sitefeed', '$btn_label');";

            $groupId = FRMAJAXLOADER_BOL_Service::getInstance()->findIdFromUrl('/groups/');
            if(!is_null($groupId)){
                $load_url = OW::getRouter()->urlForRoute('frmajaxloader.groupsfeed.newly',array('groupId' => $groupId, 'lastTS'=>''));
                $js.= "ajax_loadNewly('$load_url', ".time().", '.group-NEWSFEED_CMP_EntityFeedWidget #feed1 .ow_newsfeed', 'groupsfeed', '$btn_label');";
            }
        }

        OW::getDocument()->addOnloadScript($js);
    }

    public function checkReceivedMessage(OW_Event $event)
    {
        $params = $event->getParams();
        $paramsData = $params['data'];
        if (!isset($paramsData['type'])) {
            return;
        }

        if ($paramsData['type'] == "feedLoader") {
            if (!isset($paramsData['selectorPostfix'])) {
                return;
            }
            $selectorPostfix = $paramsData['selectorPostfix'];
            $paramsData['numberMode'] = true;
            try {
                $function = "get_{$selectorPostfix}_newly";
                $count = FRMAJAXLOADER_BOL_Service::getInstance()->$function($paramsData);
                if($count > 0){
                    $requestData = array('lastTS'=>'');
                    if($selectorPostfix == 'userfeed'){
                        $requestData['userId'] = $paramsData['userId'];
                    }
                    $url = OW::getRouter()->urlForRoute("frmajaxloader.{$selectorPostfix}.newly", $requestData);
                    $data = array(
                        'type' => 'feedLoader',
                        'selectorPostfix' => $selectorPostfix,
                        'url' => $url);
                    $event->setData(json_encode($data));
                }else{
                    return;
                }
            } catch (RedirectException $ex) {
                return;
            }
        }
    }

}
