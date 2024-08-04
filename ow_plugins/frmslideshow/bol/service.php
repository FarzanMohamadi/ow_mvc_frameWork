<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */
class FRMSLIDESHOW_BOL_Service
{
    /**
     * @var FRMSLIDESHOW_BOL_SlideDao
     */
    private $slideDao;

    /**
     * @var FRMSLIDESHOW_BOL_AlbumDao
     */
    private $albumDao;


    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->slideDao = FRMSLIDESHOW_BOL_SlideDao::getInstance();
        $this->albumDao = FRMSLIDESHOW_BOL_AlbumDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var FRMSLIDESHOW_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMSLIDESHOW_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    //----albums
    public function getAlbums(){
        return  $this->albumDao->getAlbums();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAlbumById($id)
    {
        return $this->albumDao->findById($id);
    }
    public function addAlbum($name)
    {
        $item = new FRMSLIDESHOW_BOL_Album();
        $item->name = $name;
        $this->albumDao->save($item);

        $widgetService = BOL_ComponentAdminService::getInstance();
        $widget = $widgetService->addWidget('FRMSLIDESHOW_MCMP_ExtraWidget', true);
        $uniqueName = FRMSLIDESHOW_MCMP_ExtraWidget::$uniqNamePrefix.$item->getId();
        $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX, $uniqueName);
        $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);

        return $item;
    }
    public function editAlbum($id, $name)
    {
        $item = $this->albumDao->findById($id);
        $item->name = $name;
        $this->albumDao->save($item);
        return $item;
    }
    public function deleteAlbum($id){
        $ex = new OW_Example();
        $ex->andFieldEqual('albumId', $id);
        $this->slideDao->deleteByExample($ex);
        $this->albumDao->deleteById($id);

        $uniqueName = FRMSLIDESHOW_MCMP_ExtraWidget::$uniqNamePrefix.$id;
        BOL_ComponentAdminService::getInstance()->deleteWidgetPlace($uniqueName);
    }
    public function createAllExtraWidgets(){
        $albums = $this->getAlbums();
        foreach($albums as $key=>$album){
            $widgetService = BOL_ComponentAdminService::getInstance();
            $widget = $widgetService->addWidget('FRMSLIDESHOW_MCMP_ExtraWidget', true);
            $uniqueName = FRMSLIDESHOW_MCMP_ExtraWidget::$uniqNamePrefix.$album->id;
            $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX, $uniqueName);
            $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
        }
    }
    public function deleteAllExtraWidgets(){
        $albums = $this->getAlbums();
        foreach($albums as $key=>$album){
            $uniqueName = FRMSLIDESHOW_MCMP_ExtraWidget::$uniqNamePrefix.$album->id;
            BOL_ComponentAdminService::getInstance()->deleteWidgetPlace($uniqueName);
        }
    }

    //-----slides
    public function getSlides($albumId){
        $ex = new OW_Example();
        $ex->andFieldEqual('albumId', $albumId);
        $ex->setOrder('`order` ASC');
        return  $this->slideDao->findListByExample($ex);
    }

    public function getSlideById($id){
        return  $this->slideDao->findById($id);
    }
    public function addSlide($albumId, $desc)
    {
        $item = new FRMSLIDESHOW_BOL_Slide();
        $item->albumId = $albumId;
        $item->description = $desc;
        $item->order = "10";

        $this->slideDao->save($item);
        return $item;
    }
    public function editSlide($id, $desc)
    {
        $item = $this->slideDao->findById($id);
        $item->description = $desc;
        $this->slideDao->save($item);
        return $item;
    }
    public function deleteSlide($id){
        $this->slideDao->deleteById($id);
    }
    public function setSlideOrder($id,$order){
        $item = $this->slideDao->findById($id);
        $item->order = $order;
        $this->slideDao->save($item);
        return $item;
    }

    //------forms
    public function getForm_addAlbum($action,$nameDefault=""){
        $form = new Form('addAlbum');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $name = new TextField('name');
        $name->setRequired(true)
            ->setLabel(OW::getLanguage()->text('frmslideshow', 'title'))
            ->setValue($nameDefault);
        $form->addElement($name);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    public function getForm_addSlide($action, $descDefault=""){
        $form = new Form('addSlide');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $buttons = array(
            BOL_TextFormatService::WS_BTN_BOLD,
            BOL_TextFormatService::WS_BTN_ITALIC,
            BOL_TextFormatService::WS_BTN_UNDERLINE,
            BOL_TextFormatService::WS_BTN_IMAGE,
            BOL_TextFormatService::WS_BTN_LINK,
            BOL_TextFormatService::WS_BTN_ORDERED_LIST,
            BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            BOL_TextFormatService::WS_BTN_MORE,
            BOL_TextFormatService::WS_BTN_SWITCH_HTML,
            BOL_TextFormatService::WS_BTN_HTML,
            BOL_TextFormatService::WS_BTN_VIDEO
        );
        $description = new WysiwygTextarea('description','frmslideshow', $buttons);
        $description->setSize(WysiwygTextarea::SIZE_L);
        $description->setLabel(OW::getLanguage()->text('frmslideshow', 'description'));
        $description->setRequired();
        $description->setValue($descDefault);
        $description->setHasInvitation(false);
        $form->addElement($description);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

}