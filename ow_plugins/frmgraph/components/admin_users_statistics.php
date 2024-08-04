<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_CMP_AdminUsersStatistics extends OW_Component
{

    /**
     * FRMGRAPH_CMP_AdminUserStatistics constructor.
     * @param bool $isAdminPage
     */
    public function __construct($params = null, $isAdminPage = true)
    {
        parent::__construct();
        $this->allUsersStatisticsCmp($isAdminPage);
    }

    private function allUsersStatisticsCmp($isAdminPage = false)
    {
        $graphPlugin = OW::getPluginManager()->getPlugin('frmgraph');
        $service = FRMGRAPH_BOL_Service::getInstance();
        $profileQuestions = BOL_QuestionService::getInstance()->allSelectableQuestionElements();

        OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'highcharts.js');
        OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'exporting.js');
        OW::getDocument()->addScript($graphPlugin->getStaticJsUrl().'main.js');
        OW::getDocument()->addStyleSheet($graphPlugin->getStaticCssUrl().'graph.css');

        if($isAdminPage) {
            $this->assign('sections', $service->getAdminSections(5));
        }else{
            $this->assign('sections', $service->getGraphSections(5));
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

        $form = new Form('chartForm');
        if($isAdminPage){
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.admin.graph_statistics.user'));
        }else{
            $form->setAction(OW::getRouter()->urlForRoute('frmgraph.graph_statistics.user'));
        }

        $chartField = new Selectbox('chartField');
        $chartField->setHasInvitation(false);
        $chartField->setLabel(OW::getLanguage()->text('frmgraph', 'user_statistics'));
        $questionNames = array();
        $questionTitles = array();
        foreach($profileQuestions as $question_number=>$question){
            $questionTitle = OW::getLanguage()->text('base', 'questions_question_' . $question->getAttribute('name') . '_label');
            $index = $question_number + 1;
            $chartField->addOption($index, $questionTitle);
            $questionNames[$index] = $question->getAttribute('name');
            $questionTitles[$index] = $questionTitle;
        }

        $chartField->addOption('users_status_log',  OW::getLanguage()->text('frmgraph', 'users_presence_log'));
        $questionNames['users_status_log'] = 'users_status_log';
        $questionTitles['users_status_log'] = OW::getLanguage()->text('frmgraph', 'users_presence_log');

        $chartField->addOption('admin_user_statistics',  OW::getLanguage()->text('admin', 'widget_user_statistics'));
        $questionNames['admin_user_statistics'] = 'admin_user_statistics';
        $questionTitles['admin_user_statistics'] = OW::getLanguage()->text('admin', 'widget_user_statistics');

        $chartField->addOption('admin_content_statistics',  OW::getLanguage()->text('admin', 'widget_content_statistics'));
        $questionNames['admin_content_statistics'] = 'widget_content_statistics';
        $questionTitles['admin_content_statistics'] = OW::getLanguage()->text('admin', 'widget_content_statistics');

        $form->addElement($chartField);

        $chartType = new Selectbox('chartType');
        $chartType->setHasInvitation(false);
        $chartType->setLabel(OW::getLanguage()->text('frmgraph', 'chart_type'));
        $chartType->addOption('column', OW::getLanguage()->text('frmgraph', 'column_chart'));
        $chartType->addOption('pie', OW::getLanguage()->text('frmgraph', 'pie_chart'));

        $form->addElement($chartType);

        $submitField = new Submit('submit');
        $form->addElement($submitField);

        $this->addForm($form);

        $selectedChart = $questionNames[1];
        $selectedChartTitle = $questionTitles[1];
        $data = $service->getUserStatisticsForChart($selectedChart);
        $typeOfChart = 'column';
        $useHighcharts = true;

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $data = $form->getValues();
            if (isset($data['chartType']))
                $typeOfChart = $data['chartType'];
            if (is_numeric($data['chartField'])) {
                $selectedChart = $questionNames[$data['chartField']];
                $selectedChartTitle = $questionTitles[$data['chartField']];
                $data = $service->getUserStatisticsForChart($selectedChart);
            } elseif ($data['chartField'] == 'users_status_log'){
                $data = $service->getUserStatusForChart();
                $selectedChartTitle = OW::getLanguage()->text('frmgraph', 'user_status');
            } elseif ($data['chartField'] == 'admin_user_statistics'){
                $bcw = new BASE_CLASS_WidgetParameter();
                $bcw->additionalParamList = array('defaultPeriod'=>'last_7_days');
                $initialCmp = new ADMIN_CMP_UserStatisticWidget($bcw);
                $this->addComponent('statistics',$initialCmp);
                $useHighcharts = false;
            } elseif ($data['chartField'] == 'admin_content_statistics'){
                $bcw = new BASE_CLASS_WidgetParameter();
                $bcw->additionalParamList = array('defaultPeriod'=>'last_7_days', 'defaultContentGroup'=>'profiles');
                $initialCmp = new ADMIN_CMP_ContentStatisticWidget($bcw);
                $this->addComponent('statistics',$initialCmp);
                $useHighcharts = false;
            }
        }

        if  ($useHighcharts) {
            $data = $componentDistributionsData = $service->makeDataFromArray($data);
            $distanceDistributionsJS = $service->makeHighchartDistributionDiagram(OW::getLanguage()->text('frmgraph', 'number_of_users_according_to') . ' ' . $selectedChartTitle, 'statistics_chart', $selectedChartTitle, OW::getLanguage()->text('frmgraph', 'count'), "Number of data", $data, $typeOfChart, false);
            OW::getDocument()->addOnloadScript($distanceDistributionsJS);
        }
    }

    /**
     * Add menu
     *
     * @param string $prefix
     * @return void
     */
    protected function addMenu($prefix)
    {
        $this->addComponent('menu', new BASE_CMP_WidgetMenu(array(
            'today' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_today_period'),
                'id' => $prefix . '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_TODAY,
                'active' => true
            ),
            'yesterday' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_yesterday_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY,
            ),
            'last_7_days' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_7_days_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS,
            ),
            'last_30_days' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_30_days_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS,
            ),
            'last_year' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_year_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_YEAR,
            )
        )));
    }
}

/**
 * Class ContentStatisticForm
 */
class ContentStatisticForm2 extends Form
{
    /**
     * Class constructor
     *
     * @param string $name
     * @param $defaultGroup
     * @apram string $defaultGroup
     */
    public function __construct($name, $defaultGroup)
    {
        parent::__construct($name);

        $processedGroups = ADMIN_CMP_ContentStatisticWidget::getContentTypes();

        $groupField = new Selectbox('group');
        $groupField->setOptions($processedGroups);
        $groupField->setValue($defaultGroup);
        $groupField->setHasInvitation(false);
        $this->addElement($groupField);
    }
}