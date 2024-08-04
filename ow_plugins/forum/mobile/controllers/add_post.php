<?php
/**
 * Forum add post controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.controllers
 * @since 1.0
 */
class FORUM_MCTRL_AddPost extends FORUM_MCTRL_AbstractForum
{
    /**
     * @param array|null $params
     * @throws AuthenticateException
     * @throws AuthorizationException
     * @throws Redirect404Exception
     */
    public function index( array $params = null )
    {
        if ( !isset($params['topicId']) || !($topicId = (int) $params['topicId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        // check permissions
        if ( !OW::getUser()->isAuthorized('forum') && !OW::getUser()->isAuthorized('forum', 'edit') && !OW::getUser()->isAdmin())
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'edit');
            throw new AuthorizationException($status['msg']);
        }

        // get topic info
        $topicDto = $this->forumService->findTopicById($topicId);

        if ( !$topicDto )
        {
            throw new Redirect404Exception();
        }
        
        // get a form instance
        $form = new FORUM_CLASS_PostForm(
            'post_form',
            FRMSecurityProvider::generateUniqueId(),
            $topicDto->id, 
            true
        );

        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FORUM_POST_FORM_CREATE, array('form' => $form)));
        // validate the form
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            if ( $data['topic'] && $data['topic'] == $topicDto->id && !$topicDto->locked )
            {
                $quoteId = !empty($_POST['quoteId']) ? (int) $_POST['quoteId'] : null;

                // add a quote to the text
                if ( $quoteId )
                {
                    $postQuote = new FORUM_CMP_ForumPostQuote(array(
                        'quoteId' => $quoteId
                    ));

                    $data['text'] = $postQuote->render() . $data['text'];
                }

                $postDto = $this->forumService->addPost($topicDto, $data);
                $this->redirect($this->forumService->getPostUrl($topicDto->id, $postDto->id));
            }
        }
        else
        {
        OW::getFeedback()->
                error(OW::getLanguage()->text('base', 'form_validate_common_error_message'));

        $this->redirect(OW::getRouter()->
                        urlForRoute('topic-default', array('topicId' => $topicDto->id)));
        }
    }
}
