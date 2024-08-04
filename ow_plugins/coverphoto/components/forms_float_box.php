<?php
class COVERPHOTO_CMP_FormsFloatBox extends OW_Component
{
    public function __construct( )
    {
        parent::__construct();
        $params = json_decode($_POST["params"], true);

        $entityType = $params['entityType'];
        $entityId = $params['entityId'];

        $service = COVERPHOTO_BOL_Service::getInstance();
        if(!$service->isOwner($entityType, $entityId)){
            $this->setVisible(false);
            throw new Redirect404Exception('Invalid uri was provided for routing!');
        }

        $user_cover =  $service->getSelectedCover($entityType, $entityId);
        $list = $service->findList($entityType, $entityId);
        $tplList = array();
        foreach ( $list as $listItem )
        {
            /* @var $listItem COVERPHOTO_BOL_Cover */
            $tplList[] = array(
                "title" => $listItem->title,
                "AutherName" => '',
                "AutherUrl" => '',
                "addDateTime" => UTIL_DateTime::formatDate($listItem->addDateTime),
                "coverPhotoImageUrl" => OW::getStorage()->getFileUrl(OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir().$listItem->hash),
                "useThisCoverIcon" => OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl().'img/' . 'choose.png',
                "deleteThisCoverIcon" => OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl().'img/' . 'remove.png',
                "deleteUrl" => OW::getRouter()->urlForRoute('coverphoto-forms-delete-item', array('id'=>$listItem->getId())),
                "useCoverUrl" => OW::getRouter()->urlForRoute('coverphoto-forms-use-item', array('id'=>$listItem->getId())),
                "isCurrent" => ($user_cover && $user_cover->id == $listItem->id)?true:false
            );
        }

        $this->assign("list", $tplList);
        $this->assign("coverPhotosUrl", OW::getRouter()->urlForRoute('coverphoto-index')."?entityType=$entityType&entityId=$entityId");
        $this->assign("new_cover_icon", OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl().'img/' . 'new.png');
        $this->assign("is_current_icon", OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl().'img/' . 'is_current.png');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('coverphoto')->getStaticCssUrl() . 'coverphoto.css');
    }
}
