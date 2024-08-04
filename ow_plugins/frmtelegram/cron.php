<?php
class FRMTELEGRAM_Cron extends OW_Cron
{

    public function __construct()
    {
        parent::__construct();

        $this->addJob('getUpdates', 0.05);
    }

    public function run()
    {

    }

    public function getUpdates()
    {
        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $service->getBotUpdates();
    }
}