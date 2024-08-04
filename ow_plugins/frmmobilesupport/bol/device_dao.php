<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_DeviceDao extends OW_BaseDao
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
        return 'FRMMOBILESUPPORT_BOL_Device';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmmobilesupport_device';
    }

    /***
     * @param $userId
     * @return array
     */
    public function getUserDevices($userId){
        if($userId==null){
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        return $this->findListByExample($ex);
    }

    public function getUsersDevices($userIds){
        if (empty($userIds)) {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('userId', $userIds);
        $res = $this->findListByExample($example);

        $data = array();
        foreach ($res as $item) {
            if (!isset($data[$item->userId])) {
                $data[$item->userId] = array();
            }
            $data[$item->userId][] = $item;
        }
        foreach ($userIds as $userId) {
            if (!isset($data[$userId])) {
                $data[$userId] = array();
            }
        }
        return $data;
    }

    /***
     * @param $userId
     * @param $token
     */
    public function deleteUserDevice($userId, $token){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('token', $token);
        $this->deleteByExample($ex);
    }

    /***
     * @param $token
     */
    public function deleteDevice($token){
        $ex = new OW_Example();
        $ex->andFieldEqual('token', $token);
        $this->deleteByExample($ex);
    }

    /***
     * @param $userId
     */
    public function deleteAllDevicesOfUser($userId){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $this->deleteByExample($ex);
    }

    /***
     * @param $userId
     * @param $token
     * @return array|bool
     */
    public function hasUserDevice($userId, $token){
        if($userId==null){
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('token', $token);
        return $this->findObjectByExample($ex)!=null;
    }

    /***
     * @param $token
     * @return FRMMOBILESUPPORT_BOL_Device
     */
    public function findDevice($token){
        $ex = new OW_Example();
        $ex->andFieldEqual('token', $token);
        return $this->findObjectByExample($ex);
    }

    /***
     * @param $userId
     * @param $token
     * @param $cookie
     * @return FRMMOBILESUPPORT_BOL_Device
     */
    public function findDeviceTokenRow($userId, $token, $cookie){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('token', $token);
        $ex->andFieldEqual('cookie', $cookie);
        return $this->findObjectByExample($ex);
    }


    /***
     * @param $userId
     * @param $token
     * @param $type
     * @param $cookie
     * @return FRMMOBILESUPPORT_BOL_Device
     */
    public function saveDevice($userId, $token, $type, $cookie){
        $device = new FRMMOBILESUPPORT_BOL_Device();
        $device->userId = $userId;
        $device->token = $token;
        $device->time = time();
        $device->type = $type;
        $device->cookie=$cookie;
        $this->save($device);
        return $device;
    }

    public function deleteAllDevices()
    {
        $sql = "TRUNCATE TABLE ".$this->getTableName();
        $this->dbo->query($sql);
    }
}
