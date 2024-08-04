<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmshasta.bol
 * @since 1.0
 */
class FRMSHASTA_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $fileDao;
    private $fileCategoryDao;
    private $companyDao;
    private $userCompanyDao;
    private $specialCategoryDao;
    private $userCategoriesDao;
    private $userCategoryPermissionDao;
    private $userFilePermissionDao;

    const FILE_BASED = 1;
    const COMPANY_BASED = 2;

    private function __construct()
    {
        $this->fileDao = FRMSHASTA_BOL_FileDao::getInstance();
        $this->fileCategoryDao = FRMSHASTA_BOL_FileCategoryDao::getInstance();
        $this->companyDao = FRMSHASTA_BOL_CompanyDao::getInstance();
        $this->userCompanyDao = FRMSHASTA_BOL_UserCompanyDao::getInstance();
        $this->specialCategoryDao = FRMSHASTA_BOL_SpecialCategoryDao::getInstance();
        $this->userCategoriesDao = FRMSHASTA_BOL_UserCategoriesDao::getInstance();
        $this->userCategoryPermissionDao = FRMSHASTA_BOL_UserCategoryAccessDao::getInstance();
        $this->userFilePermissionDao = FRMSHASTA_BOL_UserFileAccessDao::getInstance();
    }

    public function deleteFile($fileId) {
        if (!$this->hasUserAccessToManageFile($fileId)) {
            return false;
        }

        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'add_file',
            'entityId' => $fileId
        ));
        return $this->fileDao->deleteById($fileId);
    }

    public function deleteCategory($categoryId) {
        if (!$this->hasUserAccessManager()) {
            return false;
        }
        $files = $this->fileDao->getCategoryFiles($categoryId, 0, 1000);
        foreach ($files as $file) {
            $this->deleteFile($file->id);
        }
        return $this->fileCategoryDao->deleteById($categoryId);
    }

    public function deleteCompany($companyId) {
        if (!$this->hasUserAccessManager()) {
            return false;
        }
        $company = $this->getCompany($companyId);
        $subCompanies = $this->getSubsCompany($companyId);
        foreach ($subCompanies as $subCompany) {
            if($subCompany->parentId == $companyId){
                $subCompany->parentId = $company->parentId;
            }
        }
        $holdingQId = OW::getConfig()->getValue('frmshasta', 'holding_field');
        if ($holdingQId != null && !empty($holdingQId)) {
            $holdingQ = BOL_QuestionService::getInstance()->findQuestionById($holdingQId);
            if ($holdingQ != null) {
                $holdingQName = $holdingQ->name;
                $userIds = $this->getUserIdsByCompanyId($companyId);
                $questionList = [$holdingQName];
                foreach ($userIds as $userId){
                    BOL_QuestionDataDao::getInstance()->deleteByQuestionListAndUserId($questionList,$userId);
                    BOL_PreferenceService::getInstance()->savePreferenceValue('profile_details_update_stamp', 0, $userId);
                }
                return $this->companyDao->deleteById($companyId);
            }
        }
        return false;
    }

    public function getUserIdsByCompanyId($companyId){
        $userCompanies = $this->userCompanyDao->findByCompany($companyId);
        $userIds = array();
        foreach ($userCompanies as $userCompany){
            $userIds[] = $userCompany->userId;
        }
        return $userIds;
    }

    public function downloadFile($fileId) {
        $service = FRMSHASTA_BOL_Service::getInstance();
        $file = $service->getFile($fileId);
        if ($file == null) {
            return false;
        }

        $access = $service->hasUserAccessToManageFile($fileId);
        if ($access == false) {
            return false;
        }

        $path = $service->getFileDirectory($file->fileName);
        header('Content-Description: File Transfer');
        if(function_exists('mime_content_type')){
            $type = mime_content_type($path);
            header('Content-Type: ' . $type);
        }else{
            header('Content-Type: application/octet-stream');
        }
        header('Content-Disposition: inline; filename=' . basename($path));
        header('Content-Transfer-Encoding: binary');
        header('Pragma: public');
        header('Cache-Control: max-age=864000');
        header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 864000));
        header('Content-Length: ' . filesize($path));
        ob_clean();

        set_time_limit(0);
        $file = @fopen($path,"rb");
        if ( $file !== false ) {
            while (!feof($file)) {
                print(@fread($file, 1024 * 8));
                ob_flush();
                flush();
            }
        }
        exit();
    }

    public function hasUserAccessToManageFile($fileId) {
        if (!OW::getUser()->isAuthenticated()) {
            return false;
        }

        if (empty($fileId)) {
            return false;
        }

        $file = $this->getFile($fileId);
        if ($file != null && $file->userId == OW::getUser()->getId()) {
            return true;
        }

        $fileAccess = $this->fileDao->getFiles(array(), array(), null, null, null, 0, 10, $fileId);
        if ($fileAccess == null){
            return false;
        }

        return true;
    }

    public function getUserRoles($userId) {
        $aService = BOL_AuthorizationService::getInstance();
        $userRoles = $aService->findUserRoleList($userId);

        $userRolesIdList = array();
        foreach ( $userRoles as $role )
        {
            $userRolesIdList[] = $role->getId();
        }

        return $userRolesIdList;
    }

    public function getFileForm($component, $id = null) {
        $nameValue = null;
        $keywordsValue = '';
        $categoryIdValue = null;
        $fileName = '';
        $currentYear = date('Y', time());
        $currentDate = $currentYear . '/' . date('m') . '/' . date('d');

        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }

        if ($id != null) {
            $file = $this->fileDao->findById($id);
            if ($file != null) {
                $nameValue = $file->name;
                $keywordsValue = $file->keywords;
                $categoryIdValue = $file->categoryId;
                $fileName = $file->fileName;
                $dateTimestamp = $file->time;
                $currentDate = date('Y', $dateTimestamp) . '/' . date('m', $dateTimestamp) . '/' . date('d', $dateTimestamp);
            }
        }

        $form = new Form('manage_file');
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        if ($id == null) {
            $form->setAction(OW::getRouter()->urlForRoute('frmshasta_add_file'));
        } else {
            $form->setAction(OW::getRouter()->urlForRoute('frmshasta_edit_file', array('id' => $id)));
        }

        $nameField = new TextField('name');
        $nameField->setLabel(OW::getLanguage()->text('frmshasta', 'name'));
        $nameField->setRequired();
        $nameField->setValue($nameValue);
        $nameField->setHasInvitation(false);
        $form->addElement($nameField);

        $keywordsField = new TagsInputField('keywords');
        $keywordsField->setLabel(OW::getLanguage()->text('frmshasta', 'keywords'));
        $keywordsField->setValue($keywordsValue);
        $form->addElement($keywordsField);

        $startDate = new DateField('start_date');
        $startDate->setLabel(OW::getLanguage()->text('frmshasta', 'file_date'));
        $startDate->setMinYear($currentYear - 15);
        $startDate->setMaxYear($currentYear);
        $startDate->setRequired();
        $startDate->setValue($currentDate);
        $form->addElement($startDate);

        $allCategories = $this->getAllCategories();
        $allCategoriesOptions = array();
        foreach ($allCategories as $cat) {
            $allCategoriesOptions[$cat->id] = $cat->name;
        }
        $categoryField = new Selectbox('category');
        $categoryField->setLabel(OW::getLanguage()->text('frmshasta', 'category'));
        $categoryField->setOptions($allCategoriesOptions);
        $categoryField->setId('frmshasta_category');
        $categoryField->setValue($categoryIdValue);
        $categoryField->setRequired();
        $form->addElement($categoryField);

        $fileField = new FileField('file');
        $fileField->setLabel(OW::getLanguage()->text('frmshasta', 'file'));
        if ($id == null) {
            $fileField->setRequired();
        }
        $form->addElement($fileField);

        $idField = new HiddenField('file_id');
        $idField->setValue($id);
        $form->addElement($idField);

        $submit = new Submit('submit');
        $form->addElement($submit);

        if (OW::getRequest()->isPost() &&  isset($_POST['form_name'])) {
            if ($form->isValid($_POST)) {
                $name = $_POST['name'];
                $categoryId = $_POST['category'];
                $keywordsValue = $_POST['keywords'];
                $time = time();
                if(isset($form->getValues()['start_date']) && !empty($form->getValues()['start_date'])){
                    $dateArray = explode('/', $form->getValues()['start_date']);
                    $time = mktime(0, 0, 0, (int) $dateArray[1], (int) $dateArray[2], (int) $dateArray[0]);
                }

                if ($id != null && !$this->hasUserAccessToManageFile($id)) {
                    return null;
                }

                if(!((int)$_FILES['file']['error'] !== 0 || !is_uploaded_file($_FILES['file']['tmp_name']))){
                    $fileName = FRMSecurityProvider::generateUniqueId();
                    $fileName .= '.' . UTIL_File::getExtension($_FILES['file']['name']);
                    $tmpImgPath = $this->getFileDirectory($fileName);
                    $storage = new BASE_CLASS_FileStorage();
                    $storage->copyFile($_FILES['file']['tmp_name'], $tmpImgPath);
                } else if($id == null) {
                    OW::getFeedback()->error(OW::getLanguage()->text('frmshasta', 'file_empty'));
                    return $form;
                }

                $file = $this->fileDao->saveFile($name, OW::getUser()->getId(), time(), $time, $categoryId, $fileName, $keywordsValue, $id);
                $this->processAddFile($file);

                OW::getFeedback()->info(OW::getLanguage()->text('frmshasta', 'saved_successfully'));
//                OW::getApplication()->redirect(OW_URL_HOME);
            }
        }
        return $form;
    }

    public function processAddFile($file) {
        $validCompanyIds = $this->getTopUserCompany($file->userId);
        if ($validCompanyIds == null) {
            $validCompanyIds = array();
        }

        $userIds = $this->findHierarchicValidAccessUserIdsByCompaniesId($validCompanyIds);
        if (sizeof($userIds) > 0) {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds, true, true, false, false);
            $userName = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
            $usersUrl = BOL_UserService::getInstance()->getUserUrlsForList($userIds);

            $currentUserId = OW::getUser()->getId();
            foreach ($userIds as $userId) {
                if (!$this->hasUserAccessToManageFile($file->id) || $userId == $file->userId) {
                    continue;
                }
                $fileUrl = OW::getRouter()->urlForRoute('frmshasta_file', array('id' => $file->id));
                $event = new OW_Event('notifications.add', array(
                    'pluginKey' => 'frmshasta',
                    'entityType' => 'add_file',
                    'entityId' => $file->id,
                    'action' => 'add_file',
                    'userId' => $userId,
                    'time' => time()
                ), array(
                    'avatar' => $avatars[$currentUserId],
                    'string' => array(
                        'key' => 'frmshasta+add_file_notification',
                        'vars' => array(
                            'userName' => $userName[$currentUserId],
                            'userUrl' => $usersUrl[$currentUserId],
                            'fileName' => $file->name,
                        )
                    ),
                    'content' => '',
                    'url' => $fileUrl
                ));
                OW::getEventManager()->trigger($event);
            }
        }
    }

    public function findHierarchicValidAccessUserIds($fileOwnerId)
    {
        $validCompanyIds = $this->getTopUserCompany($fileOwnerId);
        if ($validCompanyIds == null) {
            $validCompanyIds = array();
        }

        return $this->findHierarchicValidAccessUserIdsByCompaniesId($validCompanyIds);
    }

    public function findHierarchicValidAccessUserIdsByCompaniesId($validCompanyIds = array())
    {
        $userIds = array();

        $holdingField = OW::getConfig()->getValue('frmshasta', 'holding_field');
        if (isset($holdingField) && sizeof($validCompanyIds) > 0) {
            $holdingField = BOL_QuestionService::getInstance()->findQuestionById($holdingField);
            if ($holdingField != null) {
                $userIds = BOL_QuestionService::getInstance()->findUsersByQuestionAndAnswers($holdingField->name, $validCompanyIds);
            }
        }
        return $userIds;
    }

    public function getCreateCompanyForm($id = null) {
        $nameValue = null;
        $parentId = null;
        $imageUrl = null;

        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }

        if ($id != null) {
            $company = $this->companyDao->findById($id);
            if ($company != null) {
                $nameValue = $company->name;
                $parentId = $company->parentId;
                $imageUrl = $company->imageUrl;
            }
        }

        $form = new Form('manage_company');
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $form->setMethod(Form::METHOD_POST);
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){closeCompanyForm();}');
        if ($id == null) {
            $form->setAction(OW::getRouter()->urlForRoute('frmshasta_add_company'));
        } else {
            $form->setAction(OW::getRouter()->urlForRoute('frmshasta_edit_company', array('id' => $id)));
        }

        $nameField = new TextField('name');
        $nameField->setLabel(OW::getLanguage()->text('frmshasta', 'name'));
        $nameField->setRequired();
        $nameField->setValue($nameValue);
        $nameField->setHasInvitation(false);
        $form->addElement($nameField);

        $allCompany = $this->companyDao->findAll();
        $allCompanyOptions = array();
        foreach ($allCompany as $comp) {
            $allCompanyOptions[$comp->id] = $comp->name;
        }
        $categoryField = new Selectbox('parent');
        $categoryField->setLabel(OW::getLanguage()->text('frmshasta', 'parent_name'));
        $categoryField->setOptions($allCompanyOptions);
        $categoryField->setId('frmshasta_parent');
        $categoryField->setValue($parentId);
        $form->addElement($categoryField);

        $fileField = new FileField('image');
        $fileField->setLabel(OW::getLanguage()->text('frmshasta', 'create_field_image_label'));
        $form->addElement($fileField);

        $idField = new HiddenField('company_id');
        $idField->setValue($id);
        $form->addElement($idField);

        $submit = new Submit('submit');
        $form->addElement($submit);

        if (OW::getRequest()->isPost() &&  isset($_POST['form_name'])) {
            if ($form->isValid($_POST)) {
                $name = $_POST['name'];
                $parentId = $_POST['parent'];
                $imageValid = true;
                if (!empty($_FILES) && !empty($_FILES['image']['name'])) {
                    if (!UTIL_File::validateImage($_FILES['image']['name'])) {
                        $imageValid = false;
                    } else {
                        if (!empty($_FILES['image']["tmp_name"])) {
                            $bundle = FRMSecurityProvider::generateUniqueId();
                            $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile('frmshasta', $_FILES['image'], $bundle);
                            BOL_AttachmentService::getInstance()->updateStatusForBundle('frmshasta', $bundle, 1);
                            $imageUrl = $dtoArr['url'];
                        }
                    }
                }
                if($imageValid){
                    $this->companyDao->saveCompany($name, $parentId, $imageUrl, $id);
                    OW::getFeedback()->info(OW::getLanguage()->text('frmshasta', 'saved_successfully'));
                    OW::getApplication()->redirect(OW_URL_HOME);
                }else{
                    OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                }
            }
        }
        return $form;
    }

    public function getDateFromJalali($year = null, $month = null, $defaultDay = 1) {
        $startYearValue = null;
        if (isset($_GET[$year])) {
            $startYearValue = $_GET[$year];
        }
        $startMonthValue = null;
        if ($month != null && isset($_GET[$month])) {
            $startMonthValue = $_GET[$month];
        }

        if ($startYearValue != null && $startMonthValue == null) {
            $startMonthValue = 1;
        }
        $startDateValue = null;
        if ($startYearValue != null) {
            $startDateValue = $this->convertDateToGregorian($startYearValue, $startMonthValue, $defaultDay);
        }
        return $startDateValue;
    }

    public function convertDateToGregorian($year, $month = 1, $day = 1) {
        $startDateValue = null;
        $changeToGregorianDateEventParams['faYear'] = $year;
        $changeToGregorianDateEventParams['faMonth'] = $month;
        $changeToGregorianDateEventParams['faDay'] = $day;
        $changeToGregorianDateEventParams['changeNewsJalaliToGregorian'] = true;
        $changeToGregorianDateEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::CHANGE_DATE_FORMAT_TO_GREGORIAN, $changeToGregorianDateEventParams));
        if($changeToGregorianDateEvent->getData()!=null && sizeof($changeToGregorianDateEvent->getData())>0){
            $newDateData = $changeToGregorianDateEvent->getData();
            if (isset($newDateData['gregorianYearNews']) && isset($newDateData['gregorianMonthNews']) && isset($newDateData['gregorianDayNews'])) {
                $startDateValue = $newDateData['gregorianYearNews'] . '/' .$newDateData['gregorianMonthNews'] . '/' .$newDateData['gregorianDayNews'];
            }
        }
        return $startDateValue;
    }

    public function getPersianYear($year, $month = 1, $day = 1) {
        $persianCurrentYear = $year;
        $dateRangeEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::CHANGE_DATE_RANGE_TO_JALALI, array(
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'dateField' => '',
        )));
        if($dateRangeEvent->getData()!=null && sizeof($dateRangeEvent->getData())>0){
            if (isset($dateRangeEvent->getData()['persian_year'])) {
                $persianCurrentYear = $dateRangeEvent->getData()['persian_year'];
            }
        }
        return $this->convertDateToGregorian($persianCurrentYear);
    }

    public function getFilterForm($formname, $useHoldingField = false, $useCategoryField = true, $toDate = true) {
        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }

        $form = new Form($formname);
        $form->setMethod(Form::METHOD_POST);

        $currentYear = date('Y', time());
        $persianCurrentYear = $currentYear;

        $startDateValue = $this->getDateFromJalali('fromYear', 'fromMonth');
        if ($startDateValue == null) {
            $startDateValue = $this->getPersianYear(date('Y'), date('m'), date('d'));
        }

        $startDate = new DateField('start_date');
        $startDate->setLabel(OW::getLanguage()->text('frmshasta', 'file_start_date'));
        $startDate->setMinYear($currentYear - 25);
        $startDate->setMaxYear($currentYear);
        $startDate->setValue($startDateValue);
        $form->addElement($startDate);

        if ($toDate) {
            $endDateValue = $this->getDateFromJalali('toYear', 'toMonth', 31);
            if ($endDateValue == null) {
                $endDateValue = $this->getPersianYear(date('Y') + 1, date('m'), date('d'));
            }
            $endDate = new DateField('end_date');
            $endDate->setLabel(OW::getLanguage()->text('frmshasta', 'file_end_date'));
            $endDate->setMinYear($currentYear - 25);
            $endDate->setMaxYear($currentYear + 1);
            $endDate->setValue($endDateValue);
            $form->addElement($endDate);
        }

        if ($useCategoryField) {
            $categoryIdValue = null;
            if (isset($_GET['categoryId'])) {
                $categoryIdValue = $_GET['categoryId'];
            }
            $allCategories = $this->getAllCategories();
            $allCategoriesOptions = array();
            foreach ($allCategories as $cat) {
                $allCategoriesOptions[$cat->id] = $cat->name;
            }
            $categoryField = new Selectbox('category');
            $categoryField->setLabel(OW::getLanguage()->text('frmshasta', 'category'));
            $categoryField->setOptions($allCategoriesOptions);
            $categoryField->setValue($categoryIdValue);
            $categoryField->setId('frmshasta_category');
            $form->addElement($categoryField);
        }

        if ($useHoldingField) {
            $holdingsValue = null;
            if (isset($_GET['holding'])) {
                $holdingsValue = $_GET['holding'];
            }
            $companies = $this->companyDao->findAll();
            $holdingsFieldsNames = array();
            foreach ($companies as $company) {
                $holdingsFieldsNames[$company->id] = $company->name;
            }
            $holdingsField = new Selectbox('holding');
            $holdingsField->setLabel(OW::getLanguage()->text('frmshasta', 'holding'));
            $holdingsField->setValue($holdingsValue);
            $holdingsField->setId('frmshasta_holdings');
            $holdingsField->setOptions($holdingsFieldsNames);
            $form->addElement($holdingsField);

            $subCompanyValue = false;
            if (isset($_GET['sub_company']) && $_GET['sub_company'] == 'true') {
                $subCompanyValue = true;
            }
            $subCompany = new CheckboxField('sub_company');
            $subCompany->setLabel(OW::getLanguage()->text('frmshasta', 'sub_company'));
            $subCompany->setValue($subCompanyValue);
            $form->addElement($subCompany);
        }

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmshasta', 'search'));
        $form->addElement($submit);
        return $form;
    }

    public function getCategoryForm($component, $id = null) {
        $nameValue = null;
        $monthFilterValue = true;
        $yearFilterValue = true;
        $conceptValue = self::FILE_BASED;

        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }

        if ($id != null) {
            $category = $this->fileCategoryDao->findById($id);
            if ($category != null) {
                $nameValue = $category->name;
                $monthFilterValue = $category->monthFilter;
                $yearFilterValue = $category->yearFilter;
                $conceptValue = $category->concept;
                $component->assign('deleteUrl', OW::getRouter()->urlForRoute('frmshasta_delete_category', array('id' => $category->id)));
            }
        }

        $form = new Form('category');
        $form->setMethod(Form::METHOD_POST);
        if ($id == null) {
            $form->setAction(OW::getRouter()->urlForRoute('frmshasta_add_category'));
        } else {
            $form->setAction(OW::getRouter()->urlForRoute('frmshasta_edit_category', array('id' => $id)));
        }
        $nameField = new TextField('name');
        $nameField->setLabel(OW::getLanguage()->text('frmshasta', 'name'));
        $nameField->setRequired();
        $nameField->setValue($nameValue);
        $nameField->setHasInvitation(false);
        $form->addElement($nameField);

        $monthFilterField = new CheckboxField('monthFilter');
        $monthFilterField->setValue($monthFilterValue);
        $monthFilterField->setLabel(OW::getLanguage()->text('frmshasta', 'monthFilter'));
        $form->addElement($monthFilterField);

        $yearFilterField = new CheckboxField('yearFilter');
        $yearFilterField->setValue($yearFilterValue);
        $yearFilterField->setLabel(OW::getLanguage()->text('frmshasta', 'yearFilter'));
        $form->addElement($yearFilterField);

        $categoryBased = array();
        $categoryBased[self::FILE_BASED] = OW::getLanguage()->text('frmshasta', 'file_based');
        $categoryBased[self::COMPANY_BASED] = OW::getLanguage()->text('frmshasta', 'company_based');

        $conceptField = new Selectbox('concept');
        $conceptField->setLabel(OW::getLanguage()->text('frmshasta', 'concept_based'));
        $conceptField->setOptions($categoryBased);
        $conceptField->setValue($conceptValue);
        $conceptField->setRequired(true);
        $conceptField->setHasInvitation(false);
        $form->addElement($conceptField);

        $idField = new HiddenField('category_id');
        $idField->setValue($id);
        $form->addElement($idField);

        $submit = new Submit('submit');
        $form->addElement($submit);

        if (OW::getRequest()->isPost() &&  isset($_POST['form_name'])) {
            if ($form->isValid($_POST)) {
                $name = $_POST['name'];
                $monthFilterValue = $form->getValues()['monthFilter'];
                if (empty($monthFilterValue) || $monthFilterValue == null) {
                    $monthFilterValue = false;
                }
                $yearFilterValue = $form->getValues()['yearFilter'];
                $conceptValue = $form->getValues()['concept'];
                $id = $_POST['category_id'];
                $this->fileCategoryDao->saveCategory($name, $monthFilterValue, $yearFilterValue, $conceptValue, $id);

                OW::getApplication()->redirect(OW_URL_HOME);
                OW::getFeedback()->info(OW::getLanguage()->text('frmshasta', 'saved_successfully'));
            }
        }

        return $form;
    }

    public function getFiles($validCompanyIds, $searchCompanyIds, $categoryId = null, $fromDate = null, $toDate = null, $first = 0, $count = 10, $fileId = null) {
        return $this->fileDao->getFiles($validCompanyIds, $searchCompanyIds, $categoryId, $fromDate, $toDate, $first, $count, $fileId);
    }

    public function getFilesCount($validCompanyIds, $searchCompanyIds, $categoryId = null, $fromDate = null, $toDate = null, $fileId = null) {
        return $this->fileDao->getFilesCount($validCompanyIds, $searchCompanyIds, $categoryId, $fromDate, $toDate, $fileId);
    }

    public function getUserFiles($userId, $categoryId = null, $fromDateValue = null, $toDateValue = null, $first = 0, $count = 8) {
        return $this->fileDao->getUserFiles($userId, $categoryId, $fromDateValue, $toDateValue, $first, $count);
    }

    public function getUserFilesCount($userId, $categoryId = null, $fromDateValue = null, $toDateValue = null) {
        return $this->fileDao->getUserFilesCount($userId, $categoryId, $fromDateValue, $toDateValue);
    }

    public function findUserIdsGrantedAccessToFile($fileId)
    {
        return $this->userFilePermissionDao->findUserIdsGrantedAccessToFile($fileId);
    }

    public function findUserIdsDeniedAccessToFile($fileId)
    {
        return $this->userFilePermissionDao->findUserIdsDeniedAccessToFile($fileId);
    }

    public function findUserIdsGrantedAccessToCategory($categoryId)
    {
        return $this->userCategoryPermissionDao->findUserIdsGrantedAccessToCategory($categoryId);
    }


    public function findUserIdsDeniedAccessToCategory($categoryId)
    {
        return $this->userCategoryPermissionDao->findUserIdsDeniedAccessToCategory($categoryId);
    }

    public function preparedCompanyItem($company) {
        if ($company == null) {
            return array();
        }

        return array(
            'name' => $company->name,
        );
    }

    public function preparedFileItem($file) {
        if ($file == null) {
            return array();
        }
        $category = $this->getCategory($file->categoryId);

        $userInfo = $this->getProfileUserInfo($file->userId);

        $userInfo = array(
            'holding' => $userInfo['holding']['label'],
            'name' => BOL_UserService::getInstance()->getDisplayName($file->userId),
            'url' => BOL_UserService::getInstance()->getUserUrl($file->userId),
        );



        $fileTempInfo = array(
            'id' => $file->id,
            'name' => $file->name,
            'keywords' => $file->keywords,
            'time' => UTIL_DateTime::formatSimpleDate($file->time, true),
            'categoryName' => isset($category) ? $category->name : '-',
            'categoryId' => isset($category) ? $category->id : '-',
            'uploadTime' => UTIL_DateTime::formatSimpleDate($file->uploadTime, true),
            'user' => $userInfo,
            'extension' => UTIL_File::getExtension($file->fileName),
        );

        if($this->hasUserAccessToManageFile($file->id)) {
            $fileTempInfo['editUrl'] = OW::getRouter()->urlForRoute('frmshasta_edit_file', array('id' => $file->id));
            $fileTempInfo['deleteUrl'] = OW::getRouter()->urlForRoute('frmshasta_delete_file', array('id' => $file->id));
            $fileTempInfo['viewUrl'] = OW::getRouter()->urlForRoute('frmshasta_file', array('id' => $file->id));
            $fileTempInfo['url'] = $this->getFileDownloadUrl($file->id);
            if($this->hasUserAccessManager())
            {
                $fileTempInfo['manageAccess_url'] = 'showManageAccessFileForm('.$file->id . ',\''. OW_Language::getInstance()->text('frmshasta','access_setting') .'\')';
            }
        }

        return $fileTempInfo;
    }

    public function hasUserAccessManager() {
        if (!OW::getUser()->isAuthenticated()) {
            return false;
        }
        if(OW::getUser()->isAuthorized('frmshasta','manage-access') || OW::getUser()->isAdmin())
        {
            return true;
        }
        return false;
    }

    public function getFile($fileId) {
        return $this->fileDao->findById($fileId);
    }

    public function getFilesByIdList($filesId) {
        return $this->fileDao->getFilesByIds($filesId);
    }

    public function getSubsUserCompany($userId) {
        $company = $this->getUserCompany($userId);
        if ($company == null) {
            return null;
        }
        $companiesId = $this->findChildCompanyIdRecursive($company->id);
        return $companiesId;
    }

    public function getSubsCompany($companyId) {
        $companiesId = $this->findChildCompanyIdRecursive($companyId);
        return $companiesId;
    }

    public function getTopUserCompany($userId) {
        $company = $this->getUserCompany($userId);
        if ($company == null) {
            return null;
        }
        $parents = array();
        $companiesId = $this->findParentCompanyIdRecursive($company->id, $parents);
        return $companiesId;
    }

    public function findParentCompanyIdRecursive($companyId, $parents) {
        $parents[] = $companyId;
        $company = $this->companyDao->findById($companyId);
        if (empty($company->parentId)) {
            return $parents;
        }
        $parents = $this->findParentCompanyIdRecursive($company->parentId, $parents);
        return $parents;
    }

    public function findRootCompanies() {
        return $this->companyDao->findRoots();
    }

    public function findAllRecursiveCompanies() {
        $rootCompanies = $this->findRootCompanies();
        $allCompanies = array();
        $html = '';
        foreach ($rootCompanies as $rootCompany) {
            $child = $this->findChildCompanyObjectRecursiveData($rootCompany->id, '');
            $allCompanies[] = $child;
            $html .= $child['html'];
        }
        return array(
            'companies' => $allCompanies,
            'html' => $html,
        );
    }

    public function findChildCompanyObjectRecursiveData($companyId, $html) {
        $company = $this->companyDao->findById($companyId);

        $editHtml = '<a class="edit_company" href="javascript://" onclick="showCompanyForm(\'' . $company->id .'\');"><button class="custom_button"><span class="icon_edit"></span></button></a>';
        $childInfo = array(
            'name' => $company->name,
            'id' => $company->id,
        );
        $html .= '<li>';
        $childs = $this->companyDao->findByParentId($company->id);

        $userIds = $this->findHierarchicValidAccessUserIdsByCompaniesId(array($company->id));
        $usersCount = sizeof($userIds);

        if (sizeof($childs) > 0) {
            $html .= '<span class="parent"><div class="company_logo"';
            if($company->imageUrl != "") {
                $html .= ' style="background-image: url('. $company->imageUrl .');"';
            }
            $html .= '></div>' . $company->name . ' (' . OW::getLanguage()->text('frmshasta', 'users_count') . ' : ' . $usersCount . ') ' . '</span>' . $editHtml . '<ul class="child">';
        } else {
            $html .= '<div class="company_logo"';
            if($company->imageUrl != "") {
                $html .= ' style="background-image: url('. $company->imageUrl .');"';
            }
            $html .=  '></div>' . $company->name . ' (' . OW::getLanguage()->text('frmshasta', 'users_count') . ' : ' . $usersCount . ') ' . $editHtml . '</li>';
        }

        foreach ($childs as $child) {
            $childInfo['child'] = array();
            $childTempInfo = $this->findChildCompanyObjectRecursiveData($child->id, '');
            if (isset($childTempInfo['html'])) {
                $html .= $childTempInfo['html'];
                unset($childTempInfo['html']);
            }
            $childInfo['child'][] = $childTempInfo;
        }

        if (sizeof($childs) > 0) {
            $html .= '</ul></li>';
        }

        $childInfo['html'] = $html;
        return $childInfo;
    }

    public function findChildCompanyIdRecursive($companyId) {
        $companiesId = array();
        $companiesId[] = $companyId;
        $childs = $this->companyDao->findByParentId($companyId);
        foreach ($childs as $child) {
            $companiesId = array_merge($this->findChildCompanyIdRecursive($child->id), $companiesId);
        }
        return $companiesId;
    }

    public function getCategoryFiles($categoryId, $userId) {
        $validCompanyIds = $this->getSubsUserCompany($userId);
        if ($validCompanyIds == null) {
            $validCompanyIds = array();
        }
        $files = $this->getFiles($validCompanyIds, array(), $categoryId);
        return $files;
    }

    public function getAllHoldingCount() {
        return sizeof($this->companyDao->findAll());
    }

    public function getFileBasedCategoryFiles($searchedCompaniesIds, $categoryId, $fromDate = null, $toDate = null, $first = 0, $count = 5, $useAllCompany = false) {
        $validCompanyIds = array();
        if (!$useAllCompany) {
            $validCompanyIds = $this->getSubsUserCompany(OW::getUser()->getId());
            if ($validCompanyIds == null) {
                $validCompanyIds = array();
            }
        }

        $category = $this->getCategory($categoryId);

        $yearTimeStamp = ( 60 * 60 * 24 * 365 );
        $monthTimeStamp = ( 60 * 60 * 24 * 31 );

        if ($fromDate == null){
            $fromDate = time();
            if ($category && $category->monthFilter == "1") {
                $fromDate -= $monthTimeStamp; // 1 month ago
            } else {
                $fromDate -= $yearTimeStamp; // 1 year ago
            }
            if ($toDate == null){
                $toDate = time();
            }
        } else if ($toDate == null) {
            if ($category && $category->monthFilter == "1") {
                $toDate = $fromDate + $monthTimeStamp; // 1 month ago
            } else if ($category && $category->yearFilter == "1") {
                $toDate = $fromDate + $yearTimeStamp; // 1 year ago
            } else {
                $toDate = time();
            }
        }
        return $this->getFiles($validCompanyIds, $searchedCompaniesIds, $categoryId, $fromDate, $toDate, $first, $count);
    }

    public function getUserCompany($userId) {
        $userCompany = $this->userCompanyDao->findByUser($userId);
        if ($userCompany != null) {
            $company = $this->companyDao->findById($userCompany->companyId);
            if ($company != null) {
                return $company;
            }
        }
        return null;
    }

    public function getAllCompanies() {
        $companiesInfo = array();
        $companies = $this->companyDao->findAll();
        foreach ($companies as $company) {
            $companiesInfo[$company->id] = $company->name;
        }
        return $companiesInfo;
    }

    public function getCompany($companyId) {
        return $this->companyDao->findById($companyId);
    }

    public function getUserCompanyName($userId) {
        $company = $this->getUserCompany($userId);
        if ($company != null) {
            return $company->name;
        }
        return null;
    }

    public function getUserCompanyId($userId) {
        $company = $this->getUserCompany($userId);
        if ($company != null) {
            return $company->id;
        }
        return null;
    }

    public function getProfileUserInfo($userId) {
        $holdingLabel = '';
        $holdingValue = '';
        $company = $this->getUserCompany($userId);
        if ($company != null) {
            $holdingLabel = $company->name;
            $holdingValue = $company->id;
        }

        return array(
            'holding' => array(
                'label' => $holdingLabel,
                'value' => $holdingValue,
            ),
        );
    }

    public function getCategory($categoryId) {
        return $this->fileCategoryDao->findById($categoryId);
    }

    public function getUserCategories($userId) {
        $allUserCategories = $this->userCategoriesDao->findByUser($userId);
        $allCategories = array();
        if ($allUserCategories != null) {
            $allUserCategoriesIds = (array) json_decode($allUserCategories->categories);
            if (sizeof($allUserCategoriesIds) > 0) {
                $allCategories = $this->fileCategoryDao->findByIdList($allUserCategoriesIds);
            }
        } else {
            return array();
        }

        $validCategories = array();

        if ($userId == null){
            if (!OW::getUser()->isAuthenticated()) {
                return $validCategories;
            }
            $userId = OW::getUser()->getId();
        }
        if ($allCategories != null && sizeof($allCategories) > 0) {
            $invalidCategoriesIds = $this->getUserBlockedCategories($userId);
            foreach ($allCategories as $category) {
                if (!in_array((int) $category->id, $invalidCategoriesIds)) {
                    $validCategories[] = $category;
                }
            }
        }

        return $validCategories;
    }

    public function getSpecialCategories() {
        $allSpecialCategories = $this->specialCategoryDao->findAll();
        return $allSpecialCategories;
    }

    public function getUserBlockedCategories($userId) {
        if ($userId == null) {
            return array();
        }
        $invalidCategoriesObjects = $this->userCategoryPermissionDao->findByUser($userId);
        $invalidCategoriesIds = array();
        foreach ($invalidCategoriesObjects as $invalidCategoriesObject) {
            $invalidCategoriesIds[] = (int) $invalidCategoriesObject->categoryId;
        }
        return $invalidCategoriesIds;
    }

    public function getAllCategories($userId = null) {
        $allCategories = $this->fileCategoryDao->findAll();
        $validCategories = array();

        if ($userId == null){
            if (!OW::getUser()->isAuthenticated()) {
                return $validCategories;
            }
            $userId = OW::getUser()->getId();
        }
        if ($allCategories != null && sizeof($allCategories) > 0) {
            $invalidCategoriesIds = $this->getUserBlockedCategories($userId);
            foreach ($allCategories as $category) {
                if (!in_array($category->id, $invalidCategoriesIds)) {
                    $validCategories[] = $category;
                }
            }
        }

        return $validCategories;
    }

    public function getCategoriesByIds($ids) {
        if (!isset($ids) || empty($ids)) {
            return array();
        }
        return $this->fileCategoryDao->findByIdList($ids);
    }

    public function onBeforeCompleteProfileFormRender(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['form'])) {
            $holdingQId = OW::getConfig()->getValue('frmshasta', 'holding_field');
            if ($holdingQId != null && !empty($holdingQId)) {
                $holdingQ = BOL_QuestionService::getInstance()->findQuestionById($holdingQId);
                if ($holdingQ != null) {
                    $holdingQName = $holdingQ->name;
                    $formElements = $params['form']->getElements();
                    if (isset($formElements[$holdingQName])) {
                        $formElements[$holdingQName]->setId('frmshasta_holdings');
                    }
                }
            }
        }
    }

    public function onBeforeJoinFormRender(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['form']) && isset($params['controller'])) {
            $holdingQId = OW::getConfig()->getValue('frmshasta', 'holding_field');
            if ($holdingQId != null && !empty($holdingQId)) {
                $holdingQ = BOL_QuestionService::getInstance()->findQuestionById($holdingQId);
                if ($holdingQ != null) {
                    $holdingQName = $holdingQ->name;
                    if (isset($_SESSION['join.real_question_list'])) {
                        // join form
                        $elementName = "";
                        foreach ($_SESSION['join.real_question_list'] as $key => $value) {
                            if ($value == $holdingQName) {
                                $elementName = $key;
                                break;
                            }
                        }
                        $joinFormElements = $params['form']->getElements();
                        if (isset($joinFormElements[$elementName])) {
                            $joinFormElements[$elementName]->setId('frmshasta_holdings');
                        }
                    } else {
                        // edit profile form
                        $editFormElements = $params['form']->getElements();
                        if (isset($editFormElements[$holdingQName])) {
                            $editFormElements[$holdingQName]->setId('frmshasta_holdings');
                        }
                    }
                }
            }
        }
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     *
     * @param OW_Event $event
     */
    public function beforeGetQuestionValues (OW_Event $event){
        $holdingQId = OW::getConfig()->getValue('frmshasta', 'holding_field');
        if ($holdingQId != null && !empty($holdingQId)) {
            $question = BOL_QuestionService::getInstance()->findQuestionById($holdingQId);
            if ($question != null) {
                $holdingQName = $question->name;
                $params = $event->getParams();
                if (isset($params['name']) && $params['name'] == $holdingQName) {
                    $values = $this->getAllCompanies();
                    $result = [];
                    foreach ($values as $key => $text) {
                        $item = new BOL_QuestionValue();
                        $item->setId($key);
                        $item->value = $key;
                        $item->questionName = $question->name;
                        $item->questionText = $text;
                        $result[] = $item;
                    }
                    $event->setData(['value' => ['values' => $result, 'count' => count($result)]]);
                }
            }
        }
    }

    public function notificationActions( OW_Event $event )
    {
        $event->add(array(
            'section' => 'frmshasta',
            'action' => 'add_file',
            'sectionIcon' => 'ow_ic_calendar',
            'sectionLabel' => OW::getLanguage()->text('frmshasta', 'email_notifications_section_label'),
            'description' => OW::getLanguage()->text('frmshasta', 'email_notifications_setting_status_comment'),
            'selected' => true
        ));
    }

    public function onUserRegister(OW_Event $event){
        $params = $event->getParams();
        $data = $event->getData();

        $userId = null;
        $questionParam = null;

        if (OW::getUser()->isAuthenticated()) {
            $userId = OW::getUser()->getId();
        }

        if (isset($params['userId'])) {
            $userId = $params['userId'];
        }

        if (isset($params['params'])) {
            $questionParam = $params['params'];
        } else {
            $questionParam = $data;
        }

        $holdingQId = OW::getConfig()->getValue('frmshasta', 'holding_field');
        if ($holdingQId == null || empty($holdingQId)) {
            return;
        }
        $question = BOL_QuestionService::getInstance()->findQuestionById($holdingQId);
        if ($question == null) {
            return;
        }
        $holdingQName = $question->name;

        if(!empty($questionParam[$holdingQName]) && $userId != null){
            $this->userCompanyDao->saveCompany($userId, $questionParam[$holdingQName]);
        }
    }

    public function getCustomizeCategoryForm($controller) {
        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }

        $userCategories = $this->getUserCategories(OW::getUser()->getId());
        $categoriesId = array();

        foreach ($userCategories as $cat) {
            $categoriesId[] = $cat->id;
        }

        $allCategories = $this->getAllCategories();

        $categoryCheckboxFields = array();

        $form = new Form('customize_categories');
        $form->setMethod(Form::METHOD_POST);
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmshasta_customize_categories'));
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){closeCustomizeCategoryForm();}');

        $categoryFieldsNames = array();
        foreach ($allCategories as $cat) {
            $categoryFieldsNames[] = 'cat' . $cat->id;
            $catField = new CheckboxField('cat' . $cat->id);
            $catField->setValue(in_array($cat->id, $categoriesId));
            $catField->setLabel($cat->name);
            $form->addElement($catField);
            $categoryCheckboxFields[] = 'cat' . $cat->id;
        }
        $controller->assign('categoryFieldsNames', $categoryFieldsNames);

        $submit = new Submit('submit');
        $form->addElement($submit);

        if (OW::getRequest()->isPost() && isset($_POST['form_name'])) {
            if ($form->isValid($_POST)) {
                $categorySelected = array();
                foreach ($allCategories as $cat) {
                    if ($form->getValues()['cat'.$cat->id]) {
                        $categorySelected[] = $cat->id;
                    }
                }
                $categorySelected = json_encode($categorySelected);
                $this->userCategoriesDao->saveCategories(OW::getUser()->getId(), $categorySelected);

                OW::getFeedback()->info(OW::getLanguage()->text('frmshasta', 'saved_successfully'));
            }
        }

        return $form;
    }

    public function getCustomizeSpecialCategoryForm($controller) {
        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }

        $specialCategories = $this->getSpecialCategories();
        $categoriesId = array();

        foreach ($specialCategories as $cat) {
            $categoriesId[] = $cat->categoryId;
        }

        $allCategories = $this->getAllCategories();

        $categoryCheckboxFields = array();

        $form = new Form('customize_special_categories');
        $form->setMethod(Form::METHOD_POST);
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmshasta_customize_special_categories'));
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){closeManageSpecialCategoryForm();}');

        $categoryFieldsNames = array();
        foreach ($allCategories as $cat) {
            $categoryFieldsNames[] = 'cat' . $cat->id;
            $catField = new CheckboxField('cat' . $cat->id);
            $catField->setValue(in_array($cat->id, $categoriesId));
            $catField->setLabel($cat->name);
            $form->addElement($catField);
            $categoryCheckboxFields[] = 'cat' . $cat->id;
        }
        $controller->assign('categoryFieldsNames', $categoryFieldsNames);

        $submit = new Submit('submit');
        $form->addElement($submit);

        if (OW::getRequest()->isPost() && isset($_POST['form_name'])) {
            if ($form->isValid($_POST)) {
                foreach ($allCategories as $cat) {
                    if ($form->getValues()['cat'.$cat->id]) {
                        $this->specialCategoryDao->saveSpecialCategory($cat->id);
                    } else {
                        $this->specialCategoryDao->deleteSpecialCategory($cat->id);
                    }
                }

                OW::getFeedback()->info(OW::getLanguage()->text('frmshasta', 'saved_successfully'));
            }
        }

        return $form;
    }

    public function getManageFieldForm() {
        $holdingValue = OW::getConfig()->getValue('frmshasta', 'holding_field');

        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }

        $form = new Form('manage_fields');
        $form->setMethod(Form::METHOD_POST);

        $questionsList = array();

        $questions = array();
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
        foreach ($accountTypes as $accountType){
            $questions = array_merge($questions, BOL_QuestionService::getInstance()->findEditQuestionsForAccountType($accountType->name));
        }

        $onBeforeProfileEditFormBuildEventResults = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PROFILE_EDIT_FORM_BUILD, array('questions' => $questions)));
        if(isset($onBeforeProfileEditFormBuildEventResults->getData()['questions'])){
            $questions = $onBeforeProfileEditFormBuildEventResults->getData()['questions'];
        }

        foreach ($questions as $sort => $question) {
            $questionsList[$question['id']] = OW::getLanguage()->text('base', 'questions_question_' . $question['name'] . '_label');
        }

        $holdingField = new Selectbox('holding_field');
        $holdingField->setLabel(OW::getLanguage()->text('frmshasta', 'holding'));
        $holdingField->setOptions($questionsList);
        $holdingField->setValue($holdingValue);
        $holdingField->setRequired(true);
        $holdingField->setHasInvitation(false);
        $form->addElement($holdingField);

        $submit = new Submit('submit');
        $form->addElement($submit);

        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                OW::getConfig()->saveConfig('frmshasta', 'holding_field', $form->getValues()['holding_field']);
                OW::getFeedback()->info(OW::getLanguage()->text('frmshasta', 'saved_successfully'));
            }
        }

        return $form;
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmshasta' => array(
                    'label' => $language->text('frmshasta', 'plugin_label'),
                    'actions' => array(
                        'manage-access' => $language->text('frmshasta', 'manage_access'),
                    )
                )
            )
        );
    }
    public function getQuestionValues($questionName) {
        $questionValue = array();
        $questionValuesFetch = BOL_QuestionService::getInstance()->findQuestionsValuesByQuestionNameList(array($questionName));
        foreach ($questionValuesFetch as $key => $questionValueFetch){
            $values = $questionValueFetch['values'];
            foreach ($values as $value){
                $questionOptionValue['value'] = $value->value;
                $questionOptionValue['label'] = BOL_QuestionService::getInstance()->getQuestionValueLang($key, $value->value);
                $questionValue[] = $questionOptionValue;
            }
        }
        return $questionValue;
    }

    public function getFileDownloadUrl($fileId) {
        return OW::getRouter()->urlForRoute('frmshasta_download_file', array('id' => $fileId));
    }

    /***
     * @param $file
     * @return string
     */
    public function getFileDirectory($file)
    {
        return OW::getPluginManager()->getPlugin('frmshasta')->getUserFilesDir() . $file;
    }

    public function onBeforeDocumentRenderer(OW_Event $event){
        $this->addStaticFiles();
    }

    public function addStaticFiles() {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmshasta')->getStaticJsUrl() . 'frmshasta.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmshasta')->getStaticCssUrl() . 'frmshasta.css');
        OW::getDocument()->addOnloadScript("categoryFilter = " . json_encode($this->getCategoryFilter())  . ";");
    }

    public function getCategoryFilter() {
        if (!OW::getUser()->isAuthenticated()) {
            return array();
        }
        $categories = $this->getAllCategories(OW::getUser()->getId());
        $categoriesInfo = array();
        foreach ($categories as $category) {
            $categoriesInfo[$category->id] = array(
                'month' => $category->monthFilter,
                'year' => $category->yearFilter,
            );
        }

        return $categoriesInfo;
    }

    public function updateFileAccess($selectedIds,$fileId)
    {

        if (!$this->hasUserAccessManager()) {
            throw new Redirect404Exception();
        }
        $fileInfo=$this->getFile($fileId);
        $allowedUsers = $this->findHierarchicValidAccessUserIds($fileInfo->userId);
        $accessGrantedUsers= $this->findUserIdsGrantedAccessToFile($fileId);

        foreach ($allowedUsers as $allowedUserId)
        {
            if($allowedUserId==OW::getUser()->getId())
            {
                continue;
            }
            if(!in_array($allowedUserId,$selectedIds) )
            {
                $this->userFilePermissionDao->UpdateUserAccessInfo($allowedUserId,$fileId,FRMSHASTA_BOL_UserFileAccessDao::ACCESS_DENIED);
            }else
            {
                $this->userFilePermissionDao->deleteUserAccessInfo($allowedUserId,$fileId);
            }
        }
        foreach ($accessGrantedUsers as $accessGrantedUserId)
        {
            if(!in_array($accessGrantedUserId,$selectedIds))
            {
                $this->userFilePermissionDao->deleteUserAccessInfo($accessGrantedUserId,$fileId);
            }
        }
        foreach ($selectedIds as $userId)
        {
            if(!in_array($userId,$allowedUsers))
            {
                $this->userFilePermissionDao->UpdateUserAccessInfo($userId,$fileId,FRMSHASTA_BOL_UserFileAccessDao::ACCESS_GRANTED);
            }
        }
    }

    public function updateCategoryAccess($selectedIds,$categoryId)
    {
        if (!$this->hasUserAccessManager()) {
            throw new Redirect404Exception();
        }

        $numberOfUsers = BOL_UserService::getInstance()->count(true);
        $users = BOL_UserService::getInstance()->findList(0, $numberOfUsers, true);

        $allowedUsers = array();
        foreach ($users as $user) {
            $allowedUsers[] = $user->getId();
        }

        $this->userCategoryPermissionDao->deleteUsersAccessInfo();

        foreach ($allowedUsers as $allowedUserId)
        {
            if($allowedUserId==OW::getUser()->getId())
            {
                continue;
            }
            if(!in_array($allowedUserId,$selectedIds) )
            {
                $this->userCategoryPermissionDao->UpdateUserAccessInfo($allowedUserId,$categoryId,FRMSHASTA_BOL_UserFileAccessDao::ACCESS_DENIED);
            }
        }
    }
}
