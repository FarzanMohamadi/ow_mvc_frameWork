<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/27/2017
 * Time: 9:15 AM
 */
class FRMRAHYAB_CTRL_Admin extends ADMIN_CTRL_Abstract
{


    /**
     * FRMRAHYAB_CTRL_Admin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmrahyab', 'admin_settings_heading'));
        $this->setPageTitle($language->text('frmrahyab', 'admin_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    public function index(array $params = array())
    {
        $currentSection = '1';

        if (array_key_exists('section', $params)) {
            $currentSection = $params['section'];
        }

        $language = OW::getLanguage();
        $config = OW::getConfig();

        if ($currentSection == '1') {
            $form = new Form('form');

            $field = new TextField('panel_username');
            $field->setRequired(true);
            $field->setLabel($language->text('frmrahyab', 'panel_username'));
            if ($config->configExists('frmrahyab', 'panel_username'))
                $field->setValue($config->getValue('frmrahyab', 'panel_username'));
            $form->addElement($field);

            $field = new TextField('panel_password');
            $field->setRequired(true);
            $field->setLabel($language->text('frmrahyab', 'panel_password'));
            if ($config->configExists('frmrahyab', 'panel_password'))
                $field->setValue($config->getValue('frmrahyab', 'panel_password'));
            $form->addElement($field);

            $field = new TextField('panel_number');
            $field->setRequired(true);
            $field->setLabel($language->text('frmrahyab', 'panel_number'));
            if ($config->configExists('frmrahyab', 'panel_number'))
                $field->setValue($config->getValue('frmrahyab', 'panel_number'));
            $form->addElement($field);

            $field = new TextField('provider_host');
            $field->setRequired(true);
            $field->setLabel($language->text('frmrahyab', 'provider_host'));
            if ($config->configExists('frmrahyab', 'host'))
                $field->setValue($config->getValue('frmrahyab', 'host'));
            $form->addElement($field);

            $field = new TextField('provider_port');
            $field->setRequired(true);
            $field->addValidator(new IntValidator());
            $field->setLabel($language->text('frmrahyab', 'provider_port'));
            if ($config->configExists('frmrahyab', 'port'))
                $field->setValue($config->getValue('frmrahyab', 'port'));
            $form->addElement($field);

            $field = new TextField('company');
            $field->setRequired(true);
            $field->setLabel($language->text('frmrahyab', 'company'));
            if ($config->configExists('frmrahyab', 'company'))
                $field->setValue($config->getValue('frmrahyab', 'company'));
            $form->addElement($field);

            $submit = new Submit('save');
            $form->addElement($submit);

            $this->addForm($form);

            if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
                $data = $form->getValues();

                if (!$config->configExists('frmrahyab', 'panel_username'))
                    $config->addConfig('frmrahyab', 'panel_username', $data['panel_username']);
                else
                    $config->saveConfig('frmrahyab', 'panel_username', $data['panel_username']);

                if (!$config->configExists('frmrahyab', 'panel_password'))
                    $config->addConfig('frmrahyab', 'panel_password', $data['panel_password']);
                else
                    $config->saveConfig('frmrahyab', 'panel_password', $data['panel_password']);

                if (!$config->configExists('frmrahyab', 'panel_number'))
                    $config->addConfig('frmrahyab', 'panel_number', $data['panel_number']);
                else
                    $config->saveConfig('frmrahyab', 'panel_number', $data['panel_number']);

                if (!$config->configExists('frmrahyab', 'host'))
                    $config->addConfig('frmrahyab', 'host', $data['provider_host']);
                else
                    $config->saveConfig('frmrahyab', 'host', $data['provider_host']);

                if (!$config->configExists('frmrahyab', 'port'))
                    $config->addConfig('frmrahyab', 'port', $data['provider_port']);
                else
                    $config->saveConfig('frmrahyab', 'port', $data['provider_port']);

                if (!$config->configExists('frmrahyab', 'company'))
                    $config->addConfig('frmrahyab', 'company', $data['company']);
                else
                    $config->saveConfig('frmrahyab', 'company', $data['company']);

                OW::getFeedback()->info(OW::getLanguage()->text('frmrahyab', 'modified_successfully'));
                $this->redirect();
            }
        }else{
            $page = 1;
            if(isset($_GET['page'])){
                $page = $_GET['page'];
            }
            $limit = 10;
            $tracks = FRMRAHYAB_BOL_TrackDao::getInstance()->findAllTracks($page,$limit);
            $resultTracks = array();
            foreach ($tracks as $track){
                $result = FRMRAHYAB_BOL_Service::getInstance()->checkSMSStatus($track->smsId);
                $resultTracks[] = array(
                    'text'=>$track->message,
                    'mobile'=>$track->mobile,
                    'time'=>$track->time,
                    'status'=>FRMRAHYAB_BOL_Service::getInstance()->getStatusString($result)
                );
            }
            $this->addComponent('paging', new BASE_CMP_Paging($page, ceil(FRMRAHYAB_BOL_TrackDao::getInstance()->findAllTrackCount() / $limit), 5));
            $this->assign('tracks', $resultTracks);
        }
        $this->assign('section',$currentSection);
        $this->assign('section1_label',OW::getLanguage()->text('frmrahyab', 'section1_label'));
        $this->assign('section2_label',OW::getLanguage()->text('frmrahyab', 'section2_label'));
        $this->assign('section1_url',OW::getRouter()->urlForRoute('frmrahyab_admin_setting_section',array('section'=>1)));
        $this->assign('section2_url',OW::getRouter()->urlForRoute('frmrahyab_admin_setting_section',array('section'=>2)));
    }

}