<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_UsersList extends OW_Component
{
    /**
     * FRMGRAPH_CMP_UsersList constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct($params = null)
    {
        parent::__construct();
        $this->allUsersCmp($params);
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'fileSaver.min.js' );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'xlsx.full.min.js' );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'tableExport.min.js' );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmgraph')->getStaticJsUrl() . 'frmgraph.js' );
    }

    private function allUsersCmp($params)
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        $this->assign('sections', $service->getGraphSections(6));

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmgraph')->getStaticCssUrl().'graph.css');

        $form = new Form('mainForm');
        $form->setAction(OW::getRouter()->urlForRoute('frmgraph.users_list'));

        $fieldsArray = array();
        $fields = array();

        if (OW::getConfig()->configExists('frmgraph', 'users_list_options')) {
            $JsonSavedValues = OW::getConfig()->getValue('frmgraph', 'users_list_options');
            $savedValues = json_decode($JsonSavedValues, true);
        }

        $authorizationService = BOL_AuthorizationService::getInstance();
        $userRoleIdList = $authorizationService->findNonGuestRoleList();

        foreach ( $userRoleIdList as $role )
        {
            $field = new CheckboxField('roles_' . $role->getId());
            $field->setLabel(OW::getLanguage()->text('base', 'authorization_role_' . $role->getName()));
            $form->addElement($field);
            $tplRoleList[$role->sortOrder] = $role;
        }
        ksort($tplRoleList);
        $this->assign('roleList', $tplRoleList);

        $submitField = new Submit('submit');
        $form->addElement($submitField);
        $exportExcelButton = new button('export_excel');
        $form->addElement($exportExcelButton);

        $numberOfAllUsers = BOL_UserService::getInstance()->count(true);

        $numberOfResultRecords = new TextField('numberOfResultRows');
        $numberOfResultRecords->addAttribute('type','number')->addAttribute('step','1')->addAttribute('value', $numberOfAllUsers);
        $numberOfResultsValue = null;
        if (isset($savedValues) && isset($savedValues['numberOfResultRows']))
            $numberOfResultsValue = $savedValues['numberOfResultRows'];
        if(isset($numberOfResultsValue) && isset($savedValues) && $numberOfResultsValue != null && $numberOfResultsValue > 0){
            $numberOfResultRecords->setValue($savedValues['numberOfResultRows']);
        }
        else{
            $numberOfResultRecords->setValue($numberOfAllUsers);
        }
        $form->addElement($numberOfResultRecords);

        $allSelectableQuestionElements = BOL_QuestionService::getInstance()->allSelectableQuestionElements();
        $profileQuestions = array();
        foreach ($allSelectableQuestionElements as $question_number=>$question) {
            $question_label = OW::getLanguage()->text('base', 'questions_question_' . $question->getAttribute('name') . '_label');
            $profileQuestions[$question_number]['question_label'] = $question_label;
            foreach ($question->getOptions() as $question_option_number=>$question_option){
                $questionOption = new CheckboxField('profileQuestionFilter__' . $question_option->questionName . '__' . $question_option->value);
                $questionOption->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $question_option->questionName . '_value_' . $question_option->value));
                $profileQuestions[$question_number]['options'][$question_option_number] = $questionOption;
                if (isset($savedValues) && isset($savedValues['profile_questions_filters'][$question->getName()])
                    && in_array($question_option->value, $savedValues['profile_questions_filters'][$question->getName()]))
                    $profileQuestions[$question_number]['options'][$question_option_number]->addAttribute("checked", "checked");
                $form->addElement($questionOption);
            }
        }

        $selectedGroupId = $service->getSelectedGroupId();
        if (isset($selectedGroupId)) {
            $lastMetric = $service->getLastGraphCalculatedMetricsByGroupId($selectedGroupId);
            $lastMetricDate = '';
            if ($lastMetric != null) {
                $lastMetricDate = UTIL_DateTime::formatSimpleDate($lastMetric->time);
            }
            $this->assign("lastCalculationDate", $lastMetricDate);
        }

        $this->assign('profileQuestions', $profileQuestions);
        $this->addForm($form);
        $this->assign('fieldsArray',$fieldsArray);

        //tooltip
        $tooltipKeyList = array();
        $tooltipList = array();
        $this->assign('tooltipKeyList', $tooltipKeyList);
        $this->assign('tooltipList', $tooltipList);
        $pageNumber = !(empty($_GET['page']) || $_GET['page'] == null) ? $_GET['page'] : 1;

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $values = $form->getValues();
            if( isset($values) ){
                $savedTopUsersConfigs = array();
                $existing_fields = array();
                if ($values['numberOfResultRows'] == null || $values['numberOfResultRows'] > $numberOfAllUsers || $values['numberOfResultRows'] < 1)
                    $values['numberOfResultRows'] = $numberOfAllUsers;
                foreach($fields as $fieldName){
                    if(isset($values[$fieldName]) ) {
                        if(floatval($values[$fieldName])!= 0) {
                            $existing_fields[$fieldName] = floatval($values[$fieldName]);
                        }
                        $form->getElement($fieldName)->setValue( floatval($values[$fieldName]) );
                        $savedTopUsersConfigs[$fieldName]= $values[$fieldName];
                    }
                }

                $rolesNameList = array();
                $roles_list = [];
                $all_roles_list = [];
                foreach ( $userRoleIdList as $role )
                {
                    $role_id = $role->getId();
                    if(isset($values['roles_' . $role_id]) ) {
                        if($values['roles_' . $role_id]) {
                            $roles_list[] = $role_id;
                            $rolesNameList[] = 'roles_' . $role_id;
                        }
                        $form->getElement('roles_' . $role->id)->setValue( $values['roles_' . $role_id] );
                    }
                    $all_roles_list[] = 'roles_' . $role_id;
                }

                foreach ( $values as $valueName=>$value ) {
                    if (strpos($valueName, "profileQuestionFilter__") !== false) {
                        if ($value) {
                            $form->getElement($valueName)->addAttribute("checked", "checked");
                        } else{
                            $form->getElement($valueName)->removeAttribute("checked");
                        }
                    }
                }
                $selectedGroupId = $service->getSelectedGroupId();
                $numberOfResultRows = $values['numberOfResultRows'];

                $allProfileQuestions = $values;
                $nonProfileQuestionFilters = array_merge(array("form_name", "csrf_token", "csrf_hash", "numberOfResultRows"), $fields, $rolesNameList, $all_roles_list);

                foreach ($nonProfileQuestionFilters as $filed){
                    unset($allProfileQuestions[$filed]);
                }

                $profileQuestionFilters = array();
                foreach ($allProfileQuestions as $filter_name=>$filter_value){
                    if (isset($filter_value)){
                        $filter_parts = explode("__", $filter_name);
                        $profileQuestionFilters[$filter_parts[1]][] = $filter_parts[2];
                    }
                }

                if ($numberOfResultRows == "")
                    $numberOfResultRows = $numberOfAllUsers;

                $allAvailablePagesNumber = ceil($numberOfAllUsers / $numberOfResultRows);
                if ($pageNumber > $allAvailablePagesNumber)
                    $pageNumber = $allAvailablePagesNumber;
                if ($pageNumber < 1)
                    $pageNumber = 1;

                $topUsers = $service->getTopUsersByFormula($selectedGroupId, $existing_fields, $numberOfResultRows,
                    $roles_list, $profileQuestionFilters, $pageNumber, false, true);

                $usersListWithQuestions = array();
                foreach ($topUsers['$users'] as $topUser) {
                    $usersListWithQuestions[] = BOL_UserService::getInstance()->getUserViewQuestions($topUser['userId'], true);
                }

                $questionData = self::setUserQuestionValues($usersListWithQuestions);
                $usersQuestionValues = $questionData[0];
                $questionAllLabels = $questionData[1];

                $savedTopUsersConfigs['selectedGroupId'] = $selectedGroupId;
                $savedTopUsersConfigs['existing_fields'] = $existing_fields;
                $savedTopUsersConfigs['numberOfResultRows'] = $numberOfResultRows;
                $savedTopUsersConfigs['page_number'] = $pageNumber;
                $savedTopUsersConfigs['roles_list'] = $roles_list;
                $savedTopUsersConfigs['profile_questions_filters'] = $profileQuestionFilters;

                OW::getConfig()->saveConfig('frmgraph', 'users_list_options', json_encode($savedTopUsersConfigs));

                $numberOfResultRecords->setValue($values['numberOfResultRows']);

                FRMGRAPH_CMP_UsersList::assigningTopUsers($usersQuestionValues, $pageNumber, $numberOfResultRecords->getValue(), $questionAllLabels);
                $paging = new BASE_CMP_Paging($pageNumber, ceil($topUsers['$total_size'] / $numberOfResultRecords->getValue()), $numberOfResultRecords->getValue());
                $this->assign('paging', $paging->render());
            }
        } else{
            $savedTopUsersConfigsJSON = OW::getConfig()->getValue('frmgraph', 'users_list_options');
            $savedTopUsersConfigs = json_decode($savedTopUsersConfigsJSON, true);
            if ($savedTopUsersConfigs != null) {
                $allAvailablePagesNumber = ceil($numberOfAllUsers / $savedTopUsersConfigs['numberOfResultRows']);
                if ($pageNumber > $allAvailablePagesNumber)
                    $pageNumber = $allAvailablePagesNumber;
                if ($pageNumber < 1)
                    $pageNumber = 1;
                $is_normalized = isset($savedValues['is_normalized']) ? $savedValues['is_normalized'] : false;

                $topUsers = $service->getTopUsersByFormula($savedTopUsersConfigs['selectedGroupId'],
                    $savedTopUsersConfigs['existing_fields'], $savedTopUsersConfigs['numberOfResultRows'],
                    $savedTopUsersConfigs['roles_list'], $savedTopUsersConfigs['profile_questions_filters'],
                    $pageNumber, $is_normalized, true);

                $foundUserIds = array();
                foreach ($topUsers['$users'] as $user){
                    $foundUserIds[] = $user['userId'];
                }

                $usersListWithQuestions = array();
                foreach ($foundUserIds as $foundUserId){
                    $usersListWithQuestions[] = BOL_UserService::getInstance()->getUserViewQuestions($foundUserId, true);
                }

                $questionData = self::setUserQuestionValues($usersListWithQuestions);
                $usersQuestionValues = $questionData[0];
                $questionAllLabels = $questionData[1];

                FRMGRAPH_CMP_UsersList::assigningTopUsers($usersQuestionValues, $pageNumber, $numberOfResultRecords->getValue(), $questionAllLabels);
                $numberOfRows = $numberOfResultRecords->getValue() == 0 ? $numberOfAllUsers : $numberOfResultRecords->getValue();
                $paging = new BASE_CMP_Paging($pageNumber, ceil($topUsers['$total_size'] / $numberOfRows), $numberOfResultRecords->getValue());
                $this->assign('paging', $paging->render());
            }
        }
    }

    public function assigningTopUsers($topUsers, $page_number, $per_page_count, $questionLabels){
        $userService = BOL_UserService::getInstance();
        unset($questionLabels['username']);
        unset($questionLabels['email']);
        unset($questionLabels['password']);
        $allInfo = array();
        $exportAllInfo = array();
        foreach ($topUsers as $key=>$topUser){
            $user = $userService->findUserById($topUser['userId']);
            if(isset($user)) {
                $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($user->id);
                $profileAvatarUrl = $avatarUrl;

                $new_item = array();
                foreach ($questionLabels as $questionName=>$questionTitle){
                    $new_item[$questionName] = "-";
                }

                $new_item = array_merge(array(
                        'rank' => $key + 1,
                        'avatar' => '<a href="'.$userService->getUserUrl($user->id).'"><img src="'.$profileAvatarUrl.'"/></a>',
                        'username' => '<a href="'.$userService->getUserUrl($user->id).'"><span>'.$user->username.'</span>',
                        'user_email' => $user->email)
                    , $new_item
                );

                $avatarImageInfo = BOL_AvatarService::getInstance()->getAvatarInfo($user->id, $profileAvatarUrl);
                if ($avatarImageInfo['empty'])
                    $new_item['avatar'] = '<a href="'.$userService->getUserUrl($user->id).'" class="colorful_avatar_' . $avatarImageInfo['digit'] .'"><span style="background-image: url('. "'" .$profileAvatarUrl. "'". '); background-color:' . $avatarImageInfo['color'] . '"/></a>';

                unset($topUser['userId']);
                unset($topUser['username']);
                unset($topUser['email']);
                unset($topUser['password']);
                foreach ($topUser as $scoreKey=>$score) {
                    if (array_key_exists($scoreKey, $new_item) ){
                        $itemValue = $score;
                        if (gettype($score) == 'array'){
                            $itemValue = '';
                            foreach ($score as $value){
                                $itemValue .= $value . ", ";
                            }
                            $itemValue = rtrim($itemValue, ", ");
                        }
                        if ($itemValue == "") $itemValue = "-";
                        $new_item[$scoreKey] = $itemValue;
                    }
                }
                $exportNewItem = $new_item;
                unset($exportNewItem['avatar']);
                $exportAllInfo[$key] = $exportNewItem;
                $allInfo[$key] = $new_item;
            }
        }

        $allInfo = array_slice($allInfo, ($page_number - 1) * $per_page_count, $per_page_count);
        $this->assign('allInfo', $allInfo);
        $this->assign('exportAllInfo', $exportAllInfo);
        $questionLabels['rank'] = OW::getLanguage()->text('frmgraph','label_row');
        $questionLabels['avatar'] = OW::getLanguage()->text('frmgraph','label_avatar');
        $questionLabels['user_email'] = OW::getLanguage()->text('frmgraph','label_user_email');
        $questionLabels['user_fullname'] = OW::getLanguage()->text('frmgraph','label_user_fullname');
        $questionLabels['user_info'] = OW::getLanguage()->text('frmgraph','label_user_info');
        $questionLabels['username'] = OW::getLanguage()->text('frmgraph','label_username');

        $labels = array();
        if(isset($allInfo[0])) {
            foreach ($allInfo[0] as $key => $value) {
                $labels[] = $questionLabels[$key];
            }
        }else{
            $this->assign('empty', true);
        }
        $this->assign('labels', $labels);

        $exportLabels = array();
        if(isset($exportAllInfo[0])) {
            foreach ($exportAllInfo[0] as $key => $value) {
                $exportLabels[] = $questionLabels[$key];
            }
        } else{
            $this->assign('empty', true);
        }
        $this->assign('exportLabels', $exportLabels);
    }

    private static function setUserQuestionValues($usersListWithQuestions)
    {
        $allQuestionsLabels = array();
        $allQuestionsLabelsAndTranslates = array();
        $resultQuestionLabels = array();
        $usersQuestionValues = array();
        foreach ($usersListWithQuestions as $qData){
            $questionData = $qData['data'];
            $allQuestionsLabelsAndTranslates = array_merge($allQuestionsLabelsAndTranslates, $qData['labels'] );

            foreach (array_values($questionData)[0] as $qTitle=>$qAnswer){
                if ($qAnswer != null && $qAnswer != "")
                    $allQuestionsLabels[] = $qTitle;
            }
            $userQuestionAnswers = array_values($questionData)[0];
            unset($userQuestionAnswers['password']);
            unset($userQuestionAnswers['email']);
            $usersQuestionValues[] = array_merge(array('userId'=>array_keys($questionData)[0]), $userQuestionAnswers);
        }
        foreach (array_unique($allQuestionsLabels) as $qLabel){
            $resultQuestionLabels[$qLabel] = $allQuestionsLabelsAndTranslates[$qLabel];
        }
        return [$usersQuestionValues, $resultQuestionLabels];
    }
}