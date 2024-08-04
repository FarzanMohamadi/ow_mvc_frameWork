<?php
/**
 * FRM Update Server
 */

/**
 * Data Transfer Object for `frmupdateserver` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver.bol
 * @since 1.0
 */
class FRMUPDATESERVER_BOL_UpdateInformation extends OW_Entity
{
    /**
     * @var integer
     */
    public $time;

    /**
     * @var string
     */
    public $buildNumber;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $version;

}