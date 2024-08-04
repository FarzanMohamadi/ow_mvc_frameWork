<?php
/**
 * Video cron job.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.video.bol
 * @since 1.5.3
 */
class VIDEO_Cron extends OW_Cron
{
    const THUMBS_CACHE_JOB_RUN_INTERVAL = 1;
    const THUMBS_PER_RUN = 5;

    public function __construct()
    {
        parent::__construct();

        $this->addJob('thumbsCacheProcess', self::THUMBS_CACHE_JOB_RUN_INTERVAL);
    }

    public function run() { }

    public function thumbsCacheProcess()
    {
        VIDEO_BOL_ClipService::getInstance()->cacheThumbnails(self::THUMBS_PER_RUN);
    }
}