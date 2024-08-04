<?php
class FRMSHASTA_CTRL_Service extends OW_ActionController
{
    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function files($params)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }

        $categoryId = null;
        if (isset($_GET['categoryId'])) {
            $categoryId = $_GET['categoryId'];
        }

        $service = FRMSHASTA_BOL_Service::getInstance();

        $searchedCompaniesIds = array();
        $holding = null;
        if (isset($_GET['holding']) && !empty($_GET['holding'])) {
            $searchedCompaniesIds[] = $_GET['holding'];

            if (isset($_GET['sub_company']) && $_GET['sub_company'] == 'true') {
                $searchedCompaniesIds = $service->getSubsCompany($_GET['holding']);
            }
        }

        $fromDate = null;
        $toDate = null;

        $fromDateValue = $service->getDateFromJalali('fromYear', 'fromMonth');
        $toDateValue = $service->getDateFromJalali('toYear', 'toMonth', 31);
        if ($fromDateValue != null) {
            $fromDate = strtotime($fromDateValue);
        }
        if ($toDateValue != null) {
            $toDate = strtotime($toDateValue);
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $rpp = 20;
        $first = ($page - 1) * $rpp;
        $count = $rpp;

        $validCompanyIds = $service->getSubsUserCompany(OW::getUser()->getId());
        if ($validCompanyIds == null) {
            $validCompanyIds = array();
        }

        $allFilesCount = $service->getFilesCount($validCompanyIds, $searchedCompaniesIds, $categoryId, $fromDate, $toDate);

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($allFilesCount / $rpp), 5));

        $files = $service->getFiles($validCompanyIds, $searchedCompaniesIds, $categoryId, $fromDate, $toDate, $first, $count);

        $fileIds = array();
        foreach ($files as $file) {
            $fileIds[] = $file->id;
        }

        $service->addStaticFiles();
        $this->addComponent('files', new FRMSHASTA_CMP_Files(array('fileIds' => $fileIds)));


        $filterForm = $service->getFilterForm('files_filter', true);
        $this->addForm($filterForm);

        OW::getDocument()->addOnloadScript('handleFilterForm("' . OW::getRouter()->urlForRoute('frmshasta_files') .'");');
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function addFile($params)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }

        $id = null;
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        $service = FRMSHASTA_BOL_Service::getInstance();
        $form = $service->getFileForm($this, $id);
        if ($form != null) {
            $this->addForm($form);
        }

        $service->addStaticFiles();
        if (OW::getRequest()->isPost() &&  isset($_POST['form_name'])) {
            $this->redirect(OW_URL_HOME);
        }
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function addCompany($params)
    {
        $service = FRMSHASTA_BOL_Service::getInstance();

        if (!$service->hasUserAccessManager()) {
            throw new Redirect404Exception();
        }

        $id = null;
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        $form = $service->getCreateCompanyForm($id);
        if ($form != null) {
            $this->addForm($form);
        }

        $service->addStaticFiles();
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function deleteCompany($params)
    {
        $service = FRMSHASTA_BOL_Service::getInstance();

        if (!$service->hasUserAccessManager()) {
            throw new Redirect404Exception();
        }

        $id = null;
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        if ($id == null) {
            throw new Redirect404Exception();
        }

        $service->deleteCompany($id);
        $this->redirect(OW_URL_HOME);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function deleteFile($params)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }

        $id = null;
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        if ($id == null) {
            throw new Redirect404Exception();
        }

        $service = FRMSHASTA_BOL_Service::getInstance();
        $service->deleteFile($id);
        $this->redirect(OW_URL_HOME);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function addCategory($params)
    {
        $service = FRMSHASTA_BOL_Service::getInstance();

        if (!$service->hasUserAccessManager()) {
            throw new Redirect404Exception();
        }

        $id = null;
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        $this->addForm($service->getCategoryForm($this, $id));
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function deleteCategory($params)
    {
        $service = FRMSHASTA_BOL_Service::getInstance();

        if (!$service->hasUserAccessManager()) {
            throw new Redirect404Exception();
        }

        $id = null;
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        if ($id == null) {
            throw new Redirect404Exception();
        }

        $service->deleteCategory($id);
        $this->redirect(OW_URL_HOME);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function customizeSpecialCategories($params)
    {
        $service = FRMSHASTA_BOL_Service::getInstance();

        if (!$service->hasUserAccessManager()) {
            throw new Redirect404Exception();
        }

        $service->getCustomizeSpecialCategoryForm($this);
        exit(json_encode(array('result' => true)));
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function reports($params)
    {
        $service = FRMSHASTA_BOL_Service::getInstance();
        if (!$service->hasUserAccessManager()) {
            throw new Redirect404Exception();
        }

        $specialCategories = $service->getSpecialCategories();
        $categoriesInfo = array();
        $specialCategoriesIds = array();

        foreach ($specialCategories as $specialCategory) {
            $specialCategoriesIds[] = $specialCategory->categoryId;
        }

        $allCategories = $service->getCategoriesByIds($specialCategoriesIds);

        $searchedCompaniesIds = array();
        $holding = null;
        if (isset($_GET['holding']) && !empty($_GET['holding'])) {
            $searchedCompaniesIds[] = $_GET['holding'];

            if (isset($_GET['sub_company']) && $_GET['sub_company'] == 'true') {
                $searchedCompaniesIds = $service->getSubsCompany($_GET['holding']);
            }
        }

        foreach ($allCategories as $category) {
            $fromDate = null;
            $toDate = null;
            $fromMonthValue = 'fromMonth';
            $fromYearValue = 'fromYear';
            $toMonthValue = 'toMonth';
            $toYearValue = 'toYear';

            $category = $service->getCategory($category->id);

            // find from date filter
            if ($category->monthFilter != "1") {
                $fromMonthValue = '';
            }
            if ($category->yearFilter != "1") {
                $fromYearValue = '';
            }
            $fromDateValue = $service->getDateFromJalali($fromYearValue, $fromMonthValue);
            if ($fromDateValue != null) {
                $fromDate = strtotime($fromDateValue);
            }

            // find end date filter
            if (isset($_GET[$toYearValue])) {
                if ($category->monthFilter != "1") {
                    $toMonthValue = '';
                }
                if ($category->yearFilter != "1") {
                    $toYearValue = '';
                }
                $toDateValue = $service->getDateFromJalali($toYearValue, $toMonthValue);
                if ($toDateValue != null) {
                    $toDate = strtotime($toDateValue);
                }
            }

            $catInfo = array(
                'name' => $category->name,
                'id' => $category->id,
            );
            $sentFilesComponent = array();

            if ($category->concept == $service::FILE_BASED) {
                $companiesId = array();
                $sentFiles = $service->getFileBasedCategoryFiles($searchedCompaniesIds, $category->id, $fromDate, $toDate, 0, 1000000, true);
                foreach ($sentFiles as $file) {
                    $company = $service->getUserCompany($file->userId);
                    if (!in_array($company->id, $companiesId)) {
                        $companiesId[] = $company->id;
                        $sentFilesComponent[] = 'fileId_' . $file->id;
                        $this->addComponent('fileId_' . $file->id, new FRMSHASTA_CMP_CompanyFile(array(
                            'companyId' => $company->id,
                            'searchedCompaniesIds' => array($company->id),
                            'categoryId' => $category->id,
                            'fromDate' => $fromDate,
                            'toDate' => $toDate,
                        )));
                    }
                }
                $sentCount = sizeof($sentFiles);
            } else {
                $allHoldingCount = $service->getAllHoldingCount();
                $sentFiles = $service->getFileBasedCategoryFiles($searchedCompaniesIds, $category->id, $fromDate, $toDate, 0, 1000000, true);
                $companiesId = array();
                foreach ($sentFiles as $file) {
                    $company = $service->getUserCompany($file->userId);
                    if ($company != null && !in_array($company->id, $companiesId)) {
                        $companiesId[] = $company->id;
                        $sentFilesComponent[] = 'companyId_' . $category->id . '_' . $company->id;
                        $this->addComponent('companyId_' . $category->id . '_' . $company->id, new FRMSHASTA_CMP_CompanyFile(array(
                            'companyId' => $company->id,
                            'searchedCompaniesIds' => array($company->id),
                            'categoryId' => $category->id,
                            'fromDate' => $fromDate,
                            'toDate' => $toDate,
                        )));
                    }
                }

                $notSentFilesComponent = array();
                $allCompanies = $service->getAllCompanies();
                foreach ($allCompanies as $id => $company) {
                    if ($company != null && !in_array($id, $companiesId)) {
                        $notSentFilesComponent[] = 'companyId_' . $category->id . '_' . $id;
                        $this->addComponent('companyId_' . $category->id . '_' . $id, new FRMSHASTA_CMP_CompanyFile(array('companyId' => $id, 'inactive' => true)));
                    }
                }

                $sentCount = sizeof($companiesId);
                $catInfo['not_sent_count'] = $allHoldingCount - $sentCount;
                $catInfo['not_sent_files_component_ids'] = $notSentFilesComponent;
            }
            $catInfo['sent_count'] = $sentCount;
            $catInfo['sent_files_component_ids'] = $sentFilesComponent;
            $categoriesInfo[] = $catInfo;
        }

        if (sizeof($allCategories) > 0){
            $defaultCategoryId = $allCategories[0]->id;
            if (isset($_GET['categoryId'])) {
                $defaultCategoryId = $_GET['categoryId'];
            }
            OW::getDocument()->addOnloadScript('categoryChanger("' . $defaultCategoryId .'");');
        }

        $this->addForm($service->getFilterForm('files_filter', true, false));
        $this->assign('categories', $categoriesInfo);

        OW::getDocument()->addOnloadScript('handleFilterForm("' . OW::getRouter()->urlForRoute('frmshasta_reports') .'");');
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function customizeCategories($params)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }

        $service = FRMSHASTA_BOL_Service::getInstance();
        $service->getCustomizeCategoryForm($this);
        exit(json_encode(array('result' => true)));
    }

    public function allMyFiles() {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }

        $categoryId = null;
        if (isset($_GET['categoryId'])) {
            $categoryId = $_GET['categoryId'];
        }

        $service = FRMSHASTA_BOL_Service::getInstance();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $rpp = 20;
        $first = ($page - 1) * $rpp;
        $count = $rpp;

        $fromDateValue = $service->getDateFromJalali('fromYear', 'fromMonth');
        $toDateValue = $service->getDateFromJalali('toYear', 'toMonth', 31);

        $allFilesCount = $service->getUserFilesCount(OW::getUser()->getId(), $categoryId, $fromDateValue, $toDateValue);

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($allFilesCount / $rpp), 5));

        $files = $service->getUserFiles(OW::getUser()->getId(), $categoryId, $fromDateValue, $toDateValue, $first, $count);

        $fileIds = array();
        foreach ($files as $file) {
            $fileIds[] = $file->id;
        }

        $service->addStaticFiles();

        $filterForm = $service->getFilterForm('files_filter');
        $this->addForm($filterForm);
        $this->addComponent('files', new FRMSHASTA_CMP_Files(array('fileIds' => $fileIds)));

        OW::getDocument()->addOnloadScript('handleFilterForm("' . OW::getRouter()->urlForRoute('frmshasta_view_all_my_files') .'");');
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function downloadFile($params){
        if (!isset($params['id'])) {
            throw new Redirect404Exception();
        }

        $service = FRMSHASTA_BOL_Service::getInstance();
        $access = $service->downloadFile($params['id']);
        if (!$access) {
            throw new Redirect404Exception();
        }
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function file($params){
        if (!isset($params['id'])) {
            throw new Redirect404Exception();
        }

        $service = FRMSHASTA_BOL_Service::getInstance();
        $access = $service->hasUserAccessToManageFile($params['id']);
        if (!$access) {
            throw new Redirect404Exception();
        }

        $file = $service->getFile($params['id']);
        $fileInfo = $service->preparedFileItem($file);
        if ($service->hasUserAccessManager()) {
            $fileInfo['manageAccess_url'] = 'showManageAccessFileForm('.$file->id . ',\''. OW_Language::getInstance()->text('frmshasta','access_setting') .'\')';
        }
        $file = new FRMSHASTA_CMP_File(array('fileId' => $file->id));
        $this->addComponent('file', $file);

        OW::getDocument()->addOnloadScript('document.querySelectorAll(\'.lbl-toggle\')[0].click();');
    }

    public function manageAccessFile()
    {

        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        if (!isset($_POST['fileId']) || !isset($_POST['sendIdList'])) {
            throw new Redirect404Exception();
        }

        $service = FRMSHASTA_BOL_Service::getInstance();
        if (!$service->hasUserAccessManager()) {
            throw new Redirect404Exception();
        }

        $selectedIds = json_decode($_POST['sendIdList']);

        $fileId = $_POST['fileId'];

        $service = FRMSHASTA_BOL_Service::getInstance();
        $service->updateFileAccess($selectedIds,$fileId);

        $respondArray['messageType'] = 'info';
        $respondArray['message'] = OW::getLanguage()->text('frmshasta', 'manage_success_message');
        exit(json_encode($respondArray));
    }

    public function manageAccessCategory()
    {

        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        if (!isset($_POST['categoryId']) || !isset($_POST['sendIdList'])) {
            throw new Redirect404Exception();
        }

        $service = FRMSHASTA_BOL_Service::getInstance();
        if (!$service->hasUserAccessManager()) {
            throw new Redirect404Exception();
        }

        $selectedIds = json_decode($_POST['sendIdList']);

        $categoryId = $_POST['categoryId'];

        $service = FRMSHASTA_BOL_Service::getInstance();
        $service->updateCategoryAccess($selectedIds,$categoryId);

        $respondArray['messageType'] = 'info';
        $respondArray['message'] = OW::getLanguage()->text('frmshasta', 'manage_success_message');
        exit(json_encode($respondArray));
    }
}