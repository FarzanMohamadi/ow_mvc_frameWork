<?php
class FRMINVITE_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    const CONF_MAIL_COUNT_ON_PAGE = 5;
    public function getService(){
        return FRMINVITE_BOL_Service::getInstance();
    }

    public function index($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frminvite', 'admin_invitation_settings_heading'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('frminvite', 'admin_invitation_settings_heading'));
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
            $formSettings->setAction(OW::getRouter()->urlForRoute('frminvite.admin'));
            $invitationDataCount = $this->getService()->getInvitationDetailsDataCount();
            $viewCount = $config->getValue('frminvite', 'invitation_view_count');
            $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($invitationDataCount / $viewCount), 5));
            $this->assign('sections', $service->getAdminSections($sectionId));
            $information = $this->getInvitationDetailsData($page,$invitationDataCount);
            $info = array();
            if (isset($information['data']))
                $info = $information['data'];
            $this->assign('tableData', $info);

        }
        else if($sectionId==2) {
            $this->assign('sectionId', 2);
            $form = new Form('viewCount_setting');
            $mailViewCount = new TextField('invitation_view_count');
            $mailViewCount->setLabel(OW::getLanguage()->text('frminvite', 'input_settings_view_count_label'));
            $mailViewCount->setRequired(true);
            $form->addElement($mailViewCount);

            $submit = new Submit('save');
            $form->addElement($submit);
            $this->addForm($form);
            $this->assign('sections', $service->getAdminSections($sectionId));
            if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
            {
                $data = $form->getValues();
                if((int)$data['invitation_view_count']>0) {
                    if ($config->configExists('frminvite', 'invitation_view_count')) {
                        $config->saveConfig('frminvite', 'invitation_view_count', (int)$data['invitation_view_count']);
                    }
                    OW::getFeedback()->info(OW::getLanguage()->text('frminvite', 'modified_successfully'));
                    $this->redirect();
                }
                else{
                    OW::getFeedback()->error(OW::getLanguage()->text('frminvite', 'invalid_invitation_count'));
                    $this->redirect();
                }
            }
            if($config->configExists('frminvite', 'invitation_view_count'))
            {
                $mailViewCount->setValue($config->getValue('frminvite', 'invitation_view_count'));
            }
        }else if($sectionId==3) {
            $this->assign('sectionId', 3);
            $this->assign('sections', $service->getAdminSections($sectionId));
            $form = new Form('createInvitationLink');
            $form->setAction(OW::getRouter()->urlForRoute('frminvite.admin.link'));
            $form->setAjax();
            $form->setAjaxResetOnSuccess(false);
            $submit = new Submit('save');
            $form->addElement($submit);
            $form->bindJsFunction(Form::BIND_SUCCESS, 'function( json )
            {
            	if( json.result )
            	{
                  document.getElementById("InvitationLink").style.display="block";
                  document.getElementById("InvitationLink").innerHTML = "<a id=\"aInvitationLink\" target=\"_blank\" href=\""+json.url+" \">"+json.url+"</a>";
                }
                else
                {
                }

            } ' );

            $this->addForm($form);
        }else if($sectionId==4) {
            $this->assign('sectionId', 4);
            $this->assign('sections', $service->getAdminSections($sectionId));
            $form = new Form('setting');
            $form->setAction(OW::getRouter()->urlForRoute('frminvite.admin.section-id', array('sectionId' => 4)));
            $limit = new TextField('limit');
            $limit->addValidator(new IntValidator(0));
            $limit->setRequired(true);
            $limit->setLabel(OW::getLanguage()->text('frminvite', 'limit_field'));
            if (OW::getConfig()->configExists('frminvite', 'invite_daily_limit'))
                $limit->setValue(OW::getConfig()->getValue('frminvite', 'invite_daily_limit'));
            $form->addElement($limit);
            $submit = new Submit('save');
            $form->addElement($submit);
            $this->addForm($form);
            if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
                $data = $form->getValues();
                if (isset($data['limit'])) {

                    if ($config->configExists('frminvite', 'invite_daily_limit')) {
                        $config->saveConfig('frminvite', 'invite_daily_limit', (int)$data['limit']);
                    }
                    OW::getFeedback()->info(OW::getLanguage()->text('frminvite', 'modified_successfully'));
                    $this->redirect();
                }
                else{
                    OW::getFeedback()->error(OW::getLanguage()->text('frminvite', 'invalid_invitation_count'));
                    $this->redirect();
                }
            }
        }
    }

    /**
     * @param $page
     * @param $invitationDataCount
     * @return array
     */
    public function getInvitationDetailsData($page,$invitationDataCount)
    {
        $data =$this->getService()->getInvitationDetailsData($page,$invitationDataCount);
        return $data;
    }

    public function createInvitationLink(){
        if (OW::getRequest()->isAjax()) {
            $dto = new BOL_InviteCode();
            $dto->setCode(UTIL_String::getRandomString(20));
            $dto->setUserId(0);
            $dto->setExpiration_stamp(time() + 3600 * 24 * 30);
            BOL_InviteCodeDao::getInstance()->save($dto);
            $url = OW_URL_HOME.'join?code='.$dto->code;
            exit(json_encode(array('url' => urldecode($url), 'result' => true)));
        }
    }

}
