<?php
/**
 * Data Access Object for `ow_base_mass_mailing_ignore_user` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 *
 */
class BOL_MassMailingIgnoreUserService
{
    /**
     * @var BOL_MassMailingIgnoreUserDao
     */
    private $massMailingDao;

    /**
     * @var BOL_MassMailingIgnoreUserService
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->massMailingDao = BOL_MassMailingIgnoreUserDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return BOL_MassMailingIgnoreUserService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @param int $userId
     * @return BOL_MassMailingIgnoreUser
     */
    public function findByUserId( $userId )
    {
        return $this->massMailingDao->findByUserId($userId);
    }

    /**
     * @param BOL_MassMailingIgnoreUser $object
     */
    public function save( BOL_MassMailingIgnoreUser $object )
    {
        $this->massMailingDao->save($object);
    }
}
?>
