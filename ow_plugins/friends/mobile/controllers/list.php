<?php
/**
 * @package ow_plugins.friends.controllers
 * @since 1.0
 */
class FRIENDS_MCTRL_List extends OW_MobileActionController
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
        $username = $params['user'];
        $menu = new BASE_MCMP_ContentMenu();
        $this->addComponent('menu', $menu);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js');

        $data = $this->getData( $username, array(), true, $this->usersPerPage );
        $cmp = new BASE_MCMP_BaseUserList('latest', $data, true);
        $this->addComponent('list', $cmp);
        $this->assign('listType', 'latest');

        OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(".  json_encode(array(
                'component' => 'BASE_MCMP_BaseUserList',
                'listType' => 'latest',
                'excludeList' => $data,
                'node' => '.owm_user_list',
                'showOnline' => true,
                'count' => $this->usersPerPage,
                'responderUrl' => OW::getRouter()->urlForRoute('friends_user_lists_responder',array('user'=>$username))
            )).");
        ", 50);
    }

    public function responder( $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        $username = $params['user'];

        $excludeList = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $showOnline = empty($_POST['showOnline']) ? false : $_POST['showOnline'];
        $count = empty($_POST['count']) ? $this->usersPerPage : (int)$_POST['count'];

        $data = $this->getData( $username, $excludeList, $showOnline, $count );

        echo json_encode($data);
        exit;
    }

    protected function getData( $username, $excludeList, $showOnline, $count )
    {
        $service = FRIENDS_BOL_Service::getInstance();
        $start = count($excludeList);

        $user = BOL_UserService::getInstance()->findByUsername($username);
        if ($user == null) {
            throw new Redirect404Exception();
        }
        $userId = $user->getId();

        $data = $service->findUserFriendsInList($userId, $start, $count);
        return ($data);
    }
}