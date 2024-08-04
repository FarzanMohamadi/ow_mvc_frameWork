<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_AdminAllGroups extends OW_Component
{

    /**
     * FRMGRAPH_CMP_AdminAllGroups constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct($params = null, $isAdminPage = true)
    {
        parent::__construct();
        $this->allGroupsCmp($params, $isAdminPage);
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'fileSaver.min.js' );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'xlsx.full.min.js' );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'tableExport.min.js' );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmgraph')->getStaticJsUrl() . 'frmgraph.js' );
    }

    private function allGroupsCmp($params, $isAdminPage = true)
    {

        $service = FRMGRAPH_BOL_Service::getInstance();
        if($isAdminPage){
            $this->assign('sections', $service->getAdminSections(3));
            $this->assign('subsections', $service->getAdminSubSections(3,0));
        }else{
            $this->assign('sections', $service->getGraphSections(3));
            $this->assign('subsections', $service->getGraphSubSections(3,0));
        }

        $selectedGroupId = $service->getSelectedGroupId();
        if (isset($selectedGroupId))
            $this->assign("lastCalculationDate", UTIL_DateTime::formatSimpleDate(
                $service->getLastGraphCalculatedMetricsByGroupId($selectedGroupId)->time));

        $groupService = GROUPS_BOL_Service::getInstance();
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmgraph')->getStaticCssUrl().'graph.css');

        $form = new Form('mainForm');
        if($isAdminPage){
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.admin.group.all_groups'));
        }else{
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.group.all_groups'));
        }

        $fieldsArray = array();
        $fields = array('users_count','degree_cent','closeness_cent','betweenness_cent','hub','authority','cluster_coe','page_rank',
            'contents_count','files_count','all_activities_count', 'users_interactions_count');

        if (OW::getConfig()->configExists('frmgraph', 'frmgraph_all_groups_settings')) {
            $JsonSavedValues = OW::getConfig()->getValue('frmgraph', 'frmgraph_all_groups_settings');
            $savedValues = json_decode($JsonSavedValues, true);
        }

        foreach ($fields as $fieldName) {
            $field = new TextField($fieldName);
            $field->addAttribute('type','number')->addAttribute('step','0.01');
            $field->setLabel(OW::getLanguage()->text('frmgraph','label_'.$fieldName));
            if(isset($savedValues[$fieldName]) && $savedValues[$fieldName] !=null ){
                $field->setValue($savedValues[$fieldName]);
            }
            $form->addElement($field);
            $fieldsArray[] = $fieldName;
        }

        if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
            $categories_list = FRMGROUPSPLUS_BOL_Service::getInstance()->getGroupCategoryList();
            if ($categories_list != null) {
                $tpCategoryList = array();
                foreach ($categories_list as $category) {
                    $field = new CheckboxField('categories_' . $category->getId());
                    $field->setLabel($category->getLabel());
                    $form->addElement($field);
                    $tpCategoryList[] = $category;
                }
                $this->assign('categoryList', $tpCategoryList);
            }
        }

        $submitField = new Submit('submit');
        $form->addElement($submitField);
        $exportExcelButton = new button('export_excel');
        $form->addElement($exportExcelButton);

        $numberOfAllGroups = GROUPS_BOL_Service::getInstance()->findAllGroupCount();
        $numberOfResultRecords = new TextField('results_rows_number');
        $numberOfResultRecords->addAttribute('type','number')->addAttribute('step','1')->addAttribute('value', $numberOfAllGroups);
        if(isset($savedValues['results_rows_number']) && $savedValues['results_rows_number'] != null ){
            $numberOfResultRecords->setValue($savedValues['results_rows_number']);
        }
        else{
            $numberOfResultRecords->setValue($numberOfAllGroups);
        }
        $form->addElement($numberOfResultRecords);

        $isNormalized = new CheckboxField('is_normalized');
        $isNormalized->setLabel(OW::getLanguage()->text('frmgraph','active'));
        $isNormalized->setValue(false);
        if (isset($savedValues) && isset($savedValues['is_normalized']) && $savedValues['is_normalized'] == true)
            $isNormalized->addAttribute("checked", "checked");
        $form->addElement($isNormalized);

        $this->addForm($form);
        $this->assign('fieldsArray',$fieldsArray);

        //tooltip
        $tooltipKeyList = array('degree_cent','closeness_cent','betweenness_cent','hub','authority','cluster_coe','page_rank',
            'files_count','all_activities_count', 'users_interactions_count');
        $tooltipList = array();
        foreach($tooltipKeyList as $key){
            $tooltipList[$key] = OW::getLanguage()->text('frmgraph','tooltip_'.$key);
        }
        $this->assign('tooltipKeyList', $tooltipKeyList);
        $this->assign('tooltipList', $tooltipList);

        $pageNumber = !(empty($_GET['page']) || $_GET['page'] == null) ? $_GET['page'] : 1;
        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $values = $form->getValues();
            if( $values != null ){
                $toSavedValues = array();
                $existing_fields = array();
                if ($values['results_rows_number'] == null || $values['results_rows_number'] > $numberOfAllGroups || $values['results_rows_number'] < 1)
                    $values['results_rows_number'] = $numberOfAllGroups;
                foreach($fields as $fieldName){
                    if(isset($values[$fieldName]) ) {
                        if(floatval($values[$fieldName])!= 0) {
                            $existing_fields[$fieldName] = floatval($values[$fieldName]);
                        }
                        $form->getElement($fieldName)->setValue( floatval($values[$fieldName]) );
                        $toSavedValues[$fieldName]= $values[$fieldName];
                    }
                }

                $numberOfResultRecords->setValue($values['results_rows_number']);
                $toSavedValues['results_rows_number'] = $values['results_rows_number'];
                $JsonValues=json_encode($toSavedValues);
                OW::getConfig()->saveConfig('frmgraph', 'frmgraph_all_groups_settings', $JsonValues);

                $selected_category_list = [];
                foreach ( $categories_list as $category )
                {
                    if(isset($values['categories_' . $category->getId()]) ) {
                        if($values['categories_' . $category->getId()]) {
                            $selected_category_list[] = $category->getId();
                        }
                        $form->getElement('categories_' . $category->id)->setValue( $values['categories_' . $category->getId()] );
                    }
                }

                $selectedGroupId = $service->getSelectedGroupId();
                $numberOfResultRows = $values['results_rows_number'];
                $topGroups = $service->getTopGroupsByFormula($selectedGroupId,$existing_fields,$numberOfResultRows, $pageNumber, $selected_category_list, $values['is_normalized']);
                FRMGRAPH_CMP_AdminAllGroups::assigningTopGroups($topGroups, $pageNumber, $numberOfResultRows);

                if ($numberOfResultRows == "")
                    $numberOfResultRows = $numberOfAllGroups;
                $topGroups = $service->getTopGroupsByFormula($selectedGroupId,$existing_fields,$numberOfResultRows, $pageNumber, $selected_category_list);
                $savedTopGroupsConfigs = array();
                $savedTopGroupsConfigs['selectedGroupId'] = $selectedGroupId;
                $savedTopGroupsConfigs['existing_fields'] = $existing_fields;
                $savedTopGroupsConfigs['numberOfResultRows'] = $numberOfResultRows;
                $savedTopGroupsConfigs['categoryList'] = $selected_category_list;
                $savedTopGroupsConfigs['is_normalized'] = $values['is_normalized'];

                OW::getConfig()->saveConfig('frmgraph', 'frmgraph_fetch_all_groups_configs', json_encode($savedTopGroupsConfigs));

                $paging = new BASE_CMP_Paging($pageNumber, ceil($topGroups['total_size'] / $numberOfResultRecords->getValue()), $numberOfResultRecords->getValue());
                $this->assign('paging', $paging->render());
            }
        } else {
            $savedTopGroupsConfigsJSON = OW::getConfig()->getValue('frmgraph', 'frmgraph_fetch_all_groups_configs');
            $savedTopGroupsConfigs = json_decode($savedTopGroupsConfigsJSON, true);
            if ($savedTopGroupsConfigs != null) {
                $is_normalized = isset($savedValues['is_normalized']) ? $savedValues['is_normalized'] : false;
                $topGroups = $service->getTopGroupsByFormula($savedTopGroupsConfigs['selectedGroupId'], $savedTopGroupsConfigs['existing_fields'], $savedTopGroupsConfigs['numberOfResultRows'], $pageNumber, $savedTopGroupsConfigs['categoryList'], $is_normalized);
                FRMGRAPH_CMP_AdminAllGroups::assigningTopGroups($topGroups, $pageNumber, $numberOfResultRecords->getValue());
                $paging = new BASE_CMP_Paging($pageNumber, ceil($topGroups['total_size'] / $numberOfResultRecords->getValue()), $numberOfResultRecords->getValue());
                $this->assign('paging', $paging->render());
            }
        }
    }

    public function assigningTopGroups($topGroups, $page_number, $per_page_count){
        $groupService = GROUPS_BOL_Service::getInstance();
        $allInfo = array();
        $exportAllInfo = array();
        foreach ($topGroups['groups'] as $key =>$topNode){
            $group = $groupService->findGroupById($topNode['gId']);
            if(isset($group)) {
                $new_item = array(
                    'rank' => ($page_number - 1 ) * $per_page_count + $key+1,
                    'group_title' => $group->title,
                    'group_image' => '<a href="'.$groupService->getGroupUrl($group).'"><img src="'.$groupService->getGroupImageUrl($group).'"/></a>',
                    'group_info' => '<div><a href="'.$groupService->getGroupUrl($group).'">'.$group->title.'</a></div>'
                );
                $avatarImageInfo = BOL_AvatarService::getInstance()->getAvatarInfo($group->id, $groupService->getGroupImageUrl($group));
                if ($avatarImageInfo['empty'])
                    $new_item['group_image'] = '<a href="'.$groupService->getGroupUrl($group).'" class="colorful_avatar_' . $avatarImageInfo['digit'] .'"><img src="'.$groupService->getGroupImageUrl($group).'" style="background-color: ' . $avatarImageInfo['color'] .'"/></a>';

                unset($topNode['userId']);
                foreach ($topNode as $key2 => $score) {
                    $new_item[$key2] = round($score, 3);
                }
                $exportNewItem = $new_item;
                unset($exportNewItem['group_info']);
                unset($exportNewItem['group_image']);
                unset($new_item['group_title']);
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