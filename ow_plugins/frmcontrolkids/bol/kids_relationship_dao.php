<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontrolkids.bol
 * @since 1.0
 */
class FRMCONTROLKIDS_BOL_KidsRelationshipDao extends OW_BaseDao
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

    public function getDtoClassName()
    {
        return 'FRMCONTROLKIDS_BOL_KidsRelationship';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmcontrolkids_kids_relationship';
    }

    /***
     * @param $kidUserId
     * @param $parentEmail
     * @param bool $checkAuth
     * @return FRMCONTROLKIDS_BOL_KidsRelationship|void
     */
    public function addRelationship($kidUserId, $parentEmail, $checkAuth = true)
    {
        if($checkAuth && !OW::getUser()->isAuthenticated()){
            return;
        }
        $parentUser = BOL_UserService::getInstance()->findByEmail($parentEmail);
        $kidUser = BOL_UserService::getInstance()->findUserById($kidUserId);

        $relationship = new FRMCONTROLKIDS_BOL_KidsRelationship();
        $relationship->setTime(time());
        $relationship->setKidUserId($kidUserId);
        $relationship->setParentEmail($parentEmail);
        if($parentUser!=null){
            $relationship->setParentUserId($parentUser->getId());
            FRMCONTROLKIDS_BOL_Service::getInstance()->sendLinkToParentUser($parentEmail, $kidUser->username, $kidUser->email, false);
        }else{
            FRMCONTROLKIDS_BOL_Service::getInstance()->sendLinkToParentUser($parentEmail, $kidUser->username, $kidUser->email, true);
        }
        $this->save($relationship);

        return $relationship;
    }


    /***
     * @param $kidUserId
     * @param $parentUserId
     * @return bool
     */
    public function isParentExist($kidUserId, $parentUserId){
        $ex = new OW_Example();
        $ex->andFieldEqual('parentUserId', $parentUserId);
        $ex->andFieldEqual('kidUserId', $kidUserId);
        $kids_list = $this->findListByExample($ex);
        return sizeof($kids_list)>0;
    }

    /***
     * @param $parentEmail
     * @param $parentUserId
     */
    public function updateParentUserIdUsingEmail($parentEmail, $parentUserId){
        $ex = new OW_Example();
        $ex->andFieldEqual('parentEmail', $parentEmail);
        $parents = $this->findListByExample($ex);
        foreach($parents as $parent){
            $parent->setParentUserId($parentUserId);
            $this->save($parent);
        }
    }

    /***
     * @param $parentUserId
     * @return array
     */
    public function getKids($parentUserId){
        $ex = new OW_Example();
        $ex->andFieldEqual('parentUserId', $parentUserId);
        return $this->findListByExample($ex);
    }

    /***
     * @param $kidUserId
     */
    public function deleteRelationship($kidUserId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('kidUserId',$kidUserId);
        $this->deleteByExample($ex);
    }

    /*
     *@params $userId
     */
    public function removeUserInformation($userId){
        $ex = new OW_Example();
        $ex->andFieldEqual('kidUserId',$userId);
        $this->deleteByExample($ex);
        $ex = new OW_Example();
        $ex->andFieldEqual('parentUserId',$userId);
        $this->deleteByExample($ex);
    }

    /***
     * @param $kidUserId
     * @return mixed
     */
    public function getParentInfo($kidUserId){
        $ex = new OW_Example();
        $ex->andFieldEqual('kidUserId', $kidUserId);
        return $this->findObjectByExample($ex);
    }
}
