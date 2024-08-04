<?php
/**
 * frmslideshow
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */

class FRMSLIDESHOW_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function getAdminSections($sectionId)
    {
        $sections = array();

        $sections[] = array(
            'sectionId' => 1,
            'active' => ($sectionId == 1),
            'url' => OW::getRouter()->urlForRoute('frmslideshow.admin.section-id', array('sectionId' => 1)),
            'label' => OW::getLanguage()->text('frmslideshow', 'general_settings')
        );
        $sections[] = array(
            'sectionId' => 2,
            'active' => ($sectionId == 2),
            'url' => OW::getRouter()->urlForRoute('frmslideshow.admin.section-id', array('sectionId' => 2)),
            'label' => OW::getLanguage()->text('frmslideshow', 'more_sections')
        );
        return $sections;
    }
    public function index($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmslideshow', 'admin_settings_heading'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmslideshow', 'admin_settings_heading'));
        $sectionId = 1;
        if(isset($params['sectionId'])){
            $sectionId = $params['sectionId'];
        }
        $this->assign('sections', $this->getAdminSections($sectionId));
        $this->assign('sectionId', $sectionId);

        if($sectionId==1) {

            $form = new Form("form");
            $configs = OW::getConfig()->getValues('frmslideshow');

            $textField = new TextField('news_count');
            $textField->setLabel(OW::getLanguage()->text('frmslideshow', 'news_count'))
                ->setValue($configs['news_count'])
                ->setRequired(true);
            $form->addElement($textField);

            $textField = new TextField('max_text_char');
            $textField->setLabel(OW::getLanguage()->text('frmslideshow', 'max_text_char'))
                ->setValue($configs['max_text_char'])
                ->setRequired(true);
            $form->addElement($textField);

            $submit = new Submit('submit');
            $submit->setValue(OW::getLanguage()->text('frmslideshow', 'save_btn_label'));
            $form->addElement($submit);

            if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
            {
                $data = $form->getValues();
                OW::getConfig()->saveConfig('frmslideshow', 'news_count', $data['news_count']);
                OW::getConfig()->saveConfig('frmslideshow', 'max_text_char', $data['max_text_char']);
                OW::getFeedback()->info(OW::getLanguage()->text('frmslideshow', 'saved_successfully'));
            }
            $this->addForm($form);
        }
        else if($sectionId==2) {
            $service = FRMSLIDESHOW_BOL_Service::getInstance();

            $addAlbumForm = $service->getForm_addAlbum(OW::getRouter()->urlForRoute('frmslideshow.admin.section-id',array("sectionId"=>2)));
            $this->addForm($addAlbumForm);

            if (OW::getRequest()->isPost()) {
                if ($addAlbumForm->isValid($_POST)) {
                    $name = $_REQUEST['name'];
                    $service->addAlbum($name);
                    OW::getFeedback()->info(OW::getLanguage()->text('frmslideshow', 'saved_successfully'));
                    $this->redirect();
                }
            }

            $albums = $service->getAlbums();
            $albumsArray = array();
            foreach ($albums as $item) {
                $albumsArray[] = array(
                    'name' => $item->name,
                    'id' => $item->id,
                    'editUrl' => OW::getRouter()->urlForRoute('frmslideshow.admin.edit-album', array('id' => $item->id)),
                    'questionsUrl' => OW::getRouter()->urlForRoute('frmslideshow.admin.slides', array('albumId' => $item->id)),
                    'deleteUrl' => "if(confirm('" . OW::getLanguage()->text('frmslideshow', 'delete_item_warning')
                        . "')){location.href='" . OW::getRouter()->urlForRoute('frmslideshow.admin.delete-album', array('id' => $item->id)) . "';}",
                );
            }
            $this->assign('albums', $albumsArray);

            $cssDir = OW::getPluginManager()->getPlugin("frmslideshow")->getStaticCssUrl();
            OW::getDocument()->addStyleSheet($cssDir . "frmslideshow.css");
        }
    }

    public function editAlbum($params)
    {
        if (!isset($params['id'])) {
            $this->redirect(OW::getRouter()->urlForRoute('frmslideshow.admin.section-id',array("sectionId"=> 2)));
        }
        $id = $params['id'];
        $service = FRMSLIDESHOW_BOL_Service::getInstance();
        $album = $service->getAlbumById($id);
        $form = $service->getForm_addAlbum(OW::getRouter()->urlForRoute('frmslideshow.admin.edit-album', array('id' => $id)), $album->name);
        $this->addForm($form);
        $this->assign('returnToAlbums', OW::getRouter()->urlForRoute('frmslideshow.admin.section-id',array("sectionId"=> 2)));
        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $name = $_REQUEST['name'];
                $service->editAlbum($id, $name);
                OW::getFeedback()->info(OW::getLanguage()->text('frmslideshow', 'saved_successfully'));
                $this->redirect(OW::getRouter()->urlForRoute('frmslideshow.admin.edit-album', array('id' => $id)));
            }
        }
    }

    public function deleteAlbum($params)
    {
        if (!isset($params['id'])) {
            $this->redirect(OW::getRouter()->urlForRoute('frmslideshow.admin.section-id',array("sectionId"=> 2)));
        }
        $service = FRMSLIDESHOW_BOL_Service::getInstance();
        $service->deleteAlbum($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmslideshow', 'deleted_successfully'));
        $this->redirect(OW::getRouter()->urlForRoute('frmslideshow.admin.section-id',array("sectionId"=> 2)));
    }

    //--------slides

    public function slides($params)
    {
        if (!isset($params['albumId'])) {
            $this->redirect(OW::getRouter()->urlForRoute('frmslideshow.admin.section-id',array("sectionId"=> 2)));
        }
        $service = FRMSLIDESHOW_BOL_Service::getInstance();
        $albumId = $params['albumId'];
        $album = $service->getAlbumById($albumId);
        $this->assign('albumName', $album->name);

        $form = $service->getForm_addSlide(OW::getRouter()->urlForRoute('frmslideshow.admin.slides', array('albumId' => $albumId)));
        $this->addForm($form);
        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $description = $_REQUEST['description'];
                $service->addSlide($albumId, $description);
                OW::getFeedback()->info(OW::getLanguage()->text('frmslideshow', 'saved_successfully'));
                $this->redirect(OW::getRouter()->urlForRoute('frmslideshow.admin.slides', array('albumId' => $albumId)));
            }
        }

        $slides = $service->getSlides($albumId);
        $slidesArray = array();
        $counter = 1;
        foreach ($slides as $item) {
            $slidesArray[] = array(
                'description' => strip_tags(UTIL_HtmlTag::stripTags($item->description)),
                'id' => $item->id,
                'counter' => $counter,
                'editUrl' => OW::getRouter()->urlForRoute('frmslideshow.admin.edit-slide', array('id' => $item->id)),
                'deleteUrl' => "if(confirm('".OW::getLanguage()->text('frmslideshow','delete_item_warning').
                    "')){location.href='" . OW::getRouter()->urlForRoute('frmslideshow.admin.delete-slide', array('id' => $item->id)) . "';}",
            );
            $counter++;
        }
        $this->assign('slides', $slidesArray);

        $this->assign('returnToAlbums', OW::getRouter()->urlForRoute('frmslideshow.admin.section-id',array("sectionId"=> 2)));
        $cssDir = OW::getPluginManager()->getPlugin("frmslideshow")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmslideshow.css");
    }

    public function editSlide($params)
    {
        if (!isset($params['id'])) {
            $this->redirect(OW::getRouter()->urlForRoute('frmslideshow.admin.section-id',array("sectionId"=> 2)));
        }
        $service = FRMSLIDESHOW_BOL_Service::getInstance();
        $id = $params['id'];
        $slide = $service->getSlideById($id);
        $form = $service->getForm_addSlide(OW::getRouter()->urlForRoute('frmslideshow.admin.edit-slide', array('id' => $id)),$slide->description);
        $this->addForm($form);
        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $description = $_REQUEST['description'];
                $service->editSlide($id,$description);
                OW::getFeedback()->info(OW::getLanguage()->text('frmslideshow', 'saved_successfully'));
                $this->redirect(OW::getRouter()->urlForRoute('frmslideshow.admin.edit-slide', array('id' => $id)));
            }
        }
        $this->assign('returnToSlides', OW::getRouter()->urlForRoute('frmslideshow.admin.slides', array('albumId' => $slide->albumId)));

        $cssDir = OW::getPluginManager()->getPlugin("frmslideshow")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmslideshow.css");
    }

    public function deleteSlide($params)
    {
        if (!isset($params['id'])) {
            $this->redirect(OW::getRouter()->urlForRoute('frmslideshow.admin.section-id',array("sectionId"=> 2)));
        }
        $service = FRMSLIDESHOW_BOL_Service::getInstance();
        $slide = $service->getSlideById($params['id']);
        $service->deleteSlide($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmslideshow', 'deleted_successfully'));
        $this->redirect(OW::getRouter()->urlForRoute('frmslideshow.admin.slides', array('albumId' => $slide->albumId)));
    }

    public function ajaxSaveAlbumsOrder()
    {
    }

    public function ajaxSaveSlidesOrder()
    {
        if (!empty($_POST['slide']) && is_array($_POST['slide'])) {
            $service = FRMSLIDESHOW_BOL_Service::getInstance();
            foreach ($_POST['slide'] as $index => $id) {
                $service->setSlideOrder($id,$index+1);
            }
        }
    }

}