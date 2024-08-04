<?php
/**
 * component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmshasta.classes
 * @since 1.0
 */
class FRMSHASTA_CMP_CustomizeSpecialFloatbox extends OW_Component
{
    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function __construct($params = array())
    {
        parent::__construct();
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }

        $service = FRMSHASTA_BOL_Service::getInstance();

        $form = $service->getCustomizeSpecialCategoryForm($this);
        $this->addForm($form);
        FRMSHASTA_BOL_Service::getInstance()->addStaticFiles();
    }
}