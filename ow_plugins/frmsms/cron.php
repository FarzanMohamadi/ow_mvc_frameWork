<?php
class FRMSMS_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('deleteExpiredTokens');
        $this->addJob('updateExpiredTokens',1);
        $this->addJob('sendWaitlist', 1);
        $this->addJob('deleteInvalidQuestionValues', 24*60);
    }

    public function run()
    {
        
    }

    public function deleteExpiredTokens()
    {
        //FRMSMS_BOL_Service::getInstance()->deleteExpiredTokens();
    }

    public function updateExpiredTokens()
    {
        FRMSMS_BOL_Service::getInstance()->updateExpiredTokens();
    }

    public function sendWaitlist()
    {
        FRMSMS_BOL_Service::getInstance()->processWaitList(20);
    }

    public function deleteInvalidQuestionValues(){
        FRMSMS_BOL_Service::getInstance()->deleteInvalidQuestionValues();
    }
}
