<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.slideshow.classes
 * @since 1.6.0
 */
class SLIDESHOW_CLASS_EventHandler
{
    /**
     * @var SLIDESHOW_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return SLIDESHOW_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }

    public function beforeWidgetPlaceDelete( OW_Event $event )
    {
        $params = $event->getParams();

        $class = $params['class'];

        if ( $class != 'SLIDESHOW_CMP_SlideshowWidget' )
        {
            return;
        }

        $uniqName = $params['uniqName'];

        $service = SLIDESHOW_BOL_Service::getInstance();
        $list = $service->getAllSlideList($uniqName);

        if ( $list )
        {
            foreach ( $list as $slide )
            {
                $service->addSlideToDeleteQueue($slide->id);
            }
        }
    }

    public function init()
    {
        $em = OW::getEventManager();

        $em->bind('widgets.before_place_delete', array($this, 'beforeWidgetPlaceDelete'));
    }
}