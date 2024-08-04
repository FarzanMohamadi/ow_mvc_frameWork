<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_AdminGroupView extends OW_Component
{

    /**
     * FRMGRAPH_CMP_AdminGroupView constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct($params = null, $isAdminPage = true)
    {
        parent::__construct();
        $this->groupViewCmp($params, $isAdminPage);
    }

    private function groupViewCmp($params, $isAdminPage = true)
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        if($isAdminPage){
            $this->assign('sections', $service->getAdminSections(1));
            $this->assign('subsections', $service->getAdminSubSections(1,1));
        }else{
            $this->assign('sections', $service->getGraphSections(4));
            $this->assign('subsections', $service->getGraphSubSections(4,1));
        }

        $selectedGroupId = $service->getSelectedGroupId();
        if (isset($selectedGroupId))
            $this->assign("lastCalculationDate", UTIL_DateTime::formatSimpleDate(
                $service->getLastGraphCalculatedMetricsByGroupId($selectedGroupId)->time));

        $groupService = GROUPS_BOL_Service::getInstance();
        $form = new Form('mainForm');
        if($isAdminPage){
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.admin.graph_view.group'));
        }else{
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.graph_view.group'));
        }
        $groupIdField = new TextField('gId');
        $groupIdField->setLabel(OW::getLanguage()->text('frmgraph','label_gId'));
        $groupIdField->setRequired();
        $form->addElement($groupIdField);

        $depthField = new TextField('depth');
        $depthField->setLabel(OW::getLanguage()->text('frmgraph','label_depth'));
        $depthField->setRequired();
        $depthField->setValue(2);
        $form->addElement($depthField);

        $submitField = new Submit('submit');
        $form->addElement($submitField);

        $this->addForm($form);

        $this->assign('noGroup',true);
        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $gId = $form->getValues()['gId'];
            $depth = intval($form->getValues()['depth']);
            $group = $groupService->findGroupById($gId);
            if( $group != null ){
                $this->addComponent('groupComponent', new GROUPS_CMP_BriefInfo($group->id));

                $graphPlugin = OW::getPluginManager()->getPlugin('frmgraph');
                OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'vis.min.js');
                OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'graph.js');
                OW::getDocument()->addStyleSheet($graphPlugin->getStaticCssUrl().'vis.min.css');
                OW::getDocument()->addStyleSheet($graphPlugin->getStaticCssUrl().'graph.css');

                $colors = array('#111','#54B4FF','#FF5751','#A6FF9E','#FFFF60','#c0c','#0cc');
                $groupNetwork = $service->getGroupNetwork($group->id, $depth);
                $depthCount = array();

                $legendItems = array();
                $js = "var nodes = [";

                foreach ($groupNetwork as $curNodeId => $NodeInfo) {
                    $curUser = $groupService->findGroupById($curNodeId);
                    if(!isset($curUser)){
                        continue;
                    }
                    $displayName = $curUser->title. "<br/>". OW::getLanguage()->text('frmgraph','label_gId').'='.$curUser->id;
                    $curLevel = isset($NodeInfo['depth'])?$NodeInfo['depth']:6;
                    $depthCount[$curLevel] = isset($depthCount[$curLevel])?$depthCount[$curLevel]+1:1;
                    $visualLevel = $service->getGraphVisualLevel($curLevel, $depthCount[$curLevel], 20, 10);
                    $js = $js . "{id: ".$curUser->id.", label: '".$curUser->title."', title: '".$displayName."', group: ".$curLevel.", level: ".$visualLevel.", color: '".$colors[$curLevel]."'},";
                    if(!isset($legendItems[$curLevel])){
                        $legendItems[$curLevel] = array('type'=>'node','label'=>OW::getLanguage()->text('frmgraph','label_legend_node_'.$curLevel), 'color'=>$colors[$curLevel]);
                    }
                }
                $js = substr($js, 0, strlen($js) - 1);
                $js .= "];";

                $js .= "var edges = [ ";
                foreach ($groupNetwork as $curNodeId => $NodeInfo) {
                    foreach ($NodeInfo['follows'] as $followsNodeItem) {
                        $js = $js . "{from: " . $curNodeId . ", to: " . $followsNodeItem['id'] . "},"; //, value: ".$followsNodeItem['w']."
                    }
                }
                $js = substr($js, 0, strlen($js) - 1);
                $js .= "];";

                $js .= "redrawUserGraph(nodes, edges);";
                OW::getDocument()->addOnloadScript($js);
                $this->assign('noGroup',false);
                $this->assign('legendItems',$legendItems);
            }
            else {
                $groupIdField->addError('Enter a valid group Id');
            }
        }
    }
}