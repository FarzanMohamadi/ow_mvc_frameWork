<?php
/**
 * 
 * All rights reserved.
 */

/**
 * Class FRMPUBLISHFORUMTOPIC_CLASS_EventHandler
 */
class FRMPUBLISHFORUMTOPIC_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    public  function __construct()
    {
    }

    public function init()
    {
        $service = FRMPUBLISHFORUMTOPIC_BOL_Service::getInstance();
        OW::getEventManager()->bind('on.forum.toolbar.action.render', array($service, 'onForumActionToolbarRender'));
        OW::getEventManager()->bind('on.add.form.render', array($service, 'onAddFormRender'));
    }


}