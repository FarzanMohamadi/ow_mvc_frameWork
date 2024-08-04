<?php
/**
 * Console section component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.notifications.mobile.components
 * @since 1.6.0
 */
class NOTIFICATIONS_MCMP_ConsoleSection extends OW_MobileComponent
{
    /**
     * @var NOTIFICATIONS_BOL_Service
     */
    private $service;

    const SECTION_ITEMS_LIMIT = 20;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = NOTIFICATIONS_BOL_Service::getInstance();
        $count = $this->service->findNotificationCount(OW::getUser()->getId());

        if ( !$count )
        {
            $this->setVisible(false);
        }
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $limit = self::SECTION_ITEMS_LIMIT;
        $this->addComponent('itemsCmp', new NOTIFICATIONS_MCMP_ConsoleItems($limit));
        $this->assign('loadMore', $this->service->findNotificationCount(OW::getUser()->getId()) > $limit);

        //Issa Annamoradnejad
        //added button to "view all"
        if (OW::getRequest()->isAjax()) {
            OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("notifications")->getStaticCssUrl() . 'notification.css');
            if(FRMSecurityProvider::checkPluginActive('frmmainpage', true) && !FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('notifications')) {
                $viewAll = array(
                    'label' => OW::getLanguage()->text('notifications', 'view_all'),
                    'url' => OW::getRouter()->urlForRoute('frmmainpage.notifications')
                );
                $this->assign('viewAll', $viewAll);
            }else {
                if(FRMSecurityProvider::checkPluginActive('frmmobilesupport', true)) {
                    $viewAll = array(
                        'label' => OW::getLanguage()->text('notifications', 'view_all'),
                        'url' => OW::getRouter()->urlForRoute('notifications-notifications')
                    );
                    $this->assign('viewAll', $viewAll);
                }
            }
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('notifications')->getStaticUrl() . 'js/mobile.js');

        $params = array('limit' => $limit);
        $script = 'var notificationsConsole = new OWM_NotificationsConsole(' . json_encode($params) . ');';

        OW::getDocument()->addOnloadScript($script);
    }
}