<?php
/**
 * Base session class.
 *
 * @package ow_core
 * @method static OW_Session getInstance()
 * @since 1.0
 */


class OW_Session
{
    use OW_Singleton;
    
    private static $protectedKeys = array('session.home_url', 'session.user_agent');

    private function __construct()
    {
        if ( session_id() === '' )
        {
            FRMSecurityProvider::getInstance()->set_php_ini_params();
        }
    }

    public function getName()
    {
        return md5(OW_URL_HOME);
    }

    public function start()
    {
        //TODO: maybe session_destroy ?
        session_name($this->getName());

        $cookie = session_get_cookie_params();
        $cookie['httponly'] = true;
        $secure = (strpos(strtolower(OW_URL_HOME), 'https')===0);
        session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $secure, $cookie['httponly']);

        if(defined('MYSQL_SESSION') && MYSQL_SESSION) {
            $session = new OW_SessionMysql();    //Start a new PHP MySQL session
        }
        else {
            session_start();
        }

        if ( !$this->isKeySet('session.home_url'))
        {
            $this->set('session.home_url', OW_URL_HOME, true);
        }
        else if ( $this->get('session.home_url') !== OW_URL_HOME )
        {
            $this->regenerate();
        }

        $userAgent = OW::getRequest()->getUserAgentName();

        if ( $this->isKeySet('session.user_agent'))
        {
            if ( $this->get('session.user_agent') !== $userAgent )
            {
                $this->regenerate();
            }
        }
        else
        {
            $this->set('session.user_agent', $userAgent, true);
        }
    }

    public function regenerate()
    {
        session_regenerate_id();

        $_SESSION = array();

        if ( isset($_COOKIE[$this->getName()]) )
        {
            $_COOKIE[$this->getName()] = $this->getId();
        }
    }

    public function getId()
    {
        return session_id();
    }

    public function set( $key, $value , $force = false)
    {
        if ( !$force && in_array($key, self::$protectedKeys) )
        {
            throw new Exception('Attempt to set protected key');
        }

        $_SESSION[$key] = $value;
    }

    public function get( $key )
    {
        if ( !isset($_SESSION[$key]) )
        {
            return null;
        }

        return $_SESSION[$key];
    }

    public function isKeySet( $key )
    {
        return isset($_SESSION[$key]);
    }

    public function delete( $key )
    {
        unset($_SESSION[$key]);
    }
}
