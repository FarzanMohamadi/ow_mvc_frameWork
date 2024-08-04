<?php
/**
 * The class is a gateway for auth. adapters and provides common API to authenticate users.
 *
 * @package ow_core
 * @method static OW_Auth getInstance()
 * @since 1.0
 */
class OW_Auth
{
    use OW_Singleton;
    
    /**
     * @var OW_IAuthenticator
     */
    private $authenticator;

    /**
     * @return OW_IAuthenticator
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }

    /**
     * @param OW_IAuthenticator $authenticator
     */
    public function setAuthenticator( OW_IAuthenticator $authenticator )
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Tries to authenticate user using provided adapter.
     *
     * @param OW_AuthAdapter $adapter
     * @return OW_AuthResult
     */
    public function authenticate( OW_AuthAdapter $adapter )
    {
        $result = $adapter->authenticate();

        if ( !( $result instanceof OW_AuthResult ) )
        {
            throw new LogicException('Instance of OW_AuthResult expected!');
        }

        if ( $result->isValid() )
        {
            $this->login($result->getUserId());
        }

        return $result;
    }

    /**
     * Checks if current user is authenticated.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->authenticator->isAuthenticated();
    }

    /**
     * Returns current user id.
     * If user is not authenticated 0 returned.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->authenticator->getUserId();
    }

    /**
     * Logins user by provided user id.
     *
     * @param integer $userId
     * @param bool $propagate
     */
    public function login( $userId, $propagate = true )
    {
        $userId = (int) $userId;

        if ( $userId < 1 )
        {
            throw new InvalidArgumentException('invalid userId');
        }

        if($propagate) {
            $event = new OW_Event(OW_EventManager::ON_BEFORE_USER_LOGIN, array('userId' => $userId));
            OW::getEventManager()->trigger($event);
        }

        $this->authenticator->login($userId);

        if($propagate) {
            OW::getLogger()->writeLog(OW_Log::INFO, 'user_login', ['actionType'=>OW_Log::READ, 'enType'=>'user', 'enId'=>$userId, 'success'=>$this->isAuthenticated()], false);
            $event = new OW_Event(OW_EventManager::ON_USER_LOGIN, array('userId' => $userId));
            OW::getEventManager()->trigger($event);
        }
    }

    /**
     * Logs out current user.
     *
     * @param bool $propagate
     */
    public function logout($propagate = true)
    {
        if ( !$this->isAuthenticated() )
        {
            return;
        }

        if($propagate) {
            $event = new OW_Event(OW_EventManager::ON_USER_LOGOUT, array('userId' => $this->getUserId()));
            OW::getEventManager()->trigger($event);
        }

        $this->authenticator->logout();
    }

    /**
     * Returns auth id
     *
     * @return string
     */
    public function getId()
    {
        return $this->authenticator->getId();
    }
}