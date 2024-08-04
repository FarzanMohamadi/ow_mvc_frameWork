<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 10/8/2017
 * Time: 10:46 AM
 */
class FRMNATIONALCODE_MCLASS_EventHandler
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
        $service = FRMNATIONALCODE_BOL_Service::getInstance();
        OW::getEventManager()->bind(FRMEventManager::ON_RENDER_JOIN_FORM, array($service, 'on_render_join_form'));
        OW::getEventManager()->bind('base.question_field_create', array($service, 'onQuestionFieldCreate'));
    }

}