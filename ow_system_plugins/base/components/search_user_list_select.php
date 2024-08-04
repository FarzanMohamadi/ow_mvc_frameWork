<?php
/**
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_SearchUserListSelect extends OW_Component
{
    const USER_PER_PAGE = 5;

    /**
     * @var string
     */
    private $buttonLabel;
    /**
     * @var string
     */
    private $countLabel;
    /**
     * @var string
     */
    private $headingLabel;
    /**
     * @var array
     */
    private $idList;

    /**
     * @var array
     */
    private $extraParams;
    /**
     * BASE_CMP_SearchUserListSelect constructor.
     * @param array $idList
     * @param null $entityType
     * @param null $entityId
     * @param null $selectedAccountType
     * @param null $questionValues
     * @param array $extraParams
     */
    public function __construct(array $idList,$entityType= null, $entityId = null, $selectedAccountType = null,$questionValues = null,$extraParams = null)
    {
        parent::__construct();

        $this->countLabel = OW::getLanguage()->text('base', 'avatar_user_list_select_count_label');
        $this->buttonLabel = OW::getLanguage()->text('base', 'avatar_user_list_select_button_label');
        $this->idList = $idList;
        $this->extraParams = $extraParams;
        if ( !empty($langs['buttonLabel']) )
        {
            $this->buttonLabel = $langs['buttonLabel'];
        }

        if ( isset($langs) && array_key_exists('countLabel', $langs) )
        {
            $this->countLabel = $langs['countLabel'];
        }

        if ( !empty($langs['headingLabel']) )
        {
            $this->headingLabel = $langs['headingLabel'];
        }

        $mainSearchForm = OW::getClassInstance('SearchUserForm', ['controller'=>$this,'entityType'=>$entityType,'entityId'=>$entityId,'selectedAccountType'=>$selectedAccountType,'questionValues'=>$questionValues,'extraParams'=> $extraParams]);
       // $mainSearchForm->process($_POST);
        $this->addForm($mainSearchForm);


        $filterUrl = OW::getRouter()->urlForRoute('users-search-by-rq-result');
        $loadMoreUrl = OW::getRouter()->urlForRoute('users-search-load-more');
        $this->assign('filterUrl',$filterUrl);
        $this->assign('loadMoreUrl',$loadMoreUrl);
    }

    public function getUsersByRoleId($page,$roleId)
    {
        $page = ( $page === null ) ? 1 : (int) $page;
        $first = ( $page - 1 ) * self::USER_PER_PAGE;
        $userService = BOL_UserService::getInstance();
        $users = $userService->findListByRoleId($roleId,$first,self::USER_PER_PAGE);
        return $users;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $contexId = UTIL_HtmlTag::generateAutoId('cmp');
        $this->assign('contexId', $contexId);

        if ( empty($this->idList) )
        {
            return;
        }

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($this->idList, true, false, false);
        $this->assign('avatars', $avatars);

        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($this->idList);
        $usernames = BOL_UserService::getInstance()->getUserNamesForList($this->idList);
        $orderdList = BOL_UserService::getInstance()->getRecentlyActiveOrderedIdList($this->idList);

        $this->idList = array();

        foreach( $orderdList as $list )
        {
            $this->idList[] =  $list['id'];
        }

        $arrayToAssign = array();
        $jsArray = array();

        foreach ( $this->idList as $id )
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

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'avatar_user_select.js');
        OW::getDocument()->addOnloadScript("
            var cmp = new AvatarUserSelect(" . json_encode($jsArray) . ", '" . $contexId . "');
            cmp.init();  ");
        OW::getDocument()->addOnloadScript("
$('#instant_search_txt_input').on('change input',function () {
    var q = $(this).val();
    $('.asl_users .ow_user_list_item').each(function(i,obj){
        if(obj.innerText.indexOf(q)>=0)
            obj.style.display = 'block'
        else
            obj.style.display = 'none'
    });
});
        ");

        OW::getLanguage()->addKeyForJs('base', 'avatar_user_select_empty_list_message');

        $this->assign('users', $arrayToAssign);

        $langs = array(
            'countLabel' => $this->countLabel,
            'startCountLabel' => (!empty($this->countLabel) ? str_replace('#count#', '0', $this->countLabel) : null ),
            'buttonLabel' => $this->buttonLabel,
            'startButtonLabel' => str_replace('#count#', '0', $this->buttonLabel)
        );
        $this->assign('langs', $langs);
    }
}


class SearchUserForm extends BASE_CLASS_UserQuestionForm
{
    public $controller;
    public $accountType;
    public $displayAccountType = false;
    public $displayMainSearch = true;
    /*
     * @var OW_ActionController $controller
     *
     */

    public function __construct(array $params )
    {
        parent::__construct('SearchUserForm');

        $this->controller = $params['controller'];

        $questionService = BOL_QuestionService::getInstance();
        $language = OW::getLanguage();

        $this->setId('SearchUserForm');


        $questionData = isset($params['questionValues']) ? $params['questionValues'] : null;

        if ( $questionData === null )
        {
            $questionData = array();
        }

        $accounts = $this->getAccountTypes();

        $accountList = array();
        $accountList[BOL_QuestionService::ALL_ACCOUNT_TYPES] = OW::getLanguage()->text(
            'base',
            'questions_account_type_'.BOL_QuestionService::ALL_ACCOUNT_TYPES
        );

        foreach ($accounts as $key => $account) {
            $accountList[$key] = $account;
        }

        $keys = array_keys($accountList);

        $this->accountType = $keys[0];

        if(isset($params['selectedAccountType']))
        {
            $this->accountType = $params['selectedAccountType'];
        }
        if (isset($questionData['accountType']) && in_array($questionData['accountType'], $keys)) {
            $this->accountType = $questionData['accountType'];
        }

        if (count($accounts) > 1) {
            $this->displayAccountType = true;

            $accountType = new Selectbox('accountType');
            $accountType->setLabel(OW::getLanguage()->text('base', 'questions_question_account_type_label'));
            $accountType->setRequired();
            $accountType->setId('searchAccountTypeId');
            $accountType->setOptions($accountList);
            $accountType->setValue($this->accountType);
            $accountType->setHasInvitation(false);

            $this->addElement($accountType);
        }

        if(isset($params['entityType']))
        {
            $entityTypeHidden = new HiddenField('entityType');
            $entityTypeHidden->setValue($params['entityType']);
            $entityTypeHidden->setId($params['entityType']);
            $this->addElement($entityTypeHidden);
        }
        if(isset($params['entityId']))
        {

            $entityIdHidden = new HiddenField('entityId');
            $entityIdHidden->setValue($params['entityId']);
            $this->addElement($entityIdHidden);

        }

        if(isset($params['extraParams']))
        {
            $extraParamsHidden = new HiddenField('extraParams');
            $extraParamsHidden->setValue($params['extraParams']);
            $extraParamsHidden->setId('extraParams');
            $this->addElement($extraParamsHidden);
        }

        $questions = $questionService->findSearchQuestionsForAccountType($this->accountType);

        $mainSearchQuestion = array();
        $questionNameList = array();

        foreach ($questions as $key => $question) {
            $sectionName = $question['sectionName'];
            $mainSearchQuestion[$sectionName][] = $question;
            $questionNameList[] = $question['name'];
            $questions[$key]['required'] = '0';
        }

        // TODO add something to disable or enable search in html
        if (empty($mainSearchQuestion)) {


        }

        $questionValueList = $questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $this->addQuestions($questions, $questionValueList, $questionData);

        $params['controller']->assign('questionList', $mainSearchQuestion);
        $params['controller']->assign('displayAccountType', $this->displayAccountType);
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
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
                    OW::getFeedback()->warning($status['msg']);
                    $this->controller->redirect();
                }

                if ( isset($data['accountType']) && $data['accountType'] === BOL_QuestionService::ALL_ACCOUNT_TYPES )
                {
                    unset($data['accountType']);
                }

                $userIdList = BOL_UserService::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE, true);
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


}