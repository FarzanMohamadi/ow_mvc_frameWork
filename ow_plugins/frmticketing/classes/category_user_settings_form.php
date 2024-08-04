<?php
class FRMTICKETING_CLASS_CategoryUserSettingsForm extends Form
{
    /**
     * Class constructor
     *
     * @param string $name
     */
    public function __construct( $name)
    {

        parent::__construct($name);
        $language = OW::getLanguage();

        $categories = FRMTICKETING_BOL_TicketCategoryService::getInstance()->getTicketCategoryListByStatus('active');
        $categoryField = new Selectbox('category');
        $option = array();
        $option[null] = $language->text('frmticketing','select_category');
        foreach ($categories as $category) {
            $option[$category->id] = $category->title;
        }
        $categoryField->setHasInvitation(false);
        $categoryField->setOptions($option);
        $categoryField->addAttribute('id','category');
        $categoryField->setRequired();
        $validator = new FRMTICKETING_CLASS_CategorySelectValidator();
        $validator->setErrorMessage($language->text('frmticketing', 'category_selected_is_invalid'));
        $categoryField->addValidator($validator);
        $this->addElement($categoryField);

        $fieldTitle = new TextField('username');
        $fieldTitle->setRequired();
        $fieldTitle->setInvitation($language->text('frmticketing', 'username'));
        $fieldTitle->setHasInvitation(true);
        $validator = new FRMTICKETING_CLASS_OrderTitleValidator();
        $validator->setErrorMessage($language->text('frmticketing', 'title_error_already_exist'));
        $fieldTitle->addValidator($validator);
        $this->addElement($fieldTitle);


        $submit = new Submit('add');
        $submit->setValue($language->text('frmticketing', 'form_add_category_user_submit'));
        $this->addElement($submit);
    }
}