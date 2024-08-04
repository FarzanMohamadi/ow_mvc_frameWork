<?php
/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmterms.classes
 * @since 1.0
 */
class FRMTERMS_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FRMTERMS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTERMS_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var FRMTERMS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = FRMTERMS_BOL_Service::getInstance();
    }

    public function genericInit()
    {
        $service = FRMTERMS_BOL_Service::getInstance();
        $em = OW::getEventManager();
        $em->bind('notifications.collect_actions', array($service, 'on_notify_actions'));
        $em->bind(FRMEventManager::ON_RENDER_JOIN_FORM, array($service, 'on_render_join_form'));
        OW::getEventManager()->bind("base.sitemap.get_urls", array($this, "onSitemapGetUrls"));
    }

    /**
     * Get sitemap urls
     *
     * @param OW_Event $event
     * @return void
     */
    public function onSitemapGetUrls(OW_Event $event)
    {
        $params = $event->getParams();
        $urls = array();
        switch ($params['entity']) {
            case 'section' :
                $itemsIds = FRMTERMS_BOL_Service::getInstance()->getClientSections();
                foreach ($itemsIds as $itemId) {
                    $urls[] = $itemId["url"];
                }
                break;
        }
        if ($urls) {
            $event->setData($urls);
        }
    }
}