<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class COVERPHOTO_CLASS_Form extends Form
{
    public function __construct( $name )
    {
        parent::__construct($name);

        $language = OW::getLanguage();


        //Simple text field
        $textField = new TextField("title");
        $textField->setLabel($language->text("coverphoto", "forms_title_field_label"));
        $textField->setDescription($language->text("coverphoto", "forms_title_field_description"));
        $textField->setHasInvitation(true);
        $textField->setRequired();

        $this->addElement($textField);

        //File upload field
        $uploadField = new FileField("image");
        $uploadField->setLabel($language->text("coverphoto", "upload_image"));
        $this->addElement($uploadField);


        //Submit field
        $submit = new Submit("submit");
        $submit->setLabel($language->text("coverphoto", "forms_submit_field_label"));

        $this->addElement($submit);
    }
}