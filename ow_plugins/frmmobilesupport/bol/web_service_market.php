<?php

/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.story.highlight
 * @since 1.0
 */

class FRMMOBILESUPPORT_BOL_WebServiceMarket
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


    /***
     * @return array
     */
    public function addSeller(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        $storeName= '';

        if(isset($_POST['userId'])){
            $user = $_POST['userId'];
        }elseif(isset($userId)){
            $user = $userId;
        }else{
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(isset($_POST['storeName'])){
            $storeName = strip_tags(UTIL_HtmlTag::stripTags($_POST['storeName']));
        }else{
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $result = MARKET_BOL_Service::getInstance()->updateUserRoleToSeller((int)$user, $storeName);
        if($result){
            return array('status' => true, 'result'=>$result);
        }else{
            return array('valid' => false, 'message' => 'user_not_found');
        }
    }


    /***
     * @return array
     */
    public function removeSeller(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();

        if(isset($_POST['userId'])){
            $user = $_POST['userId'];
        }elseif(isset($userId)){
            $user = $userId;
        }else{
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $result = MARKET_BOL_Service::getInstance()->removeSeller((int)$user);
        if($result){
            return array('status' => true, 'success');
        }else{
            return array('valid' => false, 'message' => 'user_not_found');
        }
    }

    /***
     * @return array
     */
    public function productHashtagSearch(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();

        if(isset($_POST['userId'])){
            $user = $_POST['userId'];
        }elseif(isset($userId)){
            $user = $userId;
        }else{
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(isset($_POST['hashtag'])){
            $hashtag = $_POST['hashtag'];
        }else{
            return array('valid' => false, 'message' => 'no_hashtag_entered');
        }

        $result = MARKET_BOL_Service::getInstance()->productHashtagSearchByArray($hashtag);
        $data = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->preparedActionsData($result);
        if($data){
            return array('status' => true, 'result'=>$data);
        }else{
            return array('valid' => false, 'message' => 'hashtag_not_found');
        }
    }

}
