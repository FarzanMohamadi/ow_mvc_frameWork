<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.notifications.mobile.classes
 * @since 1.6.0
 */
class NOTIFICATIONS_MCLASS_ConsoleEventHandler
{
    /**
     * Class instance
     *
     * @var NOTIFICATIONS_MCLASS_ConsoleEventHandler
     */
    private static $classInstance;

    const CONSOLE_PAGE_KEY = 'notifications';
    const CONSOLE_SECTION_KEY = 'notifications';

    /**
     * Returns class instance
     *
     * @return NOTIFICATIONS_MCLASS_ConsoleEventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function collectSections( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params['page'] == self::CONSOLE_PAGE_KEY )
        {
            $event->add(array(
                'key' => self::CONSOLE_SECTION_KEY,
                'component' => new NOTIFICATIONS_MCMP_ConsoleSection(),
                'order' => 3
            ));
        }
    }

    public function countNewItems( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params['page'] == self::CONSOLE_PAGE_KEY )
        {
            $service = NOTIFICATIONS_BOL_Service::getInstance();
            $event->add(
                array(self::CONSOLE_SECTION_KEY => $service->findNotificationCount(OW::getUser()->getId(), false))
            );
        }
    }

    public function getNewItems( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params['page'] == self::CONSOLE_PAGE_KEY )
        {
            $event->add(
                array(self::CONSOLE_SECTION_KEY => new NOTIFICATIONS_MCMP_ConsoleNewItems($params['timestamp']))
            );
        }
    }

    public function init()
    {
        $service = NOTIFICATIONS_BOL_Service::getInstance();
        $em = OW::getEventManager();
        $em->bind(
            MBOL_ConsoleService::EVENT_COLLECT_CONSOLE_PAGE_SECTIONS,
            array($this, 'collectSections')
        );
        $em->bind(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array($service, "onRabbitMQLogRelease"));

        $em->bind(
            MBOL_ConsoleService::EVENT_COUNT_CONSOLE_PAGE_NEW_ITEMS,
            array($this, 'countNewItems')
        );

        $em->bind(
            MBOL_ConsoleService::EVENT_COLLECT_CONSOLE_PAGE_NEW_ITEMS,
            array($this, 'getNewItems')
        );
    }
}