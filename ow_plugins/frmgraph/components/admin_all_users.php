<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_AdminAllUsers extends OW_Component
{

    /**
     * FRMGRAPH_CMP_AdminAllUsers constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct($params = null)
    {
        parent::__construct();
        $this->allUsersCmp();
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'fileSaver.min.js' );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'xlsx.full.min.js' );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'tableExport.min.js' );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmgraph')->getStaticJsUrl() . 'frmgraph.js' );
    }

    private function allUsersCmp()
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        $this->assign('sections', $service->getGraphSections(2));
        $this->assign('subsections', $service->getGraphSubSections(2, 0));

        $selectedGroupId = $service->getSelectedGroupId();
        if (isset($selectedGroupId)) {
            $lastMetric = $service->getLastGraphCalculatedMetricsByGroupId($selectedGroupId);
            $lastMetricDate = '';
            if ($lastMetric != null) {
                $lastMetricDate = UTIL_DateTime::formatSimpleDate($lastMetric->time);
            }
            $this->assign("lastCalculationDate", $lastMetricDate);
        }

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmgraph')->getStaticCssUrl().'graph.css');

        $form = new Form('mainForm');
        $form->setAction(OW::getRouter()->urlForRoute('frmgraph.user.all_users'));
        $fieldsArray = array();
        $fields = array('degree_cent','closeness_cent','betweenness_cent','eccentricity_cent','hub','authority','cluster_coe','page_rank',
            'contents_count','pictures_count','videos_count','news_count','all_contents_count','all_activities_count','all_done_activities_count');

        if (OW::getConfig()->configExists('frmgraph', 'frmgraph_all_users_settings')) {
            $JsonSavedValues = OW::getConfig()->getValue('frmgraph', 'frmgraph_all_users_settings');
            $savedValues = json_decode($JsonSavedValues, true);
        }
        foreach ($fields as $fieldName) {
            $field = new TextField($fieldName);
            $field->setLabel(OW::getLanguage()->text('frmgraph','label_'.$fieldName));
            $field->addAttribute('type','number')->addAttribute('step','0.01');
            if(isset($savedValues[$fieldName]) && $savedValues[$fieldName] !=null ){
                $field->setValue($savedValues[$fieldName]);
            }
            $form->addElement($field);
            $fieldsArray[] = $fieldName;
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

        $numberOfAllUsers =  BOL_UserService::getInstance()->count(true);

        $numberOfResultRecords = new TextField('results_rows_number');
        $numberOfResultRecords->addAttribute('type','number')->addAttribute('step','1')->addAttribute('value', $numberOfAllUsers);
        $numberOfResultsValue = null;
        if (isset($savedValues) && isset($savedValues['results_rows_number']))
            $numberOfResultsValue = $savedValues['results_rows_number'];
        if(isset($numberOfResultsValue) && isset($savedValues) && $numberOfResultsValue != null && $numberOfResultsValue > 0){
            $numberOfResultRecords->setValue($savedValues['results_rows_number']);
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
                $form->addElement($questionOption);
            }
        }

        $isNormalized = new CheckboxField('is_normalized');
        $isNormalized->setLabel(OW::getLanguage()->text('frmgraph','active'));
        $isNormalized->setValue(false);
        if (isset($savedValues) && isset($savedValues['is_normalized']) && $savedValues['is_normalized'] == true)
            $isNormalized->addAttribute("checked", "checked");
        $form->addElement($isNormalized);

        $this->assign('profileQuestions', $profileQuestions);
        $this->addForm($form);
        $this->assign('fieldsArray',$fieldsArray);

        //tooltip
        $tooltipKeyList = array('degree_cent','closeness_cent','betweenness_cent','eccentricity_cent','hub','authority','cluster_coe','page_rank',
            'all_contents_count','all_activities_count','all_done_activities_count');
        $tooltipList = array();
        foreach($tooltipKeyList as $key){
            $tooltipList[$key] = OW::getLanguage()->text('frmgraph','tooltip_'.$key);
        }
        $this->assign('tooltipKeyList', $tooltipKeyList);
        $this->assign('tooltipList', $tooltipList);
        $pageNumber = !(empty($_GET['page']) || $_GET['page'] == null) ? $_GET['page'] : 1;

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $values = $form->getValues();
            $selectedGroupId = $service->getSelectedGroupId();

            if( isset($values) ){
                $toSavedValues = array();
                $existing_fields = array();
                if ($values['results_rows_number'] == null || $values['results_rows_number'] > $numberOfAllUsers || $values['results_rows_number'] < 1)
                    $values['results_rows_number'] = $numberOfAllUsers;
                foreach($fields as $fieldName){
                    if(isset($values[$fieldName]) ) {
                        if(floatval($values[$fieldName])!= 0) {
                            $existing_fields[$fieldName] = floatval($values[$fieldName]);
                        }
                        $form->getElement($fieldName)->setValue( floatval($values[$fieldName]) );
                        $toSavedValues[$fieldName]= $values[$fieldName];

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
                $numberOfResultRows = $values['results_rows_number'];

                $allProfileQuestions = $values;
                $nonProfileQuestionFilters = array_merge(array("form_name", "csrf_token", "csrf_hash", "results_rows_number", "is_normalized"), $fields, $rolesNameList, $all_roles_list);

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

                $topUsers = $service->getTopUsersByFormula($selectedGroupId, $existing_fields, $numberOfResultRows, $roles_list, $profileQuestionFilters, $pageNumber, $values['is_normalized']);
                $savedTopUsersConfigs = array();
                $savedTopUsersConfigs['selectedGroupId'] = $selectedGroupId;
                $savedTopUsersConfigs['existing_fields'] = $existing_fields;
                $savedTopUsersConfigs['numberOfResultRows'] = $numberOfResultRows;
                $savedTopUsersConfigs['page_number'] = $pageNumber;
                $savedTopUsersConfigs['roles_list'] = $roles_list;
                $savedTopUsersConfigs['profile_questions_filters'] = $profileQuestionFilters;

                OW::getConfig()->saveConfig('frmgraph', 'frmgraph_fetch_all_users_configs', json_encode($savedTopUsersConfigs));

                $numberOfResultRecords->setValue($values['results_rows_number']);

                $toSavedValues['results_rows_number'] = $values['results_rows_number'];
                $toSavedValues['is_normalized'] = $values['is_normalized'];
                $toSavedValues['selectedGroupId'] = $selectedGroupId;
                $JsonValues=json_encode($toSavedValues);
                OW::getConfig()->saveConfig('frmgraph', 'frmgraph_all_users_settings', $JsonValues);

                FRMGRAPH_CMP_AdminAllUsers::assigningTopUsers($topUsers['$users'], $pageNumber, $numberOfResultRecords->getValue());
                $paging = new BASE_CMP_Paging($pageNumber, ceil($topUsers['$total_size'] / $numberOfResultRecords->getValue()), $numberOfResultRecords->getValue());
                $this->assign('paging', $paging->render());
            }
        } else {
            $savedTopUsersConfigsJSON = OW::getConfig()->getValue('frmgraph', 'frmgraph_fetch_all_users_configs');
            $savedTopUsersConfigs = json_decode($savedTopUsersConfigsJSON, true);
            if ($savedTopUsersConfigs != null) {
                $allAvailablePagesNumber = ceil($numberOfAllUsers / $savedTopUsersConfigs['numberOfResultRows']);
                if ($pageNumber > $allAvailablePagesNumber)
                    $pageNumber = $allAvailablePagesNumber;
                if ($pageNumber < 1)
                    $pageNumber = 1;
                $is_normalized = isset($savedValues['is_normalized']) ? $savedValues['is_normalized'] : false;
                $topUsers = $service->getTopUsersByFormula($selectedGroupId, $savedTopUsersConfigs['existing_fields'], $savedTopUsersConfigs['numberOfResultRows'], $savedTopUsersConfigs['roles_list'], $savedTopUsersConfigs['profile_questions_filters'], $pageNumber, $is_normalized);
                FRMGRAPH_CMP_AdminAllUsers::assigningTopUsers($topUsers['$users'], $pageNumber, $numberOfResultRecords->getValue());
                $numberOfRows = $numberOfResultRecords->getValue() == 0 ? $numberOfAllUsers : $numberOfResultRecords->getValue();
                $paging = new BASE_CMP_Paging($pageNumber, ceil($topUsers['$total_size'] / $numberOfRows), $numberOfResultRecords->getValue());
                $this->assign('paging', $paging->render());
            }
        }
    }

    public function assigningTopUsers($topUsers, $page_number, $per_page_count){
        $userService = BOL_UserService::getInstance();
        $allInfo = array();
        $exportAllInfo = array();
        foreach ($topUsers as $key =>$topUser){
            $user = $userService->findUserById($topUser['userId']);
            if(isset($user)) {
                $profileAvatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($user->id);
                $new_item = array(
                    'rank' => ($page_number - 1 ) * $per_page_count + $key + 1,
                    'user_fullname' => $userService->getDisplayName($user->id),
                    'user_email' => $user->email,
                    'avatar' => '<a href="'.$userService->getUserUrl($user->id).'"><img src="'.$profileAvatarUrl.'"/></a>',
                    'user_info' => '<div><a href="'.$userService->getUserUrl($user->id).'">'.$userService->getDisplayName($user->id).'</a></div><div>'.$user->email.'</div>'
                );
                $avatarImageInfo = BOL_AvatarService::getInstance()->getAvatarInfo($user->id, $profileAvatarUrl);
                if ($avatarImageInfo['empty'])
                    $new_item['avatar'] = '<a href="'.$userService->getUserUrl($user->id).'" class="colorful_avatar_' . $avatarImageInfo['digit'] .'"><span style="background-image: url('. "'" .$profileAvatarUrl. "'". '); background-color:' . $avatarImageInfo['color'] . '"/></a>';

                unset($topUser['userId']);
                foreach ($topUser as $key2 => $score) {
                    $new_item[$key2] = round($score, 3);
                }
                $exportNewItem = $new_item;
                unset($exportNewItem['user_info']);
                unset($exportNewItem['avatar']);
                unset($new_item['user_fullname']);
                unset($new_item['user_email']);
                $exportAllInfo[$key] = $exportNewItem;
                $allInfo[$key] = $new_item;
            }
        }
        $this->assign('allInfo', $allInfo);
        $this->assign('exportAllInfo', $exportAllInfo);

        $labels = array();
        if(isset($allInfo[0])) {
            foreach ($allInfo[0] as $key => $value) {
                $labels[] = OW::getLanguage()->text('frmgraph','label_'.$key);
            }
        }else{
            $this->assign('empty', true);
        }
        $this->assign('labels', $labels);

        $exportLabels = array();
        if(isset($exportAllInfo[0])) {
            foreach ($exportAllInfo[0] as $key => $value) {
                $exportLabels[] = OW::getLanguage()->text('frmgraph','label_'.$key);
            }
        }else{
            $this->assign('empty', true);
        }
        $this->assign('exportLabels', $exportLabels);
    }
}