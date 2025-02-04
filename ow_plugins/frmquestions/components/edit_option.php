<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/26/18
 * Time: 1:10 PM
 */
class FRMQUESTIONS_CMP_EditOption extends OW_Component
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
    }
}