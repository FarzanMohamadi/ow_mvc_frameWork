<?php
class BASE_CLASS_ConsoleEventHandler
{
    /**
     * Class instance
     *
     * @var BASE_CLASS_ConsoleEventHandler
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BASE_CLASS_ConsoleEventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function collectItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        $language = OW::getLanguage();
        $router = OW::getRouter();

        if ( OW::getUser()->isAuthenticated() )
        {
            // Admin menu
            $userInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));

            if ( OW::getUser()->isAdmin() )
            {
                $item = new BASE_CMP_ConsoleDropdownMenu($language->text('admin', 'main_menu_admin'));
                $item->setIconSrc(OW::getThemeManager()->getSelectedTheme()->getStaticImagesUrl(OW::getApplication()->isMobile()) . 'admin_user.png');
                $item->setUrl($router->urlForRoute('admin_default'));
                $item->addItem('head', array('label' => $language->text('admin', 'console_item_admin_dashboard'), 'url' => $router->urlForRoute('admin_default')));
                $item->addItem('main', array('label' => $language->text('admin', 'console_item_manage_theme'), 'url' => $router->urlForRoute('admin_themes_edit')));
                $item->addItem('main', array('label' => $language->text('admin', 'console_item_manage_users'), 'url' => $router->urlForRoute('admin_users_browse')));
                $item->addItem('main', array('label' => $language->text('admin', 'console_item_manage_pages'), 'url' => $router->urlForRoute('admin_pages_main')));
                $item->addItem('main', array('label' => $language->text('admin', 'console_item_manage_plugins'), 'url' => $router->urlForRoute('admin_plugins_installed')));

                $event->addItem($item, 2);
            }

            /**
             * My Profile Menu
             *
             * @var $item BASE_CMP_MyProfileConsoleItem
             */
            $item = OW::getClassInstance("BASE_CMP_MyProfileConsoleItem");
            $item->setIconSrc($userInfo[OW::getUser()->getId()]['src']);
            if(BOL_AvatarService::getInstance()->userHasAvatar(OW::getUser()->getId())){
                $item->addClass('console_my_profile');
            }else{
                $item->addClass('console_my_profile_no_avatar');
                $avatarImageInfo = BOL_AvatarService::getInstance()->getAvatarInfo(OW::getUser()->getId());
                $item->addClass('colorful_avatar_' . $avatarImageInfo['digit']);
            }
            $event->addItem($item, 1);
        }
        else
        {
            $buttonListEvent = new BASE_CLASS_EventCollector(BASE_CMP_ConnectButtonList::HOOK_REMOTE_AUTH_BUTTON_LIST);
            OW::getEventManager()->trigger($buttonListEvent);
            $buttonList = $buttonListEvent->getData();

            $iconListMarkup = '';

            foreach ( $buttonList as $button )
            {
                $iconListMarkup .= '<span class="ow_ico_signin ' . $button['iconClass'] . '"></span>';
            }

            $cmp = new BASE_CMP_SignIn(true);
            $signInMarkup = '<div style="display:none"><div id="base_cmp_floatbox_ajax_signin">' . $cmp->render() . '</div></div>';

            $item = new BASE_CMP_ConsoleItem();
            $item->setControl($signInMarkup . '<span class="ow_signin_label' . (empty($buttonList) ? '' : ' ow_signin_delimiter') . '">' . $language->text('base', 'sign_in_submit_label') . '</span>' . $iconListMarkup);

            $eventData = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_SIGNIN_BUTTON_ADD, array('item' => $item)));
            $signUpText = $language->text('base', 'console_item_sign_up_label');
            if(isset($eventData->getData()['sso_enabled'])){
                $signUpText = $language->text('frmsso', 'sign_up_button');
                if(isset($eventData->getData()['show_sign_in_button'])) {
                    $event->addItem($eventData->getData()['sign_in_button'], 2);
                }
                if (isset($eventData->getData()['show_sign_out_button'])) {
                    $item2 = new BASE_CMP_ConsoleButton(OW::getLanguage()->text('base', 'console_item_label_sign_out'), OW::getRouter()->urlForRoute('base_sign_out'));
                    $event->addItem($item2, 3);
                }
                if(!OW::getConfig()->getValue('base', 'disable_signup_button'))
                {
                    if (isset($eventData->getData()['show_sign_up_button'])) {
                        $item = new BASE_CMP_ConsoleButton($signUpText, OW::getRouter()->urlForRoute('base_join'));
                        $event->addItem($item, 1);
                    }
                }
            }
            else if(isset($eventData->getData()['frmmobileAccountSign-in']))
            {
                $event->addItem($eventData->getData()['frmmobileAccountSign-in'], 2);
            }
            else{
                $event->addItem($item, 2);
                OW::getDocument()->addOnloadScript("$('#".$item->getUniqId()."').click(function(){new OW_FloatBox({ \$contents: $('#base_cmp_floatbox_ajax_signin')});});");
                if(!OW::getConfig()->getValue('base', 'disable_signup_button'))
                {
                    $item = new BASE_CMP_ConsoleButton($signUpText, OW::getRouter()->urlForRoute('base_join'));
                    $event->addItem($item, 1);
                }
            }

        }
        /**
         * @autor Mohammad Agha Abbasloo
         * set order of lang component in console to last order
         */
        $item = new BASE_CMP_ConsoleSwitchLanguage();
        $event->addItem($item, 10);
    }

    public function defaultPing( BASE_CLASS_ConsoleDataEvent $event )
    {
        $event->setItemData('console', array(
            'time' => time()
        ));
    }

    public function ping( OW_Event $originalEvent )
    {
        $data = $originalEvent->getParams();

        $event = new BASE_CLASS_ConsoleDataEvent('console.ping', $data, $data);
        $this->defaultPing($event);

        OW::getEventManager()->trigger($event);

        $data = $event->getData();
        $originalEvent->setData($data);
    }

    public function init()
    {
        OW::getEventManager()->bind(BASE_CTRL_Ping::PING_EVENT . '.consoleUpdate', array($this, 'ping'));
        OW::getEventManager()->bind('console.collect_items', array($this, 'collectItems'));
    }
}