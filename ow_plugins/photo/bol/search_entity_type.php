<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.photo.bol
 * @since 1.6.1
 */
class PHOTO_BOL_SearchEntityType extends OW_Entity
{
    public $entityType;

    public function getEntityType()
    {
        return $this->entityType;
    }

    public function setEntityType( $value )
    {
        $this->entityType = $value;

        return $this;
    }
}
