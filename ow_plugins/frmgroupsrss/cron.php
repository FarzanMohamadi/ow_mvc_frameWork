<?php
class FRMGROUPSRSS_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();
        $config = OW::getConfig();
        $interval=60;
        if ( $config->getValue('frmgroupsrss', 'update_interval') )
        {
            $interval = $config->getValue('frmgroupsrss', 'update_interval');
        }
        $this->addJob('sendRssFeedsToGroups', (int)$interval);

        $checkForRemovedGroupsInterval = 24*60;
        $this->addJob('removeDeletedGroupsRssData', (int)$checkForRemovedGroupsInterval);
    }

    public function run()
    {

    }

    public function sendRssFeedsToGroups()
    {
        FRMGROUPSRSS_BOL_Service::getInstance()->sendRssFeedsToGroupsCronJob();
    }

    public function removeDeletedGroupsRssData()
    {
        FRMGROUPSRSS_BOL_Service::getInstance()->removeDeletedGroupsRssDataCronJob();
    }

}