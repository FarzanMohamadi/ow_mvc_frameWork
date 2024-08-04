<?php
/**
 * Created by PhpStorm.
 * User: atenagh
 * Date: 1/21/2019
 * Time: 2:40 AM
 */
class FRMEMOJI_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function dept()
    {
        $this->setPageTitle(OW::getLanguage()->text('frmemoji', 'admin_dept_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmemoji', 'admin_dept_title'));
        $form = new Form('select_emoji_image');
        $this->addForm($form);
        $Type = new Selectbox('emoji_image');
        $Type->addOption('emojione','emojione');
        $Type->addOption('apple','apple');
        $Type->setRequired(true);
        $form->addElement($Type);

        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('frmemoji', 'form_add_dept_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $emojiType = $_POST['emoji_image'];
                OW::getConfig()->saveConfig('frmemoji', 'emojiType', $emojiType);
                OW::getFeedback()->info(OW::getLanguage()->text('frmemoji', 'save_successful_message'));
            }
        }
    }
}