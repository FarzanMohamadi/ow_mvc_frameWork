<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_AdminOneGroup extends OW_Component
{

    /**
     * FRMGRAPH_CMP_AdminOneGroup constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct($params = null, $isAdminPage = true)
    {
        parent::__construct();
        $this->oneGroupCmp($params, $isAdminPage);
    }

    private function oneGroupCmp($params, $isAdminPage = true)
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        if($isAdminPage) {
            $this->assign('sections', $service->getAdminSections(3));
            $this->assign('subsections', $service->getAdminSubSections(3, 1));
        }else{
            $this->assign('sections', $service->getGraphSections(3));
            $this->assign('subsections', $service->getGraphSubSections(3, 1));
        }

        $selectedGroupId = $service->getSelectedGroupId();
        if (isset($selectedGroupId))
            $this->assign("lastCalculationDate", UTIL_DateTime::formatSimpleDate(
                $service->getLastGraphCalculatedMetricsByGroupId($selectedGroupId)->time));

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmgraph')->getStaticCssUrl().'graph.css');

        $form = new Form('mainForm');
        if($isAdminPage) {
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.admin.group.one_group'));
        }else{
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.group.one_group'));
        }
        $groupIdField = new TextField('gId');
        $groupIdField->setLabel(OW::getLanguage()->text('frmgraph','label_gId'));
        $groupIdField->setRequired();
        $form->addElement($groupIdField);

        $submitField = new Submit('submit');
        $form->addElement($submitField);

        $this->addForm($form);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $gId = $form->getValues()['gId'];
            $selectedGroupId = $service->getSelectedGroupId();
            $groupDataObject = $service->getGroupDataByGroupId($gId, $selectedGroupId);
            if(isset($groupDataObject)) {
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($gId);
                if( $group != null ) {
                    $this->addComponent('groupComponent', new GROUPS_CMP_BriefInfo($group->id));
                }else{
                    $this->assign('groupComponent', '');
                }
                $groupDataObject->time = UTIL_DateTime::formatDate($groupDataObject->time, false);
                unset($groupDataObject->id);
                $tmpArray = (array) $groupDataObject;
                $fields = array('gId', 'users_count', 'degree_cent','closeness_cent','betweenness_cent','hub','authority','cluster_coe','page_rank',
                    'contents_count','files_count','all_activities_count', 'users_interactions_count');
                $groupData = array();
                foreach ($fields as $fieldName) {
                    $groupData[$fieldName] = array('label' => OW::getLanguage()->text('frmgraph','label_'.$fieldName),
                        'value'=>$tmpArray[$fieldName]
                    );
                }
                $this->assign('groupData', $groupData);
                $this->assign('noGroup', false);

                //tooltip
                $tooltipKeyList = array('degree_cent','closeness_cent','betweenness_cent','hub','authority','cluster_coe','page_rank',
                    'files_count','all_activities_count', 'users_interactions_count');
                $tooltipList = array();
                foreach($tooltipKeyList as $key){
                    $tooltipList[$key] = OW::getLanguage()->text('frmgraph','tooltip_'.$key);
                }
                $this->assign('tooltipKeyList', $tooltipKeyList);
                $this->assign('tooltipList', $tooltipList);
                return;
            }
            else {
                $groupIdField->addError(OW::getLanguage()->text('frmgraph','no_data_to_display'));
            }
        }
        $this->assign('noGroup',true);

    }
}