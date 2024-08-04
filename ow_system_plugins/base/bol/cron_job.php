<?php
/**
 * Data Transfer Object for `base_cron_job` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_CronJob extends OW_Entity
{
    /**
     * @var string
     */
    public $methodName;
    /**
     * @var integer
     */
    public $runStamp;

}
