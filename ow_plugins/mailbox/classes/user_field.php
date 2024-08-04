<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.classes
 * @since 1.6.1
 * */
class MAILBOX_CLASS_UserField extends InvitationFormElement
{
    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $this->addAttribute('type', 'hidden');
        $this->addAttribute('class', 'userFieldHidden');
        $this->addAttribute('placeholder', OW::getLanguage()->text('mailbox', 'to'));

        $input = new UserFieldRenderable();

        $input->assign('input', UTIL_HtmlTag::generateTag('input', $this->attributes));

        return $input->render();
    }

    public function getElementJs()
    {
        $jsString = "var formElement = new MailboxUserField(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ", " . json_encode( $this->invitation ) . ");";

        return $jsString.$this->generateValidatorAndFilterJsCode("formElement");
    }
}

class UserFieldRenderable extends OW_Component
{
    public function __construct()
    {
        $this->setTemplate(OW::getPluginManager()->getPlugin('mailbox')->getCmpViewDir().'user_field.html');

        $defaultAvatarUrl = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        $this->assign('defaultAvatarUrl', $defaultAvatarUrl);
    }
}