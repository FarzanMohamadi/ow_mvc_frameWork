<?php
/**
 * Singleton. 'Suspended User' Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserResetPasswordDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const CODE = 'code';
    const EXPIRATION_TS = 'expirationTimeStamp';
    const UPDATE_TS = 'updateTimeStamp';

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
     * @var BOL_UserResetPasswordDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserResetPasswordDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_user_reset_password';
    }

    public function getDtoClassName()
    {
        return 'BOL_UserResetPassword';
    }

    /**
     * @param integer $userId
     * @return BOL_UserResetPassword
     */
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int)$userId);

        $result = $this->findObjectByExample($example);
        if ($result !== null && $result->getExpirationTimeStamp() <= time()) {
            $this->delete($result);
            return null;
        }
        return $result;
    }

    /**
     * @param string $code
     * @return BOL_UserResetPassword
     */
    public function findByCode( $code )
    {
        $hashedCode = FRMSecurityProvider::getInstance()->hashSha256Data($code);
        $example = new OW_Example();
        $example->andFieldEqual(self::CODE, $hashedCode);

        $result = $this->findObjectByExample($example);
        if ($result !== null && $result->getExpirationTimeStamp() <= time()) {
            $this->delete($result);
            return null;
        }
        return $result;
    }

    public function deleteExpiredEntities()
    {
        $example = new OW_Example();
        $example->andFieldLessOrEqual(self::EXPIRATION_TS, time());

        $this->deleteByExample($example);
    }
}