<?php
/**
 * Console section component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.friends.mobile.components
 * @since 1.6.0
 */
class FRIENDS_MCMP_ConsoleSection extends OW_MobileComponent
{
    /**
     * @var FRIENDS_BOL_Service
     */
    private $service;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = FRIENDS_BOL_Service::getInstance();
        $count = $this->service->count(null, OW::getUser()->getId(), FRIENDS_BOL_Service::STATUS_PENDING);

        if ( !$count )
        {
            $this->setVisible(false);
        }
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $limit = MBOL_ConsoleService::SECTION_ITEMS_LIMIT;
        $this->addComponent('itemsCmp', new FRIENDS_MCMP_ConsoleItems($limit));
        $this->assign('loadMore', $this->service->count(null, OW::getUser()->getId(), FRIENDS_BOL_Service::STATUS_PENDING) > $limit);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('friends')->getStaticJsUrl() . 'mobile.js');

        $params = array(
            'acceptUrl' => OW::getRouter()->urlFor('FRIENDS_MCTRL_Action', 'acceptAjax'),
            'ignoreUrl' => OW::getRouter()->urlFor('FRIENDS_MCTRL_Action', 'ignoreAjax')
        );

        $script = 'var friendsConsole = new OWM_FriendsConsole(' . json_encode($params) . ');';

        OW::getDocument()->addOnloadScript($script);
    }
}