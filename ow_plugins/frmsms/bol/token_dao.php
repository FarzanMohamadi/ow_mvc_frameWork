<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsms.bol
 * @since 1.0
 */
class FRMSMS_BOL_TokenDao extends OW_BaseDao
{
    private static $classInstance;

    const TOKEN_LIFETIME_SECONDS = 60 * 60;

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
        return 'FRMSMS_BOL_Token';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmsms_token';
    }

    /**
     * @param FRMSMS_BOL_Token
     * @return FRMSMS_BOL_Token
     */
    private function getNonExpiredToken($token)
    {
        if ($token == null)
            return null;
        else
            {
            if ($token->time <= time() - self::TOKEN_LIFETIME_SECONDS) {
                $this->delete($token);
                return null;
            }
            return $token;
        }
    }

    /***
     * @param $mobile
     * @return FRMSMS_BOL_Token
     */
    public function getUserTokenByMobile($mobile){
        $example = new OW_Example();
        $example->andFieldEqual('mobile', $mobile);
        $result = $this->findObjectByExample($example);
        return $this->getNonExpiredToken($result);
    }

    /**
     * @param $token
     * @param null $mobile
     * @return FRMSMS_BOL_Token
     */
    public function saveOrUpdateToken($token, $mobile= null){
        $tokenObj = $this->getUserTokenByMobile($mobile);
        if(!isset($tokenObj)){
            $tokenObj = new FRMSMS_BOL_Token();
            $tokenObj->try = 0;
        }else{
            $tokenObj->try++;
        }
        if (isset($mobile)) {
            $tokenObj->mobile = $mobile;
        }
        $tokenObj->time = time();
        $tokenObj->token = $token;

        $this->save($tokenObj);
        return $tokenObj;
    }

    /***
     * @param $mobile
     * @return FRMSMS_BOL_Token|null
     */
    public function increaseTryByMobile($mobile){
        $tokenObj = $this->getUserTokenByMobile($mobile);
        if(isset($tokenObj)) {
            $tokenObj->try = $tokenObj->try + 1;
            $this->save($tokenObj);
            return $tokenObj;
        }
        return null;
    }

    /***
     * @param $oldMobile
     * @param string $newMobile
     * @return FRMSMS_BOL_Token|mixed
     */
    public function updateTokenMobileByMobile($oldMobile, $newMobile){
        $tokenObj = $this->getUserTokenByMobile($oldMobile);
        if(!isset($tokenObj)) {
            return null;
        }
        $tokenObj->mobile = $newMobile;
        $this->save($tokenObj);
        return $tokenObj;
    }

    /***
     * @param $mobile
     * @return mixed
     */
    public function renewTimeToken($mobile){
        $tokenObj = $this->getUserTokenByMobile($mobile);
        if(isset($tokenObj)){
            $tokenObj->try = 1;
            $tokenObj->time = time();
            $this->save($tokenObj);
            return $tokenObj;
        }
        return null;
    }

    public function updateExpiredTokens()
    {
        $example = new OW_Example();
        $example->andFieldLessOrEqual('time', time() - self::TOKEN_LIFETIME_SECONDS);
        $expiredUserTokens = $this->findListByExample($example);
        foreach ($expiredUserTokens as $userToken){
            $userToken->try = 0;
            $userToken->time = time();
            $this->save($userToken);
        }

        $this->deleteByExample($example);
    }
    public function deleteExpiredTokens()
    {
        $service = FRMSMS_BOL_Service::getInstance();
        $example = new OW_Example();
        $example->andFieldLessOrEqual('time', time() - self::TOKEN_LIFETIME_SECONDS);
        $expiredUserTokens = $this->findListByExample($example);
        foreach ($expiredUserTokens as $userToken){
            BOL_QuestionDataDao::getInstance()->deleteByQuestionListAndUserId(array($service::$MOBILE_FIELD_NAME), $userToken->userId);
        }

        $this->deleteByExample($example);
    }

    public function deleteUserTokenByMobile($mobile)
    {
        $example = new OW_Example();
        $example->andFieldEqual('mobile', $mobile);
        $this->deleteByExample($example);
    }

}
