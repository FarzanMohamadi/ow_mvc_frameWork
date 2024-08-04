<?php
class FRMMASSMAILING_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    const CONF_MAIL_COUNT_ON_PAGE = 5;
    public function getService(){
        return FRMMASSMAILING_BOL_Service::getInstance();
    }

    public function index($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmmassmailing', 'admin_massmailing_settings_heading'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmmassmailing', 'admin_massmailing_settings_heading'));
        $service = $this->getService();
        $config =  OW::getConfig();
        $sectionId = 1;
        if(isset($params['sectionId'])){
            $sectionId = $params['sectionId'];
        }
        if($sectionId==1) {
            $page = ( empty($_GET['page']) || (int) $_GET['page'] < 0 ) ? 1 : (int) $_GET['page'];
            $this->assign('sectionId', 1);
            $formSettings = new Form('settings');
            $formSettings->setAjax();
            $formSettings->setAjaxResetOnSuccess(false);
            $formSettings->setAction(OW::getRouter()->urlForRoute('frmmassmailing.admin'));
            $mailDataCount = $this->getService()->getMassMailingDetailsDataCount();
            $viewCount = $config->getValue('frmmassmailing', 'mail_view_count');
            $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($mailDataCount / $viewCount), 5));
            $this->assign('sections', $service->getAdminSections($sectionId));
            $information = $this->getMassMailingDetailsData($page,$mailDataCount);
            $this->assign('tableData', $information['data']);

        }
        else if($sectionId==2) {
            $this->assign('sectionId', 2);
            $form = new Form('viewCount_setting');
            $mailViewCount = new TextField('mail_view_count');
            $mailViewCount->setLabel(OW::getLanguage()->text('frmmassmailing', 'input_settings_view_count_label'));
            $mailViewCount->setRequired(true);
            $form->addElement($mailViewCount);

            $submit = new Submit('save');
            $form->addElement($submit);
            $this->addForm($form);
            $this->assign('sections', $service->getAdminSections($sectionId));
            if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
            {
                $data = $form->getValues();
                if((int)$data['mail_view_count']>1) {
                    if ($config->configExists('frmmassmailing', 'mail_view_count')) {
                        $config->saveConfig('frmmassmailing', 'mail_view_count', (int)$data['mail_view_count']);
                    }
                    OW::getFeedback()->info(OW::getLanguage()->text('frmmassmailing', 'modified_successfully'));
                    $this->redirect();
                }
                else{
                    OW::getFeedback()->error(OW::getLanguage()->text('frmmassmailing', 'invalid_mail_count'));
                    $this->redirect();
                }
            }
            if($config->configExists('frmmassmailing', 'mail_view_count'))
            {
                $mailViewCount->setValue($config->getValue('frmmassmailing', 'mail_view_count'));
            }
        }
    }


    /**
     * @param $page
     * @param $mailDataCount
     * @return array
     */
    public function getMassMailingDetailsData($page,$mailDataCount)
    {
        $data =$this->getService()->getMassMailingDetailsData($page,$mailDataCount);
        return $data;
    }


}
