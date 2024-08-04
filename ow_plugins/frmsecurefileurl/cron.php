<?php
/**
 * frmsecurefileurl cron job.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.frmsecurefileurl
 * @since 1.0
 */
class FRMSECUREFILEURL_Cron extends OW_Cron
{
    const TIME_TO_LIVE = 60 * 60 * 24 * 1; //1 day

    public function __construct()
    {
        parent::__construct();

        $this->addJob('deleteExpiredUrl', 1);
    }

    public function run()
    {
        
    }

    public function deleteExpiredUrl()
    {
        $service = FRMSECUREFILEURL_BOL_Service::getInstance();
        $time = time() - self::TIME_TO_LIVE;
        $service->deleteExpired($time);
    }
}