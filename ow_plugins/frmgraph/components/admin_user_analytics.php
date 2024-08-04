<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_AdminUserAnalytics extends OW_Component
{

    /**
     * FRMGRAPH_CMP_AdminUserAnalytics constructor.
     * @param BASE_CLASS_WidgetParameter $params
     * @param $isAdminPage
     */
    public function __construct($params = null, $isAdminPage = true)
    {
        parent::__construct();
        $this->userAnalyticsCmp($params, $isAdminPage);
    }

    private function userAnalyticsCmp($params, $isAdminPage = true)
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        if($isAdminPage){
            $this->assign('sections', $service->getAdminSections(4));
            $this->assign('subsections', $service->getAdminSubSections(4,0));
        }else{
            $this->assign('sections', $service->getGraphSections(1));
            $this->assign('subsections', $service->getGraphSubSections(1,0));
        }
        $selectedGroupId = $service->getSelectedGroupId();
        $graphMetrics = $service->getLastGraphCalculatedMetricsByGroupId($selectedGroupId);
        if($graphMetrics==null){
            $this->assign("noData", true);
            return;
        }else{
            $graphMetricsArray = array(
                'time' => UTIL_DateTime::formatSimpleDate($graphMetrics->time),
                'node_count' => $graphMetrics->node_count,
                'edge_count' => $graphMetrics->edge_count,
                'diameter' => $graphMetrics->diameter,
                'degree_average' => $graphMetrics->degree_average,
                'average_distance' => $graphMetrics->average_distance,
                'cluster_coe_avg' => $graphMetrics->cluster_coe_avg,
                'contents_count' => $graphMetrics->contents_count,
                'pictures_count' => $graphMetrics->pictures_count,
                'videos_count' => $graphMetrics->videos_count,
                'news_count' => $graphMetrics->news_count,
                'users_interactions_count' => $graphMetrics->users_interactions_count,
                'all_activities_count' => $graphMetrics->all_activities_count
            );
            foreach ($graphMetricsArray as $key=>$value){
                $graphMetricsArray[$key] = array('label'=>OW::getLanguage()->text('frmgraph','label_'.$key), 'value'=>$value);
            }
            $this->assign("graphMetricsArray", $graphMetricsArray);
            $this->assign("lastCalculationDate", $graphMetricsArray['time']);
        }

        $graphPlugin = OW::getPluginManager()->getPlugin('frmgraph');
        OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'highcharts.js');
        OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'exporting.js');
        OW::getDocument()->addStyleSheet($graphPlugin->getStaticCssUrl().'graph.css');

        // CHARTS FORM
        $chartNames = array('degree','component','distance',
            'betweenness_cent','closeness_cent','eccentricity_cent','page_rank', 'hub','authority','cluster_coe',
            'contents_count','pictures_count','videos_count','news_count','all_contents_count','all_activities_count','all_done_activities_count');
        $form = new Form('chartForm');
        if($isAdminPage){
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.admin.graph_analytics.user'));
        }else{
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.graph_analytics.user'));
        }
        $chartField = new Selectbox('chartField');
        $selectedItem = $chartNames[0];
        foreach($chartNames as $item){
            $chartField->addOption($item, OW::getLanguage()->text('frmgraph','label_distribution_'.$item));
        }
        $chartField->setRequired();
        $chartField->setHasInvitation(false);
        $chartField->setLabel(OW::getLanguage()->text('frmgraph', 'label_cron_period'));
        $form->addElement($chartField);
        $submitField = new Submit('submit');
        $form->addElement($submitField);
        $this->addForm($form);
        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $data = $form->getValues();
            $selectedItem = $data['chartField'];
        }

        //DRAW SELECTED FORM
        if($selectedItem == 'degree') {
            $degreeDistributions = json_decode($graphMetrics->degree_distr);
            $degreeDistributionsData = $service->makeDataFromArray($degreeDistributions);
            $degreeDistributionsJS = $service->makeHighchartDistributionDiagram(OW::getLanguage()->text('frmgraph', 'label_distribution_degree'), 'distribution_chart',
                OW::getLanguage()->text('frmgraph', 'label_degree'), OW::getLanguage()->text('frmgraph', 'number_of_users'), "Number of data", $degreeDistributionsData);
            OW::getDocument()->addOnloadScript($degreeDistributionsJS);
        }
        else if($selectedItem == 'component') {
            $componentDistributions = json_decode($graphMetrics->component_distr);
            $componentDistributionsWithKey = array();
            if(isset($componentDistributions)) {
                foreach ($componentDistributions as $value) {
                    if (isset($componentDistributionsWithKey[$value])) {
                        $componentDistributionsWithKey[$value]++;
                    } else {
                        $componentDistributionsWithKey[$value] = 1;
                    }
                }
            }
            ksort($componentDistributionsWithKey);
            $componentDistributionsData = $service->makeDataFromArray($componentDistributionsWithKey);
            $componentDistributionsJS = $service->makeHighchartDistributionDiagram(OW::getLanguage()->text('frmgraph', 'label_distribution_component'), 'distribution_chart',
                OW::getLanguage()->text('frmgraph', 'number_of_users'), OW::getLanguage()->text('frmgraph', 'count'), "Number of data", $componentDistributionsData);
            OW::getDocument()->addOnloadScript($componentDistributionsJS);
        }
        else if($selectedItem == 'distance') {
            $distanceDistributions = json_decode($graphMetrics->distance_distr);
            $distanceDistributionsWithoutIsolatedNodes = array();
            if (isset($distanceDistributions)) {
                foreach ($distanceDistributions as $key => $value) {
                    if ($key != -1) {
                        $distanceDistributionsWithoutIsolatedNodes[$key] = $value;
                    }
                }
            }
            $distanceDistributionsData = $service->makeDataFromArray($distanceDistributionsWithoutIsolatedNodes);
            $distanceDistributionsJS = $service->makeHighchartDistributionDiagram(OW::getLanguage()->text('frmgraph', 'label_distribution_distance'), 'distribution_chart',
                OW::getLanguage()->text('frmgraph', 'distance'), OW::getLanguage()->text('frmgraph', 'count'), "Number of data", $distanceDistributionsData);
            OW::getDocument()->addOnloadScript($distanceDistributionsJS);
        }
        else{
            $generalGraph = array(
                'betweenness_cent' => array('split'=>0.1,'intValues'=>false),
                'closeness_cent' => array('split'=>0.1,'intValues'=>false),
                'eccentricity_cent' => array('split'=>1,'intValues'=>true),
                'page_rank' => array('split'=>0.1,'intValues'=>false),
                'hub' => array('split'=>0.1,'intValues'=>false),
                'authority' => array('split'=>0.1,'intValues'=>false),
                'cluster_coe' => array('split'=>0.1,'intValues'=>false),
                'contents_count' => array('split'=>5,'intValues'=>true),
                'pictures_count' => array('split'=>1,'intValues'=>true),
                'videos_count' => array('split'=>1,'intValues'=>true),
                'news_count' => array('split'=>1,'intValues'=>true),
                'all_activities_count' => array('split'=>5,'intValues'=>true),
                'all_done_activities_count' => array('split'=>5,'intValues'=>true),
                'all_contents_count' => array('split'=>5,'intValues'=>true)
            );
            if(isset($generalGraph[$selectedItem])){
                $chartDistributions = $service->getNodeDataForChart(FRMGRAPH_BOL_NodeDao::getInstance()->getTableName(), $selectedItem, $generalGraph[$selectedItem]['split'], $generalGraph[$selectedItem]['intValues']);
                $chartDistributionsData = $service->makeDataFromArray($chartDistributions);
                $chartDistributionsJS = $service->makeHighchartDistributionDiagram(OW::getLanguage()->text('frmgraph','label_distribution_'.$selectedItem), 'distribution_chart',
                    OW::getLanguage()->text('frmgraph','label_'.$selectedItem), OW::getLanguage()->text('frmgraph','number_of_users'), "Number of data", $chartDistributionsData);
                OW::getDocument()->addOnloadScript($chartDistributionsJS);
            }
        }
    }
}