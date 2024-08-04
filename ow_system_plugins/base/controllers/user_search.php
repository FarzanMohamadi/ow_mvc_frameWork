<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_UserSearch extends OW_ActionController
{

    public function __construct()
    {
        parent::__construct();

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'users_main_menu_item');

        $this->setPageHeading(OW::getLanguage()->text('base', 'user_search_page_heading'));
        $this->setPageTitle(OW::getLanguage()->text('base', 'user_search_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');
    }

    public function index()
    {
        OW::getDocument()->setDescription(OW::getLanguage()->text('base', 'users_list_user_search_meta_description'));

        $this->setDocumentKey('user_search_index');

        $this->addComponent('menu', BASE_CTRL_UserList::getMenu('search'));

        if (  !OW::getUser()->isAuthorized('base') && !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('base', 'search_users') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
            $this->assign('authMessage', $status['msg']);
            return;
        }

        $mainSearchForm = OW::getClassInstance('MainSearchForm', $this);
        $mainSearchForm->process($_POST);
        $this->addForm($mainSearchForm);

        $displayNameSearchForm = new DisplayNameSearchForm($this);
        $displayNameSearchForm->process($_POST);
        $this->addForm($displayNameSearchForm);

        // set meta info
        $params = array(
            "sectionKey" => "base.users",
            "entityKey" => "userSearch",
            "title" => "base+meta_title_user_search",
            "description" => "base+meta_desc_user_search",
            "keywords" => "base+meta_keywords_user_search"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function result()
    {
        if ( !OW::getUser()->isAuthorized('base', 'search_users') && !OW::getUser()->isAuthorized('base') && !OW::getUser()->isAdmin() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
            throw new AuthorizationException($status['msg']);
        }

        OW::getDocument()->setDescription(OW::getLanguage()->text('base', 'users_list_user_search_meta_description'));

        $this->setDocumentKey("user_search_result");

        $this->addComponent('menu', BASE_CTRL_UserList::getMenu('search'));

        $language = OW::getLanguage();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        $rpp = OW::getConfig()->getValue('base', 'users_count_on_page');

        $first = ($page - 1) * $rpp;

        $count = $rpp;

        $listId = OW::getSession()->get(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE);
        $list = BOL_UserService::getInstance()->findSearchResultList($listId, $first, $count);
        $itemCount = BOL_SearchService::getInstance()->countSearchResultItem($listId);

        $cmp = new BASE_CLASS_SearchResultList($list, $itemCount, $rpp, true);

        $this->addComponent('cmp', $cmp);
        $this->assign('listType', 'search');

        $searchUrl = OW::getRouter()->urlForRoute('users-search');
        $this->assign('searchUrl', $searchUrl);

        $params = array(
            "sectionKey" => "base.users",
            "entityKey" => "userLists",
            "title" => "base+meta_title_user_list",
            "description" => "base+meta_desc_user_list",
            "keywords" => "base+meta_keywords_user_list",
            "vars" => array( "user_list" => $language->text("base", "search_results") )
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    /**
     * Responder for search users by roles and questions
     */
    public function searchUserByRQResponder()
    {
        $respondArray = array();
        $extraParams = null;
        if(!OW::getRequest()->isAjax()){
            throw new Redirect404Exception();
        }
        if(!OW::getUser()->isAuthenticated() )
        {
            $respondArray['messageType'] = 'error';
            $respondArray['message'] = '_ERROR_';
            echo json_encode($respondArray);
            exit;
        }

        if (!isset($_POST['accountType']) || $_POST['accountType'] === BOL_QuestionService::ALL_ACCOUNT_TYPES )
        {
            unset($_POST['accountType']);
        }
        if(isset($_POST['extraParams']) && !empty($_POST['extraParams']))
        {
            $extraParams = $_POST['extraParams'];
            unset($_POST['extraParams']);
        }
        unset($_POST['form_name']);
        unset($_POST['csrf_token']);
        unset($_POST['csrf_hash']);
        $entityType = isset($_POST['entityType']) ? $_POST['entityType'] : null;
        $entityId = isset($_POST['entityId']) ? $_POST['entityId'] : null;
        unset($_POST['entityId']);
        unset($_POST['entityType']);
        $questionValues = $_POST;
        $user_per_page = 5;
        $page = (!empty($_POST['page']) && intval($_POST['page']) > 0 ) ? $_POST['page'] : 1;
        $first = ( $page - 1 ) * $user_per_page;
        $userService = BOL_UserService::getInstance();
        $additionalParameters = array();
        $queryParams = array();
        $additionalParameterEvent = OW_EventManager::getInstance()->trigger(new OW_Event('search.additional.parameter',['entityType'=>$entityType, 'entityId' => $entityId]));

        if(isset($additionalParameterEvent->getData()['where'])) {
            $additionalParameters['where'] = $additionalParameterEvent->getData()['where'];
            $queryParams = $additionalParameterEvent->getData()['whereParams'];
            }

        $totalUsersCount = (int)$userService->countUsersByQuestionValues($questionValues,true,$additionalParameters,$queryParams);

        //TODO $user_per_page must be used instead of $totalUsersCount
        $users = $userService->findUserIdListByQuestionValues($questionValues, $first, $totalUsersCount, true,$additionalParameters,$queryParams);
        $respondArray['users'] = $users;
        $respondArray['totalUsersCount'] = $totalUsersCount;
        $respondArray['page'] = $page;
        $respondArray['entityType'] = $entityType;
        $respondArray['entityId'] = $entityId;
        $respondArray['questionValues'] = $questionValues;
        $respondArray['extraParams'] = $extraParams;
        exit(json_encode($respondArray));
    }

    /**
     * Responder for load more users by roles and questions
     */
    public function loadMoreUsersByRQ()
    {
        $respondArray = array();

        if(!OW::getRequest()->isAjax()){
            throw new Redirect404Exception();
        }
        if(!OW::getUser()->isAuthenticated() )
        {
            $respondArray['messageType'] = 'error';
            $respondArray['message'] = '_ERROR_';
            echo json_encode($respondArray);
            exit;
        }

        //$roleId = $_POST['roleId'];
        $user_per_page = 5;
        $page = $_POST['page'];
        $first = ( $page - 1 ) * $user_per_page;
        $userService = BOL_UserService::getInstance();
        unset($_POST['form_name']);
        unset($_POST['csrf_token']);
        unset($_POST['csrf_hash']);
        $entityType = isset($_POST['entityType']) ? $_POST['entityType'] : null;
        $entityId = isset($_POST['entityId']) ? $_POST['entityId'] : null;
        unset($_POST['entityId']);
        unset($_POST['entityType']);
        $page = (!empty($_POST['page']) && intval($_POST['page']) > 0 ) ? $_POST['page'] : 1;
        unset($_POST['page']);
        if (!isset($_POST['accountType']) || $_POST['accountType'] === BOL_QuestionService::ALL_ACCOUNT_TYPES )
        {
            unset($_POST['accountType']);
        }
        $questionValues = $_POST;
        $userIds = $userService->findUserIdListByQuestionValues($questionValues, $first, $user_per_page, OW::getUser()->isAdmin());
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds, true, false, false);
        $respondArray['avatars'] = $avatars;

        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
        $usernames = BOL_UserService::getInstance()->getUserNamesForList($userIds);
        $orderdList = BOL_UserService::getInstance()->getRecentlyActiveOrderedIdList($userIds);

        $idList = array();

        foreach( $orderdList as $list )
        {
            $idList[] =  $list['id'];
        }

        $arrayToAssign = array();
        $jsArray = array();

        foreach ( $idList as $id )
        {
            $linkId = UTIL_HtmlTag::generateAutoId('user-select');

            if ( !empty($avatars[$id]) )
            {
                $avatars[$id]['url'] = 'javascript://';
            }

            $arrayToAssign[$id] = array(
                'id' => $id,
                'title' => empty($displayNames[$id]) ? '_DISPLAY_NAME_' : $displayNames[$id],
                'linkId' => $linkId,
                'username' => $usernames[$id]
            );

            $jsArray[$id] = array(
                'linkId' => $linkId,
                'userId' => $id
            );
        }
        $respondArray['arrayToAssign'] = $arrayToAssign;
        $respondArray['jsArray'] = $jsArray;
        $respondArray['page'] = $page;

        $contentHtmlArr = $this->userLoadMoreHtmlContent($arrayToAssign,$avatars);
        $respondArray['contentHtmlArr'] = $contentHtmlArr;
        exit(json_encode($respondArray));
    }

    private function userLoadMoreHtmlContent($arrayToAssign,$avatars)
    {
        $contentHtmlArr=array();
            foreach ($arrayToAssign as $data)
            {
                $contentHtml=
                    '
                    <div class="ow_user_list_item clearfix ow_item_set2  colorful_avatar_1  " id="'.$data["linkId"].'">
                        <div class="ow_user_list_picture">
                            <div class="colorful_avatar_container colorful_avatar_1" style="display: inline-block;">
                                <div class="ow_avatar">
                                    <a>
                                        <img src="'.$avatars[$data["id"]]["src"].'">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="ow_user_list_data">
                        '.$data["title"].'
                        <div>
                        </div>
                        </div>
                   </div>
                    ';
                $contentHtmlArr[$data['id']]=$contentHtml;
            }

            return $contentHtmlArr;
    }


}

class MainSearchForm extends BASE_CLASS_UserQuestionForm
{
    const SUBMIT_NAME = 'MainSearchFormSubmit';

    const FORM_SESSEION_VAR = 'MAIN_SEARCH_FORM_DATA';

    public $controller;
    public $accountType;
    public $displayAccountType = false;
    public $displayMainSearch = true;

    /*
     * @var OW_ActionController $controller
     * 
     */

    public function __construct( $controller )
    {
        parent::__construct('MainSearchForm');

        $this->controller = $controller;

        $questionService = BOL_QuestionService::getInstance();
        $language = OW::getLanguage();

        $this->setId('MainSearchForm');


        $questionData = OW::getSession()->get(self::FORM_SESSEION_VAR);

        if ( $questionData === null )
        {
            $questionData = array();
        }

        $accounts = $this->getAccountTypes();

        $accountList = array();
        $accountList[BOL_QuestionService::ALL_ACCOUNT_TYPES] = OW::getLanguage()->text('base', 'questions_account_type_' . BOL_QuestionService::ALL_ACCOUNT_TYPES);

        foreach ( $accounts as $key => $account )
        {
            $accountList[$key] = $account;
        }

        $keys = array_keys($accountList);

        $this->accountType = $keys[0];

        if ( isset($questionData['accountType']) && in_array($questionData['accountType'], $keys) )
        {
            $this->accountType = $questionData['accountType'];
        }

        if ( count($accounts) > 1 )
        {
            $this->displayAccountType = true;

            $accountType = new Selectbox('accountType');
            $accountType->setLabel(OW::getLanguage()->text('base', 'questions_question_account_type_label'));
            $accountType->setRequired();
            $accountType->setOptions($accountList);
            $accountType->setValue($this->accountType);
            $accountType->setHasInvitation(false);

            $this->addElement($accountType);
        }

        $questions = $questionService->findSearchQuestionsForAccountType($this->accountType);

        $mainSearchQuestion = array();
        $questionNameList = array();

        foreach ( $questions as $key => $question )
        {
            $sectionName = $question['sectionName'];
            $mainSearchQuestion[$sectionName][] = $question;
            $questionNameList[] = $question['name'];
            $questions[$key]['required'] = '0';
        }
        if (!empty($mainSearchQuestion)){
            $submit = new Submit(self::SUBMIT_NAME);
            $submit->setValue(OW::getLanguage()->text('base', 'user_search_submit_button_label'));
            $this->addElement($submit);
        }

        $questionValueList = $questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $this->addQuestions($questions, $questionValueList, $questionData);

        $controller->assign('questionList', $mainSearchQuestion);
        $controller->assign('displayAccountType', $this->displayAccountType);
    }

    public function process( $data )
    {
        if ( OW::getRequest()->isPost() && !OW::getRequest()->isAjax() && isset($data['form_name']) && $data['form_name'] === $this->getName() )
        {
            OW::getSession()->set(self::FORM_SESSEION_VAR, $data);

            if ( isset($data[self::SUBMIT_NAME]) && $this->isValid($data) && !OW::getRequest()->isAjax() )
            {
                if ( !OW::getUser()->isAuthorized('base', 'search_users') )
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');;
                    OW::getFeedback()->warning($status['msg']);
                    $this->controller->redirect();
                }
                
                if ( isset($data['accountType']) && $data['accountType'] === BOL_QuestionService::ALL_ACCOUNT_TYPES )
                {
                    unset($data['accountType']);
                }

                $userIdList = BOL_UserService::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE, OW::getUser()->isAdmin());
                $listId = 0;

                if ( count($userIdList) > 0 )
                {
                    $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
                }

                OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

                $this->controller->redirect(OW::getRouter()->urlForRoute("users-search-result", array()));
            }
            $this->controller->redirect(OW::getRouter()->urlForRoute("users-search"));
        }
    }

    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        return BOL_QuestionService::getInstance()->getSearchPresentationClass($presentation, $questionName, $configs);
    }

    protected function setFieldValue( $formField, $presentation, $value )
    {

    }
}

class DisplayNameSearchForm extends BASE_CLASS_UserQuestionForm
{
    const SUBMIT_NAME = 'DisplayNameSearchFormSubmit';

    public $controller;
    public $accountType;
    public $displayAccountType = false;
    public $displayMainSearch = true;

    /*
     * @var OW_ActionController $controller
     *
     */

    public function __construct( $controller )
    {
        parent::__construct('DisplayNameSearchForm');

        $this->controller = $controller;

        $questionService = BOL_QuestionService::getInstance();
        $language = OW::getLanguage();

        $this->setId('DisplayNameSearchForm');

        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue(OW::getLanguage()->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);

        $questionName = OW::getConfig()->getValue('base', 'display_name_question');

        $question = $questionService->findQuestionByName($questionName);

        $questionPropertyList = array();
        foreach ( $question as $property => $value )
        {
            $questionPropertyList[$property] = $value;
        }

        $this->addQuestions(array($questionName => $questionPropertyList), array(), array());

        $controller->assign('displayNameQuestion', $questionPropertyList);
    }

    public function process( $data )
    {
        if ( OW::getRequest()->isPost() && isset($data[self::SUBMIT_NAME]) && $this->isValid($data) && !$this->isAjax() )
        {
            if ( !OW::getUser()->isAuthorized('base', 'search_users') && !OW::getUser()->isAuthorized('base') && !OW::getUser()->isAdmin())
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
                OW::getFeedback()->warning($status['msg']);
                $this->controller->redirect();
            }
            
            $userIdList = BOL_UserService::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE);
            $listId = 0;

            if ( count($userIdList) > 0 )
            {
                $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
            }

            OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

            $this->controller->redirect(OW::getRouter()->urlForRoute("users-search-result", array()));
        }
    }
}