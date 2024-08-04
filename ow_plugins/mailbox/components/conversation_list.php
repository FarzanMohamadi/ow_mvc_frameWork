<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.components
 * @since 1.6.1
 * */
class MAILBOX_CMP_ConversationList extends OW_Component
{
    public function __construct($params = array())
    {
        parent::__construct();

        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('mailbox')->getStaticJsUrl().'conversation_list.js', 'text/javascript', 3008 );

        $defaultAvatarUrl = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        $this->assign('defaultAvatarUrl', $defaultAvatarUrl);

        $js = "var conversationListModel = new MAILBOX_ConversationListModel;
";

        if (!empty($params['conversationId']))
        {
            $js .= "conversationListModel.set('activeConvId', {$params['conversationId']});";
            $js .= "conversationListModel.set('pageConvId', {$params['conversationId']});";
        }

        $js .= "OW.Mailbox.conversationListController = new MAILBOX_ConversationListView({model: conversationListModel});";

        OW::getDocument()->addOnloadScript($js, 3009);

        $conversationSearchForm = new Form('conversationSearchForm');

        $search = new MAILBOX_CLASS_SearchField('contacts_search');
        $search->setHasInvitation(true);
        $search->setInvitation( OW::getLanguage()->text('mailbox', 'label_invitation_contacts_search') );
        OW::getLanguage()->addKeyForJs('mailbox', 'label_invitation_contacts_search');
        $conversationSearchForm->addElement($search);

        $search = new MAILBOX_CLASS_SearchField('conversation_search');
        $search->setHasInvitation(true);
        $search->setInvitation( OW::getLanguage()->text('mailbox', 'label_invitation_conversation_content_search') );
        OW::getLanguage()->addKeyForJs('mailbox', 'label_invitation_conversation_content_search');
        $conversationSearchForm->addElement($search);

        $this->addForm($conversationSearchForm);

        $modeList = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();
        $singleMode = count($modeList) == 1;
        $this->assign('singleMode', $singleMode);
        $this->assign('searchFriends',FRMSecurityProvider::checkPluginActive('friends', true));
    }
}