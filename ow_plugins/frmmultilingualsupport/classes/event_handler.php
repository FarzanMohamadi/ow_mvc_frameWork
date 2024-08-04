<?php
class FRMMULTILINGUALSUPPORT_CLASS_EventHandler
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

    private function __construct() { }


    public function init()
    {
        $service = FRMMULTILINGUALSUPPORT_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMMULTILINGUALSUPPORT_BOL_Service::CREATE_MULTILINGUAL_FIELD , array($service, "createMultilingualField"));
        $eventManager->bind(FRMMULTILINGUALSUPPORT_BOL_Service::STORE_MULTILINGUAL_DATA , array($service, "storeMultilingualData"));
        $eventManager->bind(FRMMULTILINGUALSUPPORT_BOL_Service::SHOW_DATA_IN_MULTILINGUAL , array($service, "showDataInMultilingual"));
        $eventManager->bind(FRMMULTILINGUALSUPPORT_BOL_Service::SHOW_STATIC_PAGE_NAME_IN_MULTILINGUAL  , array($service, "showStaticPageNameInMultilingual"));
        $eventManager->bind(FRMMULTILINGUALSUPPORT_BOL_Service::CREATE_MULTILINGUAL_FIELD_WIDGET_PAGE , array($service, "createMultilingualFieldWidgetPage"));
        $eventManager->bind(FRMMULTILINGUALSUPPORT_BOL_Service::STORE_MULTILINGUAL_DATA_WIDGET_PAGE , array($service, "storeMultilingualDataWidgetPage"));
        $eventManager->bind(FRMMULTILINGUALSUPPORT_BOL_Service::FIND_MULTI_VALUE_BY_WIDGET_UNIQUE_NAME  , array($service, "findMultiValueByWidgetUniqueName"));
    }
}