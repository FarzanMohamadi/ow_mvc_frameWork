<?php
class FRMRSS_CMP_RssFloatBox extends OW_Component
{
    public function __construct()
    {
        parent::__construct();
        $form = FRMRSS_BOL_Service::getInstance()->getRssForm();
        $this->assign('rssWithoutTagLink',OW::getRouter()->urlForRoute('rss_without_parameter'));
        $this->assign('homeUrl',OW_URL_HOME);
        $this->addForm($form);
    }
}