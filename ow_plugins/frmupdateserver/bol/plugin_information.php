<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver.bol
* Date: 8/1/2018
* Time: 9:21 AM
*/

class FRMUPDATESERVER_BOL_PluginInformation extends OW_Entity
{
    /**
     * @var string
     */
    public $itemId;

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param string
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * @var string
     */
    public $categories;

    /**
     * @return string
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param string
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

}
