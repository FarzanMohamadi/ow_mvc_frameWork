<?php
class FRMCERT_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('fetchStatistics', 60);
    }

    public function run()
    {

    }

    public function fetchStatistics()
    {
        FRMCERT_BOL_Service::getInstance()->fetchStatistics();
    }
}