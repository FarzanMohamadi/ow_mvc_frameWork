<?php
/**
 * Web user class
 *
 * @package ow_core
 * @since 1.0
 */
class OW_User
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
        $this->auth = OW_Auth::getInstance();

        if ( $this->isAuthenticated() )
        {
            $this->user = BOL_UserService::getInstance()->findUserById($this->auth->getUserId());
        }
        else
        {
            $this->user = null;
        }
    }
    /**
     *
     * @var OW_Auth
     */
    private $auth;
    /**
     * Current user object;
     *
     * @var BOL_User
     */
    private $user;

    /**
     *
     * @param string $groupName
     * @param string $actionName
     * @param array $extra
     * @return boolean
     */
    public function isAuthorized( $groupName, $actionName = null, $extra = null )
    {
        if ( $extra !== null && !is_array($extra) )
        {
            trigger_error("`ownerId` parameter has been deprecated, pass `extra` parameter instead\n", E_USER_WARNING);
        }

        return BOL_AuthorizationService::getInstance()->isActionAuthorized($groupName, $actionName, $extra)
            || BOL_AuthorizationService::getInstance()->isActionAuthorized($groupName, null, $extra);
    }

    /**
     *
     * @param OW_AuthAdapter $adapter
     * @return OW_AuthResult
     */
    public function authenticate( OW_AuthAdapter $adapter )
    {
        $result = $this->auth->authenticate($adapter);

        if ( $this->isAuthenticated() )
        {
            $this->user = BOL_UserService::getInstance()->findUserById($this->auth->getUserId());
        }

        return $result;
    }

    /**
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->auth->isAuthenticated();
    }

    /**
     * Get user id
     *
     * @return int
     */
    public function getId()
    {
        return ( $this->user === null ) ? 0 : $this->user->getId();
    }

    /**
     *
     * @return string
     */
    public function getEmail()
    {
        return ( $this->user === null ) ? '' : $this->user->email;
    }

    /**
     *
     * @return BOL_User
     */
    public function getUserObject()
    {
        return $this->user;
    }

    public function isAdmin()
    {
        return $this->isAuthorized(BOL_AuthorizationService::ADMIN_GROUP_NAME);
    }

    public function login( $userId, $propagate = true, $remember = false)
    {
        $this->auth->login($userId, $propagate);

        if ( $this->isAuthenticated() )
        {
            $this->user = BOL_UserService::getInstance()->findUserById($this->auth->getUserId());
        }
        if ( $remember )
        {
            BOL_UserService::getInstance()->setLoginCookie(null, OW::getUser()->getId());
        }
    }

    public function logout($propagate = true)
    {
        if ( $this->isAuthenticated() )
        {
            $this->auth->logout($propagate);
            $this->user = null;
        }
    }
}

