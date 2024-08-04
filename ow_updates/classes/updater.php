<?php
/**
 * @package ow_updates.classes
 * @since 1.0
 */
class Updater
{
    public static $storage = null;

    /**
     * @return OW_Database
     */
    public static function getDbo()
    {
        return OW::getDbo();
    }

    /**
     * @return UPDATE_SeoService
     */
    public static function getSeoService()
    {
        return UPDATE_SeoService::getInstance();
    }

    /**
     * @return UPDATE_LanguageService
     */
    public static function getLanguageService()
    {
        return UPDATE_LanguageService::getInstance();
    }

    /**
     * @return UPDATE_WidgetService
     */
    public static function getWidgetService()
    {
        return UPDATE_WidgetService::getInstance();
    }
    
    /**
     * @return UPDATE_WidgetService
     */
    public static function getMobileWidgeteService()
    {
        return UPDATE_MobileWidgetService::getInstance();
    }

    /**
     * @return UPDATE_ConfigService
     */
    public static function getConfigService()
    {
        return UPDATE_ConfigService::getInstance();
    }

    /**
     * @return UPDATE_NavigationService
     */
    public static function getNavigationService()
    {
        return UPDATE_NavigationService::getInstance();
    }

    /**
     * @return UPDATE_AuthorizationService
     */
    public static function getAuthorizationService()
    {
        return UPDATE_AuthorizationService::getInstance();
    }

    /**.
     * @param string $name
     * @return UPDATE_Log
     */
    public static function getLogger($name='main')
    {
        return UPDATE_Log::getInstance($name);
    }

    /**
     * @return OW_Storage
     */
    public static function getStorage()
    {
        if ( self::$storage === null )
        {
            switch ( true )
            {
                case defined('OW_USE_AMAZON_S3_CLOUDFILES') && OW_USE_AMAZON_S3_CLOUDFILES :
                    self::$storage = new BASE_CLASS_AmazonCloudStorage();
                    break;

                /* case defined('OW_USE_CLOUDFILES') && OW_USE_CLOUDFILES :
                    self::$storage = new BASE_CLASS_CloudStorage();
                    break; */

                default :
                    self::$storage = new BASE_CLASS_FileStorage();
                    break;
            }
        }

        return self::$storage;
    }
}
