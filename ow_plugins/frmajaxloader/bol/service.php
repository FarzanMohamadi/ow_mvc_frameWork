<?php
class FRMAJAXLOADER_BOL_Service
{
    /**
     * Class instance
     *
     * @var FRMAJAXLOADER_BOL_Service
     */
    private static $classInstance;

    /**
     * @return FRMAJAXLOADER_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    /***
     * @param $params
     * @return false|string
     */
    public function get_myfeed_newly($params){
        if (!OW::getUser()->isAuthenticated()) {
            return '';
        }
        $userId = OW::getUser()->getId();
        $lastTS = intval($params['lastTS']);
        $curTS = time();
        $count = 100;
        $numberMode = false;
        if(isset($params['numberMode'])){
            $numberMode = $params['numberMode'];
        }

        $driverParams = array(
            'offset' => 0,
            'length' => 11,
            'displayCount' => $count,
            'formats' => null,
            'feedType' => 'my',
            'feedId' => $userId,
            'feedAutoId'=>'feed1',
            'startTime' => $curTS,
            'endTime' => $lastTS,
            'displayType' => 'action',
            'customizeMode' => false,
            'viewMore' => true,
            'checkMore' => true
        );
        $data = array(
            'feedType' => 'my',
            'feedId' => $userId,
            'feedAutoId'=>'feed1',
            'startTime' => $curTS,
            'endTime' => $lastTS,
            'displayType' => 'activity',
            'customizeMode' => false,
            'viewMore' => true,
            'displayCount' => $count,
        );
        $params = array (
            'data' => $data,
            'driver' => array('class' => 'NEWSFEED_CLASS_UserDriver', 'params' => $driverParams));

        return $this->load_newly_general($lastTS, $params, $numberMode);
    }

    /***
     * @param $params
     * @return false|string
     */
    public function get_sitefeed_newly($params)
    {
        $lastTS = intval($params['lastTS']);
        $curTS = time();
        $count = 100;
        $numberMode = false;
        if(isset($params['numberMode'])){
            $numberMode = $params['numberMode'];
        }

        $driverParams = array(
            'offset' => 0,
            'length' => 11,
            'displayCount' => $count,
            'formats' => null,
            'feedType' => 'site',
            'feedId' => null,
            'feedAutoId'=>'feed1',
            'startTime' => $curTS,
            'endTime' => $lastTS,
            'displayType' => 'action',
            'customizeMode' => false,
            'viewMore' => true,
            'checkMore' => true
        );
        $data = array(
            'feedType' => 'site',
            'feedId' => null,
            'feedAutoId'=>'feed1',
            'startTime' => $curTS,
            'endTime' => $lastTS,
            'displayType' => 'activity',
            'customizeMode' => false,
            'viewMore' => true,
            'displayCount' => $count,
        );
        $params = array (
            'data' => $data,
            'driver' => array('class' => 'NEWSFEED_CLASS_SiteDriver', 'params' => $driverParams));

        return $this->load_newly_general($lastTS, $params, $numberMode);
    }

    /***
     * @param $params
     * @return false|string
     * @throws RedirectException
     */
    public function get_userfeed_newly($params)
    {
        if(empty($params['userId']) || empty($params['lastTS'])){
            return '';
        }
        $lastTS = intval($params['lastTS']);
        $userId = intval($params['userId']);
        $numberMode = false;
        if(isset($params['numberMode'])){
            $numberMode = $params['numberMode'];
        }
        $curTS = time();
        $count = 100;

        $user = BOL_UserService::getInstance()->findUserById($userId);

        //check if privacy allows
        $eventParams = array(
            'action' => 'base_view_profile',
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $ex )
        {
            $exception = new RedirectException(OW::getRouter()->urlForRoute('base_user_privacy_no_permission', array('username' => $user->username)));
            throw $exception;
        }

        //privacy is ok
        $driverParams = array(
            'offset' => 0,
            'length' => 11,
            'displayCount' => $count,
            'formats' => null,
            'feedType' => 'user',
            'feedId' => $userId,
            'feedAutoId'=>'feed1',
            'startTime' => $curTS,
            'endTime' => $lastTS,
            'displayType' => 'action',
            'customizeMode' => false,
            'viewMore' => true,
            'checkMore' => true
        );
        $data = array(
            'feedType' => 'user',
            'feedId' => $userId,
            'feedAutoId'=>'feed1',
            'startTime' => $curTS,
            'endTime' => $lastTS,
            'displayType' => 'activity',
            'customizeMode' => false,
            'viewMore' => true,
            'displayCount' => $count,
        );
        $params = array (
            'data' => $data,
            'driver' => array('class' => 'NEWSFEED_CLASS_FeedDriver', 'params' => $driverParams));

        return $this->load_newly_general($lastTS, $params, $numberMode);
    }

    public function get_groupsfeed_newly($params)
    {
        if(empty($params['groupId']) || empty($params['lastTS'])){
            return '';
        }

        $lastTS = intval($params['lastTS']);
        $groupId = intval($params['groupId']);

        $curTS = time();
        $count = 100;

        $numberMode = false;
        if(isset($params['numberMode'])){
            $numberMode = $params['numberMode'];
        }

        $driverParams = array(
            'offset' => 0,
            'length' => 11,
            'displayCount' => $count,
            'formats' => null,
            'feedType' => 'groups',
            'feedId' => $groupId,
            'feedAutoId'=>'feed1',
            'startTime' => $curTS,
            'endTime' => $lastTS,
            'displayType' => 'action',
            'customizeMode' => false,
            'viewMore' => true,
            'checkMore' => true
        );

        $data = array(
            'feedType' => 'groups',
            'feedId' => $groupId,
            'feedAutoId'=>'feed1',
            'startTime' => $curTS,
            'endTime' => $lastTS,
            'displayType' => 'activity',
            'customizeMode' => false,
            'viewMore' => true,
            'displayCount' => $count,
        );

        $params = array (
            'data' => $data,
            'driver' => array('class' => 'NEWSFEED_CLASS_FeedDriver', 'params' => $driverParams));

        return $this->load_newly_general($lastTS, $params, $numberMode);
    }

    /***
     * @param $lastTS
     * @param $params
     * @param $numberMode boolean determine the output's type
     * @return false|int|string
     */
    private function load_newly_general($lastTS, $params, $numberMode){

        $event = new OW_Event('feed.on_ajax_load_list', $params);
        OW::getEventManager()->trigger($event);

        try {
            $driverClass = $params['driver']['class'];
            $driver = OW::getClassInstance($driverClass);
        }catch (Exception $ex){
            return json_encode(array('status'=>'error','error_msg'=>$ex->getMessage()));
        }

        $driverParams = $params['driver']['params'];
        $driver->setup($driverParams);

        $actionListAll = $driver->getActionList();

        $actionListIds = array();
        $actionList = array();
        $maxTS = $lastTS;
        foreach($actionListAll as $key=>$actionItem){
            if(OW::getUser()->isAuthenticated()) {
                $userId = OW::getUser()->getId();
                $anything_changed = false;
                $activities = $actionItem->getActivityList();
                foreach($activities as $activityItem){
                    if( ($activityItem->userId != $userId) && ($activityItem->timeStamp > $lastTS)){
                        $anything_changed = true;
                        break;
                    }
                }
                if(!$anything_changed) {
                    continue;
                }
            }
            $actionListIds[] = $key;
            $actionList[$key] = $actionItem;

            $lastActivity = $actionItem->getLastActivity();
            if($lastActivity->timeStamp > $maxTS)
                $maxTS = $lastActivity->timeStamp;
        }

        if ($numberMode){
            return count($actionList);
        }
        $list = $this->createFeedList($actionList, $params['data']);
        $list->setDisplayType($params['data']['displayType']);
        $html = $list->render();

        $this->synchronizeData($params['data']['feedAutoId'], array(
            'data' => $params['data'],
            'driver' => $driver->getState()
        ));

        $data = array('result'=>'success', 'count' => count($actionList), 'content' => trim($html),
            'lastTS' => time(), 'idList'=>$actionListIds);

        return $this->echoMarkup($data);
    }

    /**
     *
     * @param array $actionList
     * @param array $data
     * @return NEWSFEED_CMP_FeedList
     */
    protected function createFeedList( $actionList, $data )
    {
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
        if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion'] == true) {
            return OW::getClassInstance("NEWSFEED_MCMP_FeedList", $actionList, $data);
        }
        else{
            return OW::getClassInstance("NEWSFEED_CMP_FeedList", $actionList, $data);
        }

    }

    /***
     * @param $autoId
     * @param $data
     */
    private function synchronizeData( $autoId, $data )
    {
        $script = UTIL_JsGenerator::newInstance()
            ->callFunction(array('window', 'ow_newsfeed_feed_list', $autoId, 'setData'), array($data));
        OW::getDocument()->addOnloadScript($script);
    }

    /***
     * @param $markup
     * @return false|string
     */
    public function echoMarkup($markup)
    {
        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();
        $beforeIncludes = $document->getScriptBeforeIncludes();
        if (!empty($beforeIncludes)) {
            $markup['beforeIncludes'] = $beforeIncludes;
        }

        $scripts = $document->getScripts();
        if (!empty($scripts)) {
            $markup['scriptFiles'] = $scripts;
        }

        $styleSheets = $document->getStyleSheets();
        if (!empty($styleSheets)) {
            $markup['styleSheets'] = $styleSheets;
        }

        $onloadScript = $document->getOnloadScript();
        if (!empty($onloadScript)) {
            $markup['onloadScript'] = $onloadScript;
        }

        $styleDeclarations = $document->getStyleDeclarations();
        if (!empty($styleDeclarations)) {
            $markup['styleDeclarations'] = $styleDeclarations;
        }
        return json_encode($markup);
    }

    /**
     * @param $needle
     * @return bool|string|null
     */
    public function findIdFromUrl($needle)
    {
        $id = null;
        if (strpos($_SERVER['REQUEST_URI'], $needle) !== false) {
            $id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $needle) + strlen($needle));
            if (strpos($id, '/') !== false) {
                $id = substr($id, 0, strpos($id, '/'));
            }
        }
        return $id;
    }
}