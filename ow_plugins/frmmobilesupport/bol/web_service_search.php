<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceSearch
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

    public function search(){
        if(!FRMSecurityProvider::checkPluginActive('frmadvancesearch', true)){
            return array();
        }

        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if(!$guestAccess){
            return array('valid' => false, 'message' => 'guest_cant_view');
        }

        $searchValue = '';
        if(isset($_GET['searchValue'])){
            $searchValue = $_GET['searchValue'];
        }

        $searchValue = trim($searchValue);
        $searchValue = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($searchValue, true, true);

        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        $resultData = array();
        $event = OW::getEventManager()->trigger(new OW_Event('frmadvancesearch.on_collect_search_items',
            array('q' => $searchValue, 'first' => $first, 'count' => $count, 'nativeMobile' => true), $resultData));
        $resultData = $event->getData();

        $resultData['posts'] = array();
        if (FRMSecurityProvider::checkPluginActive('newsfeed', true) && isset($resultData['newsfeed'])) {
            $actionIds = array();
            foreach ($resultData['newsfeed']['data'] as $item) {
                $actionIds[] = $item['id'];
            }
            if (sizeof($actionIds) > 0) {
                $actionList = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->findOrderedListByIdList($actionIds);
                $resultData['posts'] = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->preparedActionsData($actionList);
            }
            unset($resultData['newsfeed']);
        }

        foreach($resultData as $key => $value){
            $newData = array();
            if (isset($value['data'])) {
                $data = $value['data'];
                foreach ($data as $singleData) {
                    if(isset($singleData['title'])) {
                        $singleData['title'] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($singleData['title']);
                    }
                    if(isset($singleData['userId'])) {
                        $singleData['userId'] = (int) $singleData['userId'];
                        $singleData['user'] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($singleData['userId']);
                    }
                    if(isset($singleData['id'])) {
                        $singleData['id'] = (int) $singleData['id'];
                    }
                    $newData[] = $singleData;
                }
            }
            if ($key != 'posts') {
                $resultData[$key] = $newData;
            }
            if(OW::getConfig()->configExists('frmadvancesearch','search_allowed_'.$key)){
                $isAllowed = OW::getConfig()->getValue('frmadvancesearch','search_allowed_'.$key);
                if(!$isAllowed){
                    unset($resultData[$key]);
                }
            }
        }

        return array('searchedValue' => $searchValue, 'data'=>$resultData);
    }

    public function searchMentions($userId){
        if(!FRMSecurityProvider::checkPluginActive('frmadvancesearch', true)){
            return array();
        }

        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if(!$guestAccess){
            return array('valid' => false, 'message' => 'guest_cant_view');
        }

        $searchValue = BOL_UserService::getInstance()->getUserName($userId);

        $searchValue = trim('@' . $searchValue);
        $searchValue = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($searchValue, true, true);

        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        $resultData = array();
        $event = OW::getEventManager()->trigger(new OW_Event('frmadvancesearch.on_collect_search_items',
            array('q' => $searchValue, 'first' => $first, 'count' => $count, 'nativeMobile' => true, 'selected_section' => 'mentions'), $resultData));
        $resultData = $event->getData();

        $resultData['posts'] = array();
        if (FRMSecurityProvider::checkPluginActive('newsfeed', true) && isset($resultData['mentions'])) {
            $actionIds = array();
            foreach ($resultData['mentions']['data'] as $item) {
                $actionIds[] = $item['id'];
            }
            if (sizeof($actionIds) > 0) {
                $actionList = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->findOrderedListByIdList($actionIds);
                $resultData['posts'] = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->preparedActionsData($actionList);
            }
            unset($resultData['mentions']);
        }

        foreach($resultData as $key => $value){
            $newData = array();
            if (isset($value['data'])) {
                $data = $value['data'];
                foreach ($data as $singleData) {
                    if(isset($singleData['title'])) {
                        $singleData['title'] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($singleData['title']);
                    }
                    if(isset($singleData['userId'])) {
                        $singleData['userId'] = (int) $singleData['userId'];
                        $singleData['user'] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($singleData['userId']);
                    }
                    if(isset($singleData['id'])) {
                        $singleData['id'] = (int) $singleData['id'];
                    }
                    $newData[] = $singleData;
                }
            }
            if ($key != 'posts') {
                $resultData[$key] = $newData;
            }
            if(OW::getConfig()->configExists('frmadvancesearch','search_allowed_'.$key)){
                $isAllowed = OW::getConfig()->getValue('frmadvancesearch','search_allowed_'.$key);
                if(!$isAllowed){
                    unset($resultData[$key]);
                }
            }
        }

        return array('searchedValue' => $searchValue, 'data'=>$resultData);
    }
}