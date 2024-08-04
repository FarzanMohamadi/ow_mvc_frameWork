<?php
class BIRTHDAYS_Cron extends OW_Cron
{

    public function __construct()
    {
        parent::__construct();
        $this->addJob('birthdayCheck', 60*24);
    }

    public function run()
    {

    }

    public function birthdayCheck(){
        BIRTHDAYS_BOL_Service::getInstance()->checkBirthdays();
    }
}