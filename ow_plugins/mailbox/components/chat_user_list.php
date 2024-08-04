<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.mailbox.components
 * @since 1.6.1
 */
class MAILBOX_CMP_ChatUserList extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        if ( !OW::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }
    }

    public function render()
    {
        $userId = OW::getUser()->getId();

        $userSettingsForm = MAILBOX_BOL_ConversationService::getInstance()->getUserSettingsForm();
        $this->addForm($userSettingsForm);
        $userSettingsForm->getElement('user_id')->setValue($userId);

        $friendsEnabled = (bool)OW::getEventManager()->call('plugin.friends');
        $this->assign('friendsEnabled', $friendsEnabled);

        $showAllMembersModeEnabled = (bool)OW::getConfig()->getValue('mailbox', 'show_all_members');
        $this->assign('showAllMembersModeEnabled', $showAllMembersModeEnabled);

        $this->assign('viewAllUrl', OW::getRouter()->urlForRoute('mailbox_messages_default'));

        return parent::render();
    }
}
