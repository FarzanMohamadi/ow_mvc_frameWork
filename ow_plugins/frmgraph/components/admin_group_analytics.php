<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_AdminGroupAnalytics extends OW_Component
{

    /**
     * FRMGRAPH_CMP_AdminGroupAnalytics constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct($params = null, $isAdminPage = true)
    {
        parent::__construct();
        $this->groupAnalyticsCmp($params, $isAdminPage);
    }

    private function groupAnalyticsCmp($params, $isAdminPage = true)
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        if($isAdminPage){
            $this->assign('sections', $service->getAdminSections(4));
            $this->assign('subsections', $service->getAdminSubSections(4,1));
        }else{
            $this->assign('sections', $service->getGraphSections(1));
            $this->assign('subsections', $service->getGraphSubSections(1,1));
        }
        $selectedGroupId = $service->getSelectedGroupId();
        $graphMetrics = $service->getLastGraphCalculatedMetricsByGroupId($selectedGroupId);
        if($graphMetrics==null){
            $this->assign("noData", true);
            return;
        }else{
            $graphMetricsArray = array(
                'time' => UTIL_DateTime::formatSimpleDate($graphMetrics->time),
                'node_count' => $graphMetrics->g_node_count,
                'edge_count' => $graphMetrics->g_edge_count,
                'diameter' => $graphMetrics->g_diameter,
                'degree_average' => $graphMetrics->g_degree_average,
                'average_distance' => $graphMetrics->g_average_distance,
                'cluster_coe_avg' => $graphMetrics->g_cluster_coe_avg,
                'contents_count' => $graphMetrics->g_contents_count,
                'files_count' => $graphMetrics->g_files_count,
                'users_interactions_count' => $graphMetrics->g_users_interactions_count,
                'all_activities_count' => $graphMetrics->g_all_activities_count
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
        $chartNames = array('users_count','degree','component','distance',
            'betweenness_cent','closeness_cent','eccentricity_cent','page_rank', 'hub','authority','cluster_coe',
            'contents_count','files_count','all_activities_count');//users_interactions_count
        $form = new Form('chartForm');
        if($isAdminPage){
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.admin.graph_analytics.group'));
        }else{
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.graph_analytics.group'));
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
            $degreeDistributions = json_decode($graphMetrics->g_degree_distr);
            $degreeDistributionsData = $service->makeDataFromArray($degreeDistributions);
            $degreeDistributionsJS = $service->makeHighchartDistributionDiagram(OW::getLanguage()->text('frmgraph', 'label_distribution_degree'), 'distribution_chart',
                OW::getLanguage()->text('frmgraph', 'label_degree'), OW::getLanguage()->text('frmgraph', 'number_of_groups'), "Number of data", $degreeDistributionsData);
            OW::getDocument()->addOnloadScript($degreeDistributionsJS);
        }
        else if($selectedItem == 'component') {
            $componentDistributions = json_decode($graphMetrics->g_component_distr);
            $componentDistributionsWithKey = array();
            if  (isset($componentDistributions)) {
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
                OW::getLanguage()->text('frmgraph', 'number_of_groups'), OW::getLanguage()->text('frmgraph', 'count'), "Number of data", $componentDistributionsData);
            OW::getDocument()->addOnloadScript($componentDistributionsJS);
        }
        else if($selectedItem == 'distance') {
            $distanceDistributions = json_decode($graphMetrics->g_distance_distr);
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
                'users_count' => array('split'=>1,'intValues'=>true),
                'betweenness_cent' => array('split'=>0.1,'intValues'=>false),
                'closeness_cent' => array('split'=>0.1,'intValues'=>false),
                'eccentricity_cent' => array('split'=>1,'intValues'=>true),
                'page_rank' => array('split'=>0.1,'intValues'=>false),
                'hub' => array('split'=>0.1,'intValues'=>false),
                'authority' => array('split'=>0.1,'intValues'=>false),
                'cluster_coe' => array('split'=>0.1,'intValues'=>false),
                'contents_count' => array('split'=>5,'intValues'=>true),
                'files_count' => array('split'=>1,'intValues'=>true),
                'all_activities_count' => array('split'=>5,'intValues'=>true),
                'users_interactions_count' => array('split'=>5,'intValues'=>true)
            );
            if(isset($generalGraph[$selectedItem])){
                $chartDistributions = $service->getNodeDataForChart(FRMGRAPH_BOL_GroupDao::getInstance()->getTableName(), $selectedItem, $generalGraph[$selectedItem]['split'], $generalGraph[$selectedItem]['intValues']);
                $chartDistributionsData = $service->makeDataFromArray($chartDistributions);
                $chartDistributionsJS = $service->makeHighchartDistributionDiagram(OW::getLanguage()->text('frmgraph','label_distribution_'.$selectedItem), 'distribution_chart',
                    OW::getLanguage()->text('frmgraph','label_'.$selectedItem), OW::getLanguage()->text('frmgraph','number_of_groups'), "Number of data", $chartDistributionsData);
                OW::getDocument()->addOnloadScript($chartDistributionsJS);
            }
        }
    }
}