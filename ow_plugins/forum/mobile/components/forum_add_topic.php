<?php
/**
 * Forum add topic class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.components
 * @since 1.0
 */
class FORUM_MCMP_ForumAddTopic extends OW_MobileComponent
{
    /**
     * Class constructor
     * 
     * @param array $params
     *      integer groupId
     */
    public function __construct(array $params = array())
    {
        parent::__construct();

        $groupId = !empty($params['groupId']) 
            ? $params['groupId'] 
            : 0;

        $forumService = FORUM_BOL_ForumService::getInstance();
        $userId = OW::getUser()->getId();
        $attachmentUid = FRMSecurityProvider::generateUniqueId();
        $groupList = $forumService->getGroupSelectList(0, false, $userId);

        // get a form instance
        $form = new FORUM_CLASS_TopicAddForm(
            'topic_add_form', 
            $attachmentUid, 
            $groupList, 
            $groupId, 
            true
        );

        $form->setTitleInvitation(OW::getLanguage()->text('forum', 'new_topic_subject'));
        $form->setAction(OW::getRouter()->urlForRoute('add-topic', array(
            'groupId' => $groupId
        )));

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

        // include js files
        OW::getDocument()->addScript(OW::
                getPluginManager()->getPlugin('forum')->getStaticJsUrl() . 'mobile_attachment.js');
    }
}