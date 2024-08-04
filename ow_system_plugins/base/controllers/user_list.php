<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_UserList extends OW_ActionController
{
    private $usersPerPage;
    
    /**
     * @var BOL_SeoService
     */
    protected $seoService;

    public function __construct()
    {
        parent::__construct();
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'users_main_menu_item');

        $this->setPageHeading(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
        $this->seoService = BOL_SeoService::getInstance();
    }

    public function index( $params )
    {
        OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.check.access.users.list'));
        $language = OW::getLanguage();

        $listType = empty($params['list']) ? 'latest' : strtolower(trim($params['list']));
        $this->addComponent('menu', self::getMenu($listType));

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? intval($_GET['page']) : 1;
        list($list, $itemCount) = $this->getData($listType, (($page - 1) * $this->usersPerPage), $this->usersPerPage);

        $cmp = OW::getClassInstance("BASE_Members", $list, $itemCount, $this->usersPerPage, true, $listType);

        $this->addComponent('cmp', $cmp);

        $this->assign('listType', $listType);

        $this->setMetaForListType(array("user_list" => $language->text("base", "user_list_type_".$listType)));
        $this->setDocumentKey("base_users");
    }

    /**
     * List on blocked users
     *
     * @throws AuthenticateException
     */
    public function blocked()
    {
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

    private function getData( $listKey, $first, $count )
    {
        $service = BOL_UserService::getInstance();
        return $service->getDataForUsersList($listKey, $first, $count);
    }

    public static function getMenu( $activeListType )
    {
        $language = OW::getLanguage();

        $menuArray = array(
            array(
                'label' => $language->text('base', 'user_list_menu_item_latest'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'latest')),
                'iconClass' => 'ow_ic_latest ow_dynamic_color_icon',
                'key' => 'latest',
                'order' => 1
            ),
            array(
                'label' => $language->text('base', 'user_list_menu_item_online'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'online')),
                'iconClass' => 'ow_ic_online_users ow_dynamic_color_icon',
                'key' => 'online',
                'order' => 3
            ),
            array(
                'label' => $language->text('base', 'user_search_menu_item_label'),
                'url' => OW::getRouter()->urlForRoute('users-search'),
                'iconClass' => 'ow_ic_search ow_dynamic_color_icon',
                'key' => 'search',
                'order' => 4
            )
        );

        if ( BOL_UserService::getInstance()->countFeatured() > 0 )
        {
            $menuArray[] =  array(
                'label' => $language->text('base', 'user_list_menu_item_featured'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'featured')),
                'iconClass' => 'ow_ic_push_pin ow_dynamic_color_icon',
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

        $menu = new BASE_CMP_ContentMenu();

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

    protected function setMetaForListType( array $vars ){
        $params = array(
            "sectionKey" => "base.users",
            "entityKey" => "userLists",
            "title" => "base+meta_title_user_list",
            "description" => "base+meta_desc_user_list",
            "keywords" => "base+meta_keywords_user_list",
            "vars" => $vars
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }
}

class BASE_Members extends BASE_CMP_Users
{
    private $listKey;

    public function __construct( $list, $itemCount, $usersOnPage, $showOnline, $listKey )
    {
        $this->listKey = $listKey;

        if ( $this->listKey == 'birthdays' )
        {
            $showOnline = false;
        }

        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
    }

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

      /*This part is commented since additional fields were added to users list form issue (#4353)*/
      /*$qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
        {
            $qs[] = 'birthdate';
        }

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
        {
            $qs[] = 'sex';
        }*/

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);
        $questionListResult = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_USER_INFORMATION_RENDER, array('questionList' => $questionList, 'userIdList' => $userIdList)));
        if(isset($questionListResult->getData()['questionList'])){
            $questionList = $questionListResult->getData()['questionList'];
        }
        foreach ( $questionList as $uid => $question )
        {

            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 64; $i++ )
                {
                    $val = $i+1;
                    if ( (int) $sex == $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue . ' ' . $age
                );
            }else if ( !empty($sexValue)){
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue
                );
            }else if (!empty($age) ){
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $age
                );
            }

            if ( !empty($question['birthdate']) )
            {
                $dinfo = date_parse($question['birthdate']);

                if ( $this->listKey == 'birthdays' )
                {
                    $birthdate = '';

                    if ( intval(date('d')) + 1 == intval($dinfo['day']) )
                    {
                        $questionList[$uid]['birthday'] = OW::getLanguage()->text('base', 'date_time_tomorrow');

                        $birthdate = '<span class="ow_green" style="font-weight: bold; text-transform: uppercase;">' . $questionList[$uid]['birthday'] . '</a>';
                    }
                    else if ( intval(date('d')) == intval($dinfo['day']) )
                    {
                        $questionList[$uid]['birthday'] = OW::getLanguage()->text('base', 'date_time_today');

                        $birthdate = '<span class="ow_green" style="font-weight: bold; text-transform: uppercase;">' . $questionList[$uid]['birthday'] . '</span>';
                    }
                    else
                    {
                        $birthdate = UTIL_DateTime::formatBirthdate($dinfo['year'], $dinfo['month'], $dinfo['day']);
                    }
//This part is commented due to redundancy in user birthday
//                    $fields[$uid][] = array(
//                        'label' => OW::getLanguage()->text('birthdays', 'birthday'),
//                        'value' => $birthdate
//                    );
                }
            }
        }

        return $fields;
    }
}