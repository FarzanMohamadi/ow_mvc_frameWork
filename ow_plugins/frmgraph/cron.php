<?php
class FRMGRAPH_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $runInterval = 7*24*60; //default weekly
        if(OW::getConfig()->configExists('frmgraph','cron_period')){
            $runInterval = OW::getConfig()->getValue('frmgraph','cron_period');
        }
        $runInterval = max($runInterval, 12 * 60); // no interval less than 12 hours
        $this->addJob('recalculateAll', $runInterval);

        $this->addJob("removeFileCache", 12*60);
    }

    public function run()
    {

    }

    public function recalculateAll()
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        $service->calculateAllInformation();
    }

    public function removeFileCache(){
        FRMGRAPH_BOL_Service::getInstance()->generateCache();
    }
}