<?php
/**
 * Data Transfer Object for `base_sitemap` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.8.4
 */
class BOL_Sitemap extends OW_Entity
{
    /**
     * Url
     *
     * @var string
     */
    public $url;

    /**
     * Entity type
     *
     * @var string
     */
    public $entityType;
}
