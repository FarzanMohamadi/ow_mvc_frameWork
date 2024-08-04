<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class FRMSMS_BOL_MobileVerifyDao extends OW_BaseDao
{
    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var FRMSMS_BOL_MobileVerifyDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMSMS_BOL_MobileVerifyDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'FRMSMS_BOL_MobileVerify';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmsms_mobile_verify';
    }

    /**
     * @param string $mobile
     * @return FRMSMS_BOL_MobileVerify
     */
    public function findByMobile( $mobile )
    {
        if ( empty($mobile) )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldEqual('mobile', trim($mobile));

        return $this->findObjectByExample($example);
    }

    /**
     * @param $mobiles
     * @return FRMSMS_BOL_MobileVerify[]
     */
    public function findUserIdsByMobiles($mobiles ) {
        if (empty($mobiles)) {
            return array();
        }

        $sql = "SELECT `userId` FROM " . $this->getTableName() . " WHERE `mobile` IN (" . OW::getDbo()->mergeInClause($mobiles) . "); ";
        return OW::getDbo()->queryForColumnList($sql);
    }

    /**
     * @param string $userId
     * @return FRMSMS_BOL_MobileVerify
     */
    public function findByUser( $userId )
    {
        if ( empty($userId) )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldEqual('userId', trim($userId));

        return $this->findObjectByExample($example);
    }

    /***
     * @param $userId
     */
    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $this->deleteByExample($example);
    }

    /***
     * @param $mobile
     */
    public function deleteByMobile( $mobile )
    {
        $example = new OW_Example();
        $example->andFieldEqual('mobile', $mobile);
        $this->deleteByExample($example);
    }

    /***
     * @param $userId
     * @return int|null
     */
    public function getUserMobileByUser($userId )
    {
        if ( empty($userId) )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldEqual('userId', trim($userId));

        $item = $this->findObjectByExample($example);
        if(empty($item)){
            return null;
        }
        return $item->mobile;
    }

    /***
     * @param $userId
     * @param null $mobile
     * @param bool $valid
     * @return FRMSMS_BOL_MobileVerify
     */
    public function saveOrUpdate($userId, $mobile = null, $valid=true){
        if (!isset($userId) && !isset($mobile)) {
            return null;
        }
        $example = new OW_Example();
        if(isset($userId)) {
            $example->andFieldEqual('userId', $userId);
        }
        if(isset($mobile)) {
            $example->andFieldEqual('mobile', $mobile);
        }
        $verifyObj = $this->findObjectByExample($example);
        if (!isset($verifyObj)){
            if (isset($userId) && isset($mobile)) {
                // it could exist for another login attempt
                // which generates error
                $this->deleteByUserId($userId);
                $this->deleteByMobile($mobile);
            }
            $verifyObj = new FRMSMS_BOL_MobileVerify();
            $verifyObj->userId = $userId;
            $verifyObj->mobile = $mobile;
        }
        $verifyObj->valid = $valid;
        $this->save($verifyObj);
        return $verifyObj;
    }

    /***
     * @param $mobile
     * @param $userId
     * @return FRMSMS_BOL_MobileVerify
     */
    public function setUserIdForMobile($mobile, $userId){
        $tokenObj = $this->findByMobile($mobile);
        if(!isset($tokenObj)) {
            return $this->saveOrUpdate($userId, $mobile);
        }
        $tokenObj->userId = $userId;
        $this->save($tokenObj);
        return $tokenObj;
    }

    /***
     * @param $mobile
     * @param string $newMobile
     * @param bool $invalidate
     * @return FRMSMS_BOL_Token|mixed
     */
    public function updateUserMobile($mobile, $newMobile, $invalidate){
        $tokenObj = $this->findByMobile($mobile);
        if(!isset($tokenObj)) {
            return null;
        }
        if($mobile == $newMobile){
            return null;
        }
        $tokenObj->mobile = $newMobile;
        if($invalidate){
            $tokenObj->valid = false;
        }
        $this->save($tokenObj);
        return $tokenObj;
    }

    /***
     * @param $idList
     * @return int
     */
    public function activateUserSMSTokenByUserIds($idList){
        $sql = "UPDATE `".$this->getTableName()."` SET `valid` = 1 
            WHERE `userId` IN (".$this->dbo->mergeInClause($idList).")";
        return $this->dbo->query($sql);
    }

    /***
     * @param $first
     * @param $count
     * @return array
     */
    public function findNotVerifiedUsers($first, $count)
    {
        $params = array('first' => $first, 'count' => $count );
        $query = " SELECT userId FROM  " . $this->getTableName() . "
                    WHERE  `valid` = 0
                    LIMIT :first, :count" ;
        return $this->dbo->queryForColumnList( $query,$params);
    }

    /***
     * @param $idList
     * @return array
     */
    public function findUnverifiedStatusForUserList($idList)
    {
        $query = "SELECT `userId` FROM `" . $this->getTableName() . "`
            WHERE `valid` = 0
            AND `userId` IN (" . $this->dbo->mergeInClause($idList) . ")";

        return $this->dbo->queryForColumnList($query);
    }
}
