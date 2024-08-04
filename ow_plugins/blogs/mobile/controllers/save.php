<?php
/**
 * @package ow_plugins.blogs.controllers
 * @since 1.0
 */
class BLOGS_MCTRL_Save extends OW_MobileActionController
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

        if ( !OW::getUser()->isAuthorized('blogs', 'add') && !OW::getUser()->isAuthorized('blogs') && !OW::getUser()->isAdmin() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('blogs', 'add_blog');
            throw new AuthorizationException($status['msg']);
        }

        $form = new CreateBlogPostForm();

        $attachmentCmp = new BASE_CLASS_FileAttachment('blog', $form->getValues()['attachmentUid']);
        $this->addComponent('attachments', $attachmentCmp);

        $this->assign('send_notification', OW::getAuthorization()->isUserAuthorized(OW::getUser()->getId(), 'blogs', 'publish_notification'));

        if ( OW::getRequest()->isPost() && (!empty($_POST['command']) && in_array($_POST['command'], array('draft', 'publish')) ) && $form->isValid($_POST) )
        {
            $values = $form->process();
            $postDto = PostService::getInstance()->createBlogPost(
                $values['title'],
                $values['post'],
                $values['attachmentUid'],
                $values['enSentNotification']??false,
                $values['tf']
            );

            if(!isset($postDto)){
                throw new Redirect404Exception();
            }

            if ($postDto->isDraft()) {
                OW::getFeedback()->info(OW::getLanguage()->text('blogs', 'create_draft_success_msg'));
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('blog-manage-drafts'));
            }else{
                OW::getFeedback()->info(OW::getLanguage()->text('blogs', 'create_success_msg'));
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('post', array('id' => $postDto->getId())));
            }
        }

        $this->addForm($form);

        OW::getEventManager()->trigger(new OW_Event('frmwidgetplus.general.before.view.render', array('targetPage' => 'blogs')));

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'blogs', 'main_menu_item');
        $this->setPageHeadingIconClass('ow_ic_write');
        $this->setPageHeading(OW::getLanguage()->text('blogs', 'save_page_heading'));
        OW::getDocument()->setTitle(OW::getLanguage()->text('blogs', 'meta_title_new_blog_post'));
        OW::getDocument()->setDescription(OW::getLanguage()->text('blogs', 'meta_description_new_blog_post'));
        $this->assign('authMsg', null);
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

        if ( !OW::getUser()->isAuthorized('blogs', 'add') && !OW::getUser()->isAuthorized('blogs') && !OW::getUser()->isAdmin() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('blogs', 'add_blog');
            throw new AuthorizationException($status['msg']);
        }

        $postId = $params['id'];

        $postService = PostService::getInstance(); /* @var $service PostService */
        $postDto = $postService->findById($postId);

        if (!isset($postDto) || ($postDto->authorId != OW::getUser()->getId() && !OW::getUser()->isAuthorized('blogs')))
        {
            throw new Redirect404Exception();
        }

        $form = new EditBlogPostForm($postDto);

        $attachmentCmp = new BASE_CLASS_FileAttachment('blog', $postDto->bundleId);
        $this->addComponent('attachments', $attachmentCmp);

        $this->assign('send_notification', OW::getAuthorization()->isUserAuthorized(OW::getUser()->getId(), 'blogs', 'publish_notification'));

        if ( OW::getRequest()->isPost() && (!empty($_POST['command']) && in_array($_POST['command'], array('draft', 'publish')) ) && $form->isValid($_POST) )
        {
            $values = $form->process();
            $updatedPost = PostService::getInstance()->updateBlogPost(
                $postDto,
                $values['title'],
                $values['post'],
                $values['attachmentUid'],
                $values['enSentNotification']??false,
                $values['tf']
            );

            if(!isset($updatedPost)){
                throw new Redirect404Exception();
            }

            if ($updatedPost->isDraft()) {
                OW::getFeedback()->info(OW::getLanguage()->text('blogs', 'edit_draft_success_msg'));
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('blog-manage-drafts'));
            }else{
                OW::getFeedback()->info(OW::getLanguage()->text('blogs', 'edit_success_msg'));
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('post', array('id' => $postDto->getId())));
            }
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('post-save-edit', array('id' => $postDto->getId())));
        }

        $this->addForm($form);

        OW::getEventManager()->trigger(new OW_Event('frmwidgetplus.general.before.view.render', array('targetPage' => 'blogs')));

        $this->assign('info', array('dto' => $postDto));
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'blogs', 'main_menu_item');
        $this->setPageHeadingIconClass('ow_ic_write');
        $this->setPageHeading(OW::getLanguage()->text('blogs', 'edit_page_heading'));
        OW::getDocument()->setTitle(OW::getLanguage()->text('blogs', 'meta_title_edit_blog_post'));
        OW::getDocument()->setDescription(OW::getLanguage()->text('blogs', 'meta_description_edit_blog_post'));
        $this->setDocumentKey("blog_save_index");
        $this->assign('authMsg', null);
    }

    public function delete( $params )
    {
        if (OW::getRequest()->isAjax() || !OW::getUser()->isAuthenticated())
        {
            exit();
        }

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($params['code'])){
                throw new Redirect404Exception();
            }
            $code = $params['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_blog')));
        }
        /*
          @var $service PostService
         */
        $service = PostService::getInstance();

        $id = $params['id'];

        $dto = $service->findById($id);

        if ( !empty($dto) )
        {
            if ($dto->authorId == OW::getUser()->getId() || OW::getUser()->isAuthorized('blogs'))
            {
                OW::getEventManager()->trigger(new OW_Event(PostService::EVENT_BEFORE_DELETE, array(
                    'postId' => $id
                )));
                $service->delete($dto);
                OW::getEventManager()->trigger(new OW_Event(PostService::EVENT_AFTER_DELETE, array(
                    'postId' => $id
                )));
            }
        }

        if ( !empty($_GET['back-to']) )
        {
            if(strpos( $_GET['back-to'], ":") === false ) {
                $this->redirect($_GET['back-to']);
            }
        }
        $author = BOL_UserService::getInstance()->findUserById($dto->authorId);
        $this->redirect(OW::getRouter()->urlForRoute('blogs'));
    }
}

abstract class BlogPostForm extends Form
{
    public function __construct($formName)
    {
        parent::__construct($formName);
        $this->setMethod('post');

        $titleTextField = new TextField('title');
        $this->addElement($titleTextField->setLabel(OW::getLanguage()->text('blogs', 'save_form_lbl_title'))->setRequired(true));

        if (OW::getAuthorization()->isUserAuthorized(OW::getUser()->getId(), 'blogs', 'publish_notification')) {
            $language = OW::getLanguage();
            $enSentNotification = new CheckboxField('enSentNotification');
            $enSentNotification->setLabel($language->text('blogs', 'notification_form_lbl_published'));
            $this->addElement($enSentNotification);
        }

        // attachments
        $attachmentUidField = new HiddenField('attachmentUid');
        $attachmentUidField->setValue(FRMSecurityProvider::generateUniqueId());
        $this->addElement($attachmentUidField);

        $buttons = array(
            BOL_TextFormatService::WS_BTN_BOLD,
            BOL_TextFormatService::WS_BTN_ITALIC,
            BOL_TextFormatService::WS_BTN_UNDERLINE,
            BOL_TextFormatService::WS_BTN_IMAGE,
            BOL_TextFormatService::WS_BTN_LINK,
            BOL_TextFormatService::WS_BTN_VIDEO
        );

        $postTextArea = new MobileWysiwygTextarea('post','blogs', $buttons);
        $postTextArea->setLabel(OW::getLanguage()->text('blogs', 'save_form_lbl_post'));
        $postTextArea->setRequired(true);
        $this->addElement($postTextArea);

        $tf = new TagsInputField('tf');
        $tf->setLabel(OW::getLanguage()->text('blogs', 'tags_field_label'));
        $this->addElement($tf);
    }

    abstract public function process();
}

class CreateBlogPostForm extends BlogPostForm
{

    /**
     * CreateBlogPostForm constructor.
     * @param $formName
     */
    public function __construct()
    {
        parent::__construct('CreateBlogPostForm');

        $draftSubmit = new Submit('draft');
        $draftSubmit->addAttribute('onclick', "$('#save_post_command').attr('value', 'draft');$(this).addClass('ow_inprogress');");
        $draftText = OW::getLanguage()->text('blogs', 'sava_draft');
        $this->addElement($draftSubmit->setValue($draftText));


        $publishText = OW::getLanguage()->text('blogs', 'save_publish');
        $publishSubmit = new Submit('publish');
        $publishSubmit->addAttribute('onclick', "$('#save_post_command').attr('value', 'publish');$(this).addClass('ow_inprogress');");
        $this->addElement($publishSubmit->setValue($publishText));
    }

    public function process()
    {
        $values = $this->getValues();
        $values['title'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($values['title']));
        $values['post'] = UTIL_HtmlTag::sanitize($values['post']);
        $stringRenderer = OW::getEventManager()->trigger(
            new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,
                array('string' => $values['post'])
            )
        );
        if(isset($stringRenderer->getData()['string'])){
            $values['post'] = $stringRenderer->getData()['string'];
        }
        return $values;
    }
}

class EditBlogPostForm extends BlogPostForm
{
    /**
     *
     * @var Post
     */
    private $postDto;

    /**
     * EditBlogPostForm constructor.
     * @param $postDto
     */
    public function __construct($postDto)
    {
        parent::__construct('EditBlogPostForm');
        $this->postDto = $postDto;

        $eventAddBlogFormRender = OW::getEventManager()->trigger(new OW_Event('on.add.form.render'));

        $this->getElement('title')->setValue($this->postDto->getTitle());
        if(isset($eventAddBlogFormRender->getData()['titleValue']))
        {
            $this->getElement('title')->setValue($eventAddBlogFormRender->getData()['titleValue']);
        }
        $blogPost= $this->postDto->getPost();
        $entryEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $blogPost)));
        if(isset($entryEvent->getData()["string"])){
            $blogPost =  $entryEvent->getData()["string"];
        }
        $this->getElement('post')->setValue($blogPost);
        if(isset($eventAddBlogFormRender->getData()['bodyValue']))
        {
            $this->getElement('post')->setValue($eventAddBlogFormRender->getData()['bodyValue']);
        }
        $this->getElement('attachmentUid')->setValue($this->postDto->getBundleId());

        $tags = array();
        if ( intval($postDto->getId()) > 0 )
        {
            $arr = BOL_TagService::getInstance()->findEntityTags($postDto->getId(), 'blog-post');

            foreach ( (!empty($arr) ? $arr : array() ) as $dto )
            {
                $tags[] = $dto->getLabel();
            }
        }
        $this->getElement('tf')->setValue($tags);
        if(isset($eventAddBlogFormRender->getData()['tagsValue']))
        {
            $this->getElement('tf')->setValue($eventAddBlogFormRender->getData()['tagsValue']);
        }
        $draftSubmit = new Submit('draft');
        $draftSubmit->addAttribute('onclick', "$('#save_post_command').attr('value', 'draft');$(this).addClass('ow_inprogress');");
        $draftText = OW::getLanguage()->text('blogs', 'change_status_draft');
        $this->addElement($draftSubmit->setValue($draftText));


        $publishSubmit = new Submit('publish');
        $publishSubmit->addAttribute('onclick', "$('#save_post_command').attr('value', 'publish');$(this).addClass('ow_inprogress');");
        $publishText = OW::getLanguage()->text('blogs', 'update');
        $this->addElement($publishSubmit->setValue($publishText));
    }

    public function process()
    {
        $values = $this->getValues();
        $values['title'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($values['title']));
        $values['post'] = UTIL_HtmlTag::sanitize($values['post']);
        $stringRenderer = OW::getEventManager()->trigger(
            new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,
                array('string' => $values['post'])
            )
        );
        if(isset($stringRenderer->getData()['string'])){
            $values['post'] = $stringRenderer->getData()['string'];
        }
        return $values;
    }
}