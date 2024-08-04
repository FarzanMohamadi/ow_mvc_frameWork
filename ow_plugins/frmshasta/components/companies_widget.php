<?php
/**
 * FRMSHASTA widget
 *
 * @since 1.0
 */
class FRMSHASTA_CMP_CompaniesWidget extends BASE_CLASS_Widget
{

    /**
     * FRMSHASTA_CMP_CategoriesWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    private function assignList($params)
    {

        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }
        $service = FRMSHASTA_BOL_Service::getInstance();
        $company = $service->getUserCompany(OW::getUser()->getId());
        $companies = array(
            'html' => '',
        );
        if ($company != null) {
            $companies = $service->findChildCompanyObjectRecursiveData($company->id, '');
        }
        $this->assign('html', $companies['html']);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmshasta', 'companies'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}