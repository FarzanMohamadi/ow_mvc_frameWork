<?php
class FRMTICKETING_CLASS_CategorySettingsForm extends Form
{
    /**
     * Class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {

        parent::__construct($name);
        $language = OW::getLanguage();

        $fieldTitle = new TextField('title');
        $fieldTitle->setRequired();
        $fieldTitle->setInvitation($language->text('frmticketing', 'category_title_label'));
        $fieldTitle->setHasInvitation(true);
        $validator = new FRMTICKETING_CLASS_CategoryTitleValidator();
        $validator->setErrorMessage($language->text('frmticketing', 'title_error_already_exist'));
        $fieldTitle->addValidator($validator);
        $this->addElement($fieldTitle);

        $submit = new Submit('add');
        $submit->setValue($language->text('frmticketing', 'form_add_category_submit'));
        $this->addElement($submit);
    }
}