<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/26/18
 * Time: 1:10 PM
 */
class FRMQUESTIONS_MCMP_EditOption extends OW_MobileComponent
{
    /**
     * Constructor.
     *
     * @param $optionId
     */
    public function __construct( $optionId)
    {
        parent::__construct();

        $option = FRMQUESTIONS_BOL_Service::getInstance()->findOption($optionId);
        if(!isset($option))
        {
            return;
        }
        $form = new FRMQUESTIONS_CLASS_EditOptionForm($option);
        $this->addForm($form);
        $this->setTemplate(OW::getPluginManager()->getPlugin('frmquestions')->getMobileCmpViewDir() . 'edit_option.html');
    }
}