<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp
 * @since 1.0
 */
class FRMCFP_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

//        $this->addJob('afterEndOfInvitations', 60);
    }

    public function run()
    {
        //ignore
    }

    public function afterEndOfInvitations()
    {
        //maybe to send notifications to CFP admins

        $limit = 1500;
        $list = FRMCFP_BOL_Service::getInstance()->findCronExpiredEvents(0, $limit);

        if ( !empty($list) )
        {
            /* @var $event FRMCFP_BOL_Event */
            foreach ( $list as $cfp )
            {

            }
        }
    }
}