<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmwidgetplus
 * @since 1.0
 */
class FRMWIDGETPLUS_MCLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    private function __construct()
    {
    }

    public function init()
    {
        $service=FRMWIDGETPLUS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'addWidgetJS'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_GROUP_LIST_VIEW_RENDER, array($service, 'beforeGroupListViewRender'));
        $eventManager->bind('frm.on.before.news.list.view.render', array($service, 'beforeNewsListViewRender'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_GROUP_VIEW_RENDER, array($service, 'beforeGroupViewRender'));
    }
}