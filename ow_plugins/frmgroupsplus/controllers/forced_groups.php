<?php
/**
 * frmgroupsplus
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus.controllers
 * @since 1.0
 */
class FRMGROUPSPLUS_CTRL_ForcedGroups extends OW_ActionController
{
    /**
     *
     * @var FRMGROUPSPLUS_BOL_Service
     */
    private $service;

    public function __construct()
    {
        $this->service = FRMGROUPSPLUS_BOL_Service::getInstance();
    }

    public function index( $params )
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('frmgroupsplus', 'add-forced-groups')) {
            throw new Redirect404Exception();
        }
        $this->setPageTitle(OW::getLanguage()->text('frmgroupsplus', 'forced_groups'));
        $this->setPageHeading(OW::getLanguage()->text('frmgroupsplus', 'forced_groups'));

        // Form to add a new group
        $form = new Form('mainForm');
        $form->setAjax(true);
        $form->setAction(OW::getRouter()->urlFor('FRMGROUPSPLUS_CTRL_ForcedGroups', 'index'));
        $groupIdField = new TextField('gId');
        $groupIdField->setRequired();
        $groupIdField->setLabel(OW::getLanguage()->text('frmgroupsplus','group_id'));
        $form->addElement($groupIdField);
        $removing = new CheckboxField('removing');
        $form->addElement($removing);
        $submitField = new Submit('submit');
        $form->addElement($submitField);

        $allSelectableQuestionElements = BOL_QuestionService::getInstance()->allSelectableQuestionElements();
        $profileQuestions = array();
        $forcedStay = new CheckboxField('forcedStay');
        $forcedStay->setLabel(OW::getLanguage()->text('frmgroupsplus', 'group_forced_stay'));
        $form->addElement($forcedStay);
        $allProfileQuestionNames = array();
        foreach ($allSelectableQuestionElements as $question_number => $question) {
            $question_label = OW::getLanguage()->text('base', 'questions_question_' . $question->getAttribute('name') . '_label');
            $profileQuestions[$question_number]['question_label'] = $question_label;
            $profileQuestions[$question_number]['custom_id'] = $question_number;
            foreach ($question->getOptions() as $question_option_number => $question_option) {
                $profileQuestionNames = 'profileQuestionFilter__' . $question_option->questionName . '__' . $question_option->value;
                $questionOption = new CheckboxField($profileQuestionNames);
                $allProfileQuestionNames[] = $profileQuestionNames;
                $questionOption->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $question_option->questionName . '_value_' . $question_option->value));
                $profileQuestions[$question_number]['options'][$question_option_number] = $questionOption;
                $form->addElement($questionOption);
            }
        }

        $addNewForcedGroupButton = new button('addNewForcedGroupButton');
        $form->addElement($addNewForcedGroupButton);

        $this->assign('profileQuestions', $profileQuestions);
        $this->addForm($form);

        $profileQuestionFilters = array();
        if (OW::getRequest()->isAjax() && $form->isValid($_POST)) {
            $allProfileQuestions = $form->getValues();
            $nonProfileQuestionFilters = array("form_name", "csrf_token", "csrf_hash", "gId", "forcedStay");

            foreach ($nonProfileQuestionFilters as $filed) {
                unset($allProfileQuestions[$filed]);
            }

            foreach ($allProfileQuestions as $filter_name => $filter_value) {
                if (in_array($filter_name, $allProfileQuestionNames) && isset($filter_value) && $filter_value) {
                    $filter_parts = explode("__", $filter_name);
                    $profileQuestionFilters[$filter_parts[1]][] = $filter_parts[2];
                }
            }
            $this->addAllUsersToGroup(null);
        }

        $forcedGroups = FRMGROUPSPLUS_BOL_ForcedGroupsDao::getInstance()->findAll();

        $groups = [];
            foreach ($forcedGroups as $forcedGroup) {
                /* @var $forcedGroup FRMGROUPSPLUS_BOL_ForcedGroups */
                $gId = $forcedGroup->groupId;
                $forced = $forcedGroup->canLeave;
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($gId);
                $userCount = OW::getLanguage()->text('groups', 'feed_activity_users', array('usersCount' => GROUPS_BOL_Service::getInstance()->findUserCountForList([$gId])[$gId]));
                if (isset($group)) {
                    $groups[] = ['id' => $gId, 'name' => $group->title, 'forced' => ($forced == 'on') ? 'checked' : '',
                        'href' => GROUPS_BOL_Service::getInstance()->getGroupUrl($group), 'userCount' => $userCount, 'editURL' => OW::getRouter()->urlForRoute('frmgroupsplus.forced-group-edit', array('id' => $gId))
                    ];
                }
            }

        $this->assign('groups', $groups);

        $areYouSureText = OW_Language::getInstance()->text('base', 'are_you_sure');
        $pleaseWaitText = OW_Language::getInstance()->text('frmgroupsplus', 'please_wait');
        $forcedGroupDeletedMessage = OW::getLanguage()->text('frmgroupsplus', 'forced_group_removed');
        $deleteForcedGroupUrl = OW::getRouter()->urlForRoute('frmgroupsplus.forced-group-delete');

        $js = FRMGROUPSPLUS_BOL_Service::getForcedGroupSubmitFormJS() .  "
        $('a.f_remove').click(function(e){
            var tr = $(this).closest('tr');
            var gId = $('*[name=gId]', tr).val();
            $('form[name=mainForm] input[name=gId]').val(gId);
            var forcedStay = $('input[name=forcedStay]', tr).prop('checked');
            $('form[name=mainForm] input[name=forcedStay]').prop('checked', forcedStay);
            var jc = $.confirm('". $areYouSureText ."');
            jc.buttons.ok.action = function () {
            var selectedGroupId = $(e.target).closest(\"tr\").find(\"input[name='gId']\").attr(\"value\");
            var forcedGroupDeletedMessage = '" . $forcedGroupDeletedMessage . "';
                $.ajax({
                    url: '$deleteForcedGroupUrl',
                    type: 'POST',
                    data: {'groupId': selectedGroupId},
                    dataType: 'json',
                    success: function(data){
                        OW.info(forcedGroupDeletedMessage);
                        location.reload();
                    }
                });
                OW.info('" . $pleaseWaitText . "');
            }
            if (gId != ''){
                $('#btn_loading').show();
                if($(this).hasClass('f_insert')){
                    OW.info('".OW::getLanguage()->text('frmgroupsplus','please_wait')."');
                }
            }
        });
        
        $('a.f_add_all_users').click(function(){
            var tr = $(this).closest('tr');
            var gId = $('*[name=gId]', tr).val();
            OW.info('" . OW::getLanguage()->text('frmgroupsplus', 'please_wait') . "');
            $.ajax( {
                url: '" . OW::getRouter()->urlFor('FRMGROUPSPLUS_CTRL_ForcedGroups', 'addAllUsersToGroup') . "',
                type: 'POST',
                data: { gId: gId },
                dataType: 'json',
                success: function( result )
                {
                    OW.info(result['message']);
                }
            });
        });
        
        this.myForm = window.owForms['mainForm'];
		this.myForm.bind('success', function(result){
            $('#btn_loading').hide();
		    if ( result && result.result == 'success' )
            {
                OW.info(result['message']);
                if ( result.refresh === true){
                    window.location.reload();
                }
            }
            else if ( result['message'] )
            {
                OW.error(result['message']);
            }
		});
        ";
        OW::getDocument()->addOnloadScript($js);
    }

    public function deleteItem( $params )
    {
        if ( !OW::getRequest()->isAjax() || !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('frmgroupsplus', 'add-forced-groups')) {
            throw new Redirect404Exception();
        }
        if(!isset($_POST['groupId'])) {
            throw new Redirect404Exception();
        }
        $gId = $_POST['groupId'];

        FRMGROUPSPLUS_BOL_ForcedGroupsDao::getInstance()->deleteByGroupId($gId);

        exit(json_encode(array('result' => 'success', 'refresh'=>true, 'message' => OW::getLanguage()->text('frmgroupsplus', 'forced_group_removed'))));
    }

    public function addAllUsersToGroup( $params )
    {
        if (!OW::getRequest()->isAjax() || !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('frmgroupsplus', 'add-forced-groups')) {
            throw new Redirect404Exception();
        }
        if (empty($_POST['gId']))
            exit(json_encode(array('result' => 'error', 'message' => OW::getLanguage()->text('frmgroupsplus', 'group_id_is_required'))));

        $groupService = GROUPS_BOL_Service::getInstance();
        $gId = UTIL_Validator::convertToEnglishNumbers($_POST['gId']);
        $group = $groupService->findGroupById($gId);
        if(isset($group)) {
            $forcedGroup = FRMGROUPSPLUS_BOL_ForcedGroupsDao::getInstance()->findByGroupId($gId);

            if (empty($forcedGroup)) {
                $forcedGroup = new FRMGROUPSPLUS_BOL_ForcedGroups();
                $forcedGroup->groupId = $gId;
            } else {
                $forcedGroup = $forcedGroup[0];
            }
            $forcedGroup->canLeave = (bool)($_POST['forcedStay'] == 'false');
            $forcedGroup->condition =  json_encode($_POST['profileQuestionFiltersList']);
            FRMGROUPSPLUS_BOL_ForcedGroupsDao::getInstance()->save($forcedGroup);

            $registeredUsers = $groupService->findGroupUserIdList($gId);
            if (isset($_POST['profileQuestionFiltersList'])) {
                $filteredUsers = FRMGROUPSPLUS_CTRL_ForcedGroups::findFilteredUserList($_POST['profileQuestionFiltersList']);
                $users = array();
                if (isset($filteredUsers) && sizeof($filteredUsers) != 0) {
                    $users = BOL_UserDao::getInstance()->findByIdList($filteredUsers);
                }
            } else{
                $numberOfUsers = BOL_UserService::getInstance()->count(true);
                $users = BOL_UserService::getInstance()->findList(0, $numberOfUsers, true);
            }
            $userIds = [];
            $_POST['no-join-feed'] = true;
            foreach($users as $user){
                if(! in_array($user->id, $registeredUsers)) {
                    $userIds[] = $user->id;
                    $groupService->addUser($gId, $user->id);
                }
            }
            $userIds = array_diff($userIds, $registeredUsers);

            $eventIisGroupsPlusAddAutomatically = new OW_Event('frmgroupsplus.add.users.automatically',array('groupId'=>$gId,'userIds'=>$userIds));
            OW::getEventManager()->trigger($eventIisGroupsPlusAddAutomatically);

            exit(json_encode(array('result' => 'success', 'refresh'=>true, 'forcedGroupsURL'=>OW::getRouter()->urlForRoute('frmgroupsplus.forced-groups'), 'message' => OW::getLanguage()->text('frmgroupsplus', 'all_users_added'))));
        }else{
            exit(json_encode(array('result' => 'error', 'message' => OW::getLanguage()->text('frmgroupsplus', 'group_not_found'))));
        }
    }

    private function findFilteredUserList($allProfileQuestions)
    {
        $profileQuestionFilters = null;
        foreach ($allProfileQuestions as $filter_name=>$filter_value){
            if (isset($filter_value)){
                $filter_parts = explode("__", $filter_name);
                $profileQuestionFilters[$filter_parts[1]][] = $filter_parts[2];
            }
        }

        if ($profileQuestionFilters != null) {
            $result = FRMGROUPSPLUS_BOL_Service::getFilteredUsersList($profileQuestionFilters);
            if ($result != null) {
                $listOfFilteredUSerIds = array();
                foreach ($result as $index => $item) {
                    $listOfFilteredUSerIds[] = $result[$index]['userId'];
                }
            }
        }
        if (isset($listOfFilteredUSerIds))
            return $listOfFilteredUSerIds;
        return null;
    }

    public function edit($params)
    {
        $this->setPageTitle(OW::getLanguage()->text('frmgroupsplus', 'edit_forced_group'));
        $this->setPageHeading(OW::getLanguage()->text('frmgroupsplus', 'edit_forced_group'));

        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('frmgroupsplus', 'add-forced-groups')) {
            throw new Redirect404Exception();
        }
        if(!isset( $params['id'])) {
            throw new Redirect404Exception();
        }
        $groupId = $params['id'];
        $forcedGroup = FRMGROUPSPLUS_BOL_ForcedGroupsDao::getInstance()->findByGroupId($groupId);
        $forcedGroup = $forcedGroup[0];

        if (isset($forcedGroup)) {

            $forcedGroupConfigs = new Form('forcedGroupConfigs');
            $allSelectableQuestionElements = BOL_QuestionService::getInstance()->allSelectableQuestionElements();
            $forcedStay = new CheckboxField('forcedStay');
            $forcedStay->setLabel(OW::getLanguage()->text('frmgroupspluscustom_id', 'label_forcedStay'));

            $groupIdField = new TextField('gId');
            $groupIdField->setLabel(OW::getLanguage()->text('frmgroupsplus', 'label_gId'))->addAttribute('value', $groupId);
            $groupIdField->setRequired();
            $forcedGroupConfigs->addElement($groupIdField);

            $editNewForcedGroupButton = new button('editNewForcedGroupButton');
            $editNewForcedGroupButton->addAttribute('value', OW::getLanguage()->text('frmgroupsplus', 'edit_item'));
            $forcedGroupConfigs->addElement($editNewForcedGroupButton);

            if (!$forcedGroup->canLeave) {
                $forcedStay->addAttribute('id', 'submitForcedGroupConfigs');
            }

            $profileQuestions = array();
            foreach ($allSelectableQuestionElements as $question_number => $question) {
                $question_label = OW::getLanguage()->text('base', 'questions_question_' . $question->getAttribute('name') . '_label');
                $profileQuestions[$question_number]['question_label'] = $question_label;
                $profileQuestions[$question_number]['custom_id'] = $question_number;
                foreach ($question->getOptions() as $question_option_number => $question_option) {
                    $questionOption = new CheckboxField('profileQuestionFilter__' . $question_option->questionName . '__' . $question_option->value);
                    $questionOption->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $question_option->questionName . '_value_' . $question_option->value));
                    $profileQuestions[$question_number]['options'][$question_option_number] = $questionOption;
                    $forcedGroupConditions = json_decode($forcedGroup->condition);
                    if (isset($forcedGroupConditions) &&  isset(get_object_vars($forcedGroupConditions)['profileQuestionFilter__' . $question_option->questionName . '__' . $question_option->value]) == "1")
                        $questionOption->addAttribute('checked', true);
                    $forcedGroupConfigs->addElement($questionOption);
                }
            }
            $this->assign('profileQuestions', $profileQuestions);
            $this->assign('forceStay', !$forcedGroup->canLeave);
            $this->addForm($forcedGroupConfigs);

            $this->assign('forcedGroupsIndexUrl', OW::getRouter()->urlForRoute('frmgroupsplus.forced-groups'));

            $js = FRMGROUPSPLUS_BOL_Service::getForcedGroupSubmitFormJS();
            OW::getDocument()->addOnloadScript($js);
        }
        else{
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
            OW::getFeedback()->error(OW::getLanguage()->text('frmgroupsplus', 'forced_group_not_found'));
        }
    }
}