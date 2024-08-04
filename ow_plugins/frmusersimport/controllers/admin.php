<?php
class FRMUSERSIMPORT_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        $lang = OW::getLanguage();

        $this->setPageHeading($lang->text('frmusersimport', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmusersimport', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /***
     * @param $params
     * @throws Redirect404Exception
     */
    public function index($params)
    {
        $service = FRMUSERSIMPORT_BOL_Service::getInstance();
        $language = OW::getLanguage();
        $isActive = FRMSecurityProvider::checkPluginActive('frmsms', true);
        if(!$isActive){
            $this->assign('sms_plugin_is_not_active', $language->text('frmusersimport', 'frmsms_plugin_not_active'));
        }
        $userImportForm = $service->getUsersImportForm(OW::getRouter()->urlForRoute('frmusersimport-admin'));
        $this->addForm($userImportForm);

        $userImportFormSetting = $service->getUsersImportFormSetting();
        $this->addForm($userImportFormSetting);

        if ( OW::getRequest()->isPost() )
        {
            if ( isset($_POST['form_name']) && $_POST['form_name'] == FRMUSERSIMPORT_BOL_Service::$USER_IMPORT_FORM_NAME && $userImportForm->isValid($_POST))
            {
                $resultError = $service->processFileImported();
                if(sizeof($resultError) == 0) {
                    OW::getFeedback()->info($language->text('frmusersimport', 'import_successfully'));
                }else{
                    $this->assign("linesError", $resultError);
                }
            }

            if ( isset($_POST['form_name']) && $_POST['form_name'] == FRMUSERSIMPORT_BOL_Service::$USER_IMPORT_SETTING_FORM_NAME && $userImportFormSetting->isValid($_POST) ){
                OW::getConfig()->saveConfig('frmusersimport', 'notification_type', $userImportFormSetting->getElement('type')->getValue());
                exit(json_encode(array('result' => true)));
            }
        }

        if(isset($params['key']) && $params['key'] == 'sleepy'){
            if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin()){
                throw new Redirect404Exception();
            }
            $service = FRMUSERSIMPORT_BOL_Service::getInstance();
            $language = OW::getLanguage();

            $emailsSent = $service->sendAccountInformationToSleepyUsers();
            $lines = array();
            foreach ($emailsSent as $email){
                $lines[] = $service->getSentEmailMessageToAdmin($email);
            }
            if(sizeof($lines) == 0) {
                $lines[] = $service->getEmptyEmailSentListMessage();
                OW::getFeedback()->warning($language->text('frmusersimport', 'empty_sleepy_users'));
            }else{
                OW::getFeedback()->info($language->text('frmusersimport', 'resend_successfully'));
            }
            $this->assign("linesError", $lines);
        }

        $this->assign('sleepyUrl', OW::getRouter()->urlForRoute('frmusersimport-admin-sleepy', array('key' => 'sleepy')));
        $guideline_heading = Ow::getLanguage()->text('frmusersimport','guideline_heading');
        $guideline = $this->getHelp();
        $this->assign('guideline',$guideline);
        $this->assign('guideline_heading',$guideline_heading);
    }
    private function getHelp(){
        $image1 =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmusersimport')->getStaticUrl(). 'img/image1.png" />' .
            '</div>' .
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmusersimport','help_image_caption1') .
            '</div>';

        $image2 =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmusersimport')->getStaticUrl(). 'img/image2.png" />' .
            '</div>' .
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmusersimport','help_image_caption2') .
            '</div>';


        $image3 =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmusersimport')->getStaticUrl(). 'img/image3.png" />' .
            '</div>' .
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmusersimport','help_image_caption3') .
            '</div>';

        $image4 =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmusersimport')->getStaticUrl(). 'img/image4.png" />' .
            '</div>' .
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmusersimport','help_image_caption4') .
            '</div>';

        $image5 =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmusersimport')->getStaticUrl(). 'img/image5.png" />' .
            '</div>' .
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmusersimport','help_image_caption5') .
            '</div>';

        $image6 =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmusersimport')->getStaticUrl(). 'img/image6.png" />' .
            '</div>' .
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmusersimport','help_image_caption6') .
            '</div>';

        $image7 =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmusersimport')->getStaticUrl(). 'img/image7.png" />' .
            '</div>' .
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmusersimport','help_image_caption7') .
            '</div>';

        $image8 =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmusersimport')->getStaticUrl(). 'img/image8.png" />' .
            '</div>' .
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmusersimport','help_image_caption8') .
            '</div>';

        $importGuideline = OW::getLanguage()->text('frmusersimport',
            'import_user_guideline',
            array(
                'image1'=>$image1,
                'image2'=>$image2,
                'image3'=>$image3,
                'image4'=>$image4,
                'image5'=>$image5,
                'image6'=>$image6,
                'image7'=>$image7,
                'image8'=>$image8));
        return $importGuideline;

    }
}
