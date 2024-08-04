<?php
/**
 * Data Transfer Object for `photo_album` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.bol
 * @since 1.0
 */
class PHOTO_BOL_PhotoAlbum extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var string
     */
    public $entityType = 'user';
    /**
     * @var integer
     */
    public $entityId = null;
    /**
     * @var string
     */
    public $name;
    
    public $description;

    /**
     * @var integer
     */
    public $createDatetime;

}
