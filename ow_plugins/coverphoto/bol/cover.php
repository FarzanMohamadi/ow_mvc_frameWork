<?php
/**
 * coverphoto
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class COVERPHOTO_BOL_Cover extends OW_Entity
{
    /**
     * @var string
     */
    public $entityType;

    /**
     * @var int
     */
    public $entityId;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $hash;

    /**
     * @var string
     */
    public $croppedHash;

    /**
     * @var integer
     */
    public $addDateTime;

    /**
     * @var integer
     */
    public $isCurrent;
}