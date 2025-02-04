<?php
class BASE_MCTRL_Join extends BASE_CTRL_Join
{
    public function __construct()
    {        
        parent::__construct();

        $this->responderUrl = OW::getRouter()->urlFor("BASE_MCTRL_Join", "ajaxResponder");
    }

    public function index( $params )
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->urlForRoute('base_index'));
        }
        
        parent::index($params);
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'join_index.html');

        $urlParams = $_GET;
        
        if ( is_array($params) && !empty($params) )
        {
            $urlParams = array_merge($_GET, $params);
        }

        /* @var $form JoinForm */
        $form = $this->joinForm;
        
        if( !empty($form) )
        {
            $this->joinForm->setAction(OW::getRouter()->urlFor('BASE_MCTRL_Join', 'joinFormSubmit', $urlParams));
            
            BASE_MCLASS_JoinFormUtlis::setLabels($form, $form->getSortedQuestionsList());
            BASE_MCLASS_JoinFormUtlis::setInvitations($form, $form->getSortedQuestionsList());
            BASE_MCLASS_JoinFormUtlis::setColumnCount($form);

            $displayPhotoUpload = OW::getConfig()->getValue('base', 'join_display_photo_upload');

            $this->assign('requiredPhotoUpload', ($displayPhotoUpload == BOL_UserService::CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD));
            $this->assign('presentationToClass', $this->presentationToCssClass());

            $element = $this->joinForm->getElement('userPhoto');

            $this->assign('photoUploadId', 'userPhoto');

            if ( $element )
            {
                $this->assign('photoUploadId', $element->getId());
            }

            BASE_MCLASS_JoinFormUtlis::addOnloadJs($form->getName());
        }

        // set meta info
        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "join",
            "title" => "base+meta_title_join",
            "description" => "base+meta_desc_join",
            "keywords" => "base+meta_keywords_join"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_JOIN_PAGE_RENDER, array('joinCtrl' => $this)));
    }

    protected function presentationToCssClass()
    {
        return BASE_MCLASS_JoinFormUtlis::presentationToCssClass();
    }
    
    public function ajaxResponder()
    {
        parent::ajaxResponder();
    }

    public function joinFormSubmit( $params )
    {
        parent::joinFormSubmit($params);
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'join_index.html');
    }
    
    protected function createAvatar( $userId )
    {
        $avatarService = BOL_AvatarService::getInstance();

        $path = $_FILES['userPhoto']['tmp_name'];

        if ( !OW::getStorage()->fileExists($path) )
        {
            return false;
        }

        if ( !UTIL_File::validateImage($_FILES['userPhoto']['name']) )
        {
            return false;
        }

        $avatarSet = $avatarService->setUserAvatar($userId, $path, array('isModerable' => false, 'trackAction' => false ));

        return $avatarSet;
    }

    public function backStep(){
        $session = OW::getSession();
        $step = $session->get(JoinForm::SESSION_JOIN_STEP);
        $step--;
        $session->set(JoinForm::SESSION_JOIN_STEP, $step);
        $session->delete(JoinForm::SESSION_REAL_QUESTION_LIST);
        $this->redirect(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_join')));
    }
}