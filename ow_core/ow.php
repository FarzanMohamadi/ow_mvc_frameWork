<?php
/**
 * @package ow_core
 * @since 1.0
 */
final class OW
{
    const CONTEXT_MOBILE = OW_Application::CONTEXT_MOBILE;
    const CONTEXT_DESKTOP = OW_Application::CONTEXT_DESKTOP;

    private static $context;

    private static function detectContext()
    {
        if ( self::$context !== null )
        {
            return;
        }

        if ( defined('OW_USE_CONTEXT') )
        {
            switch ( true )
            {
                case OW_USE_CONTEXT == 1:
                    self::$context = self::CONTEXT_DESKTOP;
                    return;

                case OW_USE_CONTEXT == 1 << 1:
                    self::$context = self::CONTEXT_MOBILE;
                    return;

            }
        }


        $context = self::CONTEXT_DESKTOP;

        try
        {
            $isSmart = UTIL_Browser::isSmartphone();
        }
        catch ( Exception $e )
        {
            return;
        }

        if ( defined('OW_CRON') )
        {
            $context = self::CONTEXT_DESKTOP;
        }
        else if ( self::getSession()->isKeySet(OW_Application::CONTEXT_NAME) )
        {
            $context = self::getSession()->get(OW_Application::CONTEXT_NAME);
        }
        else if ( $isSmart )
        {
            $context = self::CONTEXT_MOBILE;
        }

        if ( defined('OW_USE_CONTEXT') )
        {
            if ( (OW_USE_CONTEXT & 1 << 1) == 0 && $context == self::CONTEXT_MOBILE )
            {
                $context = self::CONTEXT_DESKTOP;
            }
        }

        if ( (bool) OW::getConfig()->getValue('base', 'disable_mobile_context') && $context == self::CONTEXT_MOBILE )
        {
            $context = self::CONTEXT_DESKTOP;
        }

        self::$context = $context;
    }

    /**
     * Returns autoloader object.
     *
     * @return OW_Autoload
     */
    public static function getAutoloader()
    {
        return OW_Autoload::getInstance();
    }

    /**
     * Returns front controller object.
     *
     * @return OW_Application
     */
    public static function getApplication()
    {
        self::detectContext();

        if ( self::$context == self::CONTEXT_MOBILE ) {
            return OW_MobileApplication::getInstance();
        }

        return OW_Application::getInstance();
    }

    /**
     * Returns global config object.
     *
     * @return OW_Config
     */
    public static function getConfig()
    {
        return OW_Config::getInstance();
    }

    /**
     * Returns session object.
     *
     * @return OW_Session
     */
    public static function getSession()
    {
        return OW_Session::getInstance();
    }

    /**
     * Returns current web user object.
     *
     * @return OW_User
     */
    public static function getUser()
    {
        return OW_User::getInstance();
    }
    /**
     * Database object instance.
     *
     * @var OW_Database
     */
    private static $dboInstance;

    /**
     * @param $reconnect
     * Returns DB access object with default connection.
     *
     * @return OW_Database
     */
    public static function getDbo($reconnect = false)
    {
        if ( self::$dboInstance === null || $reconnect )
        {
            $params = array(
                'host' => '',
            );
            self::$dboInstance = null;

            // populate config
            if(defined('OW_DB_HOST')) {
                $params = array(
                    'host' => OW_DB_HOST,
                    'username' => OW_DB_USER,
                    'password' => OW_DB_PASSWORD,
                    'dbname' => OW_DB_NAME
                );
            }
            if ( defined('OW_DB_PORT') && (OW_DB_PORT !== null) )
            {
                $params['port'] = OW_DB_PORT;
            }
            if ( defined('OW_DB_SOCKET') )
            {
                $params['socket'] = OW_DB_SOCKET;
            }
            if ( OW_PROFILER_ENABLE )
            {
                $params['profilerEnable'] = true;
            }
            if ( OW_DEBUG_MODE )
            {
                $params['debugMode'] = true;
            }
            if ($reconnect) {
                $params['reconnect'] = true;
            }

            self::$dboInstance = OW_Database::getInstance($params);
        }
        return self::$dboInstance;
    }

    /**
     * Returns system mailer object.
     *
     * 	@return OW_Mailer
     */
    public static function getMailer()
    {
        return OW_Mailer::getInstance();
    }

    /**
     * Returns responded HTML document object.
     *
     * @return OW_HtmlDocument
     */
    public static function getDocument()
    {
        return OW_Response::getInstance()->getDocument();
    }

    /**
     * Returns global request object.
     *
     * @return OW_Request
     */
    public static function getRequest()
    {
        return OW_Request::getInstance();
    }

    /**
     * Returns global response object.
     *
     * @return OW_Response
     */
    public static function getResponse()
    {
        return OW_Response::getInstance();
    }

    /**
     * Returns language object.
     *
     * @return OW_Language
     */
    public static function getLanguage()
    {
        return OW_Language::getInstance();
    }

    /**
     * Returns system router object.
     *
     * @return OW_Router
     */
    public static function getRouter()
    {
        return OW_Router::getInstance();
    }

    /**
     * Returns system plugin manager object.
     *
     * @return OW_PluginManager
     */
    public static function getPluginManager()
    {
        return OW_PluginManager::getInstance();
    }

    /**
     * Returns system theme manager object.
     *
     * @return OW_ThemeManager
     */
    public static function getThemeManager()
    {
        return OW_ThemeManager::getInstance();
    }

    /**
     * Returns system event manager object.
     *
     * @return OW_EventManager
     */
    public static function getEventManager()
    {
        return OW_EventManager::getInstance();
    }

    /**
     * @return OW_Registry
     */
    public static function getRegistry()
    {
        return OW_Registry::getInstance();
    }

    /**
     * Returns global feedback object.
     *
     * @return OW_Feedback
     */
    public static function getFeedback()
    {
        return OW_Feedback::getInstance();
    }

    /**
     * Returns global navigation object.
     *
     * @return OW_Navigation
     */
    public static function getNavigation()
    {
        return OW_Navigation::getInstance();
    }

    /**
     * @return OW_RequestHandler
     */
    public static function getRequestHandler()
    {
        self::detectContext();
        return OW_RequestHandler::getInstance();
    }

    /**
     *
     * @return OW_CacheService
     */
    public static function getCacheService()
    {
        return BOL_DbCacheService::getInstance();
    }
    private static $storage;

    /**
     *
     * @return OW_Storage
     */
    public static function getStorage()
    {
        if ( self::$storage === null )
        {
            self::$storage = OW::getEventManager()->call('core.get_storage');

            if ( self::$storage === null )
            {
                switch ( true )
                {
                    case defined('OW_USE_AMAZON_S3_CLOUDFILES') && OW_USE_AMAZON_S3_CLOUDFILES :
                        self::$storage = new BASE_CLASS_AmazonCloudStorage();
                        break;

                    case defined('OW_USE_CLOUDFILES') && OW_USE_CLOUDFILES :
                        self::$storage = new BASE_CLASS_CloudStorage();
                        break;

                    default :
                        self::$storage = new BASE_CLASS_FileStorage();
                        break;
                }
            }
        }

        return self::$storage;
    }

    public static function getLogger($name='main')
    {
        return OW_Log::getInstance($name);
    }

    /**
     * @return OW_Authorization
     */
    public static function getAuthorization()
    {
        return OW_Authorization::getInstance();
    }

    /**
     * @return OW_CacheManager
     */
    public static function getCacheManager()
    {
        return OW_CacheManager::getInstance();
    }

    public static function getClassInstance( $className, $arguments = null )
    {
        $args = func_get_args();
        $constuctorArgs = array_splice($args, 1);

        return self::getClassInstanceArray($className, $constuctorArgs);
    }

    public static function getClassInstanceArray( $className, array $arguments = array() )
    {
        $params = array(
            'className' => $className,
            'arguments' => $arguments
        );

        $rClass = new ReflectionClass($className);
        if(!$rClass->isUserDefined() || $rClass->isInternal())
        {
            $logArray = array('entity_type' => 'core',
                'className' => $className, 'arguments' => $arguments);
            OW::getLogger()->writeLog(OW_Log::ALERT, 'malicious_access_class', $logArray);
            exit(0);
        }
        $eventManager = OW::getEventManager();
        $eventManager->trigger(new OW_Event("core.performance_test", array("key" => "component_construct.start", "params" => $params)));

        $event = new OW_Event("class.get_instance." . $className, $params);
        $eventManager->trigger($event);
        $instance = $event->getData();

        if ( $instance !== null )
        {
            $eventManager->trigger(new OW_Event("core.performance_test", array("key" => "component_construct.end", "params" => $params)));
            return $instance;
        }

        $event = new OW_Event("class.get_instance", $params);

        $eventManager->trigger($event);
        $instance = $event->getData();

        if ( $instance !== null )
        {
            $eventManager->trigger(new OW_Event("core.performance_test", array("key" => "component_construct.end", "params" => $params)));
            return $instance;
        }

        $eventManager->trigger(new OW_Event("core.performance_test", array("key" => "component_construct.end", "params" => $params)));
        return $rClass->newInstanceArgs($arguments);
    }

    /**
     * Returns text search manager object.
     *
     * @return OW_TextSearchManager
     */
    public static function getTextSearchManager()
    {
        return OW_TextSearchManager::getInstance();
    }
}
