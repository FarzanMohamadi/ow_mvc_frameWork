<?php
class FRMREPORT_CTRL_Admin extends ADMIN_CTRL_Abstract{
    public function index(){
        $service =  FRMREPORT_BOL_Service::getInstance();
        if(!$service->hasActivityTypePermission()){
            throw new Redirect404Exception();
        }
        $this->setPageHeading($this->text('frmreport','add_activity_type_page_heading'));
        $this->setPageTitle($this->text('frmreport','add_activity_type_page_title'));
        $activities = array();
        $deleteUrls = array();
        $editUrls = [];

        //Paging
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 10;
        $first = ($page - 1) * $perPage;
        $count = $perPage;
        $listCount = $service->getActivityListCount();
        $activityTypes =$service->getActivityTypeList($first,$count);
        $paging = new BASE_CMP_Paging($page,ceil($listCount / $perPage),5);
        $this->addComponent('paging', $paging);
        //
        $pageTitle =OW::getLanguage()->text('frmreport', 'edit_activity_page_title');
        foreach ($activityTypes as $activity ){
            $activities[$activity->id]['id'] = $activity->id;
            $activities[$activity->id]['title'] = $activity->title;
            $deleteUrls[$activity->id] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $activity->id));
            $editUrls[$activity->id] =  "OW.ajaxFloatBox('FRMREPORT_CMP_EditActivityFloatBox', {id: ".$activity->id."} , {iconClass: 'ow_ic_edit', title: '".$pageTitle."'})";
        }
        $this->assign('activities',$activities);
        $this->assign('deleteUrls',$deleteUrls);
        $this->assign('editUrls',$editUrls);

        $form = new Form('add_activity_type');

        $fieldTitle  = new TextField('title');
        $fieldTitle->setRequired();
        $fieldTitle->setInvitation($this->text('frmreport','activity_title'));
        $fieldTitle->setHasInvitation(true);
        $form->addElement($fieldTitle);

        $submit = new Submit('add');
        $submit->setValue($this->text('frmreport','add_activity_type_submit'));
        $form->addElement($submit);
        $this->addForm($form);


        if(OW::getRequest()->isPost()){
            if($form->isValid($_POST)){
                $data = $form->getValues();
                FRMREPORT_BOL_Service::getInstance()->addActivityType($data['title']);
                $this->redirect();
            }
        }
    }
    public function delete( $params )
    {
        $service =  FRMREPORT_BOL_Service::getInstance();
        if(!$service->hasActivityTypePermission()){
            throw new Redirect404Exception();
        }
        if ( isset($params['id']) )
        {
            $service->deleteActivityType((int) $params['id']);
        }
        $this->redirect(OW::getRouter()->urlForRoute('frmreport.admin'));
    }

    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }
    public function edit(){
        $service =  FRMREPORT_BOL_Service::getInstance();
        if(!$service->hasActivityTypePermission()){
            throw new Redirect404Exception();
        }
        $form = FRMREPORT_BOL_Service::getInstance()->getActivityTypeEditForm($_POST['id']);
        if ( $form->isValid($_POST) ) {
            $id = $form->getElement('id')->getValue();
            $title = $form->getElement('title')->getValue();
            FRMREPORT_BOL_Service::getInstance()->updateActivityType($id,$title);
            OW::getFeedback()->info(OW::getLanguage()->text('frmreport', 'edit_activity_successfully'));
            $this->redirect(OW::getRouter()->urlForRoute('frmreport.admin'));
        }else{
            if($form->getErrors()['title'][0]!=null) {
                OW::getFeedback()->error($form->getErrors()['title'][0]);
            }
            $this->redirect(OW::getRouter()->urlForRoute('frmreport.admin'));
        }
    }
}