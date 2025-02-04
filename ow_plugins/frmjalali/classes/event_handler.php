<?php
class FRMJALALI_CLASS_EventHandler
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

    }

    public function init()
    {
        $service = FRMJALALI_BOL_Service::getInstance();
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array( $service, 'onBeforeDocumentRender'));
        OW::getEventManager()->bind(FRMEventManager::ON_AFTER_DEFAULT_DATE_VALUE_SET, array( $service, 'onAfterDefaultDateValueSet'));
        OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_VALIDATING_FIELD, array( $service, 'onBeforeValidatingField'));
        OW::getEventManager()->bind(FRMEventManager::ON_RENDER_FORMAT_DATE_FIELD, array( $service, 'onRenderFormatDateField'));
        OW::getEventManager()->bind(FRMEventManager::SET_BIRTHDAY_RANGE_TO_JALALI, array( $service, 'setBirthdayRangeToJalali'));
        OW::getEventManager()->bind(FRMEventManager::CHANGE_DATE_RANGE_TO_JALALI, array( $service, 'changeDateRangeToJalali'));
        OW::getEventManager()->bind(FRMEventManager::CHANGE_DATE_FORMAT_TO_JALALI_FOR_BLOG_AND_NEWS, array( $service, 'changeDateFormatToJalaliForBlogAndNews'));
        OW::getEventManager()->bind(FRMEventManager::CHANGE_DATE_FORMAT_TO_GREGORIAN, array( $service, 'changeDateFormatToGregorian'));
        OW::getEventManager()->bind(FRMEventManager::CALCULATE_JALALI_MONTH_LAST_DAY, array( $service, 'calculateJalaliMonthLastDay'));
    }

}
