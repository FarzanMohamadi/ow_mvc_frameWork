<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_MCTRL_UserList extends OW_MobileActionController
{
    private $usersPerPage;

    public function __construct()
    {
        parent::__construct();
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'users_main_menu_item');

        $this->setPageHeading(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageTitle(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
    }

    public function index( $params )
    {
        OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.check.access.users.list'));
        $listType = empty($params['list']) ? 'latest' : strtolower(trim($params['list']));
        $language = OW::getLanguage();
        $this->addComponent('menu', self::getMenu($listType));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js');

        $data = $this->getData( $listType, array(), true, $this->usersPerPage );
        $cmp = new BASE_MCMP_BaseUserList($listType, $data, true);
        $this->addComponent('list', $cmp);
        $this->assign('listType', $listType);

        OW::getDocument()->addOnloadScript(" 
            window.mobileUserList = new OW_UserList(".  json_encode(array(
                    'component' => 'BASE_MCMP_BaseUserList',
                    'listType' => $listType,
                    'excludeList' => $data,
                    'node' => '.owm_user_list',
                    'showOnline' => true,
                    'count' => $this->usersPerPage,
                    'responderUrl' => OW::getRouter()->urlForRoute('base_user_lists_responder')
                )).");
        ", 50);
    }

    public function responder( $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        
        $listKey = empty($_POST['list']) ? 'latest' : strtolower(trim($_POST['list']));
        $excludeList = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $showOnline = empty($_POST['showOnline']) ? false : $_POST['showOnline'];
        $count = empty($_POST['count']) ? $this->usersPerPage : (int)$_POST['count'];

        $data = $this->getData( $listKey, $excludeList, $showOnline, $count );

        echo json_encode($data);
        exit;
    }

    protected function getData( $listKey, $excludeList = array(), $showOnline, $count )
    {
        $list = array();

        $start = count($excludeList);

        while ( $count > count($list) )
        {
            $service = BOL_UserService::getInstance();
            $tmpList =  $service->getDataForUsersList($listKey, $start, $count);
            $itemList = $tmpList[0];
            $itemCount = $tmpList[1];

            if ( empty($itemList)  )
            {
                break;
            }
            
            foreach ( $itemList as $key => $item )
            {
                if ( count($list) == $count )
                {
                    break;
                }

                if ( !in_array($item->id, $excludeList) )
                {
                    $list[] = $item->id;
                }
            }
            
            $start += $count;

            if ( $start >= $itemCount )
            {
                break;
            }
        }

        return $list;
    }

    public static function getMenu( $activeListType )
    {
        $language = OW::getLanguage();

        $menuArray = array(
            array(
                'label' => $language->text('base', 'user_list_menu_item_latest'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'latest')),
                'iconClass' => 'ow_ic_clock',
                'key' => 'latest',
                'order' => 1
            ),
            array(
                'label' => $language->text('base', 'user_list_menu_item_online'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'online')),
                'iconClass' => 'ow_ic_push_pin',
                'key' => 'online',
                'order' => 3
            ),
            /* array(
                'label' => $language->text('base', 'user_search_menu_item_label'),
                'url' => OW::getRouter()->urlForRoute('users-search'),
                'iconClass' => 'ow_ic_lens',
                'key' => 'search',
                'order' => 4
            ) */
        );

        if ( BOL_UserService::getInstance()->countFeatured() > 0 )
        {
            $menuArray[] =  array(
                'label' => $language->text('base', 'user_list_menu_item_featured'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'featured')),
                'iconClass' => 'ow_ic_push_pin',
                'key' => 'featured',
                'order' => 2
            );
        }

        $event = new BASE_CLASS_EventCollector('base.add_user_list');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        if ( !empty($data) )
        {
            $menuArray = array_merge($menuArray, $data);
        }

        $menu = new BASE_MCMP_ContentMenu();

        foreach ( $menuArray as $item )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setLabel($item['label']);
            $menuItem->setIconClass($item['iconClass']);
            $menuItem->setUrl($item['url']);
            $menuItem->setKey($item['key']);
            $menuItem->setOrder(empty($item['order']) ? 999 : $item['order']);
            $menu->addElement($menuItem);

            if ( $activeListType == $item['key'] )
            {
                $menuItem->setActive(true);
            }
        }

        return $menu;
    }

    /**
     * List on blocked users
     *
     * @throws AuthenticateException
     */
    public function blocked() {
        $userId = OW::getUser()->getId();
        $this->setPageTitle(OW::getLanguage()->text('base', 'my_blocked_users'));
        $this->setPageHeading(OW::getLanguage()->text('base', 'my_blocked_users'));
        $this->setDocumentKey('my_blocked_users');
        if ( !OW::getUser()->isAuthenticated() || $userId === null )
        {
            throw new AuthenticateException();
        }

        // unblock action
        if ( OW::getRequest()->isPost() && !empty($_POST['userId']) )
        {
            BOL_UserService::getInstance()->unblock($_POST['userId']);

            // reload the current page
            OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_profile_unblocked'));

            $this->redirect();
        }

        // process pagination params
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = $this->usersPerPage;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $service = BOL_UserService::getInstance();
        $listCount = $service->countBlockedUsers($userId);

        $blockedList = $listCount
            ? $service->findBlockedUserList($userId, $first, $count)
            : array();

        $listCmp = new BASE_CMP_BlockedUserList(BOL_UserService::
        getInstance()->findUserListByIdList($blockedList), $listCount, $perPage);

        // init components
        $this->addComponent('listCmp', $listCmp);
    }
}

