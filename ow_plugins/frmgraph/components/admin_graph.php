<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_AdminGraph extends OW_Component
{

    /**
     * FRMGRAPH_CMP_AdminGraph constructor.
     * @param BASE_CLASS_WidgetParameter $params
     * @param $isAdminPage
     */
    public function __construct($params = null, $isAdminPage = true)
    {
        parent::__construct();
        $this->graphCmp($params, $isAdminPage);
    }

    private function graphCmp($params, $isAdminPage = true)
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        if($isAdminPage){
            $this->assign('sections', $service->getAdminSections(1));
            $this->assign('subsections', $service->getAdminSubSections(1,2));
        }else{
            $this->assign('sections', $service->getGraphSections(1));
            $this->assign('subsections', $service->getGraphSubSections(1,2));
        }

        $selectedGroupId = $service->getSelectedGroupId();
        if (isset($selectedGroupId))
            $this->assign("lastCalculationDate", UTIL_DateTime::formatSimpleDate(
                $service->getLastGraphCalculatedMetricsByGroupId($selectedGroupId)->time));

        $graphPlugin = OW::getPluginManager()->getPlugin('frmgraph');
        OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'vis.min.js');
        OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'graph.js');
        OW::getDocument()->addStyleSheet($graphPlugin->getStaticCssUrl().'vis.min.css');
        OW::getDocument()->addStyleSheet($graphPlugin->getStaticCssUrl().'graph.css');
        $clusterByQuestion = OW::getConfig()->getValue('frmgraph', 'question');

        $js = "var nodes = [";
        $numberOfUsers = BOL_UserService::getInstance()->count(true);
        $users = BOL_UserService::getInstance()->findList(0, $numberOfUsers, true);

        $userIdList = array();
        foreach ($users as $user) {
            $userIdList[] = $user->id;
        }

        $questionClusterValue = BOL_QuestionService::getInstance()->getQuestionData($userIdList, array($clusterByQuestion));

        foreach ($users as $key => $user) {
            $groupValue = -1;
            if(isset($questionClusterValue[$user->id][$clusterByQuestion])){
                $groupValue = $questionClusterValue[$user->id][$clusterByQuestion];
            }
            $js = $js . "{id: ".$user->id.", label: '".$user->username."', title: '".$user->email."', value: 1, group: ".$groupValue."},";
        }
        $js = substr($js, 0, strlen($js) - 1);
        $js .= "];";

        $js .= "var edges = [";
        $edgeList = $service->getAllRelationship();
        foreach ($edgeList as $friendship){
            $js = $js . "{from: ".$friendship['userId'].", to: ".$friendship['feedId']."},";
        }
        $js = substr($js, 0, strlen($js) - 1);
        $js .= "];";
        $js .= "redrawAll(nodes, edges);";
        OW::getDocument()->addOnloadScript($js);


        //cluster
        $serverForm = new Form('clusterForm');

        $config = OW::getConfig();
        $questionField = new Selectbox('question');
        $questionField->setLabel(OW::getLanguage()->text('frmgraph', 'cluster_by_question'));
        $questionField->addOptions($service->getAllQuestionsProfile());
        $questionField->setValue($config->getValue('frmgraph', 'question'));
        $serverForm->addElement($questionField);

        $submitField = new Submit('submit');
        $serverForm->addElement($submitField);
        $this->addForm($serverForm);

        if (OW::getRequest()->isPost() && $serverForm->isValid($_POST)) {
            $data = $serverForm->getValues();
            $config->saveConfig('frmgraph', 'question', $data['question']);
            OW::getFeedback()->info(OW::getLanguage()->text('frmgraph', 'modified_successfully'));
            $this->redirect();
        }
    }
}