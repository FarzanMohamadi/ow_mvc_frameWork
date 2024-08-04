<?php
class FRMAUDIO_Cron extends OW_Cron
{

    public function __construct()
    {
        parent::__construct();
        $this->addJob('removeTemps', 60);
    }

    public function run()
    {

    }

    public function removeTemps()
    {
        FRMAUDIO_BOL_Service::getInstance()->removeTempAudios(60*60*24*2);
    }
}