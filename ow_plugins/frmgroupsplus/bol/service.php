<?php
/**
 * 
 * All rights reserved.
 */



/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus
 * @since 1.0
 */
class FRMGROUPSPLUS_BOL_Service
{
    const SET_MOBILE_USER_MANAGER_STATUS = 'frmgroupsplus.set.mobile.user.manager.status';
    const SET_USER_MANAGER_STATUS = 'frmgroupsplus.set.user.manager.status';
    const DELETE_USER_AS_MANAGER = 'frmgroupsplus.delete.user.as.manager';
    const DELETE_FILES = 'frmgroupsplus.delete.files';
    const ADD_FILE_WIDGET = 'frmgroupsplus.add.file.widget';
    const PENDING_USERS_COMPONENT = 'frmgroupsplus.pending.users.component';
    const CHECK_USER_MANAGER_STATUS = 'frmgroupsplus.check.user.manager.status';
    const ON_UPDATE_GROUP_STATUS = 'frmgroupsplus.on.update.group.status';
    const CHECK_CAN_INVITE_ALL = 'frmgroupsplus.check.can.invite.all';
    const ADD_USERS_AUTOMATICALLY = 'frmgroupsplus.add.users.automatically';
    const SET_CHANNEL_GROUP = 'frmgroupsplus.set.channel.group';
    const SET_CHANNEL_FOR_GROUP = 'frmgroupsplus.set.channel.for.group';
    const ON_CHANNEL_ADD_WIDGET = 'frmgroupsplus.on.channel.add.widget';
    const ON_CHANNEL_LOAD = 'frmgroupsplus.on.channel.load';

    const WCC_CHANNEL = 'channel';
    const WCC_GROUP = 'group';

    const WCU_MANAGERS= 'manager';
    const WCU_PARTICIPANT = 'participant';

    private static $classInstance;

    private  $groupInformationDao;
    private  $groupManagersDao;
    private  $categoryDao;
    private  $groupFileDao;
    private  $channelService;
    private  $groupSettingDao;
    const STATUS_ACTIVE = "active";
    const STATUS_APPROVAL = "approval";
    const STATUS_SUSPENDED = "suspended";
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    private function __construct()
    {
        $this->groupInformationDao = FRMGROUPSPLUS_BOL_GroupInformationDao::getInstance();
        $this->groupManagersDao = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance();
        $this->categoryDao = FRMGROUPSPLUS_BOL_CategoryDao::getInstance();
        $this->groupFileDao = FRMGROUPSPLUS_BOL_GroupFilesDao::getInstance();
        $this->channelService = FRMGROUPSPLUS_BOL_ChannelService::getInstance();
        $this->groupSettingDao = FRMGROUPSPLUS_BOL_GroupSettingDao::getInstance();
    }

    public static function getForcedGroupSubmitFormJS()
    {
        return "$(document).ready(function () {
            $('input[name=\"addNewForcedGroupButton\"], input[name=\"editNewForcedGroupButton\"]').on('click', function (e) {
                $(e.target).addClass(\"ow_inprogress\");
                handleForcedGroupFormSubmission(e);
            });
            
            $('form[name=\"mainForm\"]').on('submit', function (e) {
                e.preventDefault();
                handleForcedGroupFormSubmission(e);
            });
        });
        
        $('input[id^=\"select_all_options_\"]').on('change', function (e) {
            if  ($(e.target).is(':checked'))
                $(e.target).closest('tr').find('input[type=\"checkbox\"]').attr(\"checked\", \"checked\");
            else
                $(e.target).closest('tr').find('input[type=\"checkbox\"]').removeAttr(\"checked\");
        });
        
        function handleForcedGroupFormSubmission(e) {
             var profileQuestionFiltersList = {}
             var all_inputs = $('input[name^=\"profileQuestionFilter\"]');
             $.each(all_inputs, function(index, value){
                 if ($(value).is(\":checked\"))
                 profileQuestionFiltersList[$(value).attr('name')] = 1
             });
             var gId = $(e.target).closest('form').find('input[name=\"gId\"]').val()
             $.ajax( {
                 url: '" . OW::getRouter()->urlFor('FRMGROUPSPLUS_CTRL_ForcedGroups', 'addAllUsersToGroup') . "',
                 type: 'POST',
                 data: { gId: gId, profileQuestionFiltersList: profileQuestionFiltersList, forcedStay: $('input[name=\"forcedStay\"]').is(\":checked\") },
                 dataType: 'json',
                 success: function( result )
                 {           
                     $(e.target).removeClass(\"ow_inprogress\");         
                     if  (result['result'] === 'success'){
                         OW.info(result['message']);
                         if (result['refresh'])
                             if ($(e.target).attr('name') === 'addNewForcedGroupButton')
                                 window.location.reload();
                             else
                                 window.location = result['forcedGroupsURL'];
                     }
                     else
                         OW.error(result['message']);
                 },
                 error : function(result){
                                $(e.target).removeClass(\"ow_inprogress\"); 
                                OW.error(result['message']);
                 }     
             });       
        }
        ";
    }

    public function addGroupFilterForm(OW_Event $event)
    {
        $params = $event->getParams();
        $tab = 'latest';
        $categoryStatus=null;
        $searchTitle=null;
        $status = self::STATUS_ACTIVE;
        $url =null;
       if (isset($params['tab'])) {
            $tab = $params['tab'];
        }
        if (isset($params['categoryStatus']) && !empty(trim($params['categoryStatus']))) {
            $categoryStatus = $params['categoryStatus'];
        }
        if (isset($params['searchTitle']) && !empty(trim($params['searchTitle']))) {
            $searchTitle = $params['searchTitle'];
        }

        if (isset($params['url']) && !empty(trim($params['url']))) {
            $url = $params['url'];
        }

        if (isset($params['status']) && !empty(trim($params['status']))) {
            $status = $params['status'];
        }
        $plugin = OW::getPluginManager()->getPlugin('frmgroupsplus');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'frmgroupsplus.js');
        $event->setData(array('groupFilterForm' => $this->getGroupFilterForm('GroupFilterForm', $tab,$categoryStatus,$searchTitle,$url,$status)));
    }


    public function getResultForListItemGroup(OW_Event $event)
    {
        $params = $event->getParams();

        $groupService = GROUPS_BOL_Service::getInstance();
        $groupController = $params['groupController'];
        $tab='';
        $categoryStatus=null;
        $status=self::STATUS_ACTIVE;
        $searchTitle=null;
        $latest=null;
        $popular=false;
        $activeTab=1;
        $groupIds = array();
        $page =1;
        $type = null;
        $url = $params['url'];
        $perPage = $params['perPage'];
        if(isset($params['page'])){
            $page = $params['page'];
        }
        if(isset($params['type'])){
            $type = $params['type'];
        }
        if (OW::getRequest()->isPost()) {
            $categoryStatus = $_POST['categoryStatus'];
            $searchTitle = $_POST['searchTitle'];
        }

        $first = ($page - 1) * $perPage;
        $count = $perPage;

        if(isset($_GET['categoryStatus'])){
            $categoryStatus = $_GET['categoryStatus'];
            $first = ($page - 1) * $perPage;
            $count = $perPage;
        }

        if(isset($_GET['searchTitle'])){
            $searchTitle = $_GET['searchTitle'];
        }

        if(isset($_GET['status']) && $this->checkUserHasRolesToManageSpecificUsersAndApproveSettingEnabled()){
            $status = $_GET['status'];
        }

        if(isset($params['activeTab'])){
            $tab = $params['activeTab'];
        }
        if(isset($params['popular'])){
            $popular = $params['popular'];
        }
        if(isset($params['latest'])){
            $latest = $params['latest'];
        }
        $userId=null;
        if(isset($params['userId'])){
            $userId = $params['userId'];
        }

        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ADD_GROUP_FILTER_FORM, array('tab' => $tab, 'categoryStatus' =>$categoryStatus, 'searchTitle' => $searchTitle,'url'=>$url,'status'=>$status)));
        if (isset($resultsEvent->getData()['groupFilterForm'])) {
            $groupFilterForm = $resultsEvent->getData()['groupFilterForm'];
        }
        if($categoryStatus!=null) {
            $groupIds = $this->getGroupIdListByCategoryID($categoryStatus);
            if($groupIds==null){
                $groupIds[]=-1;
            }
        }
        $groups = $groupService->findGroupsByFiltering($popular,$status,$latest,$first,$count,$userId,$groupIds,$searchTitle, $type);
        $groupsCount =$groupService->findGroupsByFilteringCount($popular,$status,$latest,$userId,$groupIds,$searchTitle, $type);
        $params = array('groups' => $groups, 'groupsCount' => $groupsCount, 'page'=>$page);
        if(isset($searchTitle) && !empty($searchTitle))
        {
            $params['searchTitle'] = $searchTitle;
        }
        if(isset($categoryStatus) && !empty($categoryStatus))
        {
            $params['categoryStatus'] = $categoryStatus;
        }

        if(isset($status)) {
            $params['status'] = $status;
        }

        $event->setData($params);
        $this->setGroupController($activeTab, $groupFilterForm, $groupController);
    }

    public function setGroupController($activeTab, $filterForm, $groupController)
    {
        if (isset($filterForm)) {
            $groupController->assign('filterForm', true);
            $groupController->addForm($filterForm);
            $filterFormElementsKey = array();
            foreach ($filterForm->getElements() as $element) {
                if ($element->getAttribute('type') != 'hidden') {
                    $filterFormElementsKey[] = $element->getAttribute('name');
                }
            }
            $groupController->assign('filterFormElementsKey', $filterFormElementsKey);
        }
    }

    /**
     * @param $name
     * @param $tab
     * @param null $selectedCategory
     * @param null $searchedTitle
     * @param null $url
     * @param string $status
     * @return Form Form
     */
    public function getGroupFilterForm($name, $tab, $selectedCategory=null,$searchedTitle=null,$url=null,$status=self::STATUS_ACTIVE)
    {
        $form = new Form($name);
        if(isset($url)) {
            $form->setAction($url);
        }
        $form->setMethod(Form::METHOD_GET);
        $searchTitle = new TextField('searchTitle');
        $searchTitle->addAttribute('placeholder',OW::getLanguage()->text('frmgroupsplus', 'search_title'));
        $searchTitle->addAttribute('class','group_search_title');
        $searchTitle->addAttribute('id','searchTitle');
        if($searchedTitle!=null) {
            $searchTitle->setValue($searchedTitle);
        }
        $searchTitle->setHasInvitation(false);
        $form->addElement($searchTitle);

        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ADD_GROUP_FILTER_ELEMENT, array('form' => $form, 'selectedCategory' => $selectedCategory)));
        if(isset($resultsEvent->getData()['form'])) {
            $form = $resultsEvent->getData()['form'];
        }
        $form = $this->addApproveFieldToGroupList($form, $status);
        return $form;
    }
    /***
     * @param $name
     * @return string
     */
    public function getIconUrl($name){
        return OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'images/File_Extentions/'.$name.'.png';
    }

    /***
     * @param $ext
     * @return string
     */
    public function getProperIcon($ext){
        $videoFormats = array('mov','mkv','mp4','avi','flv','ogg','mpg','mpeg');

        $wordFormats = array('docx','doc','docm','dotx','dotm');

        $excelFormats = array('xlsx','xls','xlsm');

        $zipFormats = array('zip','rar');

        if (FRMSecurityProvider::themeCoreDetector()){
            $imageFormats =array('jpg','jpeg','gif','tiff');
        }else{
            $imageFormats =array('jpg','jpeg','gif','tiff','png');
        }


        if(in_array($ext,$videoFormats)){
            return $this->getIconUrl('avi');
        }
        else if(in_array($ext,$wordFormats)){
            return $this->getIconUrl('doc');
        }
        else if(in_array($ext,$excelFormats)){
            return $this->getIconUrl('xls');
        }
        else if(in_array($ext,$zipFormats)){
            return $this->getIconUrl('zip');
        }
        else if(in_array($ext,$imageFormats)){
            return $this->getIconUrl('jpg');
        }
        else if(strcmp($ext,'png')==0){
            return $this->getIconUrl('png');
        }
        else if(strcmp($ext,'pdf')==0){
            return $this->getIconUrl('pdf');
        }
        else if(strcmp($ext,'txt')==0){
            return $this->getIconUrl('txt');
        }
        else{
            return $this->getIconUrl('file');
        }
    }

    /**
     * @param Form $form
     * @param null $selectedCategory
     * @param null $groupId
     */
    public function addCategoryElementToForm($form,$selectedCategory =null , $groupId = null)
    {
        $categories = $this->getGroupCategoryList();
        $categoryStatus = new Selectbox('categoryStatus');
        $option = array();
        $option[null] = OW::getLanguage()->text('frmgroupsplus','select_category');
        foreach ($categories as $category) {
            $option[$category->id] = $category->label;
        }
        $categoryStatus->setHasInvitation(false);
        if(isset($selectedCategory)) {
            $categoryStatus->setValue($selectedCategory);
        }else if(isset($groupId)){
            $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_GROUP_SELECTED_CATEGORY_ID, array('groupId' =>$groupId)));
            if(isset($resultsEvent->getData()['selectedCategoryId'])) {
                $categoryStatus->setValue($resultsEvent->getData()['selectedCategoryId']);
            }
        }
        $categoryStatus->setOptions($option);
        $categoryStatus->addAttribute('id','categoryStatus');
        $form->addElement($categoryStatus);
        return $form;
    }


    public function addNewElementsToGroupForm(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if (!isset($params['form'])) {
            return;
        }
        $form = $params['form'];
        $selectedCategory = null;
        if(isset($params['selectedCategory'])) {
            $selectedCategory = $params['selectedCategory'];
        }
        $groupId = null;
        if(isset($params['groupId'])){
            $groupId = $params['groupId'];
        }
        $form = $this->addCategoryElementToForm($form,$selectedCategory,$groupId);

        $data['form'] = $form;
        $data['hasCategoryFilter'] = true;
        $event->setData($data);
    }

    /*
    * get group selected category id
    */
    public function getGroupSelectedCategoryId(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['groupId'])){
            $categoryId = $this->getGroupCategoryByGroupId($params['groupId']);
            $event->setData(array('selectedCategoryId' => $categoryId));
        }
    }

    /*
    * get group selected category id
    */
    public function getGroupSelectedCategoryLabel(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['groupId'])){
            $categoryId = $this->getGroupCategoryByGroupId($params['groupId']);
            if($categoryId!=null) {
                $category = $this->categoryDao->findById($categoryId);
                if($category != null){
                    $event->setData(array('categoryLabel' => $category->label,'categoryStatus'=>$categoryId));
                }
            }
        }
    }


    public function addCategoryToGroup(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['groupId']) && isset($params['categoryId']))
        {
            $categoryId = $params['categoryId'];
            $categoryList = $this->getGroupCategoryIdList();
            if(!in_array($categoryId, $categoryList)){
                $categoryId = null;
            }
            $this->groupInformationDao->addCategoryToGroup($params['groupId'], $categoryId);

        }
    }


    public function getGroupCategoryList()
    {
        return $this->categoryDao->findAll();
    }

    public function getGroupCategoryIdList()
    {
        return $this->categoryDao->findAllIds();
    }

    public function getCategoryById($id)
    {
        return $this->categoryDao->findById($id);
    }
    public function getGroupInformationByCategoryId($categoryId)
    {
        return $this->groupInformationDao->getGroupInformationByCategoryId($categoryId);
    }

    public function getGroupIdListByCategoryID($categoryId)
    {
        if($categoryId!=null) {
            $groupInfoList = $this->getGroupInformationByCategoryId($categoryId);
            $groupIdList = array();
            foreach ($groupInfoList as $groupInfo) {
                $groupIdList[] = $groupInfo->groupId;
            }
            return $groupIdList;
        }
    }


    public function getGroupCategoryByGroupId($groupId)
    {
        $groupInfo =  $this->groupInformationDao->getGroupInformationByGroupId($groupId);
        if(isset($groupInfo->categoryId)) {
            return $groupInfo->categoryId;
        }
        return null;
    }


    public function getGroupCategoryByGroupIds($groupIds)
    {
        return $this->groupInformationDao->getGroupInformationByGroupIds($groupIds);
    }

    public function addGroupCategory($label)
    {
        $category = new FRMGROUPSPLUS_BOL_Category();
        $category->label = $label;
        FRMGROUPSPLUS_BOL_CategoryDao::getInstance()->save($category);
    }

    public function deleteGroupCategory( $categoryId )
    {
        $categoryId = (int) $categoryId;
        if ( $categoryId > 0 )
        {
            $this->groupInformationDao->deleteByCategoryId($categoryId);
            $this->categoryDao->deleteById($categoryId);
        }
    }

    public function getItemForm($id)
    {
        $item = $this->getCategoryById($id);
        $formName = 'edit-item';
        $submitLabel = 'edit';
        $actionRoute = OW::getRouter()->urlFor('FRMGROUPSPLUS_CTRL_Admin', 'editItem');

        $form = new Form($formName);
        $form->setAction($actionRoute);

        if ($item != null) {
            $idField = new HiddenField('id');
            $idField->setValue($item->id);
            $form->addElement($idField);
        }

        $fieldLabel = new TextField('label');
        $fieldLabel->setRequired();
        $fieldLabel->setInvitation(OW::getLanguage()->text('frmgroupsplus', 'label_category_label'));
        $fieldLabel->setValue($item->label);
        $fieldLabel->setHasInvitation(true);
        $validator = new FRMGROUPSPLUS_CLASS_LabelValidator();
        $language = OW::getLanguage();
        $validator->setErrorMessage($language->text('frmgroupsplus', 'label_error_already_exist'));
        $fieldLabel->addValidator($validator);
        $form->addElement($fieldLabel);

        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('base', 'ow_ic_save'));
        $form->addElement($submit);

        return $form;
    }

    public function editItem($id, $label)
    {
        $item = $this->getCategoryById($id);
        if ($item == null) {
            return;
        }
        if ($label == null) {
            $label = false;
        }
        $item->label = $label;

        $this->categoryDao->save($item);
        return $item;
    }

    public function getSearchBox(OW_Event $event)
    {

    }

    public function addWidgetToOthers(OW_Event $event)
    {
        $params = $event->getParams();

        if ( !isset($params['place']) || !isset($params['section']) )
        {
            return;
        }
        try
        {
            $widgetService = BOL_ComponentAdminService::getInstance();
            $widget = $widgetService->addWidget('FRMGROUPSPLUS_CMP_PendingInvitation', false);
            $widgetUniqID = $params['place'] . '-' . $widget->className;

            //*remove if exists
            $widgets = $widgetService->findPlaceComponentList($params['place']);
            foreach ( $widgets as $w )
            {
                if($w['uniqName'] == $widgetUniqID)
                    $widgetService->deleteWidgetPlace($widgetUniqID);
            }
            //----------*/

            //add
            $placeWidget = $widgetService->addWidgetToPlace($widget, $params['place'], $widgetUniqID);
            $widgetService->addWidgetToPosition($placeWidget, $params['section'], -1);
        }
        catch ( Exception $e ) { }
    }

    public function setUserManagerStatus(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['contextParentActionKey']) && isset($params['userId']) &&
            isset($params['groupOwnerId'])&& isset($params['groupId']) && isset($params['contextActionMenu'])){
            if ($params['userId'] != $params['groupOwnerId']) {
                $contextAction = new BASE_ContextAction();
                $contextAction->setParentKey($params['contextParentActionKey']);
                if ($params['groupOwnerId'] != $params['userId']) {
                    $isManager = false;
                    if (isset($params['managerIds'])) {
                        $isManager = in_array($params['userId'], $params['managerIds']);
                    } else {
                        $groupManager = $this->groupManagersDao->getGroupManagerByUidAndGid($params['groupId'],$params['userId']);
                        $isManager = isset($groupManager);
                    }
                    if($isManager){
                        $contextAction->setKey('delete_user_as_manager');
                        $contextAction->setLabel(OW::getLanguage()->text('frmgroupsplus', 'remove_group_user_manager_label'));
                        $callbackUri = OW::getRequest()->getRequestUri();
                        $deleteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('FRMGROUPSPLUS_CTRL_Groups', 'deleteUserAsManager', array(
                            'groupId' => $params['groupId'],
                            'userId' => $params['userId']
                        )), array(
                            'redirectUri' => urlencode($callbackUri)
                        ));

                        $contextAction->setUrl('javascript://');
                        $contextAction->addAttribute('data-message', OW::getLanguage()->text('frmgroupsplus', 'delete_group_user_confirmation'));
                        $contextAction->addAttribute('onclick', "return confirm_redirect($(this).data().message, '$deleteUrl')");
                        $contextAction->addAttribute('class', "delete_from_group_admins_icon");
                    }else {
                        $contextAction->setKey('add_user_as_manager');
                        $contextAction->setLabel(OW::getLanguage()->text('frmgroupsplus', 'add_group_user_as_manager_label'));
                        $callbackUri = OW::getRequest()->getRequestUri();
                        $addUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('FRMGROUPSPLUS_CTRL_Groups', 'addUserAsManager', array(
                            'groupId' => $params['groupId'],
                            'userId' => $params['userId']
                        )), array(
                            'redirectUri' => urlencode($callbackUri)
                        ));
                        $contextAction->setUrl('javascript://');
                        $contextAction->addAttribute('data-message', OW::getLanguage()->text('base', 'are_you_sure'));
                        $contextAction->addAttribute('onclick', "return confirm_redirect($(this).data().message, '$addUrl')");
                        $contextAction->addAttribute('class', "add_to_group_admins_icon");
                    }
                } else {
                    $contextAction->setUrl('javascript://');
                    $contextAction->addAttribute('data-message', OW::getLanguage()->text('frmgroupsplus', 'group_owner_delete_error'));
                    $contextAction->addAttribute('onclick', "OW.error($(this).data().message); return false;");
                }
                $params['contextActionMenu']->addAction($contextAction);
            }
        }
    }

    public function deleteUserManager($groupId,$userIds){
        if(!isset($groupId) || !isset($userIds) ){
            return;
        }
        $this->groupManagersDao->deleteGroupManagerByUidAndGid($groupId,$userIds);
    }

    public function addUserAsManager($groupId,$userId){
        if(!isset($groupId) || !isset($userId) ){
            return;
        }
        $this->groupManagersDao->addUserAsManager($groupId,$userId);
    }

    public function checkUserManagerStatus(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['userId'])){
            $userId = $params['userId'];
        }
        else{
            $userId = OW::getUser()->getId();
        }

        if (isset($params['all_manager_ids'])) {
            $managerUsers = $this->groupManagersDao->getGroupManagersByGroupId($params['groupId']);
            $managerIds = array();
            foreach ($managerUsers as $managerUser) {
                $managerIds[] = $managerUser->userId;
            }
            $event->setData(array('managerIds'=>$managerIds));
        } else if(isset($params['groupId'])){
            $isManager = false;
            $userGroupManager = $this->groupManagersDao->getGroupManagerByUidAndGid($params['groupId'],$userId);
            if(isset($userGroupManager)){
                $isManager = true;
            }

            $event->setData(array('isUserManager'=>$isManager));
        }
    }
    public function deleteUserAsManager(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['groupId']) && isset($params['userIds']) && sizeof($params['userIds'])>0 ){
            $this->groupManagersDao->deleteGroupManagerByUidAndGid($params['groupId'],$params['userIds']);
        }
    }

    public function setMobileUserManagerStatus(OW_Event $event)
    {
        $params = $event->getParams();
        $additionalInfo = array();
        if (isset($params['additionalInfo'])) {
            $additionalInfo = $params['additionalInfo'];
        }
        if(isset($params['contextMenu']) && isset($params['userId']) &&
            isset($params['groupOwnerId'])&& isset($params['groupId'])){
            if ($params['userId'] != $params['groupOwnerId']) {
                if ($params['groupOwnerId'] != $params['userId']) {
                    $groupManager = false;
                    $checkGroupManager = true;
                    if (isset($additionalInfo['cache']['groups_managers'][$params['groupId']])) {
                        $groupManager = in_array($params['userId'], $additionalInfo['cache']['groups_managers'][$params['groupId']]);
                        $checkGroupManager = false;
                    }
                    if ($checkGroupManager) {
                        $groupManager = $this->groupManagersDao->getGroupManagerByUidAndGid($params['groupId'],$params['userId']);
                        $groupManager = isset($groupManager);
                    }
                    if($groupManager){
                        $callbackUri = OW::getRequest()->getRequestUri();
                        $deleteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('FRMGROUPSPLUS_CTRL_Groups', 'deleteUserAsManager', array(
                            'groupId' => $params['groupId'],
                            'userId' => $params['userId']
                        )), array(
                            'redirectUri' => urlencode($callbackUri)
                        ));
                        array_unshift($params['contextMenu'], array(
                            'label' => OW::getLanguage()->text('frmgroupsplus', 'remove_group_user_manager_label'),
                            'attributes' => array(
                                'onclick' => 'return confirm_redirect($(this).data(\'confirm-msg\'), \''.$deleteUrl.'\');',
                                "data-confirm-msg" => OW::getLanguage()->text('frmgroupsplus', 'delete_group_user_confirmation')
                            ),
                            "class" => "owm_red_btn",
                            "order" => "2"
                        ));

                    }else {
                        $callbackUri = OW::getRequest()->getRequestUri();
                        $addUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('FRMGROUPSPLUS_CTRL_Groups', 'addUserAsManager', array(
                            'groupId' => $params['groupId'],
                            'userId' => $params['userId']
                        )), array(
                            'redirectUri' => urlencode($callbackUri)
                        ));

                        array_unshift($params['contextMenu'], array(
                            'label' => OW::getLanguage()->text('frmgroupsplus', 'add_group_user_as_manager_label'),
                            'attributes' => array(
                                'onclick' => "return confirm_redirect('".OW::getLanguage()->text('base', 'are_you_sure')."','$addUrl');"
                            ),
                            "class" => "owm_red_btn",
                            "order" => "2"
                        ));
                    }
                }
                $event->setData(array('contextMenu'=>$params['contextMenu']));
            }
        }
    }

    /***
     * @param $groupId
     * @param int $first
     * @param $count
     * @return array<BOL_Attachment>
     */
    public function findFileList($groupId, $first, $count, $searchTitle=null)
    {
        $trueAttachmentIds=array();
        $attachmentResults=array();
        $attachmentIds = $this->groupFileDao->findAttachmentIdListByGroupId($groupId, $first, $count);
        if(sizeof($attachmentIds)>0) {
            $attachmentList = BOL_AttachmentDao::getInstance()->findAttachmentsByIds($attachmentIds);
            foreach ($attachmentList as $attachment) {
                if (in_array($attachment->id, $attachmentIds)) {
                    if (isset($searchTitle) && $searchTitle != '') {
                        if (strpos($attachment->origFileName, $searchTitle) !== false) {
                            $attachmentResults[] = $attachment;
                        }
                    } else {
                        $attachmentResults[] = $attachment;
                    }
                    $trueAttachmentIds[] = $attachment->id;
                }
            }

            $falseAttachmentIds = array_diff($attachmentIds, $trueAttachmentIds);
            if ($falseAttachmentIds != null) {
                foreach ($falseAttachmentIds as $falseAttachmentId) {
                    $this->deleteFileForGroup($groupId, $falseAttachmentId);
                }
            }
        }
        return $attachmentResults;

    }

    /**
     * @param $groupId
     * @param null $searchTitle
     * @return mixed
     */
    public function findFileListCount($groupId,$searchTitle=null)
    {
        return $this->groupFileDao->findCountByGroupId($groupId,$searchTitle);

    }

    public function getUploadFileForm($groupId)
    {
        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('frmgroupsplus', 'file_create_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_new');
        OW::getDocument()->setTitle($language->text('frmgroupsplus', 'file_create_page_title'));
        OW::getDocument()->setDescription($language->text('frmgroupsplus', 'file_create_page_description'));

        $form = new FRMGROUPSPLUS_FileUploadForm($groupId);
        $actionRoute = OW::getRouter()->urlFor('FRMGROUPSPLUS_CTRL_Groups', 'addFile', array('groupId' => $groupId));
        $form->setAction($actionRoute);
        return $form;
    }

    public function addFileForGroup($groupId, $attachmentId){
        return $this->groupFileDao->addFileForGroup($groupId,$attachmentId);
    }

    public function deleteFileForGroup($groupId, $attachmentId){
        $fileId = $this->findFileIdByAidAndGid($groupId, $attachmentId);
        BOL_AttachmentService::getInstance()->deleteAttachmentById($attachmentId);
        OW::getEventManager()->trigger(new OW_Event("feed.delete_item", array(
            'entityType' => 'groups-add-file',
            'entityId' => $fileId
        )));
        OW::getEventManager()->trigger(new OW_Event('notifications.remove', array(
            'entityType' => 'groups-add-file',
            'entityId' => $fileId
        )));
        $this->groupFileDao->deleteGroupFilesByAidAndGid($groupId,$attachmentId);
    }

    public function deleteFileForGroupByGroupId($groupId){
        $this->groupFileDao->deleteGroupFilesByGroupId($groupId);
    }

    public function findFileIdByAidAndGid($groupId, $attachmentId){
        return $this->groupFileDao->findFileIdByAidAndGid($groupId,$attachmentId);
    }
    public function deleteFiles(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['groupId'])) {
            $filesDto = $this->groupFileDao->getGroupFilesByGroupId($params['groupId']);
            foreach ($filesDto as $file) {
                try {
                    OW::getEventManager()->trigger(new OW_Event("feed.delete_item", array(
                        'entityType' => 'groups-add-file',
                        'entityId' => $file->id
                    )));
                    OW::getEventManager()->call('notifications.remove', array(
                        'entityType' => 'groups-add-file',
                        'entityId' => $file->id
                    ));
                    $this->deleteFileForGroupByGroupId($params['groupId']);
                    BOL_AttachmentService::getInstance()->deleteAttachmentById($file->attachmentId);
                } catch (Exception $e) {

                }
            }
        }
        else if(isset($params['allFiles'])) {
            $filesDto = $this->groupFileDao->findAllFiles();
            foreach ($filesDto as $file) {
                try {
                    BOL_AttachmentService::getInstance()->deleteAttachmentById($file->attachmentId);
                    OW::getEventManager()->trigger(new OW_Event("feed.delete_item", array(
                        'entityType' => 'groups-add-file',
                        'entityId' => $file->id
                    )));
                    OW::getEventManager()->call('notifications.remove', array(
                        'entityType' => 'groups-add-file',
                        'entityId' => $file->id
                    ));
                } catch (Exception $e) {

                }
            }
        }
    }

    public function addFileWidget(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['controller']) && isset($params['groupId'])){
            $groupId = $params['groupId'];
            $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);

            $isChannel = false;
            $hideCommentFeatures = false;
            $hideLikeFeatures = false;
            $showGroupChatForm = false;
            $canReply = false;

            $managerIds = array();
            if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
                $groupManagerIds = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagersByGroupIds(array($groupId));
                $managerIds = array();
                if (isset($groupManagerIds[$groupId])) {
                    $managerIds = $groupManagerIds[$groupId];
                }
            }
            $isCurrentUserManager = in_array(OW::getUser()->getId(), $managerIds);
            $additionalInfo = array(
                'isManager' => $isCurrentUserManager,
                'group_object' => $groupDto,
            );
            $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.load', array('groupId' => $groupId, 'group' => $groupDto, 'additionalInfo' => $additionalInfo)));
            if (isset($channelEvent->getData()['isChannel']) && $channelEvent->getData()['isChannel'] == true) {
                $isChannel = true;
            }
            if ((isset($channelEvent->getData()['hideCommentFeatures']) && $channelEvent->getData()['hideCommentFeatures'] == true)) {
                $hideCommentFeatures = true;
            }
            if ((isset($channelEvent->getData()['hideLikeFeatures']) && $channelEvent->getData()['hideLikeFeatures'] == true)) {
                $hideLikeFeatures = true;
            }
            if(isset($channelEvent->getData()['showGroupChatForm'])){
                $showGroupChatForm = $channelEvent->getData()['showGroupChatForm'];
            }
            $isMemberOfGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
            if(isset($channelEvent->getData()['canReply']) && $channelEvent->getData()['canReply'] && $isMemberOfGroup){
                $canReply = true;
            }
            $additionalInfo = isset($params['additionalInfo'])?$params['additionalInfo']:array();

            $bcw = new BASE_CLASS_WidgetParameter();
            $bcw->additionalParamList = array(
                'entityId' => $groupId,
                'entity' => 'groups',
                'group' => $groupDto,
                'isChannel' => $isChannel,
                'hideCommentFeatures' => $hideCommentFeatures,
                'hideLikeFeatures' => $hideLikeFeatures,
                'showGroupChatForm' => $showGroupChatForm,
                'canReplyInGroup' => $canReply,
                'currentUserIsMemberOfGroup' => $isMemberOfGroup,
                'currentUserIsManager' => $isCurrentUserManager,
                'additionalInfo' => $additionalInfo
            );

            $groupController = $params['controller'];
            if (FRMSecurityProvider::isNewFileManagerEnabledForMobile()){
                $groupController->addComponent('groupFileList', new FRMFILEMANAGER_CMP_MainWidget($bcw));
            }else{
                $groupController->addComponent('groupFileList', new FRMGROUPSPLUS_MCMP_FileListWidget($bcw));
            }
            $fileBoxInformation = array(
                'show_title' => true,
                'title' => OW_Language::getInstance()->text('frmgroupsplus', 'widget_files_title'),
                'wrap_in_box' => true,
                'icon' => 'ow_ic_info',
                'type' => "",
            );
            $groupController->assign('fileBoxInformation', $fileBoxInformation);
        }
    }

    public function addPendingUsersList(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['controller']) && isset($params['groupId'])){
            $groupController = $params['controller'];
            $groupController->addComponent('groupPendingUserList', new FRMGROUPSPLUS_MCMP_PendingUserList($params['groupId']));
            $pendingUsersListInfo = array(
                'show_title' => true,
                'title' => OW_Language::getInstance()->text('frmgroupsplus', 'cmp_pending_users_title'),
                'wrap_in_box' => true,
                'icon' => 'ow_ic_info',
                'type' => "",
            );
            $groupController->assign('pendingUsersListInfo', $pendingUsersListInfo);
        }
    }

    public function onCollectNotificationActions( BASE_CLASS_EventCollector $e )
    {

        $e->add(array(
            'section' => 'groups',
            'action' => 'group_approve',
            'sectionIcon' => 'ow_add',
            'sectionLabel' => OW::getLanguage()->text('frmgroupsplus', 'email_notification_section_label'),
            'description' => OW::getLanguage()->text('frmgroupsplus', 'group_approve_status'),
            'selected' => true
        ));

        $e->add(array(
            'section' => 'groups',
            'action' => 'groups-add-file',
            'description' => OW::getLanguage()->text('frmgroupsplus', 'email_notifications_setting_file'),
            'selected' => true,
            'sectionLabel' => OW::getLanguage()->text('frmgroupsplus', 'email_notification_section_label'),
            'sectionIcon' => 'ow_ic_write'
        ));
        $e->add(array(
            'section' => 'groups',
            'action' => 'groups-update-status',
            'description' => OW::getLanguage()->text('frmgroupsplus', 'email_notifications_setting_status'),
            'selected' => true,
            'sectionLabel' => OW::getLanguage()->text('frmgroupsplus', 'email_notification_section_label'),
            'sectionIcon' => 'ow_ic_write'
        ));
    }

    public function onGroupUserInvitation(OW_Event $event){
        $invitationParams =  $event->getParams();

        $groupId = $invitationParams['groupId'];
        $userId = $invitationParams['userId'];
        $inviterId = $invitationParams['inviterId'];
        $inviteId = $invitationParams['inviteId'];

        $userService = BOL_UserService::getInstance();
        $groupService = GROUPS_BOL_Service::getInstance();

        $displayName = $userService->getDisplayName($inviterId);
        $inviterUrl = $userService->getUserUrl($inviterId);

        $groupTitle = $groupService->findGroupById($groupId)->title;
        $groupUrl = OW::getRouter()->urlForRoute('groups-view', array('groupId' => $groupId));

        $invitationUrl = OW::getRouter()->urlForRoute('groups-invite-list');

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($inviterId));

        $params = array(
            'pluginKey' => 'groups',
            'entityType' => 'user_invitation',
            'entityId' => $inviteId,
            'action' => 'groups-invitation',
            'userId' => $userId,
            'time' => time()
        );

        $data = array(
            'groupId'=>$groupId,
            'avatar' => $avatars[$inviterId],
            'string' => array(
                'key' => 'frmgroupsplus+group_user_invitation_notification',
                'vars' => array(
                    'userName' => $displayName,
                    'userUrl' => $inviterUrl,
                    'groupTitle' => $groupTitle,
                    'groupUrl'=> $groupUrl
                )
            ),
            'url' => $invitationUrl,
        );

        $e = new OW_Event('notifications.add', $params, $data);
        OW::getEventManager()->trigger($e);
    }

    public function onNotificationRender( OW_Event $e )
    {
        //how to show
        $params = $e->getParams();
        if ( $params['pluginKey'] != 'groups' || $params['entityType'] != 'user_invitation')
        {
            return;
        }
        $data = $params['data'];

        if ( !isset($data['avatar']['urlInfo']['vars']['username']) )
        {
            return;
        }

        $userService = BOL_UserService::getInstance();
        $user = $userService->findByUsername($data['avatar']['urlInfo']['vars']['username']);
        if ( !$user )
        {
            return;
        }
        $e->setData($data);
    }

    public function onUpdateGroupStatus(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['feedId']) && (isset($params['feedType']) && $params['feedType']=='groups') && isset($params['status'])) {
            $groupService = GROUPS_BOL_Service::getInstance();
            $group = $groupService->findGroupById($params['feedId']);

            if(!$group){
                return;
            }

            // notification when reply a post by another users
            $entityId = isset($params['statusId'])?$params['statusId']:$params['feedId'];
            if (isset($params['status']) && isset($params['statusId']) && !empty($_POST['reply_to'])) {
                NEWSFEED_BOL_Service::getInstance()->replyNotification($params['status'],$params['statusId'],$entityId,$group);
            }

            // notif to all group members
            $userIds = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($group->id);
            $groupUrl = $groupService->getGroupUrl($group);
            /*
              * send notification to group members
             */
            $userId = OW::getUser()->getId();

            $updateNotifierEvent = OW::getEventManager()->trigger(new OW_Event('update.notifierId.group.status.notification',array('group'=>$group)));
            if(isset($updateNotifierEvent->getData()['userId'])){
                $userId = $updateNotifierEvent->getData()['userId'];
            }


            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
            $avatar = $avatars[$userId];
            $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
            $defaultEntityType='groups-status';
            if(isset($params['entityType']))
            {
                $defaultEntityType=$params['entityType'];
            }
            $notificationParams = array(
                'pluginKey' => 'groups',
                'action' => 'groups-update-status',
                'entityType' => $defaultEntityType,
                'entityId' => $entityId,
                'userId' => null,
                'time' => time()
            );

            if(FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
                $action = NEWSFEED_BOL_Service::getInstance()->findAction($defaultEntityType, $params['statusId']);
                $actionId = $action->id;
                $mainUrl = OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $actionId));
            }
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $groupUrl)));
            if(isset($stringRenderer->getData()['string'])){
                $groupUrl = $stringRenderer->getData()['string'];
            }
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $avatar['src'])));
            if(isset($stringRenderer->getData()['string'])){
                $avatar['src'] = $stringRenderer->getData()['string'];
            }

            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $avatar['url'])));
            if(isset($stringRenderer->getData()['string'])){
                $avatar['url'] = $stringRenderer->getData()['string'];
            }

            $notificationData = array(
                'string' => array(
                    "key" => empty(trim($params['status']))? 'frmgroupsplus+notif_update_status_string_no_status':'frmgroupsplus+notif_update_status_string',
                    "vars" => array(
                        'groupTitle' => $group->title,
                        'groupUrl' => $groupUrl,
                        'userName' => BOL_UserService::getInstance()->getDisplayName($userId),
                        'userUrl' => $userUrl,
                        'status' =>  $params['status']
                    )
                ),
                'avatar' => $avatar,
                'content' => '',
                'url' => isset($mainUrl)?$mainUrl:$groupUrl
            );

            // send status update notifications in batch to userIds
            $userIds = array_diff($userIds, [OW::getUser()->getId()]);
            $event = new OW_Event('notifications.batch.add',
                ['userIds'=>$userIds, 'params'=>$notificationParams],
                $notificationData);
            OW::getEventManager()->trigger($event);
        }
    }

    public function deleteWidget( OW_Event $event )
    {
        BOL_ComponentAdminService::getInstance()->deleteWidget('FRMGROUPSPLUS_CMP_PendingInvitation');
    }

    public function pluginDeactivate( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'frmgroupsplus' )
        {
            return;
        }
        if ( OW::getConfig()->getValue('groups', 'is_frmgroupsplus_connected') )
        {
            $event = new OW_Event('frmgroupsplus.delete_widget');
            OW::getEventManager()->trigger($event);
        }
    }

    public function pluginUninstall( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'frmgroupsplus' )
        {
            return;
        }
        if ( OW::getConfig()->getValue('groups', 'is_frmgroupsplus_connected') )
        {
            $event = new OW_Event('frmgroupsplus.delete_widget');
            OW::getEventManager()->trigger($event);
        }
    }

    public function onCanInviteAll(OW_Event $event)
    {
        $params = $event->getParams();
        $data=$event->getData();
        if(isset($params['checkAccess'])){
            $hasAccess=false;
            $directInvite=false;
            if(OW::getUser()->isAuthorized('frmgroupsplus', 'all-search')){
                $hasAccess=true;
            }
            if(OW::getUser()->isAuthorized('frmgroupsplus', 'direct-add')){
                $directInvite = true;
            }
            $data['hasAccess']=$hasAccess;
            $data['directInvite']=$directInvite;
            $event->setData($data);
        }else if (OW::getUser()->isAuthorized('frmgroupsplus', 'all-search')) {
            $numberOfUsers = BOL_UserService::getInstance()->count(true);
            $users = BOL_UserService::getInstance()->findList(0, $numberOfUsers, true);
            $userIds = array();
            foreach ($users as $user) {
                $userIds[] = $user->getId();
            }
            $userDisapproveStatus = BOL_UserService::getInstance()->findUnapprovedStatusForUserList($userIds);

            $validUserIds = array();
            $userApproveConfig = OW::getConfig()->getValue('base', 'mandatory_user_approve');
            $usersEmailVerifyConfig = OW::getConfig()->getValue('base', 'confirm_email');

            foreach ($users as $user) {
                $userEmailStatus = $user->emailVerify == '0';
                if ($user->getId() == OW::getUser()->getId() ||
                    ($userApproveConfig && $userDisapproveStatus[$user->getId()]==true) ||
                    ($usersEmailVerifyConfig && $userEmailStatus)) {
                    continue;
                }

                $validUserIds[] = $user->getId();
            }
            if (sizeof($validUserIds) > 0) {
                $data['userIds']=$validUserIds;
                $event->setData($data);
            }
        }
    }

    public function addUsersAutomatically( OW_Event $event )
    {
        $params = $event->getParams();
        if(isset($params['userIds']) && isset($params['groupId'])) {
            $groupId = $params['groupId'];
            $userIds = $params['userIds'];

            foreach ($userIds as $userId) {
                GROUPS_BOL_Service::getInstance()->addUser($groupId, $userId);
            }

            $joinFeedString = true;
            if (OW::getConfig()->configExists('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed')) {
                $fileUploadFeedValue = json_decode(OW::getConfig()->getValue('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed'));
                if (!in_array('joinFeed', $fileUploadFeedValue)) {
                    $joinFeedString = false;
                }
            }
            if ($joinFeedString) {
                $inviterUserId = isset($params['inviter']) ? $params['inviter'] : OW::getUser()->getId();
                $groups = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
                $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($inviterUserId));

                $notificationParams = array(
                    'pluginKey' => 'groups',
                    'action' => 'groups-invitation',
                    'entityType' => 'groups-join',
                    'entityId' => (int)$groupId,
                    'userId' => null,
                    'time' => time()
                );

                $notificationData = array(
                    'string' => array(
                        'key' => 'frmgroupsplus+joined_notification_string',
                        'vars' => array(
                            'groupTitle' => $groups->title,
                            'groupUrl' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => (int)$groupId)),
                            'userName' => BOL_UserService::getInstance()->getDisplayName($inviterUserId),
                            'userUrl' => BOL_UserService::getInstance()->getUserUrl($inviterUserId)
                        )
                    ),
                    'avatar' => $avatars[$inviterUserId],
                    'content' => '',
                    'url' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => (int)$groupId))
                );

                // send notifications in batch to userIds
                $event = new OW_Event('notifications.batch.add',
                    ['userIds'=>$userIds, 'params'=>$notificationParams],
                    $notificationData);
                OW::getEventManager()->trigger($event);
            }
        }
    }

    public function memberListPageRender(OW_Event $event){
        $params = $event->getParams();
        $groupDto = $params['groupDto'];
        $managerList = array();
        if (isset($groupDto)) {
            $managers = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagersByGroupId($groupDto->getId());
            foreach ($managers as $manager){
                $managerList[] = BOL_UserDao::getInstance()->findById($manager->userId);
            }
        }

        $adminList = array();
        $adminList[] = BOL_UserDao::getInstance()->findById($groupDto->userId);
        if(isset($managerList) && is_array($managerList)){
            foreach ($managerList as $manager){
                $userExists = false;
                foreach($adminList as $admin){
                    if($admin->getId() === $manager->getId()){
                        $userExists = true;
                        break;
                    }
                }
                if(!$userExists){
                    $adminList[] = $manager;
                }
            }
        }
        $adminListCount = sizeof($adminList);

        $adminListCmp = new GROUPS_UserList($groupDto, $adminList, $adminListCount, 20);

        $extraComponents = array(
            array(
                'label'=>'frmgroupsplus+group_managers',
                'name' => 'managerList',
                'component' => $adminListCmp
            )
        );
        $event->setData($extraComponents);
    }

    public function setChannelGroup( OW_Event $event )
    {
        $params = $event->getParams();
        $data = array();
        $channelField = new RadioField('whoCanCreateContent');
        $channelField->setRequired();
        $channelField->addOptions(
            array(
                FRMGROUPSPLUS_BOL_Service::WCC_GROUP => OW::getLanguage()->text('frmgroupsplus', 'form_who_can_create_content_participants'),
                FRMGROUPSPLUS_BOL_Service::WCC_CHANNEL => OW::getLanguage()->text('frmgroupsplus', 'form_who_can_create_content_creators')
            )
        );
        $channelField->setLabel(OW::getLanguage()->text('frmgroupsplus', 'who_can_create_content'));
        if(isset($params['groupId']) && isset($params['form']) ){
            $groupId = $params['groupId'];
            $isChannel = FRMGROUPSPLUS_BOL_ChannelService::getInstance()->isChannel($groupId);
            $form = $params['form'];

            if ($isChannel){
                $data['isChannel']=true;
                $channelField->setValue(FRMGROUPSPLUS_BOL_Service::WCC_CHANNEL);
            }
            else{
                $data['isChannel']=false;
                $channelField->setValue(FRMGROUPSPLUS_BOL_Service::WCC_GROUP);
            }
            $form->addElement($channelField);
            $data['form'] = $form;
            $event->setData($data);

        }
        else if (isset($params['form'])) {
            $channelField->setValue(FRMGROUPSPLUS_BOL_Service::WCC_GROUP);
            $form = $params['form'];
            $form->addElement($channelField);
            $data['form'] = $form;
            $data['isChannel'] = false;
            $event->setData($data);
        }

    }


    public function canCreateTopic(OW_Event $event)
    {
        $config = OW::getConfig();
        if(!$config->configExists('frmgroupsplus', 'showAddTopic')||($config->configExists('frmgroupsplus', 'showAddTopic')&&!$config->getValue('frmgroupsplus', 'showAddTopic')))
            return;
        $params = $event->getParams();
        $data = array();
        $data['accessCreateTopic']=true;
        if(isset($params['groupId']))
        {
            $groupId=$params['groupId'];
            $groupSetting=$this->groupSettingDao->findByGroupId($params['groupId']);
            if(isset($groupSetting))
            {
                $isManager = false;
                if (isset($params['additionalInfo']['currentUserIsManager']) && $params['additionalInfo']['entityId'] == $groupId) {
                    $isManager = $params['additionalInfo']['currentUserIsManager'];
                } else {
                    $isManager=$this->groupManagersDao->getGroupManagerByUidAndGid($groupId, OW::getUser()->getId());
                }
                if($groupSetting->getWhoCanCreateTopic()==FRMGROUPSPLUS_BOL_Service::WCU_MANAGERS && !$isManager)
                {
                    $data['accessCreateTopic']=false;
                }
            }
        }
        $event->setData($data);
    }

    public function canUploadInFileWidget(OW_Event $event)
    {
        $config = OW::getConfig();
        if(!$config->configExists('frmgroupsplus', 'showFileUploadSettings')||($config->configExists('frmgroupsplus', 'showFileUploadSettings')&&!OW::getConfig()->getValue('frmgroupsplus', 'showFileUploadSettings')))
            return;
        $params = $event->getParams();
        $data = array();
        $data['accessUploadFile']=true;
        if(!OW::getUser()->isAuthenticated())
            $data['accessUploadFile']=false;

        elseif(isset($params['groupId']))
        {
            $groupId=$params['groupId'];

            $userId=OW::getUser()->getId();
            $groupSetting=$this->groupSettingDao->findByGroupId($params['groupId']);
            if(isset($groupSetting))
            {
                $isManager = false;
                if (isset($params['additionalInfo']['currentUserIsManager']) && isset($params['additionalInfo']['entityId']) && $params['additionalInfo']['entityId'] == $groupId) {
                    $isManager = $params['additionalInfo']['currentUserIsManager'];
                } else if (isset($params['additionalInfo']['currentUserIsManager']) && isset($params['additionalInfo']['group']) && $params['additionalInfo']['group']->id == $groupId) {
                    $isManager = $params['additionalInfo']['currentUserIsManager'];
                } else {
                    $isManager = $this->groupManagersDao->getGroupManagerByUidAndGid($groupId, OW::getUser()->getId());
                }

                if($groupSetting->getWhoCanUploadFile()==FRMGROUPSPLUS_BOL_Service::WCU_MANAGERS && !isset($isManager))
                {
                    $data['accessUploadFile']=false;
                }
            }
            $isUserInGroup = false;
            if (isset($params['additionalInfo']['currentUserIsMemberOfGroup']) && isset($params['additionalInfo']['entityId']) && $params['additionalInfo']['entityId'] == $groupId) {
                $isUserInGroup = $params['additionalInfo']['currentUserIsMemberOfGroup'];
            } else if (isset($params['additionalInfo']['currentUserIsMemberOfGroup']) && isset($params['additionalInfo']['group']) && $params['additionalInfo']['group']->id == $groupId) {
                $isUserInGroup = $params['additionalInfo']['currentUserIsMemberOfGroup'];
            } else {
                $isUserInGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
            }
            if (!$isUserInGroup)
                $data['accessUploadFile'] = false;

        }
        $event->setData($data);
    }

    public function addGroupSettingElements( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        /**
         * file upload setting
         */

        if (isset($params['form'])) {
            $form = $params['form'];

            if (isset($params['groupId'])) {
                $groupId = $params['groupId'];
                $groupSetting = FRMGROUPSPLUS_BOL_GroupSettingDao::getInstance()->findByGroupId($groupId);
            }
            $config = OW::getConfig();
            if ($config->configExists('frmgroupsplus', 'showFileUploadSettings')&& $config->getValue('frmgroupsplus', 'showFileUploadSettings')) {
                $whoCanUploadFileInFileWidgetField = new RadioField('whoCanUploadInFileWidget');
                $whoCanUploadFileInFileWidgetField->setRequired();
                $whoCanUploadFileInFileWidgetField->addOptions(
                    array(
                        FRMGROUPSPLUS_BOL_Service::WCU_PARTICIPANT => OW::getLanguage()->text('frmgroupsplus', 'who_can_setting_participant'),
                        FRMGROUPSPLUS_BOL_Service::WCU_MANAGERS => OW::getLanguage()->text('frmgroupsplus', 'who_can_setting_manager')
                    )
                );
                $whoCanUploadFileInFileWidgetField->setLabel(OW::getLanguage()->text('frmgroupsplus', 'who_can_upload_file_widget'));
                if (isset($groupSetting)) {
                    $whoCanUploadFileInFileWidgetField->setValue($groupSetting->getWhoCanUploadFile());
                } else {
                    $whoCanUploadFileInFileWidgetField->setValue(FRMGROUPSPLUS_BOL_Service::WCU_PARTICIPANT);
                }
                $data['uploadFile'] = true;
                $form->addElement($whoCanUploadFileInFileWidgetField);

            }
            /**
             * topic create setting
             */
            if ($config->configExists('frmgroupsplus', 'showAddTopic') && $config->getValue('frmgroupsplus', 'showAddTopic')) {
                $whoCanCreateTopic = new RadioField('whoCanCreateTopic');
                $whoCanCreateTopic->setRequired();
                $whoCanCreateTopic->addOptions(
                    array(
                        FRMGROUPSPLUS_BOL_Service::WCU_PARTICIPANT => OW::getLanguage()->text('frmgroupsplus', 'who_can_setting_participant'),
                        FRMGROUPSPLUS_BOL_Service::WCU_MANAGERS => OW::getLanguage()->text('frmgroupsplus', 'who_can_setting_manager')
                    )
                );
                $whoCanCreateTopic->setLabel(OW::getLanguage()->text('frmgroupsplus', 'who_can_create_topic'));

                $forumConnected = false;
                $is_forum_connected = OW::getConfig()->getValue('groups', 'is_forum_connected');

                if (OW::getPluginManager()->isPluginActive('forum') && $is_forum_connected) {
                    $forumConnected = true;
                }
                if (isset($groupSetting)) {
                    $whoCanCreateTopic->setValue($groupSetting->getWhoCanCreateTopic());
                } else {
                    $whoCanCreateTopic->setValue(FRMGROUPSPLUS_BOL_Service::WCU_PARTICIPANT);
                }
                if ($forumConnected) {
                    $data['createTopic'] = true;
                    $form->addElement($whoCanCreateTopic);
                }
            }
            $data['form'] = $form;
            $event->setData($data);
        }
    }
    public function setChannelForGroup( OW_Event $event ){
        $params = $event->getParams();
        if(isset($params['groupId']) && isset($params['isChannel']))
        {
            $isChannel =  ($params['isChannel'] == FRMGROUPSPLUS_BOL_Service::WCC_CHANNEL);
            $this->channelService->setChannel($params['groupId'], $isChannel);
        }
    }

    public function setGroupSetting( OW_Event $event ){
        $params = $event->getParams();
        if(isset($params['groupId']) && isset($params['values']) )
        {
            $groupId=$params['groupId'];
            $values=$params['values'];
            $whoCanUploadFile=FRMGROUPSPLUS_BOL_Service::WCU_PARTICIPANT;
            if(isset($values['whoCanUploadInFileWidget']) && in_array($values['whoCanUploadInFileWidget'],array(FRMGROUPSPLUS_BOL_Service::WCU_PARTICIPANT,FRMGROUPSPLUS_BOL_Service::WCU_MANAGERS)))
            {
                $whoCanUploadFile=$values['whoCanUploadInFileWidget'];
            }

            $whoCanCreateTopic=FRMGROUPSPLUS_BOL_Service::WCU_PARTICIPANT;
            if(isset($values['whoCanCreateTopic']) && in_array($values['whoCanCreateTopic'],array(FRMGROUPSPLUS_BOL_Service::WCU_PARTICIPANT,FRMGROUPSPLUS_BOL_Service::WCU_MANAGERS)))
            {
                $whoCanCreateTopic=$values['whoCanCreateTopic'];
            }
            $this->groupSettingDao->addSetting($groupId,$whoCanUploadFile,$whoCanCreateTopic);
        }
    }

    public function deleteGroupSetting(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['groupId'])) {
            $this->deleteGroupSettingByGroupId($params['groupId']);
        }
    }

    public function onChannelAddWidget( OW_Event $event ){
        $params = $event->getParams();
        $groupId = null;
        $cache = array();
        if (isset($params['additionalInfo']['cache'])) {
            $cache = $params['additionalInfo']['cache'];
        }
        if(isset($params['groupId'])){
            $groupId = $params['groupId'];
        }
        else if (isset($params['feedType']) && isset($params['feedId']) && $params['feedType'] == 'groups') {
            $groupId = $params['feedId'];
        }
        else if (isset($params['action']) && $params['action']->getActivity("create")!=null ) {
            $actionFeedDao = NEWSFEED_BOL_ActionFeedDao::getInstance();
            $actionFeed = $actionFeedDao->findByActivityIds(array($params['action']->getActivity("create")->id));
            if (!empty($actionFeed) && $actionFeed[0]->feedType == "groups"){
                $groupId = $actionFeed[0]->feedId;
            }
        }
        if (isset($groupId)){
            $group = null;
            $isChannel = false;
            $isManager = false;
            if (isset($params['isManager'])) {
                $isManager = $params['isManager'];
            } else {
                if (isset($params['additionalInfo']['isManager'])) {
                    $isManager = $params['additionalInfo']['isManager'];
                } else {
                    if (isset($cache['groups_managers'])) {
                        if (isset($cache['groups_managers'][$groupId])) {
                            $managerIds = $cache['groups_managers'][$groupId];
                            if (in_array(OW::getUser()->getId(), $managerIds)) {
                                $isManager = true;
                            }
                        }
                    } else {
                        $isManager = $this->groupManagersDao->getGroupManagerByUidAndGid($groupId, OW::getUser()->getId());
                    }
                    if (isset($isManager) && $isManager) {
                        $isManager = true;
                    } else {
                        $isManager = false;
                    }
                }
            }

            $isChannel = false;
            if (isset($params['isChannel'])) {
                $isChannel = $params['isChannel'];
            } else {
                if (isset($cache['groups_channel'][$groupId])) {
                    $isChannel = $cache['groups_channel'][$groupId];
                } else {
                    $isChannel = $this->channelService->isChannel($groupId);
                }
            }

            if (isset($params['group']) && $params['group']->id == $groupId) {
                $group = $params['group'];
            }
            if (isset($params['additionalInfo']['group']) && $params['additionalInfo']['group']->id == $groupId) {
                $group = $params['additionalInfo']['group'];
            }
            if (isset($cache['groups'][$groupId])) {
                $group = $cache['groups'][$groupId];
            }

            if ($group == null) {
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
            }

            if ($group != null) {
                $isCreator = $group->userId == OW::getUser()->getId() ? true : false;
                if ($isChannel && !$isManager && !$isCreator && !OW::getUser()->isAuthorized('groups'))
                    $event->setData(array("channelParticipant" => true));
                else
                    $event->setData(array("channelParticipant" => false));
            } else {
                $event->setData(array("channelParticipant" => false));
            }
        }
    }

    public function deleteGroupSettingByGroupId($groupId){
        $this->groupSettingDao->deleteByGroupId($groupId);
    }

    public function manageAddFile($groupId, $item, $createFeed = true){
        $resultArr = array('result' => false, 'message' => 'General error');
        $bundle = FRMSecurityProvider::generateUniqueId();

        $pluginKey = 'frmgroupsplus';
        if(isset($_POST['name']) && $_POST['name']!=""){
            $itemName = explode('.',$item['name'] );
            $item['name'] = $_POST['name'].'.'.end($itemName);
        }
        try {
            $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile($pluginKey, $item, $bundle);
        } catch (Exception $e) {
            $resultArr['message'] = $e->getMessage();
            OW::getFeedback()->error($resultArr['message']);
            return $resultArr;
        }
        OW::getEventManager()->call('base.attachment_save_image', array('uid' => $bundle, 'pluginKey' => $pluginKey));
        $resultArr['result'] = true;
        $resultArr['message'] = 'successful';
        $resultArr['url'] = $dtoArr['url'];
        $resultArr['dtoArr'] = $dtoArr;

        $attachmentId = $dtoArr['dto']->id;
        $fileId = $this->addFileForGroup($groupId,$attachmentId);

        /*
         * add feed action to group
         */
        $groupService = GROUPS_BOL_Service::getInstance();
        $group = $groupService->findGroupById($groupId);
        $url = $groupService->getGroupUrl($group);

        $private = $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE;
        $visibility = $private
            ? 14 // VISIBILITY_FOLLOW + VISIBILITY_AUTHOR + VISIBILITY_FEED
            : 15; // Visible for all (15)

        $fileActivityFeedConfig=json_decode(OW::getConfig()->getValue('frmgroupsplus','groupFileAndJoinAndLeaveFeed'));
        if(isset($fileActivityFeedConfig) && in_array('fileFeed',$fileActivityFeedConfig)){
            $data = array(
                'time' => time(),
                'string' => array(
                    "key" => 'frmgroupsplus+feed_add_file_string',
                    "vars" => array(
                        'groupTitle' => $group->title,
                        'groupUrl' => $url,
                        'fileUrl' => $this->getAttachmentUrl($dtoArr['dto']->fileName),
                        'fileName' => $dtoArr['dto']->origFileName
                    )
                ),
                'view' => array(
                    'iconClass' => 'ow_ic_add'
                ),
                'data' => array(
                    'fileAddId' => $fileId
                )
            );

            if($createFeed == true ){
                $event = new OW_Event('feed.action', array(
                    'feedType' => 'groups',
                    'feedId' => $group->id,
                    'entityType' => 'groups-add-file',
                    'entityId' => $fileId,
                    'pluginKey' => 'groups',
                    'userId' => OW::getUser()->getId(),
                    'visibility' => $visibility
                ), $data);

                OW::getEventManager()->trigger($event);
            }
        }

        /*
         * send notification to group members
         */

        $userIds = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($group->id);
        $userId = OW::getUser()->getId();
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $notificationParams = array(
            'pluginKey' => 'groups',
            'action' => 'groups-add-file',
            'entityType' => 'groups-add-file',
            'entityId' => $fileId,
            'userId' => null,
            'time' => time()
        );

        $notificationData = array(
            'string' => array(
                "key" => 'frmgroupsplus+notif_add_file_string',
                "vars" => array(
                    'groupTitle' => $group->title,
                    'groupUrl' => $url,
                    'userName' => BOL_UserService::getInstance()->getDisplayName($userId),
                    'fileName' => $dtoArr['dto']->origFileName,
                    'userUrl' => $userUrl
                )
            ),
            'avatar' => $avatar,
            'content' => '',
            'url' => $url, //$this->getAttachmentUrl($dtoArr['dto']->fileName)
        );

        // send notifications in batch to userIds
        $userIds = array_diff($userIds, [OW::getUser()->getId()]);
        $event = new OW_Event('notifications.batch.add',
            ['userIds' => $userIds, 'params' => $notificationParams],
            $notificationData);
        OW::getEventManager()->trigger($event);

        OW::getFeedback()->info(OW::getLanguage()->text('frmgroupsplus', 'add_file_successful'));

        return $resultArr;
    }

    public function getAttachmentUrl($name)
    {
        return OW::getStorage()->getFileUrl($this->getAttachmentDir($name));
    }

    public function getAttachmentDir($name)
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS .$name ;
    }

    public function onChannelLoad( OW_Event $event ){
        $params = $event->getParams();
        $eventData=$event->getData();
        $groupId = null;
        $cache = array();
        if (isset($params['additionalInfo']['cache'])) {
            $cache = $params['additionalInfo']['cache'];
        } else if (isset($params['cache'])) {
            $cache = $params['cache'];
        }
        if(isset($params['groupId']) ){
            $groupId = $params['groupId'];
        }
        else if (isset($params['action']) && $params['action']->getActivity("create")!=null ) {
            $actionFeedDao = NEWSFEED_BOL_ActionFeedDao::getInstance();
            $createActivity = null;
            $createActivity = $params['action']->getActivity("create");
            $createActivityId = $createActivity->id;
            $actionFeed = null;
            if (isset($cache['feed_by_creator_activity']) && array_key_exists($createActivityId, $cache['feed_by_creator_activity'])) {
                if (isset($cache['feed_by_creator_activity'][$createActivityId])) {
                    $actionFeedItem = $cache['feed_by_creator_activity'][$createActivityId];
                    if($actionFeedItem->feedType == "groups"){
                        $actionFeed = $actionFeedItem;
                    }
                }
            } else {
                $actionFeeds = $actionFeedDao->findByActivityIds(array($createActivityId));
                if (!empty($actionFeeds)){
                    foreach ($actionFeeds as $actionFeedItem){
                        if($actionFeedItem->feedType == "groups"){
                            $actionFeed = $actionFeedItem;
                            break;
                        }
                    }
                }
            }
            if ($actionFeed != null) {
                $groupId = $actionFeed->feedId;
            }

        }
        $isChannel=false;
        if (isset($groupId)){
            if (isset($cache['groups_channel'][$groupId])) {
                $isChannel = $cache['groups_channel'][$groupId];
            } else {
                $isChannel = $this->channelService->isChannel($groupId);
            }
        }
        if($isChannel) {
            $eventData['isChannel']=true;
            $event->setData($eventData);
        }
    }

    public function isGroupChannel(OW_Event $event )
    {
        $params = $event->getParams();
        if(isset($params['feedId']) && isset($params['feedType']) && $params['feedType']=='groups'){
            $isChannel = $this->channelService->isChannel($params['feedId']);
            if( $isChannel ){
                $event->setData(array("isChannel" => true));
            }
        }
    }

    public function getFileUrlByFileId($fileId, $params = array()){
        $item = null;
        if (isset($params['cache']['group_files'][$fileId])) {
            $attachmentId = $params['cache']['group_files'][$fileId]->attachmentId;
            if (isset($params['cache']['attachments'][$attachmentId])) {
                $item = $params['cache']['attachments'][$attachmentId];
            }
        }
        if ($item == null) {
            $file = $this->groupFileDao->findById($fileId);
            if(!isset($file)){
                return null;
            }
            $item = BOL_AttachmentDao::getInstance()->findById($file->attachmentId);
        }
        $path = OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS . $item->fileName;
        $fileUrl = OW::getStorage()->getFileUrl( $path, false, $params );
        return $fileUrl;
    }

    public function feedOnItemRenderActivity( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if(!isset($data["string"]["key"]) || $data["string"]["key"]!= "frmgroupsplus+feed_add_file_string")
            return;
        else {
            $g = explode('/', $data["string"]["vars"]["groupUrl"]);
            $groupId = end($g);
            $groupService = GROUPS_BOL_Service::getInstance();
            $group = null;
            if (isset($params['cache']['groups'][$groupId])) {
                $group = $params['cache']['groups'][$groupId];
            }
            if ($group == null) {
                $group = $groupService->findGroupById($groupId);
            }
            if(isset($group)) {
                $data["string"]["vars"]["groupTitle"] = $group->title;
                $data["string"]["vars"]["groupUrl"] = $groupService->getGroupUrl($group);
                $data["string"]["vars"]["fileUrl"] = FRMGROUPSPLUS_BOL_Service::getInstance()->getFileUrlByFileId($params["action"]["entityId"], $params);
                $event->setData($data);
            }
            else
                return;
        }

    }

    public function addConsoleItem( BASE_CLASS_EventCollector $event )
    {
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmgroupsplus', 'add-forced-groups')) {
            $event->add(array('label' => OW::getLanguage()->text('frmgroupsplus', 'forced_groups'), 'url' => OW_Router::getInstance()->urlForRoute('frmgroupsplus.forced-groups')));
        }
    }

    public static function getFilteredUsersList($profileQuestionFilters)
    {
        if (isset($profileQuestionFilters) && $profileQuestionFilters != null) {
            $q = "SELECT DISTINCT `userId` FROM `" . OW_DB_PREFIX . "base_question_data` as table1 WHERE";
            foreach ($profileQuestionFilters as $filter_name => $filter_value) {
                if (isset($filter_value) && $filter_value) {
                    $q .= "\n table1.userId IN ( SELECT DISTINCT `userId` FROM `" . OW_DB_PREFIX . "base_question_data` WHERE(`questionName` = '" . $filter_name . "' And `intValue` in(";
                    foreach ($filter_value as $value)
                        $q .= $value . ",";
                    $q = rtrim($q, ',');
                    $q .= "))) AND";
                }
            }
            $q = rtrim($q, 'AND');
            return OW::getDbo()->queryForList($q);
        }
        else{
            $numberOfUsers = BOL_UserService::getInstance()->count(true);
            $users = BOL_UserService::getInstance()->findList(0, $numberOfUsers, true);
            $userIds = array();
            foreach ($users as $user){
                $userIds[] = array('userId' => $user->id);
            }
            return $userIds;
        }
    }

    public function onUserRegistered(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['forEditProfile']) && $params['forEditProfile']==true){
            return;
        }
        if(isset($params['userId'])){
            $user = BOL_UserService::getInstance()->findUserById($params['userId']);
            if($user != null){
                $userId = $params['userId'];
                $forcedGroups = FRMGROUPSPLUS_BOL_ForcedGroupsDao::getInstance()->findAll();
                if (!empty($forcedGroups)) {
                    foreach ($forcedGroups as $forcedGroup) {
                        $groupConditions = json_decode( $forcedGroup->condition);
                        $forcedGroupFilters = array();
                        if (!empty($groupConditions)) {
                            foreach ($groupConditions as $filter_name => $groupCondition) {
                                if (!empty($groupCondition)) {
                                    $filter_parts = explode("__", $filter_name);
                                    $forcedGroupFilters[$filter_parts[1]][] = $filter_parts[2];
                                }
                            }
                        }

                        $allProfileQuestions = array();
                        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
                        foreach ($accountTypes as $accountType) {
                            $allProfileQuestions = array_merge(
                                $allProfileQuestions, BOL_QuestionService::getInstance()->findSignUpQuestionsForAccountType($accountType->name));
                        }
                        $allProfileQuestionNames = array();
                        foreach ($allProfileQuestions as $profileQuestion) {
                            $allProfileQuestionNames[] = $profileQuestion['name'];
                        }
                        foreach ($forcedGroupFilters as $profileQuestionFilterName => $profileQuestionFilterValue) {
                            if (!in_array($profileQuestionFilterName, $allProfileQuestionNames))
                                unset($forcedGroupFilters[$profileQuestionFilterName]);
                        }

                        $listOfFilteredUsers = FRMGROUPSPLUS_BOL_Service::getFilteredUsersList($forcedGroupFilters);
                        if (isset($listOfFilteredUsers)) {
                            foreach ($listOfFilteredUsers as $index => $item) {
                                $listOfFilteredUSerIds[] = $listOfFilteredUsers[$index]['userId'];
                            }
                            if (isset($listOfFilteredUSerIds)) {
                                if (in_array($userId, $listOfFilteredUSerIds)) {
                                    $group = GROUPS_BOL_Service::getInstance()->findGroupById($forcedGroup->groupId);
                                    if (isset($group)) {
                                        $eventIisGroupsPlusAddAutomatically = new OW_Event('frmgroupsplus.add.users.automatically', array('groupId' => $forcedGroup->groupId, 'userIds' => [$userId], 'inviter' => 1));
                                        OW::getEventManager()->trigger($eventIisGroupsPlusAddAutomatically);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function onBeforeUserLeave(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['userIds'])){
            $gId = $params['groupId'];
            $userIds = $params['userIds'];
            $users = BOL_UserService::getInstance()->findUserListByIdList($userIds);
            if(isset($users) && sizeof($users)>0){
                $forcedGroup = FRMGROUPSPLUS_BOL_ForcedGroupsDao::getInstance()->findForeceGroubObjByGroupId($gId);
                if( !empty($forcedGroup) && isset($forcedGroup->canLeave)&& !$forcedGroup->canLeave){
                    $event->setData(['cancel'=>true]);
                }
            }
        }
    }

    public function onCommentNotification( OW_Event $event )
    {
        if (!FRMSecurityProvider::checkPluginActive('newsfeed', true))
        {
            return;
        }
        $params = $event->getParams();

        if ($params['pluginKey'] != 'groups' && $params['entityType'] != 'groups-add-file')
        {
            return;
        }

        $userId = $params['userId'];
        $commentId = $params['commentId'];

        $userService = BOL_UserService::getInstance();

        $action = NEWSFEED_BOL_Service::getInstance()->findAction($params['entityType'], $params['entityId']);

        if ( empty($action) )
        {
            return;
        }

        $actionData = json_decode($action->data, true);
        $status = empty($actionData['data']['status'])
            ? empty($actionData['string']) ? null : $actionData['string']
            : $actionData['data']['status'];

        if ( empty($actionData['data']['userId']) )
        {
            $cActivities = NEWSFEED_BOL_Service::getInstance()->findActivity( NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE . ':' . $action->id);
            $cActivity = reset($cActivities);

            if ( empty($cActivity) )
            {
                return;
            }

            $ownerId = $cActivity->userId;
        }
        else
        {
            $ownerId = $actionData['data']['userId'];
        }

        $comment = BOL_CommentService::getInstance()->findComment($commentId);

        $contentImage = null;

        if ( !empty($comment->attachment) )
        {
            $attachment = json_decode($comment->attachment, true);

            if ( !empty($attachment["thumbnail_url"]) )
            {
                $contentImage = $attachment["thumbnail_url"];
            }
            if ( $attachment["type"] == "photo" )
            {
                $contentImage = $attachment["url"];
            }
        }

        $url = OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $action->id));

        if ( $ownerId != $userId )
        {
            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId), true, true, true, false);

            $stringKey = empty($status)
                ? 'frmgroupsplus+email_notifications_empty_status_comment'
                : 'frmgroupsplus+email_notifications_status_comment';
            $attachmentUrl=isset($status['vars']['fileUrl']) ? $status['vars']['fileUrl'] : null;
            $status = OW::getLanguage()->text('frmgroupsplus','feed_add_file_string',$status['vars']);
            $event = new OW_Event('notifications.add', array(
                'pluginKey' => 'frmgroupsplus',
                'entityType' => 'status_comment',
                'entityId' => $commentId,
                'userId' => $ownerId,
                'action' => 'newsfeed-status_comment'
            ), array(
                'format' => "text",
                'avatar' => $avatar[$userId],
                'string' => array(
                    'key' => $stringKey,
                    'vars' => array(
                        'userName' => $userService->getDisplayName($userId),
                        'userUrl' => $userService->getUserUrl($userId),
                        'status' => UTIL_String::truncate(UTIL_HtmlTag::stripTags($status), 20, '...'),
                        'url' => $url
                    )
                ),
                'attachmentUrl' => $attachmentUrl ,
                'content' => $comment->getMessage(),
                'contentImage' => $contentImage,
                'url' => $url
            ));

            OW::getEventManager()->trigger($event);
        }
    }


    public function deleteComment( OW_Event $e )
    {
        $params = $e->getParams();
        $commentId = $params['commentId'];

        $event = new OW_Event('feed.delete_activity', array(
            'entityType' => $params['entityType'],
            'entityId' => $params['entityId'],
            'activityType' => 'comment',
            'activityId' => $commentId
        ));
        OW::getEventManager()->trigger($event);

        if ($params['pluginKey']!='groups' || empty($params['entityType']) || ($params['entityType'] !== 'groups-add-file') )
            return;

        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'status_comment',
            'entityId' => $commentId
        ));
        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'base_profile_wall',
            'entityId' => $commentId
        ));
    }

    public function onUnregisterUser( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $this->groupManagersDao->deleteGroupManagerByUserId($userId);
    }


    public function onCollectSearchItems(OW_Event $event){
        if (!OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('groups', 'view'))
        {
            return;
        }
        $params = $event->getParams();
        $selected_section = null;
        if(!empty($params['selected_section']))
            $selected_section = $params['selected_section'];
        if( isset($selected_section) && $selected_section != OW_Language::getInstance()->text('frmadvancesearch','all_sections') && $selected_section!= OW::getLanguage()->text('frmadvancesearch', 'files_label') )
            return;
        $searchValue = '';
        if ( !empty($params['q']) )
        {
            $searchValue = $params['q'];
        }
        $searchValue = strip_tags(UTIL_HtmlTag::stripTags($searchValue));
        $maxCount = empty($params['maxCount'])?10:$params['maxCount'];
        $first= empty($params['first'])?0:$params['first'];
        $first=(int)$first;
        $pageCount=empty($params['count'])?$first+$maxCount:$params['count'];
        $pageCount=(int)$pageCount;

        $files = array();

        if (!isset($params['do_query']) || $params['do_query']) {
            $files = FRMGROUPSPLUS_BOL_GroupFilesDao::getInstance()->findFileByFiltering($first, $pageCount, $searchValue, OW::getUser()->getId());
        }
        $count = 0;
        $result = array();
        $userIdList = array_column($files, 'userId');
        $userIdListUnique = array_unique($userIdList);
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIdListUnique);
        $userUrls = BOL_UserService::getInstance()->getUserUrlsForList($userIdListUnique);
        foreach($files as $item){
            /* @var $item GROUPS_BOL_Group */
            $itemInformation = array();
            $itemInformation['title'] = $item["origFileName"];
            $itemInformation['id'] = $item["id"];
            $userId = $item["userId"];
            $itemInformation['userId'] = $userId;
            $itemInformation['displayName'] = $displayNames[$userId];
            $itemInformation['userUrl'] = $userUrls[$userId];
            $itemInformation['createdDate'] =$item["addStamp"];
            $itemInformation['link'] =$this->getAttachmentUrl($item['fileName']);
            $itemInformation['label'] = OW::getLanguage()->text('frmadvancesearch', 'files_label');
            $itemInformation['emptyImage'] = true;
            $itemInformation['image'] = OW::getPluginManager()->getPlugin('frmgroupsplus')->getStaticUrl() . 'images/file_default_image.svg';


            $result[] = $itemInformation;
            $count++;
            if($count == $maxCount){
                break;
            }
        }

        $data = $event->getData();
        if(isset($data['file']))
            $data['file']['data'] =  array_merge($result,$data['file']['data']);
        else
            $data['file'] = array('label' => OW::getLanguage()->text('frmadvancesearch', 'files_label'), 'data' => $result);
        $event->setData($data);
    }

    public function revoke()
    {
        $groupService = GROUPS_BOL_Service::getInstance();

        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            throw new AuthenticateException();
        }

        $response = array();

        $targetUserId = json_decode($_POST['userId']);
        $groupId = $_POST['groupId'];
        $group = $groupService->findGroupById($groupId);

        if(!OW::getUser()->isAdmin()){
            if(!$groupService->isCurrentUserInvite($group->id))
                exit(json_encode(array('result ' => false, 'error' => 'error')));
        }

        $eventIisGroupsPlusCheckCanSearchAll = new OW_Event('frmgroupsplus.check.can.invite.all',array('checkAccess'=>true));
        OW::getEventManager()->trigger($eventIisGroupsPlusCheckCanSearchAll);
        if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['hasAccess'])){
            $hasAccess=true;
        }
        if ($userId == $targetUserId)
            return;
        if (isset($hasAccess)) {
            $groupService->deleteInvite($group->id, $targetUserId);
        }

        $response['messageType'] = 'info';
        $response['message'] = OW::getLanguage()->text('groups', 'users_revoke_invitation_success_message', array('count' => 1));
        exit(json_encode($response));
    }


    public function checkApproveSettingEnable(){
        $config = OW::getConfig();
        if ($config->configExists('frmgroupsplus', 'groupApproveStatus')
            & $config->getValue('frmgroupsplus', 'groupApproveStatus') == 1) {
            return true;
        }else{
            return false;
        }
    }

    public function checkGroupStatusApproveSettingEnableEvent(OW_Event $event){
        $params = $event->getParams();
        $data = $event->getData();
        $config = OW::getConfig();

        if(isset($params['groupStatus']) && $params['groupStatus']!=self::STATUS_APPROVAL)
        {
            return;
        }

        if ($config->configExists('frmgroupsplus', 'groupApproveStatus')
            & $config->getValue('frmgroupsplus', 'groupApproveStatus') == 1) {
            $data['roleModeratorCanCheck'] =  true;
            $event->setData($data);
        }
    }


    /**
     * @param $groupId
     * @param int|null $groupCreatorId
     * @return bool
     * @throws Redirect404Exception
     */
    public function approveGroupById ( $groupId ){
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if(!FRMSecurityProvider::checkPluginActive('groups',true)) {
            return false;
        }
        if(!$this->checkUserIsGroupModeratorAndApproveSettingEnabled(OW::getUser()->getId(),$group->status))
        {
            return false;
        }
        GROUPS_BOL_GroupDao::getInstance()->activateGroupStatusById($groupId);
        return true;
    }

    /**
     * @param Form $form
     * @param string $value
     * @return Form
     */
    public function addApproveFieldToGroupList( $form, $value=self::STATUS_ACTIVE ){
        $canUserModerateThisUserByQuestionRole = GROUPS_BOL_Service::getInstance()->checkIfUserHasRolesToManageSpecificUsers();
        if($this->checkApproveSettingEnable() && $canUserModerateThisUserByQuestionRole) {
            $approveStatus = new Selectbox('status');
            $approveStatus->setHasInvitation(false);
            $approveOption[self::STATUS_ACTIVE] = OW::getLanguage()->text('frmgroupsplus','active_groups');
            $approveOption[self::STATUS_APPROVAL] =  OW::getLanguage()->text('frmgroupsplus','unapproved_groups');
            $approveStatus->setOptions($approveOption);
            $approveStatus->setValue($value);
            $approveStatus->addAttribute('id','status');
            $form->addElement($approveStatus);
            OW::getDocument()->addStyleDeclaration('
                .ow_group_list .ow_automargin.ow_superwide form input#searchTitle {min-width: 40% !important;}
                .ow_group_list .ow_automargin.ow_superwide form {max-width: 660px;}
              ');
        }
        return $form;
    }

    public function checkCurrentUserUnapprovedGroupPermissions ( $groupDto ){
        $permissions = array('canApprove'=>false,'canView'=>false );
        $canApprove = OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('groups') ;
        if ($canApprove){
            $permissions['canApprove']=true;
            $permissions['canView']=true;
        }
        return $permissions;
    }

    /**
     * @param $groupDto
     * @return mixed
     */
    public function updateStatusGroupsObject( $groupDto ){
        $groupDto->status = GROUPS_BOL_Group::STATUS_APPROVAL;
        return $groupDto;
    }

    /**
     * @param GROUPS_BOL_Group $groupObject
     * @param int $creatorUserId
     */
    public function sendNotificationToModeratorsForUnapprovedGroup ( $groupObject, $creatorUserId ){
        $url = OW::getRouter()->urlForRoute('groups-view', array('groupId' => $groupObject->id));
        $userService = BOL_UserService::getInstance();
        $moderatorIds = BOL_AuthorizationService::getInstance()->findModeratorsUserIdByGroupNames(['groups','admin']);

        $questionRolesModeratorIds = $this->findQuestionRoleModeratorIdsForUserId($groupObject->userId);
        $moderatorIds = array_unique(array_merge($moderatorIds, $questionRolesModeratorIds));

        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($creatorUserId) , true, true, false, false);
        $creatorName = $userService->getDisplayName($creatorUserId);
        $creatorUrl = $userService->getUserUrl($creatorUserId);
        foreach ($moderatorIds as $moderatorId) {
            $event = new OW_Event('notifications.add', array(
                'pluginKey' => 'groups',
                'entityType' => 'group_approve',
                'entityId' => $groupObject->id,
                'userId' => $moderatorId,
                'action' => 'group_approve'
            ), array(
                'avatar' => $avatar[$creatorUserId],
                'string' => array(
                    'key' => 'frmgroupsplus+group_approve_notification',
                    'vars' => array(
                        'userName' => $creatorName,
                        'userUrl' => $creatorUrl,
                        'groupName' => $groupObject->title,
                        'groupUrl' => $url
                    )
                ),
                'url' => $url
            ));

            OW::getEventManager()->trigger($event);
        }
    }

    /**
     * @param OW_Event $event
     */
    public function AddApproveFeature(OW_Event $event )
    {
        $params = $event->getParams();
        $eventData= $event->getData();
        if(!isset($params['groupId'])|| !isset($params['groupStatus']))
        {
           return;
        }
        if($params['groupStatus']==self::STATUS_ACTIVE)
        {
            return;
        }

        $canUserModerateThisUserByQuestionRole = false;
        if(isset($params['groupCreatorId'])) {
            $canUserModerateThisUserByQuestionRole = GROUPS_BOL_Service::getInstance()->canUserModerateThisUserByQuestionRole($params['groupCreatorId'],$params['groupStatus']);
        }

        if(OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('groups') || $canUserModerateThisUserByQuestionRole) {
            $groupCreatorUserId = null;
            if (isset($params['groupCreatorId'])) {
                $groupCreatorUserId = $params['groupCreatorId'];
            }
            $eventData['approveFeature'] = $this->addApproveSpecificationsIfGroupIsUnapproved($params['groupId'], $params['groupStatus'], $groupCreatorUserId);
            $event->setData($eventData);
        }
    }

    /**
     * @param $groupId
     * @param $groupStatus
     * @param int|null $groupCreatorUserId
     * @return array|void
     */
    public function addApproveSpecificationsIfGroupIsUnapproved ( $groupId,$groupStatus, $groupCreatorUserId=null ){
        $lang = OW::getLanguage()->text('frmgroupsplus', 'approve_confirm_msg');
        $approveLink = OW::getRouter()->urlForRoute('frmgroupsplus.group-approve', array('groupId' => $groupId));
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$groupId,'isPermanent'=>false,'activityType'=>'approve_group')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $approveLinkCode = $frmSecuritymanagerEvent->getData()['code'];
            $approveLink = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmgroupsplus.group-approve', array('groupId' => $groupId)),array('code' =>$approveLinkCode));
        }
        return array(
            'approve' => false,
            'approveLink' => $approveLink,
            'toolbarArray' => array(
                'label' => OW::getLanguage()->text('base', 'approve'),
                'button' => true,
                'click' => 'var button = $.confirm(\'' . $lang . '\');button.buttons.ok.action = function () {window.location =\'' . $approveLink . '\';};',
                'class' => 'group_details_approve_btn_label'),
        );
    }

    public function checkGroupApproveStatusByGroupId( $groupId ){
        if ( !$this->checkApproveSettingEnable() ) {
            return true;
        }
        $unapprovedGroupsList = (array)json_decode( OW::getConfig()->getValue('frmgroupsplus', 'unapprovedGroupsList'));
        if( in_array($groupId,$unapprovedGroupsList) & $this->checkApproveSettingEnable() ){
            return false;
        }else{
            return true;
        }
    }

    /**
     * @param OW_Event $event
     */
    public function onGroupCreateCheckNeedApprove( OW_Event $event )
    {
        $params = $event->getParams();
        if (!isset($params['group']) || !isset($params['userId'])) {
            return;
        }
        if ($this->checkUserIsGroupModeratorOrApproveSettingDisabled()) {
            return;
        }
        $groupDto = $this->updateStatusGroupsObject($params['group']);
        $event->setData(
            array(
                'groupDto' => $groupDto
            )
        );
    }

    public function afterGroupCreateSendNotification( OW_Event $event ){
        $params = $event->getParams();
        if (!isset($params['group']->id) || !isset($params['group']->status) || !isset($params['userId']) ) {
            return;
        }
        if ( $params['group']->status == GROUPS_BOL_Group::STATUS_ACTIVE) {
            return;
        }
        $this->sendNotificationToModeratorsForUnapprovedGroup ( $params['group'], $params['userId'] );
    }


    /**
     * @param $status
     * @param int|null $groupCreatorUserId
     * @return bool
     */
    public function checkUserAccessGroupBasedOnStatus($status, $groupCreatorUserId=null)
    {
        if($status==self::STATUS_ACTIVE)
        {
            return true;
        }
        else if($status==self::STATUS_APPROVAL && $this->checkUserIsGroupModeratorOrApproveSettingDisabled($groupCreatorUserId))
        {
            return true;
        }
        return false;
    }


    public function checkAccessGroupBasedOnStatus(OW_Event $event){
        $params = $event->getParams();
        $eventData = $event->getData();
        if (!isset($params['groupStatus']) ) {
            return;
        }

        $groupCreatorUserId = null;
        if (isset($params['groupCreatorUserId'])) {
            $groupCreatorUserId = $params['groupCreatorUserId'];
        }
        $eventData['hasAccess'] = $this->checkUserAccessGroupBasedOnStatus($params['groupStatus'], $groupCreatorUserId);
        $event->setData( $eventData );
    }

    /**
     * @param int|null $groupCreatorUserId
     * @return bool
     */
    public function checkUserIsGroupModeratorOrApproveSettingDisabled($groupCreatorUserId=null)
    {
        $isModerator = OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('groups') ;
        $canCreateGroupWithoutApprovalNeed = OW::getUser()->isAuthorized('frmgroupsplus', 'create_group_without_approval_need');
        $isQuestionRoleModerator = $this->checkUserIsQuestionRoleModeratorForGroup($groupCreatorUserId);
        if ( !$this->checkApproveSettingEnable() || $isModerator || $canCreateGroupWithoutApprovalNeed || $isQuestionRoleModerator) {
            return true;
        }
        return false;
    }


    public function onGetGroupsListMobile(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['nativeMobile']) || !$params['nativeMobile'] ){
            return;
        }
        $data = $event->getData();
        if($this->checkUserIsGroupModeratorAndApproveSettingEnabled(OW::getUser()->getId()))
        {
           $data['isNativeAdminOrGroupModerator'] = true;
        }else{
            $data['isNativeAdminOrGroupModerator'] = false;
        }
        $event->setData($data);
    }


    /**
     * @param null $userId
     * @param null $groupStatus
     * @return bool
     */
    public function checkUserIsGroupModeratorAndApproveSettingEnabled($userId=null,$groupStatus=null) {
        $isQuestionRoleModerator=false;
        $approveSettingEnableEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.check.status.and.approve.setting.enable',array('groupStatus'=>$groupStatus)));
        if(isset($approveSettingEnableEvent->getData()['roleModeratorCanCheck']) && $approveSettingEnableEvent->getData()['roleModeratorCanCheck']) {
            $questionRolesModeratorIds = OW::getEventManager()->trigger(
                new OW_Event(FRMEventManager::FIND_MODERATOR_FOR_USER,
                    array('userId' => $userId), array()));
            $isQuestionRoleModerator = in_array(OW::getUser()->getId(),$questionRolesModeratorIds->getData());
        }

        $isModerator = OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('groups') || $isQuestionRoleModerator;
        if ( $this->checkApproveSettingEnable()&& $isModerator) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function checkUserHasRolesToManageSpecificUsersAndApproveSettingEnabled()
    {
        $canUserModerateUsersByQuestionRole = GROUPS_BOL_Service::getInstance()->checkIfUserHasRolesToManageSpecificUsers();
        $isModerator = OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('groups') || $canUserModerateUsersByQuestionRole;
        if ( $this->checkApproveSettingEnable()&& $isModerator) {
            return true;
        }
        return false;
    }

    /**
     * @param int|null $groupUserCratorId
     * @return bool
     */
    private function checkUserIsQuestionRoleModeratorForGroup($groupUserCratorId) {
        $isQuestionRoleModerator = false;
        if ($groupUserCratorId) {
            $isQuestionRoleModerator = OW::getEventManager()->trigger(
                new OW_Event(FRMEventManager::HAS_USER_AUTHORIZE_TO_MANAGE_USERS,
                    array('userId' => $groupUserCratorId)));
            $isQuestionRoleModerator = isset($isQuestionRoleModerator->getData()['valid']) && $isQuestionRoleModerator->getData()['valid'];
        }
        return $isQuestionRoleModerator;
    }

    /**
     * @param OW_Event $event
     */
    public function checkGroupApproveFeedback( OW_Event $event){

        if($this->checkUserIsGroupModeratorOrApproveSettingDisabled())
        {
            return;
        }
        $event->setData(array(
            'feedback'=> OW::getLanguage()->text('frmgroupsplus', 'after_group_create_unapproved_feedback'),
            'feedbackText' => OW::getLanguage()->text('frmgroupsplus', 'unapproved_group_warning'),
            'needsApprove' =>true
        ));
    }

    public function checkGroupApproveStatusEvent( OW_Event $event){
        $approvedStatus = null;
        $params = $event->getParams();
        if(isset($params['feedId'])){
            if( !$this->checkGroupApproveStatusByGroupId( $params['feedId'] ) ){
                $event->setData(array(
                    'isUnapprovedGroup'=> true
                ));
            };
        }
    }

    /***
     * For frmfilemanager initialization
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param OW_Event $event
     */
    public function importFilesToFileWidget(OW_Event $event){
        $params = $event->getParams();
        if (isset($params['type']) && $params['type'] != 'groups'){
            return;
        }

        $service = FRMFILEMANAGER_BOL_Service::getInstance();

        $dir0Id = $service->insert('frm:groups', 1, 'directory', time(), '', false, true);
        $all_groups = GROUPS_BOL_GroupDao::getInstance()->findAllIds();
        foreach ($all_groups as $gId){
            $dirId = $service->insert('frm:groups:'.$gId, $dir0Id,'directory', time(), '', true, true);
            $files = $this->findFileList($gId, 0, 1000);
            foreach($files as $attachment){
                /** @var BOL_Attachment $attachment */
                $content = $service->contentForAttachment($attachment);
                $mimeType = UTIL_File::getMimeTypeByFileName($attachment->origFileName);
                $service->insert($attachment->origFileName, $dirId, $mimeType, $attachment->addStamp, $content, true, false, $attachment->size);
            }
        }
    }

    /***
     * For frmfilemanager pricacy check
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param OW_Event $event
     */
    public function checkPrivacyForFileWidget(OW_Event $event){
        $params = $event->getParams();
        if (!isset($params['entityType']) || $params['entityType'] != 'groups'){
            return;
        }

        $data = $event->getData();
        if ($params['level'] <= 1){
            $data['read'] = false;
            $data['write'] = false;
        }
        elseif ($params['level'] == 2){
            // outside a group folder: Block for now
            $read = false;
            if(!$read)
            {
                $data['read'] = false;
                $data['write'] = false;
                $data['name'] = OW::getLanguage()->text('groups', 'private_page_heading');
            }
            else{
                $group_id = (int)$params['entityId'];
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($group_id);
                $data['name'] = $group_id . ': '. $group->title;
            }
        }
        elseif($params['level'] >= 3){
            // inside a group, such as group 1
            $group_id = (int)$params['entityId'];
            $group = GROUPS_BOL_Service::getInstance()->findGroupById($group_id);

            if (isset($data['read'])){
                $read = GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($group);
                $data['read'] = $read;
                if(!$read)
                {
                    $data['write'] = false;
                    $data['name'] = OW::getLanguage()->text('groups', 'private_page_heading');
                }
            }

            if (isset($data['write'])){
                if ($params['type'] == 'directory'){
                    if (isset($params['is_parent_dir']) && $params['is_parent_dir']){
                        $data['write'] = $this->canCurrentUserUploadFile($group);
                    }else {
                        $data['write'] = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
                    }
                } else {
                    // edit file
                    $data['write'] = false;
                    if (GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group))
                    {
                        $data['write'] = true;
                    }
                    else {
                        $content = json_decode($params['content'], true);
                        $attachmentId = $content['a_id'];
                        $attachment = BOL_AttachmentDao::getInstance()->findById($attachmentId);
                        if ($attachment->userId == OW::getUser()->getId()) {
                            $data['write'] = true;
                        }
                    }
                }
            }
        }
        $event->setData($data);
    }

    /***
     * For frmfilemanager after remove
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param OW_Event $event
     */
    public function afterFileEntityRemoved(OW_Event $event){
        $params = $event->getParams();
        if (!isset($params['entityType']) || $params['entityType'] != 'groups'){
            return;
        }
        if ($params['type'] == 'directory') {
            return;
        }
        $this->deleteFileForGroup($params['entityId'], $params['attachmentId']);
    }
    /***
     * @param GROUPS_BOL_Group $group
     */
    public function canCurrentUserUploadFile($group){
        $canEditGroup = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
        if($canEditGroup)
            return true;

        $isAuthorizedUpload=true;
        $groupSettingEvent = OW::getEventManager()->trigger(new OW_Event('can.upload.in.file.widget',
            array('groupId'=>$group->id, 'additionalInfo' => [])));
        if(isset($groupSettingEvent->getData()['accessUploadFile'])) {
            $isAuthorizedUpload = $groupSettingEvent->getData()['accessUploadFile'];
        }
        return $isAuthorizedUpload;
    }

    /***
     * @param OW_Event $event
     */
    public function onInviteAllUsers(OW_Event $event)
    {
        $data = $event->getData();
        $allUsers = BOL_UserDao::getInstance()->findList(0,100000);
        $allUsersIdList = array_column( $allUsers, 'id');
        if (!empty($allUsersIdList)) {
            $data['allUsersIdList'] = $allUsersIdList;
            $event->setData($data);
        }
    }

    /**
     * @param int $userId
     * @return array
     */
    private function findQuestionRoleModeratorIdsForUserId($userId) {
        $moderatorIds = array();
        $questionRolesModeratorIds = OW::getEventManager()->trigger(
            new OW_Event(FRMEventManager::FIND_MODERATOR_FOR_USER,
                array('userId' => $userId), $moderatorIds));
        $questionRolesModeratorIds = $questionRolesModeratorIds->getData();
        return $questionRolesModeratorIds;
    }
}

class FRMGROUPSPLUS_FileUploadForm extends Form
{
    public function __construct($groupId)
    {
        parent::__construct('fileUploadForm');

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $language = OW::getLanguage();

        $nameField = new TextField('name');
        $nameField->setLabel($language->text('frmgroupsplus', 'create_field_file_name_label'));
        $nameField->setRequired(true);
        $this->addElement($nameField);

        $fileField = new FileField('fileUpload');
        $fileField->setLabel($language->text('frmgroupsplus', 'create_field_file_upload_label'));
        $fileField->setRequired();
        $this->addElement($fileField);

        $groupIdElement = new HiddenField('id');
        $groupIdElement->setValue($groupId);
        $this->addElement($groupIdElement);

        $saveField = new Submit('save');
        $saveField->setValue(OW::getLanguage()->text('frmgroupsplus', 'create_submit_btn_label'));
        $this->addElement($saveField);
    }
}

class FRMGROUPSPLUS_UserList extends BASE_CMP_Users
{
    /**
     *
     * @var GROUPS_BOL_Group
     */
    protected $groupDto;

    public function __construct( GROUPS_BOL_Group $groupDto, $list, $itemCount, $usersOnPage, $showOnline = true)
    {
        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
        $this->groupDto = $groupDto;
    }

    public function getContextMenu($userId, $additionalInfo = array())
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return null;
        }

        $isOwner = $this->groupDto->userId == OW::getUser()->getId();
        $isGroupModerator = OW::getUser()->isAuthorized('groups');
        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$this->groupDto->getId()));
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
        if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
            $isGroupModerator=$eventIisGroupsPlusManager->getData()['isUserManager'];
        }
        $contextActionMenu = new BASE_CMP_ContextAction();

        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('group_user_' . $userId);
        $contextActionMenu->addAction($contextParentAction);

        if ( ($isOwner || $isGroupModerator) && ($isGroupModerator || $userId != OW::getUser()->getId()) && $userId!=$this->groupDto->userId)
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setKey('delete_group_user');
            $contextAction->setLabel(OW::getLanguage()->text('groups', 'delete_group_user_label'));

            if ( $this->groupDto->userId != $userId )
            {
                $callbackUri = OW::getRequest()->getRequestUri();
                $urlParams = array(
                    'redirectUri' => urlencode($callbackUri)
                );
                $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                    array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'deleteUser_group')));
                if(isset($frmSecuritymanagerEvent->getData()['code'])){
                    $urlParams['code'] = $frmSecuritymanagerEvent->getData()['code'];

                }
                $deleteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'deleteUser', array(
                    'groupId' => $this->groupDto->id,
                    'userId' => $userId
                )),$urlParams );

                $contextAction->setUrl($deleteUrl);

                $contextAction->addAttribute('data-message', OW::getLanguage()->text('groups', 'delete_group_user_confirmation'));
                $contextAction->addAttribute('onclick', "return confirm($(this).data().message)");
            }
            else
            {
                $contextAction->setUrl('javascript://');
                $contextAction->addAttribute('data-message', OW::getLanguage()->text('groups', 'group_owner_delete_error'));
                $contextAction->addAttribute('onclick', "OW.error($(this).data().message); return false;");
            }

            $contextActionMenu->addAction($contextAction);
            $eventIisGroupsplus = new OW_Event('frmgroupsplus.set.user.manager.status', array('contextParentActionKey'=>$contextParentAction->getKey(),
                'userId'=>$userId,'groupOwnerId'=>$this->groupDto->userId,'groupId'=>$this->groupDto->id,'contextActionMenu'=>$contextActionMenu));
            OW::getEventManager()->trigger($eventIisGroupsplus);
        }

        return $contextActionMenu;
    }

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate !== null && $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex !== null && $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

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
            }
        }

        return $fields;
    }

}
