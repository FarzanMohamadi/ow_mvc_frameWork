<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class FRMWORDSCORRECTION_Cron extends OW_Cron
{

    public function __construct()
    {
        parent::__construct();

        $this->addJob('correctAll', 60*24);
    }

    public function run()
    {
        //ignore
    }

    public function correctAll()
    {
        FRMWORDSCORRECTION_BOL_Service::getInstance()->correctAll();
    }

}