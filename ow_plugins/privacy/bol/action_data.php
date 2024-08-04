<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.privacy.bol
 * @since 1.0
 */
class PRIVACY_BOL_ActionData extends OW_Entity
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $pluginKey;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $value = '';
}
