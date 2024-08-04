<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class COVERPHOTO_CTRL_Forms extends OW_ActionController
{
    /**
     * @var COVERPHOTO_BOL_Service
     */
    protected $service;

    public function __construct()
    {
        $this->service = COVERPHOTO_BOL_Service::getInstance();

        OW::getDocument()->addStyleDeclaration('.ow_page_padding {    margin-top: 0px;}');

        if (!OW::getUser()->isAuthenticated())
        {
            $this->redirect(OW::getRouter()->urlForRoute('static_sign_in'));
        }
    }

    public function upload()
    {
        if(empty($_GET['entityType']) || empty($_GET['entityId'])){
            throw new Redirect404Exception('Invalid uri was provided for routing!');
        }
        $entityType = $_GET['entityType'];
        $entityId = $_GET['entityId'];

        if(!$this->service->isOwner($entityType, $entityId)){
            throw new Redirect404Exception('Invalid uri was provided for routing!');
        }

        $language = OW::getLanguage();

        OW::getDocument()->setTitle($language->text("coverphoto", "forms_page_title"));
        OW::getDocument()->setHeading($language->text("coverphoto", "forms_page_heading"));

        $form = new COVERPHOTO_CLASS_Form("coverphoto_form");
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $this->addForm($form);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $values = $form->getValues();

            $resp = $this->service->uploadNewCover($entityType, $entityId, $values["title"], 'image');
            if($resp['result']) {
                OW::getFeedback()->info($resp['message']);
            }else{
                OW::getFeedback()->error($resp['message']);
            }
            $this->redirect();
        }

        $user_cover = COVERPHOTO_BOL_Service::getInstance()->getSelectedCover($entityType, $entityId);
        $list = $this->service->findList($entityType, $entityId);
        $tplList = array();
        foreach ($list as $listItem) {
            /* @var $listItem COVERPHOTO_BOL_Cover */
            $tplList[] = array(
                "title" => $listItem->title,
                "AutherName" => '',
                "AutherUrl" => '',
                "addDateTime" => UTIL_DateTime::formatDate($listItem->addDateTime),
                "coverPhotoImageUrl" => OW::getStorage()->getFileUrl(OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir() . $listItem->hash),
                "useThisCoverIcon" => OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl() . 'img/' . 'choose.png',
                "deleteThisCoverIcon" => OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl() . 'img/' . 'remove.png',
                "deleteUrl" => OW::getRouter()->urlForRoute('coverphoto-forms-delete-item', array('id' => $listItem->getId())),
                "useCoverUrl" => OW::getRouter()->urlForRoute('coverphoto-forms-use-item', array('id' => $listItem->getId())),
                "isCurrent" => ($user_cover && $user_cover->id == $listItem->id) ? true : false
            );
        }

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('coverphoto')->getStaticCssUrl() . 'coverphoto.css');
        $this->assign("is_current_icon", OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl() . 'img/' . 'is_current.png');
        $this->assign("list", $tplList);
        $this->assign('backURL', $this->service->getPageURL($entityType, $entityId));
        $this->setDocumentKey("user_coverphotos");
    }

    private function safeRedirect(){
        if ( !empty($_SERVER['HTTP_REFERER']) )
        {
            $this->redirect($_SERVER['HTTP_REFERER']);
        }
        $this->redirect(OW::getRouter()->urlForRoute('index'));
    }

    public function deleteItem($params)
    {
        $this->service->deleteDatabaseRecord($params['id']);

        OW::getFeedback()->info(OW::getLanguage()->text('coverphoto', 'database_record_deleted'));

        $this->safeRedirect();
    }

    public function useItem($params)
    {
        $this->service->selectThisCover($params['id']);

        OW::getFeedback()->info(OW::getLanguage()->text('coverphoto', 'database_record_used'));

        $this->safeRedirect();
    }

    public function coverCrop($params)
    {
        $user_cover = COVERPHOTO_BOL_CoverDao::getInstance()->findById($params['id']);
        $this->service->addCroppedCover($user_cover, abs($_POST['pos']), abs($_POST['cover_photo_height']));

        $responseJson = Array(
            "status" => 200,
            "url" => OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir() . $user_cover->croppedHash
        );
        exit(json_encode($responseJson));
    }
}
