<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmnews.controllers
 * @since 1.0
 */
class FRMNEWS_MCTRL_Save extends OW_MobileActionController
{

    public function create()
    {
        if (OW::getRequest()->isAjax())
        {
            exit();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $entryService = EntryService::getInstance();

        if (!$entryService->canAddNews()) {
            throw new Redirect404Exception();
        }

        $form = new createNewsEntryForm();

        if ( OW::getRequest()->isPost() && (!empty($_POST['command']) && in_array($_POST['command'], array('draft', 'publish')) ) && $form->isValid($_POST) )
        {
            $values = $form->process();
            $entryDto = $entryService->createNewsEntry(
                $values['title'],
                $values['entry'],
                $values['tf'],
                $values['enSentNotification']??false,
                $_POST['command'] == 'draft'
            );
            $this->assign('info', array('dto' => $entryDto));
            if ($entryDto->getImage() )
            {
                $this->assign('imgsrc', $entryService->generateImageUrl($entryDto->getImage(), true));
            }

            if($entryDto->isDraft())
            {
                OW::getFeedback()->info(OW::getLanguage()->text('frmnews', 'create_draft_success_msg'));
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('news-manage-drafts'));
            }else{
                OW::getFeedback()->info(OW::getLanguage()->text('frmnews', 'create_success_msg'));
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmnews', array('id' => $entryDto->getId())));
            }
        }

        $this->addForm($form);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmnews')->getStaticCssUrl().'news.css');

        $this->assign('authMsg', null);
        $this->assign('backUrl', (OW::getRouter()->urlForRoute('frmnews')));
        $this->assign("urlForBack",OW::getRouter()->urlForRoute("frmnews"));
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmnews', 'main_menu_item');
        $this->setPageHeadingIconClass('ow_ic_write');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmnews')->getStaticJsUrl().'frmnews.js');
        $this->setPageHeading(OW::getLanguage()->text('frmnews', 'save_page_heading'));
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmnews', 'meta_title_new_news_entry'));
        OW::getDocument()->setDescription(OW::getLanguage()->text('frmnews', 'meta_description_new_news_entry'));
        $this->setDocumentKey("news_save_index");
    }

    public function edit($params = array())
    {
        if (OW::getRequest()->isAjax())
        {
            exit();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $entryService = EntryService::getInstance();

        $id = empty($params['id']) ? 0 : $params['id'];
        $entry = $entryService->findById($id);
        if (!isset($entry) || !$entryService->canEditNews($entry)) {
            throw new Redirect404Exception();
        }

        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWS_VIEW_RENDER,
            array('newsId' => $entry->getId(), 'pageType' => 'edit')));

        $this->assign('enPublishDate', true);

        if($_POST['deleteImage']==1)
        {
            if( !empty($entry->getImage()) )
            {
                $entryService->deleteEntryImage($entry->getId());
            }
        }

        if ($entry->getImage() )
        {
            $this->assign('imgsrc', $entryService->generateImageUrl($entry->getImage(), true));
        }

        $form = new editNewsEntryForm($entry);

        if ( OW::getRequest()->isPost() && (!empty($_POST['command']) && in_array($_POST['command'], array('draft', 'publish')) ) && $form->isValid($_POST) )
        {
            $values = $form->process();
            $updatedEntry = $entryService->updateNewsEntry(
                $entry,
                $values['title'],
                $values['entry'],
                $values['publish_date']??time(),
                $values['tf'],
                $values['enSentNotification']??false,
                $_POST['command'] == 'draft'
            );
            $this->assign('info', array('dto' => $updatedEntry));

            if($updatedEntry->isDraft())
            {
                OW::getFeedback()->info(OW::getLanguage()->text('frmnews', 'create_draft_success_msg'));
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('news-manage-drafts'));
            }else{
                OW::getFeedback()->info(OW::getLanguage()->text('frmnews', 'create_success_msg'));
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmnews', array('id' => $updatedEntry->getId())));
            }

            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('entry-save-edit', array('id' => $updatedEntry->getId())));
        }

        $this->addForm($form);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmnews')->getStaticCssUrl().'news.css');

        $this->assign('info', array('dto' => $entry));
        $this->assign('backUrl', (OW::getRouter()->urlForRoute('frmnews')));
        $this->assign("urlForBack",OW::getRouter()->urlForRoute("frmnews"));
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmnews', 'main_menu_item');
        $this->setPageHeadingIconClass('ow_ic_write');
        $this->setPageHeading(OW::getLanguage()->text('frmnews', 'edit_page_heading'));
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmnews', 'meta_title_edit_news_entry'));
        OW::getDocument()->setDescription(OW::getLanguage()->text('frmnews', 'meta_description_edit_news_entry'));
        $this->assign('authMsg', null);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmnews')->getStaticJsUrl().'frmnews.js');
        $this->setDocumentKey("news_save_index");
    }

    public function delete( $params )
    {
        if (OW::getRequest()->isAjax() || !OW::getUser()->isAuthenticated())
        {
            exit();
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code = $params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_news')));
        }
        /*
          @var $service EntryService
         */
        $service = EntryService::getInstance();

        $id = $params['id'];

        $dto = $service->findById($id);

        if(empty($dto) || !$service->canEditNews($dto))
        {
            throw new Redirect404Exception();
        }
        if ( !empty($dto) )
        {
            OW::getEventManager()->trigger(new OW_Event(EntryService::EVENT_BEFORE_DELETE, array(
                'entryId' => $id
            )));
            $service->delete($dto);
            OW::getEventManager()->trigger(new OW_Event(EntryService::EVENT_AFTER_DELETE, array(
                'entryId' => $id
            )));
        }

        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'news-add_news',
            'entityId' => $id
        ));

        if ( !empty($_GET['back-to']) )
        {
            if(strpos( $_GET['back-to'], ":") === false ) {
                $this->redirect($_GET['back-to']);
            }
        }
        $author = BOL_UserService::getInstance()->findUserById($dto->authorId);
        if(isset($author)){
            $this->redirect(OW::getRouter()->urlForRoute('frmnews-default', array('user' => $author->getUsername())));
        }else{
            $this->redirect(OW::getRouter()->urlForRoute('frmnews'));
        }
    }
}

abstract class newsEntryForm extends Form
{

    /**
     * newsEntryForm constructor.
     * @param $formName
     */
    public function __construct($formName)
    {
        parent::__construct($formName);
        $this->setMethod('post');
        $language = OW::getLanguage();

        $enRoleList = new CheckboxField('enSentNotification');
        $enRoleList->setLabel($language->text('frmnews', 'notification_form_lbl_published'));
        $this->addElement($enRoleList);

        $titleTextField = new TextField('title');
        $titleTextField->setLabel($language->text('frmnews', 'save_form_lbl_title'))->setRequired(true);
        $this->addElement($titleTextField);

        $buttons = array(
            BOL_TextFormatService::WS_BTN_BOLD,
            BOL_TextFormatService::WS_BTN_ITALIC,
            BOL_TextFormatService::WS_BTN_UNDERLINE,
            BOL_TextFormatService::WS_BTN_LINK,
            BOL_TextFormatService::WS_BTN_IMAGE
        );

        $entryTextArea = new MobileWysiwygTextarea('entry','frmnews', $buttons);
        $entryTextArea->setLabel($language->text('frmnews', 'save_form_lbl_entry'));
        $entryTextArea->setRequired(true);
        $this->addElement($entryTextArea);

        $imageField = new FileField('image');
        $imageField->setLabel($language->text('frmnews', 'add_form_image_label'));
        $this->addElement($imageField);

        $deleteImageField = new HiddenField('deleteImage');
        $deleteImageField->setId('deleteImage');
        $deleteImageField->setValue('false');
        $this->addElement($deleteImageField);

        $tf = new TagsInputField('tf');
        $tf->setLabel(OW::getLanguage()->text('frmnews', 'tags_field_label'));
        $this->addElement($tf);

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
    }
}

class createNewsEntryForm extends newsEntryForm
{

    /**
     * createNewsEntryForm constructor.
     */
    public function __construct()
    {
        parent::__construct('createNewsEntryForm');

        $draftSubmit = new Submit('draft');
        $draftSubmit->addAttribute('onclick', "$('#save_entry_command').attr('value', 'draft');$(this).addClass('ow_inprogress');");
        $draftText = OW::getLanguage()->text('frmnews', 'sava_draft');
        $this->addElement($draftSubmit->setValue($draftText));

        $publishSubmit = new Submit('publish');
        $publishSubmit->addAttribute('onclick', "$('#save_entry_command').attr('value', 'publish');$(this).addClass('ow_inprogress');");
        $publishText = OW::getLanguage()->text('frmnews', 'save_publish');
        $this->addElement($publishSubmit->setValue($publishText));
    }

    public function process()
    {
        OW::getCacheManager()->clean( array( EntryDao::CACHE_TAG_POST_COUNT ));
        $values = $this->getValues();
        $values['title'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($values['title']));
        $values['entry'] = UTIL_HtmlTag::sanitize($values['entry']);
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $values['entry'])));
        if(isset($stringRenderer->getData()['string'])){
            $values['entry'] = $stringRenderer->getData()['string'];
        }
        if ( !empty($_FILES['image']['name']) ) {
            if ((int)$_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name'])) {
                OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                OW::getApplication()->redirect();
            }
        }
        return $values;
    }
}

class editNewsEntryForm extends newsEntryForm
{
    /*
     * @var Entry
     */
    private $entryDto;

    /**
     * editNewsEntryForm constructor.
     * @param $entryDto
     */
    public function __construct($entryDto)
    {
        parent::__construct('editNewsEntryForm');
        $this->entryDto = $entryDto;

        $eventAddNewsFormRender = OW::getEventManager()->trigger(new OW_Event('on.add.form.render'));

        $this->getElement('title')->setValue($entryDto->getTitle());
        if(isset($eventAddNewsFormRender->getData()['titleValue']))
        {
            $this->getElement('title')->setValue($eventAddNewsFormRender->getData()['titleValue']);
        }
        $entryText = $entryDto->getEntry();
        $entryEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $entryDto->getEntry())));
        if(isset($entryEvent->getData()["string"])){
            $entryText =  $entryEvent->getData()["string"];
        }
        $this->getElement('entry')->setValue($entryText);
        if(isset($eventAddNewsFormRender->getData()['bodyValue']))
        {
            $this->getElement('entry')->setValue($eventAddNewsFormRender->getData()['bodyValue']);
        }

        $arr = BOL_TagService::getInstance()->findEntityTags($this->entryDto->getId(), 'news-entry');

        $tags = array();
        foreach ( (!empty($arr) ? $arr : array() ) as $dto )
        {
            $tags[] = $dto->getLabel();
        }
        $this->getElement('tf')->setValue($tags);
        if(isset($eventAddNewsFormRender->getData()['tagsValue']))
        {
            $this->getElement('tf')->setValue($eventAddNewsFormRender->getData()['tagsValue']);
        }

        if( $entryDto->getTimestamp()!=null) {
            $currentYear = date('Y', time());
            if(OW::getConfig()->getValue('frmjalali', 'dateLocale')==1){
                $currentYear=$currentYear-1;
            }
            $publishDate = new DateField('publish_date');
            $publishDate->setMinYear($currentYear - 10);
            $publishDate->setMaxYear($currentYear + 10);
            $publishDate->setRequired();
            $publishDate->setLabel(OW::getLanguage()->text('frmnews', 'save_form_lbl_date'));
            $this->addElement($publishDate);
            $publishDate = date('Y', $entryDto->getTimestamp()) . '/' . date('n', $entryDto->getTimestamp()) . '/' . date('j', $entryDto->getTimestamp());
            $this->getElement('publish_date')->setValue($publishDate);

            $enPublishDate = new CheckboxField('enPublishDate');
            $enPublishDate->setLabel(OW::getLanguage()->text('frmnews', 'save_form_lbl_date_enable'));
            $enPublishDate->addAttribute("onclick", "initPublishDateField('.published_date');");
            $this->addElement($enPublishDate);
        }

        $draftSubmit = new Submit('draft');
        $draftSubmit->addAttribute('onclick', "$('#save_entry_command').attr('value', 'draft');$(this).addClass('ow_inprogress');");
        $text = OW::getLanguage()->text('frmnews', 'change_status_draft');
        $this->addElement($draftSubmit->setValue($text));

        $publishSubmit = new Submit('publish');
        $publishSubmit->addAttribute('onclick', "$('#save_entry_command').attr('value', 'publish');$(this).addClass('ow_inprogress');");
        $publishText = OW::getLanguage()->text('frmnews', 'update');
        $this->addElement($publishSubmit->setValue($publishText));
    }

    public function process()
    {
        OW::getCacheManager()->clean( array( EntryDao::CACHE_TAG_POST_COUNT ));
        $values = $this->getValues();
        $values['title'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($values['title']));
        $values['entry'] = UTIL_HtmlTag::sanitize($values['entry']);
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $values['entry'])));
        if(isset($stringRenderer->getData()['string'])){
            $values['entry'] = $stringRenderer->getData()['string'];
        }
        if ( !empty($_FILES['image']['name']) ) {
            if ((int)$_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name'])) {
                OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                OW::getApplication()->redirect();
            }
        }
        return $values;
    }
}