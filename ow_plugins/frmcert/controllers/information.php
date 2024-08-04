<?php
/**
 * Created by PhpStorm.
 * User: Farzan Mohammadi
 * Date: 8/19/2019
 * Time: 10:30 AM
 */

class FRMCERT_CTRL_Information extends OW_ActionController
{
    public function index(){
        $item = array();
        $component = new FRMCERT_CMP_InformationBlock($item);
        $this->addComponent('frmcert', $component);
    }

}