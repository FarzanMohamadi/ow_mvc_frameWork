<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */
class FRMGRAPH_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmgraph', 'admin_settings_title'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmgraph', 'admin_settings_title'));
        $config = OW::getConfig();

        $calculateForm = new Form('calculateForm');
        $calculateForm->setAction(OW::getRouter()->urlForRoute('frmgraph.calculate'));
        $submitGetCentralityField = new Submit('submit');
        $calculateForm->addElement($submitGetCentralityField);

        $form = new Form('form');
        $form->setAction(OW::getRouter()->urlForRoute('frmgraph.admin'));

        $serverField = new TextField('server');
        $serverField->setLabel(OW::getLanguage()->text('frmgraph', 'server'));
        $serverField->setValue($config->getValue('frmgraph', 'server'));
        $serverField->setRequired();
        $form->addElement($serverField);

        $snapshotField = new Selectbox('group_id');
        $allGraphSnapshots = $service->getLatestRunsByTime();
        $selectedGroupId = -1;
        if(OW::getConfig()->configExists('frmgraph','group_id')){
            $selectedGroupId = OW::getConfig()->getValue('frmgraph','group_id');
        }
        $snapshotField->addOption('-1', OW::getLanguage()->text('frmgraph','last_snapshot'));
        foreach ( $allGraphSnapshots as $oneSnapshot )
        {
            $snapshotField->addOption($oneSnapshot->groupId, UTIL_DateTime::formatSimpleDate($oneSnapshot->time));
        }
        $snapshotField->setValue($selectedGroupId);
        $snapshotField->setRequired();
        $snapshotField->setHasInvitation(false);
        $snapshotField->setLabel(OW::getLanguage()->text('frmgraph', 'label_which_snapshot_result'));
        $form->addElement($snapshotField);

        $cronField = new Selectbox('cron_period');
        $selectedItem = 7*24*60;
        if(OW::getConfig()->configExists('frmgraph','cron_period')){
            $selectedItem = OW::getConfig()->getValue('frmgraph','cron_period');
        }
        $cronField->addOption(-1, OW::getLanguage()->text('frmgraph','disabled'));
        $cronField->addOption(24*60, OW::getLanguage()->text('frmgraph','time_daily'));
        $cronField->addOption(7*24*60, OW::getLanguage()->text('frmgraph','time_weekly'));
        $cronField->addOption(30*7*24*60, OW::getLanguage()->text('frmgraph','time_monthly'));
        $cronField->setValue($selectedItem);
        $cronField->setRequired();
        $cronField->setHasInvitation(false);
        $cronField->setLabel(OW::getLanguage()->text('frmgraph', 'label_cron_period'));
        $form->addElement($cronField);

        $submitField = new Submit('submit');
        $form->addElement($submitField);

        $this->addForm($calculateForm);
        $this->addForm($form);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $data = $form->getValues();
            $config->saveConfig('frmgraph', 'server', $data['server']);
            if($data['group_id']==-1){
                $config->deleteConfig('frmgraph', 'group_id');
            }
            else {
                $config->saveConfig('frmgraph', 'group_id', $data['group_id']);
            }
            $config->saveConfig('frmgraph', 'cron_period', $data['cron_period']);

            // clear cache
            FRMGRAPH_BOL_Service::getInstance()->generateCache();

            OW::getFeedback()->info(OW::getLanguage()->text('frmgraph', 'modified_successfully'));
            $this->redirect();
        }


        $testForm = new Form('test');
        $testForm->setAction(OW::getRouter()->urlForRoute('frmgraph.admin'));
        $submitField = new Submit('submit');
        $testForm->addElement($submitField);
        $this->addForm($testForm);
        if (OW::getRequest()->isPost() && $testForm->isValid($_POST)) {
            $url = $config->getValue('frmgraph', 'server');
            $result = OW::getStorage()->fileGetContent($url);
            if(isset($result) && !empty($result)){
                $json = json_decode($result,true);
                if(isset($json) && isset($json['version'])){
                    OW::getFeedback()->info(OW::getLanguage()->text('frmgraph', 'test_connection_successfully'));
                    $this->redirect();
                }
            }
            OW::getFeedback()->error(OW::getLanguage()->text('frmgraph', 'test_connection_failed'));
            $this->redirect();
        }
    }

    public function graph()
    {
        $graphCmp = new FRMGRAPH_CMP_AdminGraph();
        $this->addComponent('graphCmp',$graphCmp);
    }

    public function calculateAllInformation(){
        $service = FRMGRAPH_BOL_Service::getInstance();
        $service->calculateAllInformation();
        OW::getFeedback()->info(OW::getLanguage()->text('frmgraph', 'calculate_all_metrics_successfully'));
        $this->redirect(OW::getRouter()->urlForRoute('frmgraph.admin'));
    }
}
