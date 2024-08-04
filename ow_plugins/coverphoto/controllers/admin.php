<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class COVERPHOTO_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $this->setPageTitle(OW::getLanguage()->text('coverphoto', 'admin_title'));
        $this->setPageHeading(OW::getLanguage()->text('coverphoto', 'admin_title'));

        $userURL = COVERPHOTO_BOL_Service::getInstance()->getCoverURL('user', 0);
        $this->assign('userURL', $userURL);
        $groupsURL = COVERPHOTO_BOL_Service::getInstance()->getCoverURL('groups', 0);
        $this->assign('groupsURL', $groupsURL);

        // form settings
        $form = new Form('settings');
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $el1 = new FileField('user');
        $el1->setLabel(OW::getLanguage()->text('coverphoto', 'default_user'));
        $form->addElement($el1);

        $el1 = new FileField('groups');
        $el1->setLabel(OW::getLanguage()->text('coverphoto', 'default_groups'));
        $form->addElement($el1);

        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('base', 'form_element_submit_default_value'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $resp = ['result'=>true, 'message'=>OW::getLanguage()->text('admin', 'updated_msg')];
                if(empty($_FILES['user']) || $_FILES['user']['size']==0){
                    OW::getConfig()->deleteConfig('coverphoto', 'user_default_cover');
                }else {
                    $resp = COVERPHOTO_BOL_Service::getInstance()->uploadNewDefaultCover('user', 'user');
                    print_r($resp);
                }
                if(empty($_FILES['groups']) || $_FILES['groups']['size']==0){
                    OW::getConfig()->deleteConfig('coverphoto', 'groups_default_cover');
                }else {
                    $resp = COVERPHOTO_BOL_Service::getInstance()->uploadNewDefaultCover('groups', 'groups');
                    print_r($resp);
                }
                if($resp['result']) {
                    OW::getFeedback()->info($resp['message']);
                }else{
                    OW::getFeedback()->error($resp['message']);
                }
            }
        }
    }
}