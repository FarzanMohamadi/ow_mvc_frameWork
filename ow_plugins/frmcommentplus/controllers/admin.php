<?php
class FRMCOMMENTPLUS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index() {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmcommentplus', 'admin_settings_heading'));
        $this->setPageTitle(OW::getLanguage()->text('frmcommentplus', 'admin_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmcommentplus', 'admin_heading'));

        $frmcommentplusAdminForm = new Form('frmcommentplusAdminForm');
        $config = OW::getConfig();

        $enableReplyPostComment = new CheckboxField('enableReplyPostComment');
        $enableReplyPostComment->setLabel(OW::getLanguage()->text('frmcommentplus', 'enable_reply_post_comment_settings_label'));
        $enableReplyPostComment->setValue($config->getValue('frmcommentplus', 'enableReplyPostComment'));
        $frmcommentplusAdminForm->addElement($enableReplyPostComment);

        $frmcommentplusAdminFormSubmit = new Submit('frmcommentplusAdminFormSubmit');
        $frmcommentplusAdminFormSubmit->setValue(OW::getLanguage()->text('frmcommentplus', 'frmcommentplus_admin_form_submit'));
        $frmcommentplusAdminForm->addElement($frmcommentplusAdminFormSubmit);

        $this->addForm($frmcommentplusAdminForm);

        if (OW::getRequest()->isPost()) {
            if ($frmcommentplusAdminForm->isValid($_POST)) {

                    $data = $frmcommentplusAdminForm->getValues();

                    if(!isset($data["enableReplyPostComment"])) {
                        $config->saveConfig('frmcommentplus', 'enableReplyPostComment', 0);
                    } else {
                        $config->saveConfig('frmcommentplus', 'enableReplyPostComment', 1);
                    }

                    OW::getFeedback()->info(OW::getLanguage()->text('frmcommentplus', 'frmcommentplus_submit_successful_message'));
                    $this->redirect();
            }
        }
    }
}