<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/27/2017
 * Time: 9:15 AM
 */
class FRMFARAPAYAMAK_CTRL_Admin extends ADMIN_CTRL_Abstract
{


    /**
     * FRMFRAPAYAMAK_CTRL_Admin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmfarapayamak', 'admin_settings_heading'));
        $this->setPageTitle($language->text('frmfarapayamak', 'admin_settings_heading'));
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
            $field = new Selectbox('provider');
            $field->addOption('REST', 'REST');
            $field->addOption('SOAP', 'SOAP');
            $field->setRequired(true);
            $field->setLabel($language->text('frmfarapayamak', 'provider'));
            if ($config->configExists('frmfarapayamak', 'provider'))
                $field->setValue($config->getValue('frmfarapayamak', 'provider'));
            $form->addElement($field);

            $field = new TextField('panel_username');
            $field->setRequired(true);
            $field->setLabel($language->text('frmfarapayamak', 'panel_username'));
            if ($config->configExists('frmfarapayamak', 'panel_username'))
                $field->setValue($config->getValue('frmfarapayamak', 'panel_username'));
            $form->addElement($field);

            $field = new TextField('panel_password');
            $field->setRequired(true);
            $field->setLabel($language->text('frmfarapayamak', 'panel_password'));
            if ($config->configExists('frmfarapayamak', 'panel_password'))
                $field->setValue($config->getValue('frmfarapayamak', 'panel_password'));
            $form->addElement($field);

            $field = new TextField('panel_number');
            $field->setRequired(true);
            $field->setLabel($language->text('frmfarapayamak', 'panel_number'));
            if ($config->configExists('frmfarapayamak', 'panel_number'))
                $field->setValue($config->getValue('frmfarapayamak', 'panel_number'));
            $form->addElement($field);

            $field = new TextField('provider_soap_url');
            $field->setLabel($language->text('frmfarapayamak', 'provider_soap_url'));
            if ($config->configExists('frmfarapayamak', 'provider_soap_url'))
                $field->setValue($config->getValue('frmfarapayamak', 'provider_soap_url'));
            $form->addElement($field);

            $restUrls = array();
            if ($config->configExists('frmfarapayamak', 'provider_rest_urls')) {
                $rest = $config->getValue('frmfarapayamak', 'provider_rest_urls');
                $restUrls = json_decode($rest, true);
            }

            $field = new TextField('provider_rest_send_sms_url');
            $field->setLabel($language->text('frmfarapayamak', 'provider_rest_send_sms_url'));
            if (isset($restUrls['send_sms']))
                $field->setValue($restUrls['send_sms']);
            $form->addElement($field);

            $field = new TextField('provider_rest_get_credit_url');
            $field->setLabel($language->text('frmfarapayamak', 'provider_rest_get_credit_url'));
            if (isset($restUrls['get_credit']))
                $field->setValue($restUrls['get_credit']);
            $form->addElement($field);

            $field = new TextField('provider_rest_check_status_url');
            $field->setLabel($language->text('frmfarapayamak', 'provider_rest_check_status_url'));
            if (isset($restUrls['check_status']))
                $field->setValue($restUrls['check_status']);
            $form->addElement($field);

            $submit = new Submit('save');
            $form->addElement($submit);

            if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
                $data = $form->getValues();
                if (!$config->configExists('frmfarapayamak', 'provider'))
                    $config->addConfig('frmfarapayamak', 'provider', $data['provider']);
                else
                    $config->saveConfig('frmfarapayamak', 'provider', $data['provider']);

                if (!$config->configExists('frmfarapayamak', 'panel_username'))
                    $config->addConfig('frmfarapayamak', 'panel_username', $data['panel_username']);
                else
                    $config->saveConfig('frmfarapayamak', 'panel_username', $data['panel_username']);

                if (!$config->configExists('frmfarapayamak', 'panel_password'))
                    $config->addConfig('frmfarapayamak', 'panel_password', $data['panel_password']);
                else
                    $config->saveConfig('frmfarapayamak', 'panel_password', $data['panel_password']);

                if (!$config->configExists('frmfarapayamak', 'panel_number'))
                    $config->addConfig('frmfarapayamak', 'panel_number', $data['panel_number']);
                else
                    $config->saveConfig('frmfarapayamak', 'panel_number', $data['panel_number']);

                if (!$config->configExists('frmfarapayamak', 'provider_soap_url'))
                    $config->addConfig('frmfarapayamak', 'provider_soap_url', $data['provider_soap_url']);
                else
                    $config->saveConfig('frmfarapayamak', 'provider_soap_url', $data['provider_soap_url']);

                $restUrls = array(
                    'get_credit' => $data['provider_rest_get_credit_url'],
                    'send_sms' => $data['provider_rest_send_sms_url'],
                    'check_status' => $data['provider_rest_check_status_url']
                );

                if (!$config->configExists('frmfarapayamak', 'provider_rest_urls'))
                    $config->addConfig('frmfarapayamak', 'provider_rest_urls', json_encode($restUrls));
                else
                    $config->saveConfig('frmfarapayamak', 'provider_rest_urls', json_encode($restUrls));

                OW::getFeedback()->info(OW::getLanguage()->text('frmfarapayamak', 'modified_successfully'));
                $this->redirect();
            }
            $this->addForm($form);
        } else {
            $page = 1;
            if(isset($_GET['page'])){
                $page = $_GET['page'];
            }
            $limit = 10;
            $tracks = FRMFARAPAYAMAK_BOL_TrackDao::getInstance()->findAllTracks($page,$limit);
            $resultTracks = array();
            foreach ($tracks as $track){
                $result = FRMFARAPAYAMAK_BOL_Service::getInstance()->checkSMSStatus($track->smsId);
                $resultTracks[] = array(
                    'text'=>$track->message,
                    'mobile'=>$track->mobile,
                    'time'=>$track->time,
                    'status'=>FRMFARAPAYAMAK_BOL_Service::getInstance()->getStatusString($result)
                );
            }
            $this->addComponent('paging', new BASE_CMP_Paging($page, ceil(FRMFARAPAYAMAK_BOL_TrackDao::getInstance()->findAllTrackCount() / $limit), 5));
            $this->assign('tracks', $resultTracks);
        }
        $this->assign('section',$currentSection);
        $this->assign('section1_label',OW::getLanguage()->text('frmfarapayamak', 'section1_label'));
        $this->assign('section2_label',OW::getLanguage()->text('frmfarapayamak', 'section2_label'));
        $this->assign('section1_url',OW::getRouter()->urlForRoute('frmfarapayamak_admin_setting_section',array('section'=>1)));
        $this->assign('section2_url',OW::getRouter()->urlForRoute('frmfarapayamak_admin_setting_section',array('section'=>2)));
    }

    public function tracking(){

    }

}