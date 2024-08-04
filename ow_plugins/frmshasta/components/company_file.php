<?php
/**
 * component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmshasta.classes
 * @since 1.0
 */
class FRMSHASTA_CMP_CompanyFile extends OW_Component
{
    /**
     * FRMSHASTA_CMP_CompanyFile constructor.
     * @param $params
     * @throws Redirect404Exception
     */
    public function __construct($params = array())
    {
        parent::__construct();

        $service = FRMSHASTA_BOL_Service::getInstance();

        $fileIds = array();
        if (isset($params['searchedCompaniesIds']) && isset($params['categoryId'])) {
            $files = $service->getFileBasedCategoryFiles($params['searchedCompaniesIds'], $params['categoryId'], $params['fromDate'], $params['toDate'], 0, 1000000, true);
            foreach ($files as $file) {
                $fileIds[] = $file->id;
            }
        }

        $this->addComponent('files', new FRMSHASTA_CMP_Files(array('fileIds' => $fileIds)));
        $companyId = $params['companyId'];
        $company = $service->getCompany($companyId);
        $companyData = $service->preparedCompanyItem($company);
        $this->assign('company', $companyData);

        if (isset($params['inactive']) && $params['inactive'] == true){
            $this->assign('inactive_class', 'inactive');
        } else {
            $this->assign('inactive_class', '');
        }
        FRMSHASTA_BOL_Service::getInstance()->addStaticFiles();
    }
}