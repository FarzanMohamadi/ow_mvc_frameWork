<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_AdminUserView extends OW_Component
{

    /**
     * FRMGRAPH_CMP_AdminUserView constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct($params = null, $isAdminPage = true)
    {
        parent::__construct();
        $this->userViewCmp($params, $isAdminPage);
    }

    private function userViewCmp($params, $isAdminPage = true)
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        if($isAdminPage){
            $this->assign('sections', $service->getAdminSections(1));
            $this->assign('subsections', $service->getAdminSubSections(1,0));
        }else{
            $this->assign('sections', $service->getGraphSections(4));
            $this->assign('subsections', $service->getGraphSubSections(4,0));
        }

        $selectedGroupId = $service->getSelectedGroupId();
        if (isset($selectedGroupId))
            $this->assign("lastCalculationDate", UTIL_DateTime::formatSimpleDate(
                $service->getLastGraphCalculatedMetricsByGroupId($selectedGroupId)->time));

        $userService = BOL_UserService::getInstance();
        $form = new Form('mainForm');
        if($isAdminPage){
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.admin.graph_view.user'));
        }else{
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.graph_view.user'));
        }
        $usernameField = new TextField('username');
        $usernameField->setLabel(OW::getLanguage()->text('frmgraph','label_username'));
        $usernameField->setRequired();
        $form->addElement($usernameField);

        $depthField = new TextField('depth');
        $depthField->setLabel(OW::getLanguage()->text('frmgraph','label_depth'));
        $depthField->setRequired();
        $depthField->setValue(2);
        $form->addElement($depthField);

        $submitField = new Submit('submit');
        $form->addElement($submitField);

        $this->addForm($form);

        $this->assign('noUsername',true);
        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $username = $form->getValues()['username'];
            $depth = intval($form->getValues()['depth']);
            $user = BOL_UserService::getInstance()->findByUsername($username);
            if( $user != null ){
                $this->addComponent('userComponent', new FRMGRAPH_CMP_UserInfo($user->username));

                $graphPlugin = OW::getPluginManager()->getPlugin('frmgraph');
                OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'vis.min.js');
                OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'graph.js');
                OW::getDocument()->addStyleSheet($graphPlugin->getStaticCssUrl().'vis.min.css');
                OW::getDocument()->addStyleSheet($graphPlugin->getStaticCssUrl().'graph.css');

                $colors = array('#111','#54B4FF','#FF5751','#A6FF9E','#FFFF60','#c0c','#0cc');
                $userNetwork = $service->getUserNetwork($user->id, $depth);
                $depthCount = array();

                $legendItems = array();
                $js = "var nodes = [";

                foreach ($userNetwork as $curUserId => $userInfo) {
                    $curUser = $userService->findUserById($curUserId);
                    if(!isset($curUser)){
                        continue;
                    }
                    $displayName = $userService->getDisplayName($curUser->id). "<br/>". OW::getLanguage()->text('frmgraph','label_username').'='.$curUser->username;
                    $curLevel = isset($userInfo['depth'])?$userInfo['depth']:6;
                    $depthCount[$curLevel] = isset($depthCount[$curLevel])?$depthCount[$curLevel]+1:1;
                    $visualLevel = $service->getGraphVisualLevel($curLevel, $depthCount[$curLevel], 20, 10);
                    $js = $js . "{id: ".$curUser->id.", label: '".$userService->getDisplayName($curUser->id)."', title: '".$displayName."', group: ".$curLevel.", level: ".$visualLevel.", color: '".$colors[$curLevel]."'},";
                    if(!isset($legendItems[$curLevel])){
                        $legendItems[$curLevel] = array('type'=>'node','label'=>OW::getLanguage()->text('frmgraph','label_legend_node_'.$curLevel), 'color'=>$colors[$curLevel]);
                    }
                }
                $js = substr($js, 0, strlen($js) - 1);
                $js .= "];";

                $js .= "var edges = [ ";
                foreach ($userNetwork as $curUserId => $userInfo) {
                    foreach ($userInfo['follows'] as $followsUserId) {
                        $js = $js . "{from: " . $curUserId . ", to: " . $followsUserId . "},";
                    }
                }
                $js = substr($js, 0, strlen($js) - 1);
                $js .= "];";

                $js .= "redrawUserGraph(nodes, edges);";
                OW::getDocument()->addOnloadScript($js);
                $this->assign('noUsername',false);
                $this->assign('legendItems',$legendItems);
            }
            else {
                $usernameField->addError('Enter a valid username');
            }
        }
    }
}