<?php
/**
 * Forum edit post controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.controllers
 * @since 1.0
 */
class FORUM_MCTRL_EditPost extends FORUM_MCTRL_AbstractForum
{
    /**
     * Controller's default action
     *
     * @param array $params
     * @throws AuthorizationException|Redirect404Exception|AuthenticateException
     */
    public function index( array $params = null )
    {
        if ( !isset($params['id']) || !($postId = (int) $params['id']) )
        {
            throw new Redirect404Exception();
        }

        $forumService = FORUM_BOL_ForumService::getInstance();
        $postDto = $forumService->findPostById($postId);

        if ( !$postDto )
        {
            throw new Redirect404Exception();
        }

        $userId = OW::getUser()->getId();
        $topicId = $postDto->topicId;
        $topicDto = $forumService->findTopicById($topicId);

        $forumGroup = $forumService->getGroupInfo($topicDto->groupId);
        $forumSection = $forumService->findSectionById($forumGroup->sectionId);
        //commented codes prevented users to edit forum's topic of a group in mobile version
/*        if ( $forumSection->isHidden )
        {
            throw new Redirect404Exception();
        }*/

        $isModerator = OW::getUser()->isAuthorized('forum');
        $isHidden = $forumSection->isHidden;
        if($isHidden) {
            if(FRMSecurityProvider::checkPluginActive('groups', true)) {
                $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($forumGroup->entityId);
                if (GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto)) {
                    $isModerator = true;
                }
            }
        }
        $canEdit = $postDto->userId == $userId;

        if ( !$canEdit && !$isModerator )
        {
            throw new AuthorizationException();
        }

        $attachmentUid = FRMSecurityProvider::generateUniqueId();

        // get a form instance
        $form = new FORUM_CLASS_PostForm(
            'post_form', 
            $attachmentUid, 
            $topicId, 
            true
        );

        // validate the form
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            // update the post
            $this->forumService->editPost($userId, $data, $postDto);
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FORUM_POST_FORM_CREATE, array('form' => $form, 'postId' => $postDto->id)));
            $this->redirect(OW::getRouter()->
                    urlForRoute('topic-default', array('topicId' => $topicId)));
        }

        OW::getFeedback()->
                error(OW::getLanguage()->text('base', 'form_validate_common_error_message'));

        // an error occured
        $this->redirect(OW::getRouter()->
                        urlForRoute('topic-default', array('topicId' => $topicId)));
    }
}
