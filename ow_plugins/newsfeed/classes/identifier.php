<?php
/**
 *
 * @package ow_plugins.newsfeed.classes
 * @since 1.0
 */
class NEWSFEED_CLASS_Identifier
{
    public $id;
    public $type;
    
    public function __construct($type, $id)
    {
        $this->type = trim($type);
        $this->id = $id;
    }
}