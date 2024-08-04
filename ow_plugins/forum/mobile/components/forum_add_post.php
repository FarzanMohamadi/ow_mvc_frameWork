<?php
/**
 * Forum add post class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.components
 * @since 1.0
 */
class FORUM_MCMP_ForumAddPost extends OW_MobileComponent
{
    /**
     * Class constructor
     * 
     * @param array $params
     *      integer topicId
     *      integer postId optional
     */
    public function __construct(array $params = array())
    {
        parent::__construct();

        $topicId = !empty($params['topicId']) 
            ? $params['topicId'] 
            : null;

        $postId = !empty($params['postId']) 
            ? $params['postId'] 
            : null;

        $attachmentUid = FRMSecurityProvider::generateUniqueId();

        // get a form instance
        $form = new FORUM_CLASS_PostForm(
            'post_form', 
            $attachmentUid, 
            $topicId, 
            true
        );

        $form->setAction(OW::getRouter()->urlForRoute('add-post', array(
            'topicId' => $topicId
        )));
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FORUM_POST_FORM_CREATE, array('form' => $form)));

        $this->addForm($form);

        // attachments
        $enableAttachments = OW::getConfig()->getValue('forum', 'enable_attachments');

        if ( $enableAttachments )
        {
            $attachmentCmp = new BASE_CLASS_FileAttachment('forum', $attachmentUid);
            $this->addComponent('attachments', $attachmentCmp);
        }

        // assign view variables
        $this->assign('enableAttachments', $enableAttachments);
        $this->assign('attachmentUid', $attachmentUid);

        // add a quote text
        if ( $postId )
        {
            $postQuote = new FORUM_CMP_ForumPostQuote(array(
                'quoteId' => $postId
            ));

            $this->assign('quoteText', $postQuote->render());
            $this->assign('quoteId', $postId);
        }

        // include js files
        OW::getDocument()->addScript(OW::
                getPluginManager()->getPlugin('forum')->getStaticJsUrl() . 'mobile_attachment.js');
    }
}