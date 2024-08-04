<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver
 * Date: 8/1/2018
 * Time: 9:21 AM
 */

class FRMUPDATESERVER_CMP_EditItemFloatBox extends OW_Component
{
    public function __construct($id)
    {
        parent::__construct();
        $form = FRMUPDATESERVER_BOL_Service::getInstance()->getCategoryItemForm($id);
        $this->addForm($form);
    }
}
