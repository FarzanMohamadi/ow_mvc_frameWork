<?php
/**
 * Forum edit post action controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.controllers
 * @since 1.0
 */
class FORUM_CTRL_EditPost extends OW_ActionController
{

    public function index( array $params = null )
    {
        $forumService = FORUM_BOL_ForumService::getInstance();

        if ( !isset($params['id']) || !($postId = (int) $params['id']) )
        {
            throw new Redirect404Exception();
        }

        $postDto = $forumService->findPostById($postId);
        $eventPostListData = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FORUM_POST_RENDER, array('postId' => $postId)));
        if(isset($eventPostListData->getData()['extendedText'])){
            $this->assign('extendedText', $eventPostListData->getData()['extendedText']);
        }

        if ( !$postDto )
        {
            throw new Redirect404Exception();
        }

        $userId = OW::getUser()->getId();
        $topicId = $postDto->topicId;
        $topicDto = $forumService->findTopicById($topicId);

        $forumGroup = $forumService->getGroupInfo($topicDto->groupId);
        $forumSection = $forumService->findSectionById($forumGroup->sectionId);

        $isHidden = $forumSection->isHidden;

        if ( $isHidden )
        {
            $isModerator = OW::getUser()->isAuthorized($forumSection->entity);

            $eventParams = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'add_topic');
            $event = new OW_Event('forum.check_permissions', $eventParams);
            OW::getEventManager()->trigger($event);
            $canPost = $event->getData();

            //check permissions
            $canEdit = OW::getUser()->isAuthorized($forumSection->entity, 'add_topic') && $userId == $postDto->userId;

            if ( !$isModerator )
            {
                if ( !$canPost )
                {
                    throw new AuthorizationException();
                }
                else if ( !$canEdit )
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus(
                        $forumSection->entity, 'add_topic'
                    );
                    throw new AuthorizationException($status['msg']);
                }
            }
        }
        else
        {
            $isModerator = OW::getUser()->isAuthorized('forum');
            $canEdit = $postDto->userId == OW::getUser()->getId();

            if ( !$canEdit && !$isModerator )
            {
                throw new AuthorizationException();
            }
        }

        $this->assign('postId', $postId);

        $uid = FRMSecurityProvider::generateUniqueId();
        $editPostForm = $this->generateEditPostForm($postDto, $uid);
        $this->addForm($editPostForm);

        $lang = OW::getLanguage();
        $router = OW::getRouter();

        $enableAttachments = OW::getConfig()->getValue('forum', 'enable_attachments');
        $this->assign('enableAttachments', $enableAttachments);

        if ( $enableAttachments )
        {
            $attachments = FORUM_BOL_PostAttachmentService::getInstance()->findAttachmentsByPostIdList(array($postId));
            foreach ($attachments as $attachment) {
                for ($i = 0; $i < count($attachment); $i++) {
                    $ext = UTIL_File::getExtension($attachment[$i]['fileName']);
                    $attachment[$i]['extension'] = $ext;
                    $attachments[$attachment[$i]['postId']] = $attachment;
                }
            }
            $this->assign('attachments', $attachments);

            $attachmentCmp = new BASE_CLASS_FileAttachment('forum', $uid);
            $this->addComponent('attachmentsCmp', $attachmentCmp);
        }

        if ( OW::getRequest()->isPost() && $editPostForm->isValid($_POST) )
        {
            $values = $editPostForm->getValues();

            // update the post
            $forumService->editPost($userId, $values, $postDto);

            $this->redirect($forumService->getPostUrl($topicId, $postId, true));
        }

        $topicInfo = $forumService->getTopicInfo($topicId);
        $groupUrl = $router->urlForRoute('group-default', array('groupId' => $topicDto->groupId));
        $topicUrl = $forumService->getPostUrl($topicId, $postId);

        $lang->addKeyForJs('forum', 'confirm_delete_attachment');

        OW::getDocument()->setHeading(OW::getLanguage()->text('forum', 'edit_post_title'));
        OW::getDocument()->setHeadingIconClass('ow_ic_edit');

        $this->assign('isHidden', $isHidden);
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$topicDto->id,'isPermanent'=>true,'activityType'=>'delete_attachment')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $attachmentDeleteCode = $frmSecuritymanagerEvent->getData()['code'];
            $this->assign('attachmentDeleteCode',$attachmentDeleteCode);
        }
        if ( $isHidden )
        {
            $event = new OW_Event('forum.find_forum_caption', array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId));
            OW::getEventManager()->trigger($event);

            $eventData = $event->getData();

            /** @var OW_Component $componentForumCaption */
            $componentForumCaption = $eventData['component'];

            if (!empty($componentForumCaption))
            {
                $this->assign('componentForumCaption', $componentForumCaption->render());
            }
            else
            {
                $componentForumCaption = false;
                $this->assign('componentForumCaption', $componentForumCaption);
            }

            $bcItems = array(
                array(
                    'href' => OW::getRouter()->urlForRoute('topic-default', array('topicId' => $topicId)),
                    'label' => OW::getLanguage()->text('forum', 'back_to_topic')
                )
            );

            $breadCrumbCmp = new BASE_CMP_Breadcrumb($bcItems);
            $this->addComponent('breadcrumb', $breadCrumbCmp);

            OW::getNavigation()->deactivateMenuItems(OW_Navigation::MAIN);
            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, $forumSection->entity, $eventData['key']);
        }
        else
        {
            $bcItems = array(
                array(
                    'href' => $router->urlForRoute('forum-default'),
                    'label' => $lang->text('forum', 'forum_group')
                ),
                array(
                    'href' => $router->urlForRoute('forum-default') . '#section-' . $topicInfo['sectionId'],
                    'label' => $topicInfo['sectionName']
                ),
                array(
                    'href' => $groupUrl,
                    'label' => $topicInfo['groupName']
                ),
                array(
                    'href' => $topicUrl,
                    'label' => htmlspecialchars($topicDto->title)
                )
            );

            $breadCrumbCmp = new BASE_CMP_Breadcrumb($bcItems, $lang->text('forum', 'post_location'));
            $this->addComponent('breadcrumb', $breadCrumbCmp);

            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'forum', 'forum');
        }
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('forum')->getStaticJsUrl() .'forum.js');
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("forum")->getStaticCssUrl() .'forum.css');

    }

    /**
     * Generates edit post form.
     *
     * @param $postDto
     * @param $uid
     * @return Form
     */
    private function generateEditPostForm( $postDto, $uid )
    {
        $form = new Form('edit-post-form');
        $form->setEnctype('multipart/form-data');
        
        $lang = OW::getLanguage();

        $postIdField = new HiddenField('post-id');
        $postIdField->setValue($postDto->id);
        $form->addElement($postIdField);

        $attachmentUid = new HiddenField('attachmentUid');
        $attachmentUid->setValue($uid);
        $attachmentUid->setRequired(true);
        $form->addElement($attachmentUid);

        $topicIdField = new HiddenField('topic');
        $topicIdField->setValue($postDto->topicId);
        $form->addElement($topicIdField);

        $btnSet = array(BOL_TextFormatService::WS_BTN_IMAGE, BOL_TextFormatService::WS_BTN_VIDEO, BOL_TextFormatService::WS_BTN_HTML);
        $postText = new WysiwygTextarea('text','forum', $btnSet);
        $postText->setRequired(true);
        $postText->setValue($postDto->text);
        $sValidator = new StringValidator(1, 50000);
        $sValidator->setErrorMessage($lang->text('forum', 'chars_limit_exceeded', array('limit' => 50000)));
        $postText->addValidator($sValidator);
        $form->addElement($postText);

        $submit = new Submit('save');
        $submit->setValue($lang->text('base', 'edit_button'));
        $form->addElement($submit);

        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FORUM_POST_FORM_CREATE, array('postId' => $postDto->id, 'form'=>$form)));

        return $form;
    }
}