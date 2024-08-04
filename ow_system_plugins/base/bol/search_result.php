<?php
/**
 * Data Transfer Object for `base_search_result` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_SearchResult extends OW_Entity
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $searchId;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $sortOrder;
}
