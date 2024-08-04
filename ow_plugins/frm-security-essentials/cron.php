<?php
class FRMSECURITYESSENTIALS_Cron extends OW_Cron
{



    public function __construct()
    {
        parent::__construct();

        $this->addJob('deleteExpiredRequests', 1440);
        /*
         * run this cron every week
         */
        $this->addJob('checkUsersSetPrivacy', 1440 * 7);
    }


    public function deleteExpiredRequests()
    {
        FRMSECURITYESSENTIALS_BOL_Service::getInstance()->deleteExpiredRequests();
    }

    public function checkUsersSetPrivacy()
    {
        FRMSECURITYESSENTIALS_BOL_Service::getInstance()->checkUsersSetPrivacy();
    }
    public function run()
    {

    }
}
