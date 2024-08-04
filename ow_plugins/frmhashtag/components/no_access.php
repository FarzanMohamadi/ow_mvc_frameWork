<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */
class FRMHASHTAG_CMP_NoAccess extends OW_Component
{

    public function __construct($allCount)
    {
        parent::__construct();
        $sum = 0;
        foreach ($allCount as $key=>$value){
            $sum += $value;
        }
        $this->assign('msg', OW::getLanguage()->text('frmhashtag','able_to_see_text',array('num'=>0,'all'=>$sum)));

    }
}