<?php
class FRMREPORT_MCTRL_Report extends OW_MobileActionController {
    public function index($params){
        $service = FRMREPORT_BOL_Service::getInstance();
        $groupId = (int) $params['groupId'];
        if(!$service->canViewReportsOfGroup($groupId)){
            throw new Redirect404Exception();
        }
        $this->setPageHeading($this->text('frmreport','index_report_page_heading'));
        $this->setPageTitle($this->text('frmreport','index_report_page_title'));

        $groupReports = array();

        $form = new Form('list_report');
        $this->addForm($form);

        $fieldSemester = new Selectbox('semester');
        $fieldSemester->addOption(-1,$this->text('frmreport','all_semester'));
        $fieldSemester->addOption(1,$this->text('frmreport','first_semester'));
        $fieldSemester->addOption(2,$this->text('frmreport','second_semester'));
        $fieldSemester->setHasInvitation(false);
        $fieldSemester->setLabel($this->text('frmreport','form_label_semester'));
        $form->addElement($fieldSemester);

        $fieldYear = new TextField('year');
        $fieldYear->setLabel($this->text('frmreport','form_label_year'));
        $validator = new IntValidator(1000,9999);
        $validator->setErrorMessage($this->text('frmreport','year_number_of_digit_error') );
        $fieldYear->addValidator($validator);
        $form->addElement($fieldYear);

        $submit = new Submit('search');
        $submit->setValue($this->text('frmreport', 'form_label_search_report'));
        $form->addElement($submit);

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 10;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        if(OW::getRequest()->isPost()){
            if($form->isValid($_POST)){
                $data = $form->getValues();
                $year = $data['year'];
                $semester = $data['semester'];

                $queryParams = array();
                if($semester == 1 || $semester==2){
                    $queryParams['semester']= $semester;
                }
                if(!empty($year)){
                    $queryParams['year']= $year;
                }
                $url=OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('report_index',array('groupId'=>$groupId)),$queryParams);
                $this->redirect($url);

            }
        }else{

            if(!empty($_GET['year'])){
                $year= $_GET['year'];
            }else{
                $year = -1;
            }
            if(!empty($_GET['semester'])){
                $semester= $_GET['semester'];
            }else{
                $semester = -1;
            }
            if($year == -1 && $semester ==-1){
                $groupReports = $service->getReports($groupId,$first,$count);
                $listCount=$service->getNumberOfReports($groupId);
            }else{
                $groupReports = $service->getSemesterReports($groupId,$year,$semester,$first,$count);
                $listCount = $service->getNumberOfSemesterReport($groupId,$year,$semester);
            }
        }
        $paging = new BASE_CMP_PagingMobile($page,ceil($listCount / $perPage),5);
        $this->addComponent('paging', $paging);

        $detailUrls = array();
        foreach ($groupReports as $report){
            $detailUrls[$report['id']] = OW::getRouter()->urlForRoute('report_detail',array('reportId' => $report['id']));
        }

        $backToGroup = OW::getRouter()->urlForRoute('groups-view',array('groupId'=> $groupId));
        $js = UTIL_JsGenerator::newInstance();
        $js->newFunction('window.location.href=url', array('url'), 'redirect');
        $js->jQueryEvent('#back-to-group_btn', 'click', UTIL_JsGenerator::composeJsString(
            'redirect({$url});', array('url' => $backToGroup)));
        OW::getDocument()->addOnloadScript($js);

        $this->assign('reports',$groupReports);
        $this->assign('detailUrls',$detailUrls);
        $this->addComponent('groupBriefInfo', new GROUPS_CMP_BriefInfo($groupId));

    }
    public function add($params){
        $groupId = (int) $params['groupId'];
        $service = FRMREPORT_BOL_Service::getInstance();
        if(!$service->canCreateReport($groupId)){
            throw new Redirect404Exception();
        }
        $this->setPageHeading($this->text('frmreport','add_report_page_heading'));
        $this->setPageTitle($this->text('frmreport','add_report_page_title'));



        $activityTypes = array();

        $activityTypeList = $service->getActivityTypeList();

        $form = new Form('add_report_form');

        $fieldYear = new TextField('year');
        $fieldYear->setLabel($this->text('frmreport', 'form_label_year'));
        $fieldYear->setRequired();
        $validator = new IntValidator(1000,9999);
        $validator->setErrorMessage($this->text('frmreport','year_number_of_digit_error') );
        $fieldYear->addValidator($validator);
        $form->addElement($fieldYear);

        $fieldSemester = new Selectbox('semester');
        $fieldSemester->addOption('1',$this->text('frmreport','first_semester'));
        $fieldSemester->addOption('2',$this->text('frmreport','second_semester'));
        $fieldSemester->setRequired(true);
        $fieldSemester->setHasInvitation(false);
        $fieldSemester->setLabel($this->text('frmreport','form_label_semester'));
        $form->addElement($fieldSemester);



        foreach ($activityTypeList as $activityType){
            $id = $activityType->id;
            $countFieldName ='count' . $id;
            $descriptionFiledName = 'description'.$id;

            $activityTypes[$id]['activityTypeId'] = $id;
            $activityTypes[$id]['title'] = $activityType->title;
            $activityTypes[$id]['count'] = $countFieldName;
            $activityTypes[$id]['description'] = $descriptionFiledName;

            $fieldCount = new TextField($countFieldName);
            $fieldCount->setLabel($this->text('frmreport', 'form_label_count'));
            $form->addElement($fieldCount);

            $fieldDescription = new Textarea($descriptionFiledName);
            $fieldDescription->setLabel($this->text('frmreport', 'form_label_description'));
            $form->addElement($fieldDescription);
        }


        $submit = new Submit('send');
        $submit->setValue($this->text('frmreport', 'form_label_submit_report'));
        $form->addElement($submit);


        $this->addForm($form);

        if(OW::getRequest()->isPost()){
            if($form->isValid($_POST)){
                $data = $form->getValues();
                $year = $data['year'];
                $semester = $data['semester'];
                if($service->hasConflictWithOtherReports($groupId,-1,$year,$semester)){
                    OW::getFeedback()->error($this->text('frmreport','reports_confliction_error'));
                }else{
                    $reportId=$service->createReport($groupId,$year,$semester);
                    if(isset($reportId)){
                        foreach($activityTypes as $activityItem){
                            $countFieldName= $activityItem['count'];
                            $descriptionFiledName = $activityItem['description'];
                            $activityTypeId = $activityItem['activityTypeId'];

                            if($data[$countFieldName]!="" || $data[$descriptionFiledName]!=""){
                                $count = $data[$countFieldName];
                                $description = $data[$descriptionFiledName];
                                $service->addReportDetail($reportId,$activityTypeId,$count,$description);
                            }
                        }
                        OW::getFeedback()->info($this->text('frmreport','report_added_successfully'));
                        $this->redirect(OW::getRouter()->urlForRoute('report_index',array('groupId' => $groupId)));
                    }else{
                        OW::getFeedback()->error($this->text('frmreport','add_report_error'));
                    }
                    $this->redirect();
                }
            }
        }

        $backToGroup = OW::getRouter()->urlForRoute('groups-view',array('groupId'=> $groupId));
        $js = UTIL_JsGenerator::newInstance();
        $js->newFunction('window.location.href=url', array('url'), 'redirect');
        $js->jQueryEvent('#back-to-group_btn', 'click', UTIL_JsGenerator::composeJsString(
            'redirect({$url});', array('url' => $backToGroup)));
        OW::getDocument()->addOnloadScript($js);

        $this->assign('activityTypes',$activityTypes);
    }
    public function detail($params){
        $reportId = (int) $params['reportId'];
        $service = FRMREPORT_BOL_Service::getInstance();
        if(!$service->canViewReport($reportId)){
            throw new Redirect404Exception();
        }
        $activityTypes = array();

        $activityTypeList = $service->getActivityTypeList();

        $form = new Form('report_detail_form');

        foreach ($activityTypeList as $activityType){
            $id = $activityType->id;

            $activityTypes[$id]['activityTypeId'] = $id;
            $activityTypes[$id]['title'] = $activityType->title;
            $hasItem= $service->hasReportItem($reportId,$id);
            if($hasItem){
                $reportItem = $service->getReportItem($reportId,$id);
                $activityTypes[$id]['count'] = $reportItem->count;
                $activityTypes[$id]['description'] = $reportItem->description;
            }else{
                $activityTypes[$id]['count'] = '';
                $activityTypes[$id]['description'] = '';

            }
        }
        $submit = new Submit('edit');
        $submit->setValue($this->text('frmreport', 'form_label_edit_report'));
        $form->addElement($submit);
        $this->addForm($form);

        if(OW::getRequest()->isPost()){
            if($form->isValid($_POST)) {
                $this->redirect(OW::getRouter()->urlForRoute('report_edit',array('reportId' => $reportId)));
            }
        }
        $this->assign('activityTypes',$activityTypes);

        $report = $service->findReport($reportId);
        $groupId = $report->groupId;
        $groupTitle = GROUPS_BOL_Service::getInstance()->findGroupById($groupId)->title;
        $groupUrl = OW::getRouter()->urlForRoute('groups-view',array('groupId'=> $groupId));
        $backUrl = OW::getRouter()->urlForRoute('report_index',array('groupId' => $groupId));
        $js = UTIL_JsGenerator::newInstance();
        $js->newFunction('window.location.href=url', array('url'), 'redirect');
        $js->jQueryEvent('#report-back_btn', 'click', UTIL_JsGenerator::composeJsString(
            'redirect({$url});', array('url' => $backUrl)));
        OW::getDocument()->addOnloadScript($js);

        $this->assign('report',$report);
        $this->assign('groupTitle',$groupTitle);
        $this->assign('groupUrl',$groupUrl);
    }
    public function edit($params){
        $reportId = (int) $params['reportId'];
        $service = FRMREPORT_BOL_Service::getInstance();
        if(!$service->canEditReport($reportId)){
            throw new Redirect404Exception();
        }
        $report = $service->findReport($reportId);
        $reportItems = array();

        $activityTypeList = $service->getActivityTypeList();

        $form = new Form('report_edit_form');

        $fieldYear = new TextField('year');
        $fieldYear->setLabel($this->text('frmreport', 'form_label_year'));
        $fieldYear->setRequired();
        $validator = new IntValidator(1000,9999);
        $validator->setErrorMessage($this->text('frmreport','year_number_of_digit_error') );
        $fieldYear->addValidator($validator);
        $fieldYear->setValue($report->year);
        $form->addElement($fieldYear);

        $fieldSemester = new Selectbox('semester');
        $fieldSemester->addOption('1',$this->text('frmreport','first_semester'));
        $fieldSemester->addOption('2',$this->text('frmreport','second_semester'));
        $fieldSemester->setRequired(true);
        $fieldSemester->setHasInvitation(false);
        $fieldSemester->setLabel($this->text('frmreport','form_label_semester'));
        $fieldSemester->setValue($report->semester);
        $form->addElement($fieldSemester);


        foreach ($activityTypeList as $activityType){
            $id = $activityType->id;
            $countFieldName ='count' . $id;
            $descriptionFiledName = 'description'.$id;

            $reportItems[$id]['reportDetailId'] = -1;
            $reportItems[$id]['activityTypeId'] = $id;
            $reportItems[$id]['title'] = $activityType->title;
            $reportItems[$id]['count'] = $countFieldName;
            $reportItems[$id]['description'] = $descriptionFiledName;

            $fieldCount = new TextField($countFieldName);
            $fieldCount->setLabel($this->text('frmreport', 'form_label_count'));
            $form->addElement($fieldCount);

            $fieldDescription = new Textarea($descriptionFiledName);
            $fieldDescription->setLabel($this->text('frmreport', 'form_label_description'));
            $form->addElement($fieldDescription);

            $hasItem= $service->hasReportItem($reportId,$id);
            if($hasItem){
                $reportItem = $service->getReportItem($reportId,$id);
                $reportItems[$id]['reportDetailId'] = $reportItem->id;
                $fieldCount->setValue($reportItem->count);
                $fieldDescription->setValue($reportItem->description);
            }
        }
        $submit = new Submit('send');
        $submit->setValue($this->text('frmreport', 'form_label_submit_report'));
        $form->addElement($submit);

        $this->addForm($form);

        if(OW::getRequest()->isPost()){
            if($form->isValid($_POST)) {
                $data = $form->getValues();
                $year = $data['year'];
                $semester = $data['semester'];
                $groupId= $report->groupId;
                if($service->hasConflictWithOtherReports($groupId,$reportId,$year,$semester)){
                    OW::getFeedback()->error($this->text('frmreport','reports_confliction_error'));
                }else{
                    $service->updateReport($reportId,$year,$semester);
                    foreach($reportItems as $reportItem){
                        $countFieldName= $reportItem['count'];
                        $descriptionFiledName = $reportItem['description'];
                        $activityTypeId = $reportItem['activityTypeId'];
                        $reportDetailId=$reportItem['reportDetailId'];

                        $count = $data[$countFieldName];
                        $description = $data[$descriptionFiledName];
                        if($count!="" || $description!=""){
                            if($reportDetailId!=-1){
                                //update ReportDetail
                                $service->updateReportDetail($reportDetailId,$count,$description);
                            }else{
                                //create ReportDetail
                                $service->addReportDetail($reportId,$activityTypeId,$count,$description);
                            }
                        }else{
                            if($reportDetailId!=-1){
                                $service->deleteReportDetail($reportDetailId);
                                //Delete ReportDetail
                            }
                        }
                    }
                    OW::getFeedback()->info($this->text('frmreport','report_edited_successfully'));
                    $this->redirect(OW::getRouter()->urlForRoute('report_detail',array('reportId' => $reportId)));
                }
            }
        }

        $this->assign('activityTypes',$reportItems);

        $backUrl = OW::getRouter()->urlForRoute('report_detail',array('reportId' => $reportId));
        $deleteUrl = OW::getRouter()->urlFor('FRMREPORT_MCTRL_Report', 'delete', array('reportId' => $reportId));

        $js = UTIL_JsGenerator::newInstance();
        $js->newFunction('window.location.href=url', array('url'), 'redirect');

        $js->jQueryEvent('#report-back_btn', 'click', UTIL_JsGenerator::composeJsString(
            'redirect({$url});', array('url' => $backUrl)));

        $lang = OW::getLanguage()->text('frmreport', 'delete_confirm_msg');
        $js->jQueryEvent('#report-delete_btn', 'click', UTIL_JsGenerator::composeJsString(
            'if( confirm({$lang}) ) redirect({$url});', array('url' => $deleteUrl, 'lang' => $lang)));

        OW::getDocument()->addOnloadScript($js);

        $groupId = $report->groupId;
        $groupTitle = GROUPS_BOL_Service::getInstance()->findGroupById($groupId)->title;
        $groupUrl = OW::getRouter()->urlForRoute('groups-view',array('groupId'=> $groupId));

        $this->assign('report',$report);
        $this->assign('groupTitle',$groupTitle);
        $this->assign('groupUrl',$groupUrl);
    }
    public function delete($params){
        $service = FRMREPORT_BOL_Service::getInstance();
        $reportId = (int) $params['reportId'];
        if(!$service->canEditReport($reportId)){
            throw new AuthenticateException();
        }
        $groupId = $service->findGroupOfReport($reportId);
        $service->deleteReport($reportId);
        OW::getFeedback()->info($this->text('frmreport','report_deleted_successfully'));
        $this->redirect(OW::getRouter()->urlForRoute('report_index',array('groupId' => $groupId)));
    }
    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }
}